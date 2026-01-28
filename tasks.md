# Elections HQ - Development Tasks

**Project:** Election Management System  
**Stack:** Laravel 12.x, MySQL 8.0, Redis, Livewire, Filament, Tailwind CSS  
**Target:** MVP ready for UPSA election (April 2026)

---

## Phase 0: Environment Setup

### Task 0.1: Create Laravel Project
```bash
composer create-project laravel/laravel elections-hq
cd elections-hq
```

### Task 0.2: Install Core Dependencies
```bash
# Authentication & OAuth
composer require laravel/socialite

# Admin Panel
composer require filament/filament:"^3.0" -W
php artisan filament:install --panels

# Frontend Interactivity
composer require livewire/livewire

# Development Tools
composer require --dev laravel/pint
composer require --dev pestphp/pest --with-all-dependencies
```

### Task 0.3: Install Frontend Dependencies
```bash
npm install -D tailwindcss postcss autoprefixer
# npx tailwindcss init -p
npm install alpinejs
```

### Task 0.4: Configure Environment
Edit `.env` file with:
- Database credentials (MySQL)
- Redis connection for sessions/cache/queue
- [x] **Configure Google OAuth** `[P1]`
    - [x] Add credentials to `.env` (GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET, GOOGLE_REDIRECT_URI) `[User Action]`
    - [x] Update `config/services.php` to use env vars
- Mail configuration ([comment:Mailgun] SMTP)
- App URL and timezone

### Task 0.5: Initialize Git Repository
```bash
git init
git add .
git commit -m "Initial Laravel setup with dependencies"
```

---

## Phase 1: Database Schema

### Task 1.1: Create Migrations
Create migrations in this exact order (dependencies matter):

1. **organizations** - Tenant table (name, slug, subdomain, settings, subscription info)
2. **users** - Add google_id, avatar, is_super_admin columns to default users table
3. **organization_user** - Pivot table with voter_id, allowed_email, role, status, can_vote
4. **elections** - Election details with lifecycle dates and status enum
5. **positions** - Electoral positions linked to elections
6. **candidates** - Candidates with nomination/vetting status and vote_count
7. **vote_confirmations** - WHO voted (user_id, position_id, timestamps, IP)
8. **votes** - WHAT was voted (NO user_id, NO timestamps - anonymous)
9. **notifications** - Email/SMS tracking
10. **audit_logs** - Complete audit trail

### Task 1.2: Run Migrations
```bash
php artisan migrate
```

### Task 1.3: Create Database Seeders
Create seeders for:
- Default super admin user
- Sample organization for testing
- Sample election with positions and candidates

---

## Phase 2: Core Models & Relationships

### Task 2.1: Create Models
Create Eloquent models for each table:
- Organization
- User (modify existing)
- Election
- Position
- Candidate
- Vote
- VoteConfirmation
- Notification
- AuditLog

### Task 2.2: Define Relationships
Each model needs proper relationships:
- Organization hasMany Elections, Users (through pivot)
- Election belongsTo Organization, hasMany Positions
- Position belongsTo Election, hasMany Candidates
- Candidate belongsTo Position, User
- User belongsToMany Organizations (with pivot data)

### Task 2.3: Create BelongsToOrganization Trait
Implement global scope trait that:
- Automatically sets organization_id on create
- Filters all queries by current organization context
- Apply to: Election, Position, Candidate, Vote, VoteConfirmation, Notification, AuditLog

### Task 2.4: Create Model Factories
Create factories for testing all models.

---

## Phase 3: Multi-Tenancy Infrastructure

### Task 3.1: Organization Resolution Middleware
Create `SetOrganizationContext` middleware that:
- Resolves organization from subdomain OR custom domain
- Sets organization in app container
- Makes it available via helper function `current_organization()`

### Task 3.2: Organization Timezone Middleware
Create `SetOrganizationTimezone` middleware that:
- Sets PHP timezone to organization's timezone
- Ensures election dates display correctly

