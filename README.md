# ZARINALABS

The web platform that manages IT training for Lions Fort. Public users create an account,
verify their email, browse courses and events, register for one, and get a QR code. At the
door the admin scans that QR code to mark them present. One admin runs everything from
`/admin`.

Website only. English only. **No payments of any kind.**

## Stack

| | |
| --- | --- |
| PHP | 8.5 |
| Laravel | 13 |
| MySQL | 8 |
| Livewire | 4 (all public-site interactivity) |
| Filament | 5 (admin panel) |
| Tailwind CSS | 4 (CSS-first, theme in `resources/css/app.css`) |
| QR codes | `endroid/qr-code`, rendered server side as SVG |
| Icons | Heroicons, solid only |
| Queue | database driver — **all mail is queued** |
| Tests | Pest + PHPUnit |

There is no JavaScript HTTP layer: no axios, no `fetch()`, no jQuery. Alpine ships inside
Livewire and is used only for the mobile menu toggle.

Conventions and gotchas live in [CLAUDE.md](CLAUDE.md). Read it before changing anything.

---

## Local setup (macOS)

You need PHP 8.5, Composer, Node 20+, and MySQL 8.

```bash
composer install
npm install

cp .env.example .env
php artisan key:generate
```

### Database

Create the database and point `.env` at it:

```sql
CREATE DATABASE zarinalabs CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=zarinalabs
DB_USERNAME=root
DB_PASSWORD=your-password
```

If MySQL came from the official `.dmg`, the `mysql` client is not on your `PATH`. It lives at
`/usr/local/mysql/bin/mysql` (or `/usr/local/mysql-8.1.0-macos13-x86_64/bin/mysql`).

Then:

```bash
php artisan migrate --seed
php artisan storage:link      # serves instructor photos from storage/app/public
npm run build
```

### Run it

```bash
composer dev
```

That runs the web server, the queue worker, the log tailer, and Vite together. Or separately:

```bash
php artisan serve
php artisan queue:work        # required, or no email is ever sent
npm run dev
```

**The queue worker is not optional in development.** Mail is queued, so verification and
registration emails sit in the `jobs` table until a worker picks them up. Locally
`MAIL_MAILER=log`, so the email is written to `storage/logs/laravel.log`.

### Seeded accounts

| | |
| --- | --- |
| Admin | `admin@zarinalabs.test` / `password` |
| Students | 15 verified users, all with the password `password` |

**Change the admin password before going anywhere near production.** The seeder is for local
and staging only — never run `db:seed` against a live database.

### Tests

```bash
composer test        # clears the config cache first, then runs the suite
```

Tests run against an in-memory SQLite database configured in `phpunit.xml`. Two guards in
`tests/TestCase.php` refuse to boot if a cached config exists or if the default connection
points at the real `zarinalabs` database — a cached config once made `RefreshDatabase` wipe
the development data.

**Never run `php artisan optimize` locally.** A cached config or route file will serve stale
routes and freeze your `.env`. If anything behaves strangely, run `php artisan optimize:clear`.

---

## Deploying to an Ubuntu VPS

Assets are built on your machine or in CI, so **the server does not need Node**.

```bash
npm run build                 # produces public/build/
```

Ship the repository including `public/build/`, or run the build step in CI and deploy the
artifact.

### 1. Server packages

```bash
sudo apt update
sudo apt install -y nginx mysql-server php8.5-fpm php8.5-mysql php8.5-mbstring \
    php8.5-xml php8.5-curl php8.5-zip php8.5-gd unzip
```

Install Composer, then:

```bash
cd /var/www/zarinalabs
composer install --no-dev --optimize-autoloader
```

### 2. Environment

```dotenv
APP_NAME=ZARINALABS
APP_ENV=production
APP_DEBUG=false
APP_URL=https://zarinalabs.com

APP_LOCALE=en

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=zarinalabs
DB_USERNAME=zarinalabs
DB_PASSWORD=a-strong-password

QUEUE_CONNECTION=database
SESSION_DRIVER=database
CACHE_STORE=database

MAIL_MAILER=smtp
MAIL_HOST=smtp.your-provider.com
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_SCHEME=tls
MAIL_FROM_ADDRESS="noreply@zarinalabs.com"
MAIL_FROM_NAME=ZARINALABS
```

```bash
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan optimize            # caches config, routes and views. Production only.
```

Writable directories:

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### 3. nginx

`/etc/nginx/sites-available/zarinalabs`:

```nginx
server {
    listen 80;
    server_name zarinalabs.com www.zarinalabs.com;
    root /var/www/zarinalabs/public;

    index index.php;
    charset utf-8;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    client_max_body_size 8M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.5-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

```bash
sudo ln -s /etc/nginx/sites-available/zarinalabs /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

Then add TLS with `sudo certbot --nginx -d zarinalabs.com -d www.zarinalabs.com`.
The QR check-in links are signed against `APP_URL`, so it must be the real HTTPS URL.

### 4. Queue worker as a systemd service

Without this, **no email is ever delivered**.

`/etc/systemd/system/zarinalabs-queue.service`:

```ini
[Unit]
Description=ZARINALABS queue worker
After=network.target mysql.service

[Service]
User=www-data
Group=www-data
Restart=always
RestartSec=5
ExecStart=/usr/bin/php /var/www/zarinalabs/artisan queue:work --sleep=3 --tries=3 --max-time=3600
WorkingDirectory=/var/www/zarinalabs

[Install]
WantedBy=multi-user.target
```

```bash
sudo systemctl daemon-reload
sudo systemctl enable --now zarinalabs-queue
sudo systemctl status zarinalabs-queue
```

### 5. On every deploy

```bash
php artisan down
git pull                       # public/build/ comes with it, or is uploaded by CI
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize           # re-caches config, routes, views
sudo systemctl restart zarinalabs-queue   # workers hold old code in memory
php artisan up
```

Restarting the queue worker matters: a long-running worker keeps the previous code loaded
until it is restarted.

---

## What this project deliberately does not do

Payments, certificates, CV builder, user file uploads, instructor accounts, marketing or
reminder emails, multi-language, charts and analytics, roles beyond admin and user, social
login, comments or reviews, dark mode, a public API, a mobile app, and video hosting (links
only).

Course completion is a human decision. The admin reads the attendance matrix and decides.
Nothing about it is automated.
