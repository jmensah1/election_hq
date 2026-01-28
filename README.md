# Elections HQ

A robust, multi-tenant election management system designed for universities, organizations, and associations. Built with security and anonymity at its core.

## 🚀 Key Features

- **Multi-Tenancy**: Single installation supports multiple isolated organizations (Org A cannot see Org B's data).
- **Vote Anonymity**: Revolutionary dual-table architecture that completely decouples "Who voted" from "What they voted for".
- **Google OAuth**: Seamless login for voters using their organizational email (Workspace/Gmail).
- **Filament Admin Panel**: Powerful backend for election officers to manage candidates, positions, and results.
- **Livewire Voting Booth**: Interactive, single-page application feel for the voting process.
- **Real-Time Results**: Instant vote counting and results publication.
- **Audit Trails**: comprehensive logging of all actions for accountability.

## 🛠 Tech Stack

- **Framework**: [Laravel 12.x](https://laravel.com)
- **Admin Panel**: [Filament 3.x](https://filamentphp.com)
- **Frontend**: [Livewire 3.x](https://livewire.laravel.com) & [Tailwind CSS](https://tailwindcss.com)
- **Database**: MySQL 8.0
- **Cache/Queue**: Redis
- **Testing**: PHPUnit / Pest

## 🏗 Architecture & Security

### Vote Anonymity
The system uses a strictly decoupled database design to ensure vote anonymity:
1.  **`vote_confirmations`**: Records *that* a user voted ( User ID + Timestamp + IP).
2.  **`votes`**: Records *the vote itself* (Candidate ID only).
**Crucially, there is NO foreign key or common ID linking these two tables.** Even a database administrator cannot trace a specific vote back to a specific user.

### Security Measures
- **Rate Limiting**: Strict limits on voting endpoints to prevent automation.
- **Session Hardening**: 15-minute session lifetimes with aggressive expiration.
- **RBAC**: Role-based access control (Super Admin, Admin, Election Officer, Voter).

## ⚙️ Installation

### Prerequisites
- PHP 8.2+
- MySQL 8.0+
- Redis
- Node.js & NPM

### Setup Steps

1.  **Clone the Repository**
    ```bash
    git clone https://github.com/yourusername/elections-hq.git
    cd elections-hq
    ```

2.  **Install Dependencies**
    ```bash
    composer install
    npm install
    ```

3.  **Environment Configuration**
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```
    *Update `.env` with your Database, Redis, and Google OAuth credentials.*

4.  **Database Setup**
    ```bash
    php artisan migrate
    php artisan db:seed
    ```

5.  **Build Frontend**
    ```bash
    npm run build
    ```

6.  **Run Application**
    ```bash
    php artisan serve
    php artisan queue:work
    ```

## 🧪 Testing

Run the comprehensive test suite (Unit + Feature):

```bash
php artisan test
```

## 📄 License

This software is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