### Task 3.3: Register Middleware
Add middleware to appropriate route groups in `bootstrap/app.php`.

---

## Phase 4: Authentication System

### Task 4.1: Configure Socialite for Google
- [x] In `config/services.php`, add Google OAuth configuration.

### Task 4.2: Create Auth Routes
- [x] Define routes for:
    - `GET /auth/google` - Redirect to Google
    - `GET /auth/google/callback` - Handle callback
    - `POST /logout` - Logout

### Task 4.3: Create GoogleAuthController
- [x] Implement controller that:
    - Redirects to Google OAuth
    - Handles callback
    - Checks if email exists in organization_user (the "guest list")
    - Creates/updates user record
    - Links user to organization membership
    - Sets status to 'active' on first login
    - Logs user in and redirects to voter portal

### Task 4.4: Create GoogleAuthService
- [x] Extract business logic from controller:
    - `handleLogin($googleUser)` - Main authentication flow
    - Validate email against organization's allowed list
    - Return user or throw appropriate exception

### Task 4.5: Admin Authentication
- [x] Configure Filament to use standard Laravel auth for admin users (email/password).
    - *Note: Filament defaults to standard auth, no extra config needed for Phase 4 MVP.*

---

## Phase 5: Filament Admin Panel

### Task 5.1: Create Admin User Resource
- [x] Filament resource to manage admin/staff users with:
    - List, create, edit, delete
    - Role assignment
    - Organization assignment

### Task 5.2: Create Organization Resource
- [x] Filament resource for organizations:
    - Name, slug, subdomain, custom domain
    - Logo upload
    - Timezone selection
    - Subscription management
    - Settings JSON editor

### Task 5.3: Create Election Resource
- [x] Filament resource for elections:
    - Title, description, slug
    - All lifecycle dates (nomination, vetting, voting)
    - Status management with state transitions
    - Settings (require_photo, max_votes_per_position)

### Task 5.4: Create Position Resource
- [x] Filament resource for positions:
    - Name, description
    - Display order (drag-drop reordering)
    - Max candidates, max votes per position
    - Relation manager within Election

### Task 5.5: Create Candidate Resource
- [x] Filament resource for candidates:
    - User selection (from organization members)
    - Position assignment
    - Photo upload
    - Manifesto text editor
    - Nomination status management
    - Vetting status with notes

### Task 5.6: Create Voter Import Feature
- [x] Filament action/page for:
    - CSV upload (voter_id, email columns)
    - Preview before import
    - Bulk insert into organization_user
    - Duplicate detection and handling

### Task 5.7: Create Results Dashboard
- [x] Filament page showing:
    - Vote counts per candidate per position
    - Participation statistics
    - Voting timeline (from vote_confirmations)
    - Export to PDF/CSV

### Task 5.8: Create Audit Log Viewer
- [x] Filament resource for viewing audit logs:
    - Filterable by user, action, entity type
    - Date range filtering
    - Read-only (no edit/delete)

---

## Phase 6: Voting System (Core)

### Task 6.1: Create VotingService
Service class handling vote submission:
- `castVote(Election $election, User $user, array $ballot)` - Main method
- Wrap in database transaction
- Insert VoteConfirmation (with user_id, timestamps)
- Insert Vote (NO user_id, NO timestamps)
- Dispatch confirmation email job
- Create audit log entry

### Task 6.2: Create Vote Prevention Logic
In VotingService, implement:
- Check if user already voted for position (query vote_confirmations)
- Rely on database UNIQUE constraint as backup
- Handle constraint violation gracefully (AlreadyVotedException)

### Task 6.3: Create Eligibility Checks
Create `CheckVoterEligibility` middleware/policy:
- User must be in organization_user with can_vote = true
- Election status must be 'voting'
- Current time within voting window
- User hasn't already voted for all positions

