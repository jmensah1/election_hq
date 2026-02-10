# Production Update Guide (Email Branding + Laravel Horizon)

Follow these steps on your production server after running `git pull`.

## 1. Update Code & Dependencies
```bash
cd /var/www/html

# Pull latest changes
git pull

# Install new dependencies (Horizon)
# We use --ignore-platform-reqs if needed, or ensuring pcntl/posix are installed on prod (they usually are)
# Standard command:
sudo -u www-data composer install --optimize-autoloader --no-dev
```

## 2. Publish Horizon Assets
We need to publish the Horizon assets (CSS/JS for the dashboard) to the public folder.
```bash
php artisan horizon:publish
```

## 3. Update Database & Caches
```bash
# Run migrations (if any - safe to run even if none)
php artisan migrate --force

# Clear and cache config (Essential for new Horizon config)
php artisan config:clear
php artisan config:cache

# Clear view cache (For the new email templates)
php artisan view:clear

# Restart queue workers (Important!)
php artisan queue:restart
```

## 4. Switch from Queue Worker to Horizon (CRITICAL)
You are currently running `php artisan queue:work` via Supervisor. We need to switch this to `php artisan horizon`.

1.  **Open your Supervisor config:**
    *(Filename provided by you)*
    ```bash
    nano /etc/supervisor/conf.d/laravel-worker.conf
    ```

2.  **Update the Config:**
    
    **(BEFORE) Your current config:**
    ```ini
    [program:laravel-worker]
    process_name=%(program_name)s_%(process_num)02d
    command=php /var/www/html/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
    autostart=true
    autorestart=true
    stopasgroup=true
    killasgroup=true
    user=www-data
    numprocs=2
    redirect_stderr=true
    stdout_logfile=/var/www/html/storage/logs/worker.log
    stopwaitsecs=3600
    ```

    **(AFTER) Change it to this:**
    ```ini
    [program:laravel-worker]
    process_name=%(program_name)s
    command=php /var/www/html/artisan horizon
    autostart=true
    autorestart=true
    stopasgroup=true
    killasgroup=true
    user=www-data
    redirect_stderr=true
    stdout_logfile=/var/www/html/storage/logs/horizon.log
    stopwaitsecs=3600
    ```
    
    **Key Changes:**
    *   Changed `command` to `php /var/www/html/artisan horizon`.
    *   Removed `numprocs=2` (Horizon manages its own process count internally).
    *   Changed `process_name` to simplified `%(program_name)s` (optional but cleaner for Horizon).
    *   Updated `stdout_logfile` to `horizon.log` (optional, but good for clarity).

3.  **Apply Changes:**
    ```bash
    supervisorctl reread
    supervisorctl update
    
    # Verify it started
    supervisorctl status
    ```

    *You should see `laravel-worker` running.*

## 5. Verify Installation
1.  Log in to your admin panel as a **Super Admin**.
2.  You should see a **"Horizon"** link in the sidebar under "System".
3.  Click it to open the dashboard. It should show "Active".
