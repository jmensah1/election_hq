---
trigger: always_on
---

# Elections HQ - Agent Guide

Allways refrence elections-hq-architecture.md for the architecture of the app.

**Purpose:** This document ensures the coding agent stays aligned with the architecture and doesn't hallucinate features or patterns that don't exist in the spec.

---

## Critical Architecture Constraints

### DO NOT DEVIATE FROM THESE RULES

#### 1. Vote Anonymity (NON-NEGOTIABLE)

The `votes` table must NEVER contain:
- ❌ `user_id`
- ❌ `voter_id`
- ❌ `created_at`
- ❌ `updated_at`
- ❌ `voted_at`
- ❌ Any timestamp field
- ❌ Any hash linking to user
- ❌ IP address
- ❌ Any identifying information

The `votes` table ONLY contains:
- ✅ `id` (auto-increment)
- ✅ `organization_id`
- ✅ `election_id`
- ✅ `position_id`
- ✅ `candidate_id`

**Why:** This prevents timing correlation attacks. Even a DBA with full access cannot link votes to voters.

#### 2. Two-Table Voting Model

Every vote submission creates TWO records:
1. `vote_confirmations` - WHO voted (has user_id, timestamps, IP)
2. `votes` - WHAT was voted (NO user link)

These tables have NO foreign key relationship to each other.

```php
// CORRECT pattern
DB::transaction(function () {
    VoteConfirmation::create([
        'user_id' => $user->id,        // YES - track who voted
        'voted_at' => now(),            // YES - track when
        'ip_address' => request()->ip() // YES - for audit
    ]);
    
    Vote::create([
        'candidate_id' => $candidateId  // NO user_id, NO timestamp
    ]);
});
```

#### 3. Multi-Tenancy via organization_id

Every table (except `users` and `organizations`) has `organization_id`:
- Elections, Positions, Candidates, Votes, VoteConfirmations, AuditLogs, Notifications

Use `BelongsToOrganization` trait with global scope filtering.

#### 4. Authentication Model

**Voters:** Google OAuth ONLY
- No passwords stored for voters
- Email must exist in `organization_user.allowed_email`
- `organization_user` is the "guest list"

**Admins:** Email/password via Filament
- Standard Laravel authentication
- Separate from voter flow

---

## Technology Stack (DO NOT CHANGE)

| Component | Technology | Notes |
|-----------|------------|-------|
| Framework | Laravel 12.x | Do not use older syntax |
| Database | MySQL 8.0 | InnoDB, utf8mb4 |
| Cache/Session/Queue | Redis | Single Redis instance |
| Admin Panel | Filament 3.x | Use Filament resources |
| Frontend | Livewire 3.x | For voter portal interactivity |
| CSS | Tailwind CSS 3.x | No Bootstrap, no custom CSS frameworks |
| JS | Alpine.js | Comes with Livewire, minimal custom JS |
| OAuth | Laravel Socialite | Google provider only for MVP |
| Mail | Laravel Mail | Mailgun or SMTP |

---

## Code Patterns to Follow

### Service Pattern
All business logic goes in Services, not Controllers.

```php
// CORRECT - Controller calls Service
class VotingController extends Controller
{
    public function store(VoteRequest $request, Election $election, VotingService $service)
    {
        $service->castVote($election, auth()->user(), $request->validated());
        return redirect()->route('voter.confirmation');
    }
}

// WRONG - Business logic in Controller
class VotingController extends Controller
{
    public function store(VoteRequest $request, Election $election)
    {
        DB::transaction(function () { /* voting logic here */ }); // NO!
    }
}
```

### Repository Pattern (Optional)
Use for complex queries, not required for simple CRUD.

### Global Scope for Multi-Tenancy

```php
// CORRECT - Use trait
class Election extends Model
{
    use BelongsToOrganization;
}

// The trait handles filtering automatically
$elections = Election::all(); // Already filtered by current org
```

### Form Requests for Validation

```php
// CORRECT - Dedicated FormRequest
class VoteRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'votes' => 'required|array',
            'votes.*' => 'required|integer|exists:candidates,id',
        ];
    }
}
```

### Policies for Authorization

```php
// CORRECT - Use Policy
class ElectionPolicy
{
    public function vote(User $user, Election $election): bool
    {
        // Authorization logic here
    }
}

// In Controller
$this->authorize('vote', $election);
```

---

## Features NOT in MVP (Do Not Implement)

The following are explicitly excluded from Phase 1:

- ❌ Write-in candidates
- ❌ Draft vote storage / auto-save
- ❌ Vote receipt codes / verification
- ❌ SMS notifications (email only for MVP)
- ❌ Real-time WebSocket results
- ❌ Multi-language support
- ❌ Payment integration
- ❌ API for third parties
- ❌ Mobile app / PWA
- ❌ Advanced fraud detection algorithms
- ❌ Session fingerprinting

If asked to implement any of these, respond: "This feature is deferred to Phase 2+. Focusing on MVP scope."

---

## Database Schema Reference

### Critical Tables

```sql
-- vote_confirmations: WHO voted
CREATE TABLE vote_confirmations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id BIGINT UNSIGNED NOT NULL,
    election_id BIGINT UNSIGNED NOT NULL,
    position_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    voted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    UNIQUE KEY unique_vote_check (election_id, position_id, user_id)
);

-- votes: WHAT was voted (ANONYMOUS)
CREATE TABLE votes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id BIGINT UNSIGNED NOT NULL,
    election_id BIGINT UNSIGNED NOT NULL,
    position_id BIGINT UNSIGNED NOT NULL,
    candidate_id BIGINT UNSIGNED NOT NULL
    -- NO user_id
    -- NO timestamps
);
```