### Task 6.4: Create VoteRequest Form Request
Validate incoming vote submission:
- Each position requires a candidate selection
- Candidate must belong to that position
- Candidate must be approved (vetting_status = 'passed')

### Task 6.5: Candidate Self-Service Portal
- [x] Create `CandidatePortal` Livewire component.
- [x] Implement file upload (photo) and manifesto editor.
- [x] Restrict access to users with linked Candidate records only (Email Invitation Flow).
- [ ] Handle state transitions (pending_submission -> pending_vetting).

---

## Phase 7: Voter Portal (Frontend)

### Task 7.1: Create Voter Layout
Blade layout for voter-facing pages:
- Clean, mobile-responsive design
- Organization branding (logo, colors)
- Minimal navigation
- Logout button

### Task 7.2: Create Login Page
Simple page with:
- Organization name/logo
- "Sign in with Google" button
- Brief instructions

### Task 7.3: Create Election List Page
Show available elections:
- Elections in 'voting' status only
- Election title, description
- Voting window dates
- "Vote Now" button

### Task 7.4: Create Voting Booth Page (Livewire)
Multi-step voting interface:
- One position per step/section
- Display candidates with photos and manifestos
- Radio buttons for single selection
- Progress indicator
- "Review & Submit" final step

### Task 7.5: Create Vote Confirmation Page
After successful vote:
- Success message with timestamp
- List of positions voted for (not choices)
- Option to view results (if published)
- Logout button

### Task 7.6: Create Results Page
Public results display:
- Available only after results_published = true
- Show vote counts per candidate
- Highlight winners
- Participation statistics

---

## Phase 8: Services & Jobs

### Task 8.1: Create NotificationService
Handle all notifications:
- `sendVoteConfirmation(User, Election)` - Email after voting
- `sendElectionReminder(Election, message)` - Bulk reminders
- `sendResultsAnnouncement(Election)` - Results published

### Task 8.2: Create SendVoteConfirmation Job
Queued job for sending vote confirmation emails:
- Use Laravel Mail
- Include election name, timestamp
- Do NOT include vote choices (anonymity)

### Task 8.3: Create AuditService
Centralized audit logging:
- `log(action, entity, oldValues, newValues)` - Generic logging
- Auto-capture user_id, IP, user_agent
- Called from services and controllers

### Task 8.4: Create ResultsService
Calculate and format results:
- `calculateResults(Election)` - Vote counts per candidate
- `getParticipationStats(Election)` - Turnout numbers
- `determineWinners(Election)` - Mark is_winner on candidates

### Task 8.5: Create ElectionLifecycleService
Manage election state transitions:
- Validate transitions (draft → nomination → vetting → voting → completed)
- Auto-transition based on dates (scheduler)
- Send notifications on state changes

---

## Phase 9: Security Implementation

### Task 9.1: Configure Rate Limiting
In `RouteServiceProvider` or `bootstrap/app.php`:
- Vote endpoint: 3/minute per IP, 5/minute per user
- OAuth callback: 10/minute
- Login: 5 attempts per 3 minutes (Laravel default)

### Task 9.2: Configure Session Security
In `config/session.php`:
- lifetime: 15 minutes during elections
- expire_on_close: true
- secure: true (HTTPS only)
- http_only: true
- same_site: lax

### Task 9.3: Create ElectionPolicy
Authorization policy for elections:
- `vote(User, Election)` - Can user vote in this election?
- `viewResults(User, Election)` - Can user see results?
- `manage(User, Election)` - Can user manage election?

### Task 9.4: Input Validation
Ensure all form requests have proper validation:
- VoteRequest validates candidate belongs to position
- All IDs are integers and exist in database
- Text fields have max length limits

---

## Phase 10: Testing

### Task 10.1: Create Feature Tests
Test critical flows:
- Google OAuth login flow (mock Socialite)
- Vote submission (success and double-vote prevention)
- Election state transitions
- Results calculation

