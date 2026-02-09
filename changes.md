# Recent Changes Documentation

## 1. SMS Sending for Tenants

### Overview
SMS functionality has been implemented with strict tenant isolation and plan-based access control. This ensures that costs are managed and features are only available to eligible organizations.

### Key Changes
- **Dual Verification**: The ability to send SMS is now controlled by two factors:
    1.  **Subscription Plan**: The tenant's plan must explicitly allow SMS (e.g., Premium, Enterprise).
    2.  **Organization Toggle**: A specific `sms_enabled` flag on the `Organization` model must be set to `true`.
- **Logic**: `PlanLimitService::canUseSMS($organization)` returns `true` ONLY if both conditions are met.
- **Enforcement**: 
    - **Notifications**: Before sending any SMS (e.g., OTP, alerts), the `NotificationService` checks this permission. If denied, the SMS is efficiently skipped without erroring out the entire process.
    - **UI/Import**: The "Import Voters" feature conditionally requires phone numbers only if SMS is enabled for that tenant.

---

## 2. Pricing & Welcome Page Updates

### Overview
The pricing structure on the main landing page (`welcome.blade.php`) and the backend definitions (`PlanLimitService.php`) have been synchronized to reflect new business offerings.

### Plan Changes
| Plan | Price (Monthly) | Voters | Elections | Storage | Custom Domain | SMS |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **New** | ₵100 | 300 | 1 | 500 MB | ❌ | ❌ |
| **Basic** | ₵180 | 500 | 3 | 1 GB | ✅ | ❌ |
| **Premium** | ₵550 | 2,000 | Unlimited | 10 GB | ✅ | ✅ |
| **Enterprise** | ₵1,800+ | Unlimited | Unlimited | Unlimited | ✅ | ✅ |

### UI Improvements
- **Billing Toggle**: A new toggle allows users to view Monthly vs. Annual pricing (Annual reflects ~15% discount).
- **Plan Cards**: Visual hierarchy improved with "Premium" designated as the popular choice.
- **Tenant Isolation**: Pricing section is strictly hidden when accessing via a tenant subdomain (e.g., `university.elections-hq.me`).

---

## 3. Plan Limit Enforcement

### How Limits Are Enforced
Plan limitations are enforced strictly at the application level using `PlanLimitService`.

1.  **Voter Creation**:
    - **Where**: `App\Filament\Resources\VoterResource\Pages\CreateVoter.php`
    - **Mechanism**: Inside `mutateFormDataBeforeCreate`, the system calls `$planService->canAddVoter($org)`.
    - **Result**: If the limit is reached, a "Plan Limit Reached" notification is displayed, and the process is **halted** (`$this->halt()`), preventing the database insertion.

2.  **Election Creation**:
    - **Where**: `App\Filament\Resources\ElectionResource\Pages\CreateElection.php`
    - **Mechanism**: Similar to voters, `mutateFormDataBeforeCreate` calls `$planService->canCreateElection($org)`.
    - **Result**: If the limit is reached (e.g., attempting to create a 2nd election on the "New" plan), the action is blocked with a user-friendly error message.

3.  **SMS Usage**:
    - **Where**: `App\Services\NotificationService.php` and `PlanLimitService.php`
    - **Mechanism**: The `canUseSMS` check validation runs before any message dispatch.
