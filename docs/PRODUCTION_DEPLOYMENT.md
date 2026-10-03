# Wollo University Property Recovery Portal — Production Deployment & Hardening Guide

## 1. Production Architecture Overview

The Wollo University Property Recovery Portal is designed for deployment with high security, session isolation, and resilient real-time streaming:

- **Web Server / Reverse Proxy**: Nginx 1.24+ with TLS 1.3 / Let's Encrypt SSL
- **Application Runtime**: PHP 8.4+ (PHP-FPM or Laravel Octane / FrankenPHP)
- **Database**: MySQL 8.0+ / MariaDB 10.11+
- **Cache & Session**: Redis 7.0+ (or MySQL database driver)
- **Queue Worker**: Supervisor daemon running `php artisan queue:work`
- **Scheduler**: Linux `cron` executing `php artisan schedule:run` every minute

---

## 2. Environment Hardening Configuration

Ensure `.env` in production specifies:

```dotenv
APP_NAME="Wollo University Property Recovery Portal"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://lostfound.wu.edu.et

FRONTEND_URL=https://lostfound.wu.edu.et
CORS_ALLOWED_ORIGINS=https://lostfound.wu.edu.et

# Cookie & Session Security
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_PATH=/
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax

# Sanctum Stateful SPA Domains
SANCTUM_STATEFUL_DOMAINS=lostfound.wu.edu.et

# Queue & Cache
QUEUE_CONNECTION=database
CACHE_STORE=database

# Mail Transport
MAIL_MAILER=smtp
MAIL_HOST=mail.wu.edu.et
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=notifications@wu.edu.et
MAIL_FROM_NAME="Wollo University Property Recovery"
```

---

## 3. Nginx Reverse Proxy Configuration

```nginx
server {
    listen 80;
    server_name lostfound.wu.edu.et;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name lostfound.wu.edu.et;

    ssl_certificate /etc/letsencrypt/live/lostfound.wu.edu.et/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/lostfound.wu.edu.et/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Content-Security-Policy "default-src 'self'; img-src 'self' data: https:; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com;" always;

    root /var/www/wollo-lost-found/server/public;
    index index.php;

    charset utf-8;

    # Static SPA assets (Vue frontend)
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Real-Time SSE Stream Endpoint Buffer Bypass
    location = /api/v1/notifications/stream {
        try_files $uri /index.php?$query_string;
        proxy_set_header Connection '';
        proxy_http_version 1.1;
        chunked_transfer_encoding off;
        proxy_buffering off;
        proxy_cache off;
        fastcgi_buffering off;
        fastcgi_keep_conn on;
        proxy_read_timeout 86400s;
        fastcgi_read_timeout 86400s;
    }

    # PHP-FPM FastCGI Handler
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## 4. Supervisor Queue Daemon

Create `/etc/supervisor/conf.d/wu-lostfound-worker.conf`:

```ini
[program:wu-lostfound-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/wollo-lost-found/server/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/supervisor/wu-lostfound-worker.log
stopwaitsecs=3600
```

---

## 5. Cron Scheduled Tasks

Add to `/etc/cron.d/wu-lostfound`:

```cron
* * * * * www-data cd /var/www/wollo-lost-found/server && php artisan schedule:run >> /dev/null 2>&1
```

Tasks executed automatically (registered in `routes/console.php`):
- `auth:cleanup-expired`: Purges expired OTP and auth-verification records from `auth_verifications` (daily 01:00).
- `items:expire-inactive`: Expires inactive unclaimed items based on retention policy (daily 02:00).
- `items:send-expiry-warnings`: Dispatches in-app and email notifications 7 days prior to item expiration (daily 08:00).
- `reports:cleanup-expired`: Purges generated CSV/PDF exports older than 7 days (weekly Monday 03:00).
- `reports:generate-system`: Generates weekly system-wide analytics report (weekly Monday 04:00).