### Task 10.2: Create Unit Tests
Test service methods:
- VotingService::castVote
- GoogleAuthService::handleLogin
- ResultsService::calculateResults

### Task 10.3: Test Multi-Tenancy
Verify data isolation:
- Organization A cannot see Organization B data
- Global scopes work correctly
- Cross-organization access blocked

### Task 10.4: Load Testing
Use Laravel's built-in tools or k6/Artillery:
- Simulate 500 concurrent voters
- Measure response times
- Identify bottlenecks

---

## Phase 11: Deployment Preparation

### Task 11.1: Production Configuration
Create production .env template:
- APP_ENV=production
- APP_DEBUG=false
- Proper cache/session/queue drivers
- Real mail credentials

### Task 11.2: Optimize for Production
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
npm run build
```

### Task 11.3: Create Deployment Script
Bash script or use Laravel Envoy:
- Pull latest code
- Install dependencies
- Run migrations
- Clear and rebuild caches
- Restart queue workers

### Task 11.4: Configure Backups
Set up automated backups:
- Daily MySQL dump
- Copy to offsite storage
- Test restore procedure

### Task 11.5: Set Up Monitoring
Configure basic monitoring:
- Error logging (Laravel log or external service)
- Server health checks
- Database connection monitoring

---

## Phase 12: Final Polish

### Task 12.1: Error Pages
Create custom error pages:
- 404 - Election/page not found
- 403 - Not authorized (not on voter list)
- 500 - Server error with contact info

### Task 12.2: Flash Messages
Implement consistent flash messaging:
- Success (green)
- Error (red)
- Warning (yellow)
- Info (blue)

### Task 12.3: Loading States
Add loading indicators:
- Vote submission button
- Page transitions
- Data fetching

### Task 12.4: Mobile Responsiveness
Test and fix all pages on:
- iPhone SE (small)
- iPhone 14 (medium)
- iPad (tablet)
- Desktop

### Task 12.5: Accessibility
Basic accessibility checks:
- Form labels
- Button focus states
- Color contrast
- Screen reader testing

---

## Completion Checklist

- [ ] Laravel project created and configured
- [ ] All migrations created and run
- [ ] All models with relationships
- [ ] Multi-tenancy working (org isolation)
- [ ] Google OAuth login working
- [x] Filament admin panel functional
- [x] Voter CSV import working
- [ ] Voting booth complete
- [ ] Vote anonymity verified (no user link in votes table)
- [ ] Double-voting prevented
- [ ] Results display working
- [ ] Email notifications sending
- [ ] Rate limiting configured
- [ ] All tests passing
- [ ] Production deployment successful
- [ ] Backup system tested

---

## Quick Reference: File Locations

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Auth/GoogleAuthController.php
│   │   └── Voter/VotingController.php
│   ├── Middleware/
│   │   ├── SetOrganizationContext.php
│   │   └── SetOrganizationTimezone.php
│   └── Requests/VoteRequest.php
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
│   ├── GoogleAuthService.php
│   ├── VotingService.php
│   ├── ResultsService.php
│   └── NotificationService.php
├── Policies/ElectionPolicy.php
├── Traits/BelongsToOrganization.php
├── Filament/
│   └── Resources/
│       ├── OrganizationResource.php
│       ├── ElectionResource.php
│       ├── PositionResource.php
│       └── CandidateResource.php
└── Jobs/SendVoteConfirmation.php

resources/views/
├── layouts/voter.blade.php
├── auth/login.blade.php
├── voter/
│   ├── elections.blade.php
│   ├── vote.blade.php
│   ├── confirmation.blade.php
│   └── results.blade.php
└── livewire/voting-booth.blade.php
```

---

**Estimated Timeline:** 10-15 weeks for solo developer  
**Priority Order:** Phase 0 → 1 → 2 → 3 → 4 → 6 → 7 → 5 → 8 → 9 → 10 → 11 → 12

Build voting system first (Phases 0-4, 6-7), then admin panel (Phase 5), then polish.
