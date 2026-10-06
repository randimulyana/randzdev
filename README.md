# Randz.dev — Personal Developer Portfolio & Programming Learning Platform

Randz.dev adalah platform web personal developer berbasis Laravel yang menggabungkan tiga fungsi utama:
1. **Personal Developer Portfolio**: Showcase identitas, pengalaman, keahlian teknis, dan studi kasus project.
2. **Programming Blog & Technical Documentation**: Artikel, catatan teknis, dan tutorial pemrograman.
3. **Programming Learning Platform**: Kurikulum berstruktur dalam bentuk Learning Paths dan Lessons interaktif untuk pemula.

Dibangun dengan prinsip:
> **Build. Learn. Document. Share.**

---

## 🛠️ Tech Stack & Arsitektur

- **Framework**: [Laravel 13](https://laravel.com) (PHP 8.3+)
- **Architecture**: Server-rendered Laravel MVC Monolith
- **Frontend**: Blade + [Tailwind CSS v4](https://tailwindcss.com) + Vanilla JavaScript + Alpine.js
- **Asset Bundler**: [Vite](https://vitejs.dev)
- **Database**: MySQL 8.0+ / MariaDB 10.5+
- **Authentication**: Laravel Breeze
- **Authorization**: [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission) (Role-Based Access Control)
- **Code Highlighter**: Highlight.js (Markdown code block highlighting)
- **Testing**: [Pest PHP](https://pestphp.com) & PHPUnit
- **Code Formatter**: Laravel Pint

---

## 📋 Persyaratan Sistem (System Requirements)

- **PHP**: `>= 8.3`
  - Ekstensi wajib: `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `filter`, `hash`, `json`, `mbstring`, `openssl`, `pcre`, `pdo`, `pdo_mysql`, `session`, `tokenizer`, `xml`
- **Database**: MySQL `>= 8.0` atau MariaDB `>= 10.5`
- **Composer**: `>= 2.2`
- **Node.js**: `>= 20.x` & **npm**: `>= 10.x`

---

## 🚀 Panduan Instalasi Lokal (Local Development)

### 1. Clone Repository
```bash
git clone <REPOSITORY_URL> randzdev
cd randzdev
```

### 2. Install Dependency PHP
```bash
composer install
```

### 3. Konfigurasi Environment
```bash
cp .env.example .env
php artisan key:generate
```
Sesuaikan konfigurasi database MySQL pada file `.env` lokal:
```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=randzdev
DB_USERNAME=randzdev
DB_PASSWORD=secret
```

### 4. Jalankan Database Migration & Seeding
```bash
# Jalankan migrasi tabel
php artisan migrate

# Seed role & permissions
php artisan db:seed --class=RolePermissionSeeder

# (Opsional) Buat akun admin development lokal
# Pastikan ADMIN_PASSWORD terisi di .env terlebih dahulu
php artisan db:seed --class=AdminUserSeeder
```

### 5. Hubungkan Direktori Publik Storage
```bash
php artisan storage:link
```

### 6. Install Dependency Frontend & Kompilasi
```bash
npm install
npm run build
# Atau jalankan dev watcher:
npm run dev
```

### 7. Jalankan Server Development
```bash
php artisan serve
```
Akses aplikasi melalui browser di `http://localhost:8000`.

---

## 🧪 Testing & Code Quality

Aplikasi dilengkapi test suite komprehensif (600+ tests) menggunakan Pest PHP.

```bash
# Menjalankan seluruh test suite
php artisan test --compact

# Menjalankan test tertentu
php artisan test --filter=SecurityHardeningTest

# Menjalankan linter & code formatter Pint
vendor/bin/pint --format agent
```

---

## 🚢 Panduan Deployment Produksi (Production Deployment)

Dokumentasi lengkap mengenai arsitektur server, konfigurasi Nginx/Apache, virtual host, zero-downtime script, dan verifikasi pasca-deploy tersedia di:
👉 **[DEPLOYMENT.md](DEPLOYMENT.md)**

### Ringkasan Alur Deployment Cepat

1. **Document Root**:
   Wajib mengarah ke direktori `public/` (bukan root repository).
2. **Environment Variables**:
   ```dotenv
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://randz.dev
   SESSION_SECURE_COOKIE=true
   ```
3. **Eksekusi Perintah Produksi**:
   ```bash
   composer install --no-dev --optimize-autoloader
   npm ci --ignore-scripts && npm run build
   php artisan migrate --force
   php artisan db:seed --class=RolePermissionSeeder --force
   php artisan storage:link
   php artisan optimize
   ```
4. **Izin Berkas**:
   Pastikan direktori `storage/` dan `bootstrap/cache/` memiliki izin tulis untuk web server (`chmod -R 775`).

---

## 🔒 Keamanan & Kebijakan Hak Akses

- **Server-Side Authorization**: Seluruh operasi CRUD dan aksi publish/unpublish dilindungi oleh Spatie Permissions dan Laravel Policies (`PostPolicy`, `ProjectPolicy`, `LearningPathPolicy`, `LessonPolicy`, dll).
- **IDOR Prevention**: Nested route lessons menggunakan scoped route model binding (`scoped()`).
- **File Upload Protection**: Validasi MIME type ketat (`jpeg,png,jpg,webp,gif`), batas ukuran 2MB, dan perlindungan path traversal sanitization pada cleanup file lama.
- **Rate Limiting**: Form kontak (`/contact`) dan login form dilindungi pembatasan request per menit.
- **Production Guard**: `AdminUserSeeder` secara otomatis menolak dieksekusi di environment produksi demi mencegah kebocoran kredensial default.

---

## 📄 Lisensi

Project ini dilisensikan di bawah [MIT License](LICENSE).
