# Elections HQ - System Architecture Document
**Version:** 3.0  
**Date:** January 27, 2026  
**Tech Stack:** Laravel 11.x + MySQL 8.0 + VPS (Hostinger KVM 2)

---

## Changelog

### Version 3.0 (January 27, 2026)
**Major Security & Architecture Updates:**

1. **Enhanced Vote Anonymity**
   - Replaced `voter_records` with `vote_confirmations` table
   - Removed ALL timestamps from `votes` table to prevent timing correlation attacks
   - Implemented truly anonymous ballot storage - even database administrators cannot link votes to voters
   - Separated WHO voted (vote_confirmations) from WHAT was voted (votes) with zero correlation

2. **Refined Security Measures**
   - Removed session fingerprinting (causes issues for legitimate mobile users)
   - Simplified double-voting prevention to use database constraints instead of complex locking
   - Added OAuth callback rate limiting
   - Implemented file storage abstraction for future S3 migration
   - Added organization timezone middleware for accurate voting windows

3. **Simplified Backup & Monitoring**
   - Replaced complex backup verification with simple file existence checks (MVP appropriate)
   - Removed application-level connection pool monitoring in favor of external tools
   - Added straightforward backup verification script

4. **Removed Features for MVP**
   - Removed write-in candidate support (adds unnecessary complexity)
   - Deferred draft vote storage to Phase 2
   - Deferred vote receipt/verification codes to Phase 2
   - Removed session fingerprinting

5. **Phase Planning Updates**
   - Phase 2: Draft vote storage, receipt codes, full backup testing
   - Phase 3: Connection monitoring, advanced features
   - Clear separation of MVP vs. future features

---

## 1. Executive Summary

Elections HQ is a comprehensive election management system designed to handle the complete electoral process from nomination through result declaration. Built with multi-tenancy in mind, the system can serve multiple organizations (schools, associations, clubs) simultaneously while maintaining complete data isolation and security.

**Key Features:**
- Multi-tenant architecture (one installation, multiple organizations)
- Complete election lifecycle management
- Role-based access control (RBAC)
- Real-time vote counting and results
- Audit trail for all activities
- Mobile-responsive interface