### Election Status Enum

Valid statuses in order:
1. `draft` - Being created
2. `nomination` - Accepting nominations
3. `vetting` - Reviewing candidates
4. `voting` - Votes being cast
5. `completed` - Voting ended
6. `cancelled` - Election cancelled

Transitions must follow this order (no skipping, no going back except to cancelled).

### User Roles

```php
enum Role: string {
    case SUPER_ADMIN = 'super_admin';     // Platform-wide
    case ADMIN = 'admin';                  // Organization-wide
    case ELECTION_OFFICER = 'election_officer';
    case VOTER = 'voter';
}
```

---

## File Naming Conventions

| Type | Convention | Example |
|------|------------|---------|
| Model | Singular, PascalCase | `Election.php` |
| Controller | Singular + Controller | `VotingController.php` |
| Service | Singular + Service | `VotingService.php` |
| Migration | Plural table name | `create_elections_table.php` |
| Form Request | Action + Request | `StoreVoteRequest.php` |
| Policy | Model + Policy | `ElectionPolicy.php` |
| Job | Action description | `SendVoteConfirmation.php` |
| Livewire | Descriptive | `VotingBooth.php` |

---

## Route Structure

```php
// Public (no auth)
Route::get('/', [HomeController::class, 'index']);
Route::get('/auth/google', [GoogleAuthController::class, 'redirect']);
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback']);

// Voter (auth required, voter role)
Route::middleware(['auth', 'voter'])->prefix('vote')->group(function () {
    Route::get('/elections', [VotingController::class, 'index']);
    Route::get('/elections/{election}', [VotingController::class, 'show']);
    Route::post('/elections/{election}', [VotingController::class, 'store']);
    Route::get('/confirmation', [VotingController::class, 'confirmation']);
    Route::get('/results/{election}', [ResultsController::class, 'show']);
});

// Admin (Filament handles its own routes at /admin)
```

---

## Error Handling

### Custom Exceptions to Create

```php
// app/Exceptions/
AlreadyVotedException::class      // User tried to vote twice
IneligibleVoterException::class   // User not on voter list
ElectionNotOpenException::class   // Voting window closed
InvalidCandidateException::class  // Candidate doesn't belong to position
```

### Error Response Format

```php
// For API/AJAX responses
return response()->json([
    'success' => false,
    'message' => 'You have already voted for this position.',
    'error_code' => 'ALREADY_VOTED'
], 422);

// For web responses
return back()->withErrors(['vote' => 'You have already voted.']);
```

---

## Testing Requirements

### Must Have Tests

1. **VotingService::castVote**
   - Success case
   - Double-vote prevention
   - Transaction rollback on failure

2. **GoogleAuthService::handleLogin**
   - User on guest list → success
   - User not on guest list → rejection
   - First login creates user record
   - Repeat login updates user

3. **Multi-tenancy isolation**
   - Org A cannot see Org B elections
   - Global scope filters correctly

### Test Database

Use SQLite in-memory for speed:
```php
// phpunit.xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

---

## Common Mistakes to Avoid

### ❌ WRONG: Adding timestamps to votes table
```php
// NEVER DO THIS
Vote::create([
    'candidate_id' => $id,
    'created_at' => now()  // WRONG!
]);
```

### ❌ WRONG: Linking votes to users
```php
// NEVER DO THIS
Vote::create([
    'user_id' => auth()->id(),  // WRONG!
    'candidate_id' => $id
]);
```

### ❌ WRONG: Checking vote status from votes table
```php
// WRONG - votes table has no user_id
$hasVoted = Vote::where('user_id', $user->id)->exists();

// CORRECT - use vote_confirmations
$hasVoted = VoteConfirmation::where('user_id', $user->id)
    ->where('election_id', $election->id)
    ->exists();
```

### ❌ WRONG: Forgetting organization scope
```php
// WRONG - returns ALL elections
$elections = Election::where('status', 'voting')->get();

// CORRECT - BelongsToOrganization trait auto-filters
// Just make sure middleware sets current organization first
$elections = Election::where('status', 'voting')->get();
```

### ❌ WRONG: Business logic in controllers
```php
// WRONG
public function store(Request $request)
{
    DB::transaction(function () {
        // 50 lines of voting logic
    });
}

// CORRECT
public function store(VoteRequest $request, VotingService $service)
{
    $service->castVote(...);
}
```

---

## Quick Answers for Common Questions

**Q: Should I use UUIDs?**
A: No. Use auto-increment BIGINT for all IDs. Simpler and faster.

**Q: Should I encrypt votes?**
A: No. The two-table separation provides anonymity. Encryption adds complexity without benefit.

**Q: Should I add soft deletes?**
A: Only on `candidates` (for withdrawn candidates). Not on votes or vote_confirmations.

**Q: Should I use Laravel Passport/Sanctum for API?**
A: No API in MVP. Session-based auth only.

**Q: Should I add WebSocket for real-time results?**
A: No. Simple page refresh or polling. WebSockets are Phase 3.

**Q: Can voters see who else has voted?**
A: No. Only admins can see participation stats. Voters only see their own status.

**Q: Should I implement email verification?**
A: No. Google OAuth handles identity verification.

---

## When Stuck

1. Re-read the architecture document (`elections-hq-architecture-v3.md`)
2. Check this guide for constraints
3. Prioritize simplicity over cleverness
4. Ask: "Is this needed for MVP?"

**Default answer for scope questions:** If it's not in Phase 1 of the architecture doc, don't build it.
