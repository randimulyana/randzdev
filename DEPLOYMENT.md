# Production Deployment Guide — Randz.dev

Panduan deployment produksi untuk aplikasi **Randz.dev** (Personal Developer Portfolio & Programming Learning Platform).

---

## 1. Persyaratan Sistem Produksi (System Requirements)

- **Operating System**: Linux (Ubuntu 22.04 / 24.04 LTS direkomendasikan)
- **Web Server**: Nginx atau Apache (kompatibel dengan URL rewriting Laravel)
- **PHP**: PHP >= 8.3
  - **Required Extensions**:
    - `bcmath`
    - `ctype`
    - `curl`
    - `dom`
    - `fileinfo`
    - `filter`
    - `hash`
    - `json`
    - `mbstring`
    - `openssl`
    - `pcre`
    - `pdo`
    - `pdo_mysql`
    - `session`
    - `tokenizer`
    - `xml`
- **Database**: MySQL >= 8.0 atau MariaDB >= 10.5
- **Package Manager**:
  - Composer >= 2.2
  - Node.js >= 20.x dan npm >= 10.x (hanya dibutuhkan pada build server / CI jika build aset dijalankan di server)
- **SSL/TLS**: Sertifikat HTTPS aktif (Let's Encrypt / Cloudflare SSL)

---

## 2. Web Server & Document Root

> [!CRITICAL]
> **Document Root web server WAJIB diarahkan ke direktori `/public`, BUKAN ke root repository!**
> Mengarahkan document root ke root project akan mengekspos file sensitif seperti `.env`, kode sumber PHP, dan file konfigurasi ke publik.

### Contoh Konfigurasi Virtual Host (Nginx)

```nginx
server {
    listen 80;
    server_name randz.dev www.randz.dev;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name randz.dev www.randz.dev;

    # SSL Certificates
    ssl_certificate /etc/letsencrypt/live/randz.dev/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/randz.dev/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    # Document Root mengarah ke subdirektori public/
    root /var/www/randzdev/public;
    index index.php index.html;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    # Gzip / Compression
    gzip on;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml application/xml+rss text/javascript image/svg+xml;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    # PHP-FPM Handler
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    # Deny access to hidden files (.env, .git, dll)
    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Static assets cache
    location ~* \.(jpg|jpeg|png|gif|ico|webp|svg|css|js|woff|woff2|ttf)$ {
        expires 1y;
        add_header Cache-Control "public, no-transform";
    }
}
```

---

## 3. Direktori & Izin Berkas (File Permissions)

Web server user (biasanya `www-data` atau `nginx`) memerlukan hak akses tulis pada direktori tertentu:

```bash
# 1. Kepemilikan berkas (sesuaikan user:group sistem)
chown -R www-data:www-data /var/www/randzdev

# 2. Izin berkas standar
find /var/www/randzdev -type f -exec chmod 644 {} \;
find /var/www/randzdev -type d -exec chmod 755 {} \;

# 3. Direktori yang WAJIB dapat ditulis oleh web server
chmod -R 775 /var/www/randzdev/storage
chmod -R 775 /var/www/randzdev/bootstrap/cache
```

---

## 4. Konfigurasi Environment Produksi (`.env`)

Buat berkas `.env` di server produksi dari template `.env.example`:

```bash
cp .env.example .env
```

Sesuaikan variabel-variabel kunci berikut untuk lingkungan produksi:

```dotenv
APP_NAME="Randz.dev"
APP_ENV=production
APP_KEY=base64:...             # Generate dengan: php artisan key:generate
APP_DEBUG=false                # WAJIB false pada production!
APP_URL=https://randz.dev      # URL HTTPS domain produksi

LOG_CHANNEL=stack
LOG_LEVEL=error                # Hindari 'debug' di production agar log efisien

DB_CONNECTION=mysql
DB_HOST=127.0.0.1              # Atau host MySQL managed/RDS
DB_PORT=3306
DB_DATABASE=randzdev_prod
DB_USERNAME=randzdev_user
DB_PASSWORD="<STRONG_PASSWORD>"

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_SECURE_COOKIE=true     # WAJIB true saat menggunakan HTTPS

CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=public

# Kontak & Notifikasi Email (gunakan SMTP / Resend / Mailgun)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="no-reply@randz.dev"
MAIL_FROM_NAME="Randz.dev"
```

> [!WARNING]
> Jangan pernah meng-commit berkas `.env` atau credential produksi ke dalam Git repository.

---

## 5. Prosedur Deployment Langkah-demi-Langkah (Step-by-Step)

Jalankan urutan berikut di server produksi:

### Langkah 1: Clone atau Tarik Kode Sumber
```bash
cd /var/www
git clone <REPOSITORY_URL> randzdev
cd /var/www/randzdev
# Atau jika update:
git pull origin main
```

### Langkah 2: Install Dependency PHP (No Dev)
```bash
composer install --no-dev --optimize-autoloader --no-interaction
```

### Langkah 3: Setup Environment
```bash
# Jika deployment pertama:
cp .env.example .env
# Edit konfigurasi produksi:
nano .env

# Generate application key (HANYA saat deployment pertama):
php artisan key:generate --force
```

### Langkah 4: Jalankan Database Migration
```bash
php artisan migrate --force
```

> [!NOTE]
> Parameter `--force` wajib disertakan pada environment produksi agar Artisan tidak menanyakan konfirmasi interaktif.

### Langkah 5: Hubungkan Storage Simbolik
```bash
php artisan storage:link
```
Perintah ini membuat symbolic link dari `public/storage` ke `storage/app/public` agar berkas yang diunggah (seperti thumbnail project dan featured image blog) dapat diakses oleh browser.

### Langkah 6: Kompilasi Aset Frontend (Vite)
Jika build dilakukan langsung di server:
```bash
npm ci --ignore-scripts
npm run build
```
*(Alternatif: build aset di CI/CD pipeline lalu unggah direktori `public/build` ke server produksi).*

### Langkah 7: Seeding Awal Role & Permission (Deployment Pertama)
```bash
php artisan db:seed --class=RolePermissionSeeder --force
```
`RolePermissionSeeder` bersifat idempoten (`findOrCreate`) dan aman dijalankan ulang.

### Langkah 8: Buat Akun Administrator Pertama
Karena `AdminUserSeeder` secara sengaja menolak berjalan di environment produksi demi keamanan, buat akun admin awal menggunakan Tinker:

```bash
php artisan tinker --execute '
$user = \App\Models\User::firstOrCreate(
    ["email" => "admin@randz.dev"],
    ["name" => "Randi", "password" => \Illuminate\Support\Facades\Hash::make("<STRONG_ADMIN_PASSWORD>")]
);
$user->assignRole("admin");
echo "Admin ready: " . $user->email . PHP_EOL;
'
```

### Langkah 9: Optimasi Cache Laravel
```bash
php artisan optimize
```
Perintah `php artisan optimize` akan meng-cache:
- Konfigurasi (`config:cache`)
- Routing (`route:cache`)
- Blade views (`view:cache`)
- Events (`event:cache`)

> [!TIP]
> Jika di kemudian hari ada perubahan berkas `.env` atau routes, jalankan:
> ```bash
> php artisan optimize:clear && php artisan optimize
> ```

---

## 6. Zero-Downtime Deployment Script (Rekomendasi CI/CD)

Berikut adalah contoh script deployment otomatis (misal via GitHub Actions, Deployer, atau bash hook):

```bash
#!/usr/bin/env bash
set -e

echo "=== Memulai Deployment Randz.dev ==="

# 1. Aktifkan maintenance mode
php artisan down --retry=60 || true

# 2. Update kode
git pull origin main

# 3. Install composer dependencies
composer install --no-dev --optimize-autoloader --no-interaction

# 4. Build assets
npm ci --ignore-scripts
npm run build

# 5. Jalankan migrasi
php artisan migrate --force

# 6. Pastikan storage linked
php artisan storage:link || true

# 7. Bersihkan dan optimasi cache
php artisan optimize:clear
php artisan optimize

# 8. Matikan maintenance mode
php artisan up

echo "=== Deployment Selesai Sukses ==="
```

---

## 7. Security Deployment Checklist

Sebelum mempublikasikan aplikasi ke publik, pastikan setiap poin berikut telah diperiksa:

- [ ] **`APP_DEBUG=false`**: Halaman error debug Laravel (Ignition/Whoops) dinonaktifkan sepenuhnya.
- [ ] **`APP_ENV=production`**: Aplikasi berjalan dalam mode produksi.
- [ ] **HTTPS / SSL Aktif**: Seluruh trafik diarahkan ke `https://` melalui web server dan `SESSION_SECURE_COOKIE=true`.
- [ ] **Document Root Terisolasi**: Web server root mengarah tepat ke `/public`. File `.env` tidak dapat diunduh langsung lewat browser (`https://randz.dev/.env` mengembalikan 403 / 404).
- [ ] **APP_KEY Terisi Unik**: `APP_KEY` dihasilkan secara aman dan tidak pernah dibagikan atau di-commit.
- [ ] **Credential Database Terisolasi**: User database produksi memiliki hak akses terbatas hanya pada database `randzdev` dengan password acak yang kuat.
- [ ] **Direktori Permissions Aman**: `storage/` dan `bootstrap/cache/` dapat ditulis oleh web server, berkas PHP lainnya hanya read-only.
- [ ] **Public Upload Path Traversal Guard**: Upload thumbnail/image diproteksi dari traversal (`..` atau byte null).
- [ ] **Dynamic `/robots.txt` & `/sitemap.xml`**: Endpoint `/robots.txt` dan `/sitemap.xml` dapat diakses dengan header content-type yang tepat.
- [ ] **Health Endpoint `/up`**: Mengembalikan HTTP 200 untuk monitoring ketersediaan server.

---

## 8. Post-Deployment Smoke Verification (Pengujian Pasca-Deploy)

Lakukan pemeriksaan fungsionalitas berikut segera setelah deploy:

1. **Health Check**:
   - Akses `GET https://randz.dev/up` -> Harus mengembalikan status `200 OK`.
2. **Homepage & Public Pages**:
   - Buka `https://randz.dev/` -> Cek hero, featured projects, latest articles, footer.
   - Buka `/about`, `/projects`, `/blog`, `/learn`, `/contact`.
3. **SEO & Metadata**:
   - Buka `https://randz.dev/robots.txt` -> Berisi aturan crawling dan tautan Sitemap.
   - Buka `https://randz.dev/sitemap.xml` -> XML sitemap ter-render valid.
4. **Asset Loading**:
   - Buka DevTools (F12) -> Pastikan CSS dan JS dimuat dari `/build/assets/*` tanpa error 404.
5. **Autentikasi & Admin Area**:
   - Login di `/login` dengan kredensial administrator.
   - Buka dashboard admin di `/admin`.
   - Coba akses menu: Projects, Posts, Learning Paths, Categories, Tags, Users, Contact Messages.
6. **Upload Berkas**:
   - Uji pembuatan project/post dengan gambar thumbnail/featured image.
   - Pastikan berkas tersimpan dan tampil pada URL `/storage/...`.
7. **Contact Form & Rate Limiting**:
   - Kirim pesan melalui `/contact` -> Pastikan tersimpan dan notifikasi sukses muncul.
   - Coba submit berulang dalam 1 menit -> Middleware throttle harus aktif.
8. **Logging**:
   - Periksa `storage/logs/laravel.log` -> Pastikan tidak ada unhandled exception atau error fatal.

---

## 9. Railway Deployment Guide (Aktual)

Aplikasi Randz.dev telah di-deploy dan terkonfigurasi di [Railway](https://railway.com) menggunakan arsitektur dua service:

```text
Railway Project: randzdev
├── randzdev-app  (Web Application Service — Laravel 13, FrankenPHP, PHP 8.3)
└── MySQL         (Database Service — MySQL 9, Persistent Volume)
```

### A. Railway Service & Database Configuration
- **Application Service**: `randzdev-app` (didukung Railpack yang mengotomatisasi instalasi PHP 8.3, ekstensi `pdo_mysql`, FrankenPHP, `composer install`, `npm run build`, `php artisan optimize`, dan `php artisan storage:link`).
- **Database Service**: `MySQL` dengan volume persisten terpasang pada `/var/lib/mysql`.
- **Database Connection Variables**:
  - `DB_CONNECTION=mysql`
  - `DB_HOST=${{MySQL.MYSQLHOST}}`
  - `DB_PORT=${{MySQL.MYSQLPORT}}`
  - `DB_DATABASE=${{MySQL.MYSQLDATABASE}}`
  - `DB_USERNAME=${{MySQL.MYSQLUSER}}`
  - `DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}`

### B. Environment Variables di Railway
Konfigurasikan variabel environment pada service `randzdev-app`:
- `APP_NAME=Randz.dev`
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://randzdev-app-production.up.railway.app` (atau custom domain Anda)
- `APP_KEY=<production_app_key>`
- `LOG_CHANNEL=stderr`
- `LOG_STDERR_FORMATTER=\Monolog\Formatter\JsonFormatter`
- `SESSION_SECURE_COOKIE=true`
- `FILESYSTEM_DISK=public`
- `SESSION_DRIVER=database`
- `CACHE_STORE=database`
- `QUEUE_CONNECTION=database`

### C. Reverse Proxy & HTTPS (`trustProxies`)
Pada `bootstrap/app.php`, middleware `trustProxies(at: '*')` diaktifkan agar Laravel mempercayai header `X-Forwarded-Proto: https` dan `X-Forwarded-Host` dari edge proxy Railway. Seluruh URL canonical, asset URL, dan sitemap secara otomatis menggunakan skema `https://`.

### D. Healthcheck & Railway Configuration (`railway.json`)
Healthcheck path Railway dikonfigurasi ke `/up`:
```json
{
  "$schema": "https://railway.com/railway.schema.json",
  "build": {
    "builder": "RAILPACK"
  },
  "deploy": {
    "healthcheckPath": "/up",
    "healthcheckTimeout": 100,
    "restartPolicyType": "ON_FAILURE",
    "restartPolicyMaxRetries": 10
  }
}
```

### E. Database Migration & Initial Admin Setup
1. **Migration Otomatis**: Railpack secara otomatis menjalankan migrasi (`php artisan migrate`) saat build/deploy.
2. **Seeding Role & Permission**:
   ```bash
   railway ssh php artisan db:seed --class=RolePermissionSeeder --force
   ```
3. **Pembuatan Akun Admin Produksi**:
   Jalankan Tinker via Railway SSH untuk membuat akun admin awal secara interaktif tanpa menyimpan password ke Git:
   ```bash
   railway ssh php artisan tinker --execute '
   $user = \App\Models\User::firstOrCreate(
       ["email" => "admin@randz.dev"],
       ["name" => "Randi", "password" => \Illuminate\Support\Facades\Hash::make("<STRONG_ADMIN_PASSWORD>")]
   );
   $user->assignRole("admin");
   echo "Admin ready: " . $user->email . PHP_EOL;
   '
   ```

### F. Pertimbangan Storage & File Uploads (Storage Consideration)
- Pada arsitektur container ephemeral Railway, berkas upload lokal pada `storage/app/public` tersimpan di dalam filesystem kontainer.
- Saat kontainer di-redeploy atau diganti instance baru, berkas yang diunggah secara lokal melalui admin CMS akan di-reset ke kondisi build image awal kecuali direktori tersebut di-mount ke **Railway Volume** terpisah (misal mount path `/app/storage/app/public`), atau menggunakan **Cloud Object Storage** eksternal (seperti AWS S3, Cloudflare R2, MinIO, atau DigitalOcean Spaces) dengan driver `s3` pada `FILESYSTEM_DISK`.
- Untuk keperluan jangka panjang konten dinamis, integrasi ke S3/R2 sangat direkomendasikan.