**Key Changes in v3.0:**
- **Authentication:** Google OAuth login (supports Workspace, Gmail, Outlook, etc.) guarded by the uploaded allow-list.
- **Anonymity:** Revolutionary two-table design with zero correlation - votes cannot be traced back to users even by database administrators. The `votes` table has NO user_id and NO timestamps to prevent any correlation attacks.
- **Simplicity:** Removed complex features not needed for MVP (write-in candidates, draft storage, session fingerprinting) to focus on core voting functionality.
- **Infrastructure:** 8GB RAM nodes to handle concurrent traffic spikes.
- **Universal Compatibility:** Voter IDs are uploaded by admins and mapped to emails, not extracted. This supports any ID format (Student ID, Employee ID, Membership #).

---

## 2. System Architecture Overview

### 2.1 High-Level Architecture

```
    ┌─────────────────────────────────────────────────────────────┐
    │                        PRESENTATION LAYER                   │
    │  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐       │
    │  │   Web UI     │  │  Admin Panel │  │ Voter Portal │       │
    │  │  (Blade/Vue) │  │   (Blade)    │  │ (Google SSO) │       │
    │  └──────────────┘  └──────────────┘  └──────────────┘       │
    └─────────────────────────────────────────────────────────────┘
                              ↓
    ┌─────────────────────────────────────────────────────────────┐
    │                      APPLICATION LAYER                      │
    │  ┌────────────────────────────────────────────────────────┐ │
    │  │              Laravel Application (API + MVC)           │ │
    │  │                                                        │ │
    │  │   Socialite (Google) → Services → Repositories         │ │
    │  └────────────────────────────────────────────────────────┘ │
    │                                                             │
    │   ┌─────────────┐  ┌─────────────┐  ┌─────────────┐         │
    │   │   Queue     │  │    Cache    │  │   Session   │         │
    │   │  (Redis)    │  │   (Redis)   │  │   (Redis)   │         │
    │   └─────────────┘  └─────────────┘  └─────────────┘         │
    └─────────────────────────────────────────────────────────────┘
                                  ↓
    ┌─────────────────────────────────────────────────────────────┐
    │                        DATA LAYER                           │
    │  ┌────────────────────────────────────────────────────────┐ │
    │  │                    MySQL Database                      │ │
    │  │     (Transactions: READ COMMITTED Isolation Level)     │ │
    │  └────────────────────────────────────────────────────────┘ │
    │                                                             │
    │  ┌────────────────────────────────────────────────────────┐ │
    │  │                 File Storage (Local)                   │ │
    │  │            (Candidate photos, documents)               │ │
    │  └────────────────────────────────────────────────────────┘ │
    └─────────────────────────────────────────────────────────────┘
```      
### 2.2 Multi-Tenancy Strategy

**Approach:** Single Database with Tenant Isolation (using `organization_id` as discriminator)

**Why this approach:**
- Simpler to manage and backup
- Cost-effective for VPS hosting
- Easier to implement with Laravel's global scopes
- Sufficient for projected scale (100s of organizations)

**Implementation:**
- Every table (except `organizations` and `users`) has `organization_id` foreign key
- Laravel Global Scope automatically filters queries by current organization
- Middleware sets current organization context from subdomain/domain
- Complete data isolation enforced at application level

---

## 3. Database Schema Design

### 3.1 Core Tables

#### organizations
Stores tenant information - each organization is a separate election entity.

```sql
CREATE TABLE organizations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    subdomain VARCHAR(255) UNIQUE NULL,
    custom_domain VARCHAR(255) UNIQUE NULL,
    logo_path VARCHAR(255) NULL,
    timezone VARCHAR(50) DEFAULT 'UTC',
    status ENUM('active', 'suspended', 'inactive') DEFAULT 'active',
    
    -- Subscription
    subscription_plan ENUM('free', 'basic', 'premium', 'enterprise') DEFAULT 'free',
    subscription_expires_at TIMESTAMP NULL,
    
    -- Features
    sms_enabled BOOLEAN DEFAULT FALSE,
    sms_sender_id VARCHAR(11) NULL,
    max_voters INT DEFAULT 100,
    
    -- Settings
    settings JSON NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_slug (slug),
    INDEX idx_subdomain (subdomain),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### users
Universal user table - users can belong to multiple organizations with different roles.

```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    google_id VARCHAR(255) UNIQUE NULL, -- Google unique identifier
    avatar VARCHAR(255) NULL,           -- Google profile picture
    password VARCHAR(255) NULL,         -- Nullable (not used for voters)
    is_super_admin BOOLEAN DEFAULT FALSE,
    remember_token VARCHAR(100) NULL,
    last_login_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_google_id (google_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### organization_user (Pivot Table)
Maps users to organizations with specific roles.

```sql
CREATE TABLE organization_user (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NULL, -- Nullable! (Only linked AFTER first login)
    
    -- Admin Uploaded Data (The "Guest List")
    voter_id VARCHAR(50) NOT NULL,       -- e.g. "10280238", "EMP-005", "MEM-99"
    allowed_email VARCHAR(255) NOT NULL, -- e.g. "student@upsamail.edu.gh", "john@gmail.com"
    
    role ENUM('admin', 'election_officer', 'voter') DEFAULT 'voter',
    status ENUM('pending', 'active', 'suspended') DEFAULT 'pending', -- 'pending' until first login
    can_vote BOOLEAN DEFAULT TRUE,
    
    department VARCHAR(100) NULL, -- Optional metadata
    
    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_org_voter (organization_id, voter_id),
    UNIQUE KEY unique_org_email (organization_id, allowed_email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### elections
Each organization can have multiple elections.

```sql
CREATE TABLE elections (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    slug VARCHAR(255) NOT NULL,
    
    -- Lifecycle dates
    nomination_start_date DATETIME NOT NULL,
    nomination_end_date DATETIME NOT NULL,
    vetting_start_date DATETIME NOT NULL,
    vetting_end_date DATETIME NOT NULL,
    voting_start_date DATETIME NOT NULL,
    voting_end_date DATETIME NOT NULL,
    
    -- Status tracking
    status ENUM('draft', 'nomination', 'vetting', 'voting', 'completed', 'cancelled') DEFAULT 'draft',
    
    -- Settings
    require_photo BOOLEAN DEFAULT TRUE,
    max_votes_per_position INT DEFAULT 1,
    voter_eligibility_rules JSON NULL,
    
    -- Results
    results_published BOOLEAN DEFAULT FALSE,
    results_published_at TIMESTAMP NULL,
    
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id),
    UNIQUE KEY unique_org_slug (organization_id, slug),
    INDEX idx_organization (organization_id),
    INDEX idx_status (status),
    INDEX idx_dates (voting_start_date, voting_end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### positions
Electoral positions within an election (e.g., President, Secretary, etc.).

```sql
CREATE TABLE positions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id BIGINT UNSIGNED NOT NULL,
    election_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    display_order INT DEFAULT 0,
    max_candidates INT DEFAULT 10,
    max_votes INT DEFAULT 1,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
    FOREIGN KEY (election_id) REFERENCES elections(id) ON DELETE CASCADE,
    INDEX idx_organization (organization_id),
    INDEX idx_election (election_id),
    INDEX idx_order (election_id, display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### candidates
Candidates nominated for positions.

```sql
CREATE TABLE candidates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id BIGINT UNSIGNED NOT NULL,
    election_id BIGINT UNSIGNED NOT NULL,
    position_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    
    -- Candidate info
    candidate_number VARCHAR(20) NULL,
    manifesto TEXT NULL,
    photo_path VARCHAR(255) NULL,
    
    -- Nomination
    nomination_status ENUM('pending', 'approved', 'rejected', 'withdrawn') DEFAULT 'pending',
    nominated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    nominated_by BIGINT UNSIGNED NULL,
    
    -- Vetting
    vetting_status ENUM('pending', 'passed', 'failed', 'disqualified') DEFAULT 'pending',
    vetting_notes TEXT NULL,
    vetted_at TIMESTAMP NULL,
    vetted_by BIGINT UNSIGNED NULL,
    
    -- Results
    vote_count INT DEFAULT 0,
    is_winner BOOLEAN DEFAULT FALSE,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
    FOREIGN KEY (election_id) REFERENCES elections(id) ON DELETE CASCADE,
    FOREIGN KEY (position_id) REFERENCES positions(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (nominated_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (vetted_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_candidate (election_id, position_id, user_id),
    INDEX idx_organization (organization_id),
    INDEX idx_election (election_id),
    INDEX idx_position (position_id),
    INDEX idx_user (user_id),
    INDEX idx_nomination_status (nomination_status),
    INDEX idx_vetting_status (vetting_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### vote_confirmations
Tracks WHO voted for each position (for double-vote prevention and audit trail). Completely separate from actual votes.

**CRITICAL SECURITY DESIGN:**
This table tracks participation and prevents double-voting, but is completely decoupled from the actual votes cast. Timestamps are kept here for audit purposes and user experience ("You voted at 10:23 AM").

```sql
CREATE TABLE vote_confirmations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id BIGINT UNSIGNED NOT NULL,
    election_id BIGINT UNSIGNED NOT NULL,
    position_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    voted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Security Audit
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    
    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
    FOREIGN KEY (election_id) REFERENCES elections(id) ON DELETE CASCADE,
    FOREIGN KEY (position_id) REFERENCES positions(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    -- Prevents double-voting at database level
    UNIQUE KEY unique_vote_check (election_id, position_id, user_id),
    
    INDEX idx_organization (organization_id),
    INDEX idx_election (election_id),
    INDEX idx_user_election (user_id, election_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### votes
Stores WHAT was voted (anonymous ballot box). NO user_id, NO timestamp to prevent correlation attacks.

**CRITICAL SECURITY DESIGN:**
- The `votes` table intentionally omits `user_id` and any timestamp fields
- This prevents timing correlation attacks where a malicious database administrator could match votes to voters based on insertion order or timestamp proximity
- Even with full database access, votes cannot be traced back to individual users
- Vote counting and results work perfectly without these fields
- This is a deliberate security trade-off: we sacrifice vote timing analytics for voter anonymity

```sql
CREATE TABLE votes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id BIGINT UNSIGNED NOT NULL,
    election_id BIGINT UNSIGNED NOT NULL,
    position_id BIGINT UNSIGNED NOT NULL,
    candidate_id BIGINT UNSIGNED NOT NULL,
    
    -- ABSOLUTELY NO user_id, voter_hash, ip_address, or timestamps here
    -- NO created_at timestamp - prevents timing correlation attacks
    
    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
    FOREIGN KEY (election_id) REFERENCES elections(id) ON DELETE CASCADE,
    FOREIGN KEY (position_id) REFERENCES positions(id) ON DELETE CASCADE,
    FOREIGN KEY (candidate_id) REFERENCES candidates(id) ON DELETE CASCADE,
    
    INDEX idx_organization (organization_id),
    INDEX idx_election (election_id),
    INDEX idx_election_position (election_id, position_id),
    INDEX idx_candidate (candidate_id),
    INDEX idx_results (election_id, candidate_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```


#### notifications
Tracks all email and SMS notifications sent by the system.

```sql
CREATE TABLE notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    election_id BIGINT UNSIGNED NULL,
    
    -- Notification details
    type ENUM('email', 'sms') NOT NULL,
    category ENUM('vote_confirmation', 'election_reminder', 'results', 'admin_alert') NOT NULL,
    
    -- Recipient
    recipient VARCHAR(255) NOT NULL COMMENT 'Email address or phone number',
    
    -- Content
    subject VARCHAR(255) NULL,
    message TEXT NOT NULL,
    
    -- Status tracking
    status ENUM('pending', 'sent', 'failed', 'bounced') DEFAULT 'pending',
    sent_at TIMESTAMP NULL,
    error_message TEXT NULL,
    
    -- Cost tracking (for SMS)
    cost_amount DECIMAL(10, 4) NULL COMMENT 'Cost in USD',
    
    -- Metadata
    metadata JSON NULL COMMENT 'Additional context (provider, message_id, etc.)',
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (election_id) REFERENCES elections(id) ON DELETE SET NULL,
    INDEX idx_organization (organization_id),
    INDEX idx_user (user_id),
    INDEX idx_election (election_id),
    INDEX idx_type_category (type, category),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### audit_logs
Complete audit trail of all system activities.

```sql
CREATE TABLE audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id BIGINT UNSIGNED NULL,
    user_id BIGINT UNSIGNED NULL,
    
    -- Activity details
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id BIGINT UNSIGNED NULL,
    
    -- Changes
    old_values JSON NULL,
    new_values JSON NULL,
    
    -- Context
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_organization (organization_id),
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 3.2 Critical Database Constraints

**Transaction Requirements:**
- Vote casting must be atomic (voter_record + votes for all positions)
- Use database transactions with proper isolation level (READ COMMITTED)
- Implement row-level locking with `SELECT ... FOR UPDATE`

**Prevent Double Voting:**
```sql
-- Unique constraint prevents duplicate entries
UNIQUE KEY unique_voter_election (election_id, user_id) in voter_records

-- Application-level check before vote
BEGIN TRANSACTION;
SELECT id FROM voter_records 
WHERE election_id = ? AND user_id = ? 
FOR UPDATE;  -- Locks the row

-- If no record exists, proceed with voting
-- If record exists, reject the vote

COMMIT;
```

---

## 4. Application Layer Architecture

### 4.1 Laravel Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/
│   │   │   ├── DashboardController.php
│   │   │   ├── ElectionController.php
│   │   │   ├── UserManagementController.php
│   │   │   └── ReportsController.php
│   │   ├── Voter/
│   │   │   ├── VotingController.php
│   │   │   ├── CandidateController.php
│   │   │   └── ResultsController.php
│   │   ├── Auth/
│   │   │   ├── (Laravel Breeze/Fortify)
|   |   |   ├── GoogleAuthController.php 
│   │   │   └── LoginController.php
│   │   └── API/
│   │       └── V1/
│   ├── Middleware/
│   │   ├── SetOrganizationContext.php
│   │   ├── CheckVoterEligibility.php
│   │   ├── CheckElectionStatus.php
│   │   └── PreventDoubleVoting.php
│   └── Requests/
│       ├── NominationRequest.php
│       ├── VoteRequest.php
│       └── VettingRequest.php
├── Models/
│   ├── Organization.php
│   ├── User.php
│   ├── Election.php
│   ├── Position.php
│   ├── Candidate.php
│   ├── Vote.php
│   ├── VoteConfirmation.php
│   └── AuditLog.php
├── Services/
│   ├── ElectionService.php
│   ├── VotingService.php
│   ├── NominationService.php
│   ├── VettingService.php
│   ├── ResultsService.php
│   ├── GoogleAuthService.php
│   └── AuditService.php
├── Repositories/
│   ├── ElectionRepository.php
│   ├── VoteRepository.php
│   └── CandidateRepository.php
├── Traits/
│   ├── BelongsToOrganization.php
│   └── AuditableActivity.php
├── Events/
│   ├── VoteCast.php
│   ├── NominationSubmitted.php
│   ├── CandidateVetted.php
│   └── ResultsPublished.php
├── Listeners/
│   ├── SendVoteConfirmation.php
│   ├── UpdateVoteCount.php
│   └── NotifyAdmins.php
└── Jobs/
    ├── ProcessVote.php
    ├── GenerateElectionReport.php
    └── SendBulkNotifications.php
```

### 4.2 Key Design Patterns

#### Service Pattern
Business logic is encapsulated in service classes.

```php
// app/Services/GoogleAuthService.php
class GoogleAuthService
{
    public function handleLogin($googleUser)
    {
        $email = $googleUser->email;
        $currentOrgId = current_organization_id(); // Resolved from domain/subdomain
        
        // 1. Check the Guest List (The Guard)
        // "Is this email allowed in this organization?"
        $membership = OrganizationUser::where('organization_id', $currentOrgId)
            ->where('allowed_email', $email)
            ->first();
            
        if (!$membership) {
            abort(403, "Access Denied: You are not on the voter list for this organization.");
        }
        
        // 2. Create/Update the Global User Account
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $googleUser->name,
                'google_id' => $googleUser->id,
                'avatar' => $googleUser->avatar,
                'password' => null
            ]
        );
        
        // 3. Link the User to the Membership (if not already linked)
        if (!$membership->user_id) {
            $membership->update([
                'user_id' => $user->id,
                'status' => 'active'
            ]);
        }
        
        return $user;
    }
}
```

```php
// app/Services/VotingService.php
class VotingService
{
    public function castVote(Election $election, User $user, array $ballot): bool
    {
        // 1. Eligibility Check
        if (!$this->isEligibleToVote($election, $user)) {
             throw new IneligibleVoterException();
        }

        // 2. Atomic Transaction - Inserts into TWO separate tables with NO correlation
        return DB::transaction(function () use ($election, $user, $ballot) {
            
            foreach ($ballot as $positionId => $candidateId) {
                
                // A. Record WHO voted for this position (double-vote prevention)
                // This will throw exception if duplicate due to unique constraint
                try {
                    VoteConfirmation::create([
                        'organization_id' => $election->organization_id,
                        'election_id' => $election->id,
                        'position_id' => $positionId,
                        'user_id' => $user->id,
                        'ip_address' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                        // voted_at auto-populated
                    ]);
                } catch (\Illuminate\Database\QueryException $e) {
                    // Unique constraint violation (23000 = duplicate entry)
                    if ($e->getCode() == 23000) {
                        throw new AlreadyVotedException("You have already voted for this position.");
                    }
                    throw $e;
                }

                // B. Record WHAT was voted (completely anonymous)
                // CRITICAL: NO user_id, NO timestamp - prevents any correlation
                Vote::create([
                    'organization_id' => $election->organization_id,
                    'election_id' => $election->id,
                    'position_id' => $positionId,
                    'candidate_id' => $candidateId,
                ]);
            }
            
            // C. Queue confirmation email (async)
            dispatch(new SendVoteConfirmation($user, $election));
            
            // D. Log audit trail
            AuditLog::create([
                'organization_id' => $election->organization_id,
                'user_id' => $user->id,
                'action' => 'vote_cast',
                'auditable_type' => Election::class,
                'auditable_id' => $election->id,
                'metadata' => ['positions_count' => count($ballot)],
            ]);
            
            return true;
        });
    }
    
    /**
     * Check if user has already voted for a position
     */
    public function hasVotedForPosition(Election $election, Position $position, User $user): bool
    {
        return VoteConfirmation::where('election_id', $election->id)
            ->where('position_id', $position->id)
            ->where('user_id', $user->id)
            ->exists();
    }
}
```

#### Repository Pattern
Database queries abstracted into repositories.

```php
// app/Repositories/VoteRepository.php
class VoteRepository
{
    public function getVoteCountsByCandidate(Election $election): Collection
    {
        return Vote::where('election_id', $election->id)
            ->select('candidate_id', DB::raw('COUNT(*) as vote_count'))
            ->groupBy('candidate_id')
            ->get();
    }
    
    public function getVotingProgress(Election $election): array
    {
        $totalVoters = $election->organization
            ->users()
            ->where('can_vote', true)
            ->count();
            
        // Count distinct users who have voted (from vote_confirmations)
        $votedCount = VoteConfirmation::where('election_id', $election->id)
            ->distinct('user_id')
            ->count('user_id');
            
        return [
            'total_voters' => $totalVoters,
            'voted' => $votedCount,
            'remaining' => $totalVoters - $votedCount,
            'percentage' => $totalVoters > 0 ? ($votedCount / $totalVoters) * 100 : 0,
        ];
    }
    
    public function getVotingActivityByHour(Election $election): Collection
    {
        // Can analyze voting patterns from vote_confirmations (which has timestamps)
        // but cannot correlate with actual votes cast
        return VoteConfirmation::where('election_id', $election->id)
            ->selectRaw('HOUR(voted_at) as hour, COUNT(DISTINCT user_id) as voters')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();
    }
}
```

#### Multi-Tenancy with Global Scopes

```php
// app/Traits/BelongsToOrganization.php
trait BelongsToOrganization
{
    protected static function bootBelongsToOrganization()
    {
        // Automatically set organization_id on create
        static::creating(function ($model) {
            if (empty($model->organization_id)) {
                $model->organization_id = auth()->user()->currentOrganization->id;
            }
        });
        
        // Add global scope to filter by organization
        static::addGlobalScope('organization', function (Builder $builder) {
            if (auth()->check() && auth()->user()->currentOrganization) {
                $builder->where('organization_id', auth()->user()->currentOrganization->id);
            }
        });
    }
    
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}

// Usage in models
class Election extends Model
{
    use BelongsToOrganization;
}
```

---

## 5. Security Architecture

### 5.1 Authentication Strategy (Universal Google Auth)
- Provider: Google Workspace (Socialite).
- Flexibility: Accepts ANY email domain (Gmail, Yahoo, Outlook, Corporate).
- Security: The system only allows login if the email matches an entry in the pre-uploaded organization_user table.
- The system uses **two different authentication methods** depending on the user type:

#### Admin/Staff Authentication (Traditional)
- Email/password login (Laravel Breeze/Fortify)
- Session-based with Redis
- Full account management

### Admin Workflow
- Admin exports voter list from their system (Excel/CSV).
- Columns: Voter ID | Email Address.
- Admin uploads CSV to Elections HQ.
- System populates organization_user table.
- Voters can now log in.

**Why Passwordless for Voters?**
- Better user experience (no password to remember)
- Higher participation rates
- More secure (no weak passwords)
- Perfect for one-time voting events
- Voters don't need full accounts

### 5.2 Voter ID Format (Flexible)

The `voter_id` field accepts **any format** defined by the organization:

**Examples:**

| Organization Type | voter_id Format | Example |
|-------------------|-----------------|---------|
| University | Student ID | `PS/IT/23/0123`, `10318690`, `STU-12345` |
| Company | Employee ID | `EMP-2024-456`, `STAFF001`, `HR-789` |
| Association | Membership Number | `MEM-2024-001`, `A12345` |
| Community | Phone Number | `0241234567`, `+233241234567` |
| Club | Custom Format | `CLUB-MEMBER-123`, `john.doe` |

**Technical Constraints:**
- Stored as `VARCHAR(50)` - supports any alphanumeric format
- Must be unique within the organization
- Case-sensitive by default (can be made case-insensitive)
- No special validation - organization defines the format

**Implementation in Admin Panel:**
- Admin imports voters with their voter_id
- System validates uniqueness within organization
- Voters use this ID to initiate login

### 5.3 Voter Authentication Flow

```
┌─────────────────────────────────────────────────────────────┐
│              Voter Authentication Flow                       │
└─────────────────────────────────────────────────────────────┘

1. Voter visits: elections-hq.com/organization
   ↓
2. Clicks: "Sign in with Google"
   ↓
3. Google Auth Window opens
   - Voter selects "john.doe@gmail.com" (or any email)
   ↓
4. System Guard Check (Backend):
   - Look up "john.doe@gmail.com" in `organization_user` for "My Club"
     → Found? YES. Linked Voter ID: "MEM-2024-05"
     → Not Found? NO. Error: "You are not a registered voter."
   ↓
5. Success!
   - User is logged in.
   - Session matches Voter ID "MEM-2024-05".
   - Redirect to Voting Booth.
   ↓
6. After voting:
   ✓ Session terminated immediately
   ✓ Voter ID marked as "voted"
   ✓ Cannot login again for this election
```
```
- Email is Informational Only (non-critical).
- Usage: Vote Receipts ("Your vote was counted").
- Provider: Mailgun (API driver) or Postmark.
- Queue Priority: Low (Voting traffic always takes precedence).
```
### 5.4 Authorization Levels

| Role | Permissions |
|------|-------------|
| Super Admin | Platform-wide access, manage all organizations |
| Admin | Full control within organization, manage elections |
| Election Officer | Create/manage elections, view reports |
| Vetting Officer | Review nominations, approve/reject candidates |
| Observer | Read-only access to election data and reports |

### 5.5 Implementation

#### Policy Example

```php
// app/Policies/ElectionPolicy.php
class ElectionPolicy
{
    public function vote(User $user, Election $election): bool
    {
        // Check organization membership
        $membership = $user->organizations()
            ->where('organization_id', $election->organization_id)
            ->first();
            
        if (!$membership) return false;
        
        // Check voting eligibility
        if (!$membership->pivot->can_vote) return false;
        
        // Check election status
        if ($election->status !== 'voting') return false;
        
        // Check voting window
        if (!$election->isVotingOpen()) return false;
        
        // Check if already voted
        if ($election->hasUserVoted($user)) return false;
        
        return true;
    }
}
```

### 5.6 Notification System

The system uses a **dual-channel notification approach**:

#### Email Notifications (Primary - FREE)
Used for all critical communications:
- **Election reminders**
- **Results announcements**
- **Admin alerts**


**Email Configuration:**
```env

# Alternative: SMTP (Hostinger)
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=587
MAIL_USERNAME=noreply@elections-hq.com
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
```

#### SMS Notifications (Optional - Value-Add Feature)
Used for high-value notifications (not authentication):
- **Vote confirmations** ("Your vote has been recorded")
- **Election reminders** ("Voting closes in 1 hour")
- **Results alerts** ("Results are now available")
- **Admin alerts** (critical issues)

**SMS Providers (Africa-friendly):**

| Provider | Cost per SMS (Ghana) | Best For |
|----------|---------------------|----------|
| **Hubtel** 🇬🇭 | ~$0.02 | Ghana specifically, local support |
| **Africa's Talking** | ~$0.025 | Pan-African, reliable |
| **Termii** | ~$0.025 | West Africa focus |
| **Twilio** | ~$0.04 | Global, most reliable |

**Recommended: Hubtel or Africa's Talking** - Local presence, competitive pricing

**SMS Configuration:**
```env
SMS_PROVIDER=africastalking  # or hubtel
SMS_API_KEY=your_api_key
SMS_USERNAME=your_username
SMS_SENDER_ID=ElectionsHQ
SMS_ENABLED=false  # Enable per organization
```

#### Notification Service Implementation

```php
// app/Services/NotificationService.php
class NotificationService
{    
    // Send Vote Confirmation (Email + Optional SMS)
    public function sendVoteConfirmation(User $user, Election $election): void
    {
        // Always send email
        Mail::to($user->email)->send(new VoteConfirmationMail($election));
        
        // Send SMS if enabled for this organization
        if ($election->organization->sms_enabled && $user->phone) {
            $message = "Your vote in {$election->title} has been successfully recorded. Thank you!";
            $this->sendSMS($user, $election, $message, 'vote_confirmation');
        }
    }
    
    // Bulk Election Reminders
    public function sendElectionReminder(Election $election, string $message): void
    {
        $voters = $election->getEligibleVotersWhoHaventVoted();
        
        foreach ($voters as $voter) {
            // Email (always)
            Mail::to($voter->email)
                ->queue(new ElectionReminderMail($election, $message));
            
            // SMS (if enabled)
            if ($election->organization->sms_enabled && $voter->phone) {
                dispatch(new SendSMSJob([
                    'phone' => $voter->phone,
                    'message' => $message,
                    'organization_id' => $election->organization_id,
                    'category' => 'reminder',
                ]));
            }
        }
    }
    
    private function sendSMS(User $user, Election $election, string $message, string $category): void
    {
        // Queue SMS for background processing
        dispatch(new SendSMSJob([
            'organization_id' => $election->organization_id,
            'user_id' => $user->id,
            'election_id' => $election->id,
            'phone' => $user->phone,
            'message' => $message,
            'category' => $category,
        ]));
    }
}
```

#### Cost Analysis

**Email Costs: $0** (FREE)
- Mailgun free tier: 5,000 emails/month for 3 months
- Ongoing: 1,000 emails/month free forever
- Or use Hostinger email (included with hosting)

**SMS Costs: Optional**
- **UPSA (500 voters):**
  - Vote confirmations: 500 × $0.025 = $12.50
  - Election reminders: 500 × $0.025 = $12.50
  - Results: 500 × $0.025 = $12.50
  - **Total: ~$37.50 per election**

- **Larger Election (5,000 voters):**
  - 3 SMS per voter × 5,000 = 15,000 SMS
  - 15,000 × $0.025 = **$375**

**Monetization Strategy:**
- **Free/Basic Plan**: Email only
- **Premium Plan (+$50)**: Email + SMS notifications
- **Enterprise**: Full SMS integration + custom sender ID

### 5.7 Vote Anonymity & Integrity (Enhanced Security Model)

**CRITICAL: Decoupled Two-Table Architecture**

The system uses a revolutionary approach where voting participation and actual votes are stored in completely separate tables with NO correlation mechanism. This prevents even database administrators with full access from linking votes to voters.

#### The Two-Table Model

**Table 1: `vote_confirmations` - WHO voted**
- Contains: user_id, position_id, voted_at timestamp
- Purpose: Double-vote prevention, audit trail, participation tracking
- Visibility: Shows that "Alice voted for President at 10:23 AM"

**Table 2: `votes` - WHAT was voted**
- Contains: ONLY position_id and candidate_id
- NO user_id, NO timestamps, NO identifying information
- Purpose: Anonymous ballot storage, vote counting
- Visibility: Shows "One vote for Candidate X for President position"

**Why This Works:**

```
vote_confirmations table:          votes table:
┌─────────┬──────────┬──────────┐  ┌──────────┬──────────┐
│ user_id │ position │ voted_at │  │ position │candidate │
├─────────┼──────────┼──────────┤  ├──────────┼──────────┤
│   101   │    1     │ 10:23:45 │  │    1     │    5     │
│   102   │    1     │ 10:24:12 │  │    1     │    7     │
│   103   │    1     │ 10:25:03 │  │    1     │    5     │
└─────────┴──────────┴──────────┘  └──────────┴──────────┘

NO foreign key between tables
NO timestamp in votes table (prevents correlation)
NO hash or token linking them together
```

**Attack Scenario - All Defeated:**

❌ **Timing Correlation:** "Alice voted at 10:23:45, there's a vote at 10:23:45, must be hers!"
→ Defeated: votes table has NO timestamp

❌ **Sequential ID Correlation:** "Both records have ID #1001, must match!"
→ Defeated: Records inserted in same transaction but no provable link

❌ **Single Voter in Window:** "Only one person voted in this hour, must be theirs!"
→ Defeated: Without timestamps in votes table, impossible to determine "windows"

❌ **Database Dump Analysis:** "Let me export both tables and correlate..."
→ Defeated: No shared fields, timestamps, or hashes to correlate

**Even a malicious DBA with:**
- Full database access
- Root MySQL credentials  
- System configuration files
- Source code access

**CANNOT link a specific vote to a specific voter.**

#### Integrity Measures

**1. Double-Voting Prevention:**
- UNIQUE constraint on (election_id, position_id, user_id) in vote_confirmations
- Database enforces atomically at constraint level
- No application-level race conditions possible

**2. Atomic Transaction Guarantee:**
```php
DB::transaction(function () {
    // Both inserts succeed or both fail
    VoteConfirmation::create([...]); // WHO voted
    Vote::create([...]);              // WHAT was voted
});
```

**3. Audit Trail (Separate from Votes):**
- IP addresses and user agents stored in vote_confirmations
- Enables fraud detection without compromising vote anonymity
- Can track suspicious patterns without knowing vote choices

**4. Vote Tampering Prevention:**
- Once inserted, votes are immutable (no UPDATE queries)
- Any modification requires DELETE + INSERT (logged in audit)
- Database backups provide point-in-time recovery

#### What We Preserve

✅ "Show me who voted" - Query vote_confirmations
✅ "Show me vote counts" - Query votes  
✅ "Check if Alice voted" - Query vote_confirmations
✅ "Voter participation by hour" - Query vote_confirmations (has timestamps)
✅ "Send 'Thank you for voting at 10:23' email" - Use vote_confirmations
✅ "Prevent double-voting" - UNIQUE constraint in vote_confirmations

#### What We Sacrifice (Intentionally)

❌ "When was this specific vote cast?" - Impossible (by design)
❌ "Show me Alice's vote choice" - Impossible (by design)
❌ "Vote submission timeline" - Can see participation timeline, not vote timeline

**This trade-off is deliberate:** We prioritize voter anonymity over vote-level analytics.

#### Database Configuration

**Transaction Isolation Level:**
```sql
SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
```
- Prevents dirty reads
- Allows concurrent voting
- Balances performance with data integrity

**Row Locking (Not Used):**
We deliberately avoid `SELECT ... FOR UPDATE` because:
- UNIQUE constraint prevents duplicates atomically
- Reduces deadlock risk under high concurrency
- Simpler code with better performance
- Database handles concurrency better than application locks

### 5.8 Security Best Practices

**Input Validation:**
```php
// app/Http/Requests/VoteRequest.php
class VoteRequest extends FormRequest
{
    public function rules(): array
    {
        $election = $this->route('election');
        
        $rules = [];
        foreach ($election->positions as $position) {
            $rules["votes.{$position->id}"] = [
                'required',
                'integer',
                'exists:candidates,id',
                function ($attribute, $value, $fail) use ($position) {
                    $candidate = Candidate::find($value);
                    if ($candidate->position_id !== $position->id) {
                        $fail('Invalid candidate for this position.');
                    }
                },
            ];
        }
        
        return $rules;
    }
}
```

**SQL Injection Prevention:**
- Always use Eloquent ORM or query builder
- Never concatenate user input into raw SQL
- Prepared statements for all queries

**XSS Prevention:**
- Blade template engine auto-escapes output
- Use `{{ $variable }}` not `{!! $variable !!}`
- Sanitize user-generated content (manifestos, etc.)

**CSRF Protection:**
- Laravel's CSRF middleware enabled globally
- All forms include `@csrf` token

**Rate Limiting:**
```php
// routes/web.php - Voting endpoint
Route::middleware(['throttle:vote'])->group(function () {
    Route::post('/elections/{election}/vote', [VotingController::class, 'store']);
});

// OAuth callback rate limiting (prevents abuse)
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])
    ->middleware('throttle:10,1'); // 10 attempts per minute

// app/Providers/RouteServiceProvider.php
RateLimiter::for('vote', function (Request $request) {
    return [
        Limit::perMinute(3)->by($request->ip()),        // 3 per minute per IP
        Limit::perMinute(5)->by($request->user()?->id), // 5 per minute per user
        Limit::perHour(50)->by($request->ip()),         // 50 per hour per IP
    ];
});

// Login rate limiting (built into Laravel Fortify/Breeze)
// Already implements 5 attempts per 3 minutes
```

**Session Security:**
```php
// config/session.php
'lifetime' => env('SESSION_LIFETIME', 15),  // 15 minutes during elections
'expire_on_close' => true,
'secure' => env('SESSION_SECURE_COOKIE', true), // HTTPS only
'http_only' => true,  // Prevent JavaScript access
'same_site' => 'lax', // CSRF protection

// Regenerate session on login (automatic in Laravel)
// Regenerate session after vote (manual in VotingController)
session()->regenerate();
```

**File Storage Abstraction:**
```php
// app/Services/FileStorageService.php
class FileStorageService
{
    /**
     * Store candidate photo with abstraction for future S3 migration
     */
    public function storeCandidatePhoto($file, $candidateId): string
    {
        $disk = config('elections.storage_disk', 'public');
        return $file->store("candidates/{$candidateId}", $disk);
    }
    
    public function getCandidatePhotoUrl($path): string
    {
        $disk = config('elections.storage_disk', 'public');
        return Storage::disk($disk)->url($path);
    }
    
    public function deleteCandidatePhoto($path): bool
    {
        $disk = config('elections.storage_disk', 'public');
        return Storage::disk($disk)->delete($path);
    }
}

// config/elections.php
return [
    'storage_disk' => env('ELECTIONS_STORAGE_DISK', 'public'),
];

// Future migration: just change .env
// ELECTIONS_STORAGE_DISK=s3
// Then configure config/filesystems.php with S3 credentials
```

**Organization Timezone Middleware:**
```php
// app/Http/Middleware/SetOrganizationTimezone.php
namespace App\Http\Middleware;

class SetOrganizationTimezone
{
    public function handle($request, $next)
    {
        $organization = app('current_organization');
        
        if ($organization && $organization->timezone) {
            config(['app.timezone' => $organization->timezone]);
            date_default_timezone_set($organization->timezone);
        }
        
        return $next($request);
    }
}

// Usage in Election model
public function isVotingOpen(): bool
{
    $now = now()->setTimezone($this->organization->timezone);
    
    return $now->between(
        $this->voting_start_date->setTimezone($this->organization->timezone),
        $this->voting_end_date->setTimezone($this->organization->timezone)
    );
}
```

---

## 6. Performance Optimization

### 6.1 Database Optimization

**Indexing Strategy:**
- Primary keys on all tables (AUTO_INCREMENT)
- Foreign keys indexed automatically
- Composite indexes on frequently queried combinations
- Covering indexes for common queries

**Query Optimization:**
```php
// Bad - N+1 query problem
$candidates = Candidate::where('election_id', $election->id)->get();
foreach ($candidates as $candidate) {
    echo $candidate->user->name;  // Triggers separate query
    echo $candidate->position->name;  // Triggers separate query
}

// Good - Eager loading
$candidates = Candidate::where('election_id', $election->id)
    ->with(['user', 'position'])
    ->get();
```

**Caching Strategy:**
```php
// Cache election results for 5 minutes during voting
$results = Cache::remember(
    "election.{$election->id}.results",
    now()->addMinutes(5),
    function () use ($election) {
        return $this->calculateResults($election);
    }
);

// Invalidate cache when new vote is cast
Cache::forget("election.{$election->id}.results");
```

### 6.2 Redis for Sessions and Cache

**Configuration:**
```env
SESSION_DRIVER=redis
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

**Benefits:**
- Fast session storage (crucial for concurrent voters)
- Efficient caching of election data
- Queue processing for background jobs

### 6.3 Queue System

**Background Jobs:**
- Email notifications (vote confirmations, results)
- Report generation
- Bulk operations (user imports)
- Vote count aggregation

```php
// Dispatch job after vote is cast
dispatch(new SendVoteConfirmation($voter, $election));

// Process in background
class SendVoteConfirmation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public function handle()
    {
        Mail::to($this->voter)->send(new VoteConfirmedMail($this->election));
    }
}
```

### 6.4 Load Testing Requirements

Before go-live, test with:
- 500 concurrent voters
- 1000 votes in 10 minutes
- Tools: Apache JMeter, k6, or Laravel Dusk

**Target Metrics:**
- Page load time: < 2 seconds
- Vote submission: < 1 second
- Server CPU: < 70% during peak
- Database connections: < 50% of max

```
```
6.2 Configuration Tuning
PHP-FPM (www.conf): Optimized for 8GB RAM to handle high traffic.

pm = dynamic
pm.max_children = 100        ; Can handle ~100 simultaneous requests per second
pm.start_servers = 20
pm.min_spare_servers = 10
pm.max_spare_servers = 30
```
```
MySQL (my.cnf):
innodb_buffer_pool_size = 4G ; 50% of RAM dedicated to DB caching
max_connections = 500
```
---

## 7. Deployment Strategy

### 7.1 VPS Setup (Hostinger KVM 2)

**Server Specifications:**
- CPU: 4 vCPU Cores
- RAM: 8 GB (Crucial for concurrency)
- Disk: 200 GB NVMe
- OS: Ubuntu 22.04 LTS

**Software Stack:**
```bash
# Web server
Nginx 1.24+

# PHP
PHP 8.2+ with extensions:
- php-fpm
- php-mysql
- php-redis
- php-mbstring
- php-xml
- php-curl
- php-zip
- php-gd

# Database
MySQL 8.0+

# Cache/Queue
Redis 7.0+

# Process Manager
Supervisor (for queue workers)

# SSL
Certbot (Let's Encrypt)
```

### 7.2 Initial Deployment Steps

```bash
# 1. Update system
sudo apt update && sudo apt upgrade -y

# 2. Install LEMP stack
sudo apt install nginx mysql-server php8.2-fpm php8.2-mysql redis-server -y

# 3. Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# 4. Clone repository
cd /var/www
git clone https://github.com/yourusername/elections-hq.git
cd elections-hq

# 5. Install dependencies
composer install --optimize-autoloader --no-dev
npm install && npm run build

# 6. Configure environment
cp .env.example .env
php artisan key:generate

# 7. Database setup
mysql -u root -p
CREATE DATABASE elections_hq;
CREATE USER 'elections'@'localhost' IDENTIFIED BY 'strong_password';
GRANT ALL ON elections_hq.* TO 'elections'@'localhost';
FLUSH PRIVILEGES;

# 8. Run migrations
php artisan migrate --force

# 9. Set permissions
sudo chown -R www-data:www-data /var/www/elections-hq
sudo chmod -R 755 /var/www/elections-hq/storage

# 10. Configure Nginx
sudo nano /etc/nginx/sites-available/elections-hq
# (See nginx config below)

# 11. Enable site
sudo ln -s /etc/nginx/sites-available/elections-hq /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx

# 12. SSL Certificate
sudo certbot --nginx -d elections-hq.com -d www.elections-hq.com

# 13. Setup queue worker
sudo nano /etc/supervisor/conf.d/elections-hq-worker.conf
# (See supervisor config below)
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start elections-hq-worker:*
```

### 7.3 Nginx Configuration

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name elections-hq.com www.elections-hq.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name elections-hq.com www.elections-hq.com;
    root /var/www/elections-hq/public;

    # SSL Configuration (Certbot will add this)
    ssl_certificate /etc/letsencrypt/live/elections-hq.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/elections-hq.com/privkey.pem;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Rate limiting for voting endpoint
    location ~ ^/elections/.*/vote$ {
        limit_req zone=voting burst=5 nodelay;
        try_files $uri $uri/ /index.php?$query_string;
    }
}

# Rate limit zone
limit_req_zone $binary_remote_addr zone=voting:10m rate=10r/m;
```

### 7.4 Supervisor Configuration

```ini
[program:elections-hq-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/elections-hq/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/elections-hq/storage/logs/worker.log
stopwaitsecs=3600
```

### 7.5 Email Service Setup

#### Option A: Mailgun (Recommended)

**1. Sign up and verify domain:**
```bash
# Visit mailgun.com/signup
# Add domain: elections-hq.com
# Add DNS records to your domain:

# TXT Record:
# Host: @ or elections-hq.com
# Value: v=spf1 include:mailgun.org ~all

# DKIM Records (2 TXT records provided by Mailgun):
# Host: k1._domainkey.elections-hq.com
# Value: [provided by Mailgun]

# MX Records (2 records):
# Priority: 10, Host: mxa.mailgun.org
# Priority: 10, Host: mxb.mailgun.org

# CNAME Record:
# Host: email.elections-hq.com
# Value: mailgun.org
```

**2. Get API credentials:**
- Navigate to Settings → API Keys
- Copy your Private API Key

**3. Configure Laravel:**
```bash
# .env
MAIL_MAILER=mailgun
MAIL_FROM_ADDRESS=noreply@elections-hq.com
MAIL_FROM_NAME="Elections HQ"

MAILGUN_DOMAIN=elections-hq.com
MAILGUN_SECRET=your-private-api-key
MAILGUN_ENDPOINT=api.mailgun.net
```

**4. Install Mailgun package:**
```bash
composer require symfony/mailgun-mailer symfony/http-client
```

**5. Test email:**
```bash
php artisan tinker
Mail::raw('Test email from Elections HQ', function($msg) {
    $msg->to('your-email@example.com')->subject('Test');
});
```

#### Option B: Hostinger Email (Budget Alternative)

**1. Create email account in cPanel:**
- Login to Hostinger panel
- Navigate to Email Accounts
- Create: noreply@elections-hq.com

**2. Get SMTP credentials:**
- SMTP Host: smtp.hostinger.com
- Port: 587 (TLS) or 465 (SSL)
- Username: noreply@elections-hq.com
- Password: [your email password]

**3. Configure Laravel:**
```bash
# .env
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=587
MAIL_USERNAME=noreply@elections-hq.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@elections-hq.com
MAIL_FROM_NAME="Elections HQ"
```

**4. Test email:**
```bash
php artisan tinker
Mail::raw('Test email', function($msg) {
    $msg->to('test@example.com')->subject('Test');
});
```

### 7.6 SMS Service Setup (Optional)

#### Option A: Africa's Talking

**1. Sign up:**
- Visit africastalking.com
- Create account and get sandbox/production credentials

**2. Install package:**
```bash
composer require africastalking/africastalking
```

**3. Configure:**
```bash
# .env
SMS_PROVIDER=africastalking
SMS_USERNAME=sandbox  # or your username
SMS_API_KEY=your_api_key
SMS_SENDER_ID=ElectionsHQ
SMS_ENABLED=false  # Enable per organization
```

**4. Create SMS service:**
```php
// app/Services/SMSService.php
use AfricasTalking\SDK\AfricasTalking;

class SMSService
{
    protected $sms;
    
    public function __construct()
    {
        $username = config('services.africastalking.username');
        $apiKey = config('services.africastalking.api_key');
        
        $at = new AfricasTalking($username, $apiKey);
        $this->sms = $at->sms();
    }
    
    public function send(string $phone, string $message): array
    {
        $result = $this->sms->send([
            'to' => $phone,
            'message' => $message,
            'from' => config('services.africastalking.sender_id'),
        ]);
        
        return $result;
    }
}
```

#### Option B: Hubtel (Ghana-specific)

**1. Sign up:**
- Visit developers.hubtel.com
- Get API credentials

**2. Install HTTP client:**
```bash
composer require guzzlehttp/guzzle
```

**3. Configure:**
```bash
# .env
SMS_PROVIDER=hubtel
SMS_CLIENT_ID=your_client_id
SMS_CLIENT_SECRET=your_client_secret
SMS_SENDER_ID=ElectionsHQ
SMS_ENABLED=false
```

**4. Create SMS service:**
```php
// app/Services/HubtelSMSService.php
use GuzzleHttp\Client;

class HubtelSMSService
{
    protected $client;
    
    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://api.hubtel.com/',
            'auth' => [
                config('services.hubtel.client_id'),
                config('services.hubtel.client_secret'),
            ],
        ]);
    }
    
    public function send(string $phone, string $message): array
    {
        $response = $this->client->post('v1/messages/send', [
            'json' => [
                'From' => config('services.hubtel.sender_id'),
                'To' => $phone,
                'Content' => $message,
            ],
        ]);
        
        return json_decode($response->getBody(), true);
    }
}
```

**5. Test SMS:**
```bash
php artisan tinker
app(SMSService::class)->send('+233241234567', 'Test SMS from Elections HQ');
```

### 7.7 Backup Strategy

**Automated Backup Schedule:**
```bash
# /usr/local/bin/backup-elections.sh
#!/bin/bash
BACKUP_DIR="/backups/mysql"
DATE=$(date +%Y-%m-%d)
DB_NAME="elections_hq"
DB_USER="elections"
DB_PASS="your_password"

# Create backup directory if not exists
mkdir -p $BACKUP_DIR

# Database backup with compression
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/$DATE.sql.gz

# Backup application files (weekly)
if [ $(date +%u) -eq 7 ]; then
    tar -czf /backups/files/$DATE-app.tar.gz /var/www/elections-hq \
        --exclude=/var/www/elections-hq/node_modules \
        --exclude=/var/www/elections-hq/vendor
fi

# Keep last 7 daily database backups
find $BACKUP_DIR/20*.sql.gz -mtime +7 -delete

# Keep last 4 weekly file backups
find /backups/files/20*.tar.gz -mtime +28 -delete

echo "Backup completed: $DATE"
```

**Backup Verification (Simple):**
```bash
#!/bin/bash
# /usr/local/bin/check-backup.sh
# Runs daily at 4 AM to verify backup exists and is not corrupted

BACKUP_DIR="/backups/mysql"
TODAY=$(date +%Y-%m-%d)
BACKUP_FILE="$BACKUP_DIR/$TODAY.sql.gz"
ALERT_EMAIL="admin@elections-hq.com"

if [ -f "$BACKUP_FILE" ]; then
    # Check file size (should be at least 1MB)
    SIZE=$(stat -c%s "$BACKUP_FILE" 2>/dev/null || stat -f%z "$BACKUP_FILE")
    
    if [ $SIZE -gt 1000000 ]; then
        # Test gzip integrity
        gzip -t $BACKUP_FILE
        
        if [ $? -eq 0 ]; then
            echo "✓ Backup verified: $BACKUP_FILE ($SIZE bytes)"
            exit 0
        else
            echo "✗ Backup corrupted: $BACKUP_FILE" | mail -s "BACKUP CORRUPTED" $ALERT_EMAIL
            exit 1
        fi
    else
        echo "✗ Backup too small: $SIZE bytes" | mail -s "BACKUP TOO SMALL" $ALERT_EMAIL
        exit 1
    fi
else
    echo "✗ No backup found for $TODAY" | mail -s "BACKUP MISSING" $ALERT_EMAIL
    exit 1
fi
```

**Cron Schedule:**
```bash
# Daily backup at 2 AM
0 2 * * * /usr/local/bin/backup-elections.sh >> /var/log/backup.log 2>&1

# Verification at 4 AM
0 4 * * * /usr/local/bin/check-backup.sh >> /var/log/backup-verify.log 2>&1

# Off-site backup sync at 5 AM (if using S3 or rsync)
0 5 * * * aws s3 sync /backups s3://elections-hq-backups/ >> /var/log/backup-sync.log 2>&1
```

**Manual Restoration (Disaster Recovery):**
```bash
# 1. Stop the application
sudo supervisorctl stop elections-hq-worker:*

# 2. Restore database
gunzip < /backups/mysql/2026-04-15.sql.gz | mysql -u elections -p elections_hq

# 3. Restore files (if needed)
cd /var/www
sudo rm -rf elections-hq
sudo tar -xzf /backups/files/2026-04-15-app.tar.gz

# 4. Set permissions
sudo chown -R www-data:www-data /var/www/elections-hq
sudo chmod -R 755 /var/www/elections-hq/storage

# 5. Clear cache and restart
php artisan cache:clear
php artisan config:clear
sudo supervisorctl start elections-hq-worker:*
sudo systemctl reload nginx

# 6. Verify
php artisan migrate:status
```

**Backup Storage Recommendations:**
- **Primary:** Local VPS storage (`/backups`)
- **Secondary:** Remote sync to S3, DigitalOcean Spaces, or rsync to another server
- **Tertiary:** Download critical backups to local machine before major elections

**Note:** Full backup restoration testing should be added in Phase 2. For MVP, simple file verification is sufficient.

---

## 8. Monitoring & Maintenance

### 8.1 Application Monitoring

**Laravel Telescope (Development):**
- Monitor queries, requests, exceptions
- View queue jobs, cache hits/misses
- Debug performance issues

**Production Monitoring:**
```php
// Log errors to file and external service
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['single', 'slack'],
    ],
    'slack' => [
        'driver' => 'slack',
        'url' => env('LOG_SLACK_WEBHOOK_URL'),
        'level' => 'critical',
    ],
],
```

### 8.2 Server Monitoring

**Resource Usage:**
```bash
# CPU and Memory
htop

# Disk usage
df -h

# MySQL connections
mysqladmin processlist

# Nginx access logs
tail -f /var/log/nginx/access.log

# PHP-FPM status
sudo systemctl status php8.2-fpm
```

**Automated Alerts:**
- Set up UptimeRobot or similar for uptime monitoring
- Configure email alerts for high resource usage
- Monitor SSL certificate expiration

### 8.3 Maintenance Schedule

**Daily:**
- Check error logs
- Monitor vote submission rate during elections
- Verify backup completion

**Weekly:**
- Review performance metrics
- Check database size and optimize if needed
- Update dependencies (security patches)

**Monthly:**
- Full system update
- Review audit logs
- Performance testing
- Offsite backup verification

---

## 9. Scalability Considerations

### 9.1 Current Capacity

With VPS KVM 2 (2 cores, 8GB RAM):
- **Supported:** 5,000-10,000 registered voters
- **Concurrent:** 500+ simultaneous voters
- **Peak throughput:** 50-100 votes/minute

### 9.2 Scaling Options

**Vertical Scaling (Upgrade VPS):**
- KVM 4: 4 cores, 16GB RAM → 20,000 voters
- KVM 8: 8 cores, 32GB RAM → 50,000 voters

**Horizontal Scaling (Future):**
- Multiple web servers behind load balancer
- Separate database server
- Redis cluster for sessions
- CDN for static assets

**Database Optimization:**
- Read replicas for reporting
- Query caching with Redis
- Partitioning large tables (votes, audit_logs)

### 9.3 Multi-Organization Growth

**Current:** Single database, filtered queries
**Future (1000+ orgs):** Consider:
- Database sharding by organization_id
- Separate databases per organization
- Microservices architecture

---

## 10. Feature Roadmap

### Phase 1 (MVP - April 2026)
- ✅ User authentication (admin/staff)
- ✅ Universal Google Authentication (Socialite)
- ✅ Flexible voter ID format support
- ✅ Organization management
- ✅ Multi-tenancy with data isolation
- ✅ Election creation and lifecycle management
- ✅ Nomination system
- ✅ Vetting workflow
- ✅ Voting system with double-vote prevention
- ✅ Results display and calculation
- ✅ Email notifications (confirmations)
- ✅ Basic reporting and audit logs

**Note on Write-in Candidates:**
Write-in candidates are **NOT supported** in any phase. This is a deliberate design decision to:
- Maintain election integrity and simplify vote counting
- Prevent confusion with name variations and duplicates
- Ensure all candidates go through proper nomination and vetting
- Avoid delays in result publication requiring manual review
- Reduce complexity in the MVP and future phases

Organizations must ensure adequate nomination periods where anyone eligible can register as a candidate.

### Phase 2 (Q2 2026)
- SMS notifications (vote confirmations, reminders, results)
- Bulk voter import (CSV/Excel)
- Advanced reporting (PDF export, charts)
- Voter analytics dashboard
- Mobile-responsive UI improvements
- Candidate manifesto viewer
- Election templates
- Draft vote storage with auto-save (browser crash recovery)
- Vote receipt/verification codes (anonymous)

### Phase 3 (Q3 2026)
- Progressive Web App (PWA) for mobile
- Live results dashboard (real-time updates with WebSockets)
- Multi-language support (English, French, etc.)
- Advanced fraud detection algorithms
- API for third-party integrations
- Webhook support
- Connection pool monitoring dashboard
- Full backup verification with restore testing

### Phase 4 (Q4 2026)
- White-label solution
- Custom branding per organization
- Payment integration (Stripe/Paystack)
- Advanced analytics with data visualization
- Automated election scheduling
- Voter registration portal
- Integration with school management systems

---

## 11. Risk Assessment & Mitigation

| Risk | Impact | Probability | Mitigation |
|------|--------|-------------|------------|
| Server downtime during election | High | Low | VPS upgrade during voting, backup server ready |
| Database corruption | High | Very Low | Daily automated backups, transaction logging |
| Double voting exploit | High | Low | Database constraints, row locking, thorough testing |
| DDoS attack | Medium | Medium | Cloudflare or similar CDN, rate limiting |
| Vote manipulation | High | Low | Audit logs, vote anonymization, code review |
| Data breach | High | Low | Encryption, secure passwords, regular security audits |
| Performance degradation | Medium | Medium | Load testing before elections, monitoring, quick scaling |

---

## 12. Development Timeline

**Week 1-2: Setup & Core Models**
- VPS setup and configuration
- Laravel installation
- Database schema implementation
- Authentication system

**Week 3-4: Organization & User Management**
- Multi-tenancy implementation
- User roles and permissions
- Organization dashboard

**Week 5-6: Election Management**
- Election CRUD operations
- Position management
- Lifecycle state machine

**Week 7-8: Nomination & Vetting**
- Nomination submission
- File uploads (photos, manifestos)
- Vetting workflow

**Week 9-10: Voting System**
- Voter eligibility checks
- Vote casting interface
- Double-voting prevention
- Transaction handling

**Week 11-12: Results & Reporting**
- Vote counting
- Results display
- Basic reports
- Audit logs

**Week 13: Testing & Deployment**
- Unit tests
- Integration tests
- Load testing
- Production deployment

**Week 14-15: Buffer & Polish**
- Bug fixes
- UI/UX improvements
- Documentation
- User training for UPSA

---

## 13. Success Metrics

**Technical Metrics:**
- 99.9% uptime during voting period
- < 1 second vote submission time
- Zero double-voting incidents
- < 0.1% error rate

**Business Metrics:**
- Successful UPSA election (April 2026)
- 90%+ voter participation rate
- Positive user feedback
- 2-3 additional organizations by end of 2026

**Security Metrics:**
- Zero security breaches
- Complete audit trail
- Successful penetration testing
- Compliance with data protection

---

## Conclusion

This architecture provides a solid foundation for Elections HQ - a secure, scalable, and maintainable election management system. The design prioritizes:

1. **Security:** Vote anonymity, audit trails, access control
2. **Integrity:** Transaction handling, double-voting prevention
3. **Scalability:** Multi-tenancy, caching, optimization
4. **Maintainability:** Clean architecture, service patterns, documentation

With Laravel's robust ecosystem and proper VPS configuration, this system can reliably handle the UPSA IT Department election and scale to serve many more organizations.

**Next Steps:**
1. Review and discuss this architecture
2. Finalize database schema
3. Set up development environment
4. Begin implementation following the timeline
5. Regular check-ins and iterations

Good luck with the project! 🚀
