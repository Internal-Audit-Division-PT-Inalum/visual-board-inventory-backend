<div align="center">
  <h1>🛡️ Visual Board & Inventory System - Backend API</h1>
  <p><strong>Sistem Manajemen Audit Internal 5R & Inventaris Gudang (PT INALUM)</strong></p>
  <p>Dibangun dengan Arsitektur Skala Enterprise (Domain-Driven Design)</p>

  [![PHP Version](https://img.shields.io/badge/PHP-8.4+-blue.svg)](https://php.net)
  [![Laravel Version](https://img.shields.io/badge/Laravel-11.x-red.svg)](https://laravel.com)
  [![Filament](https://img.shields.io/badge/Filament-3.x-yellow.svg)](https://filamentphp.com)
  [![PostgreSQL](https://img.shields.io/badge/PostgreSQL-Ready-336791.svg)](https://postgresql.org)
</div>

---

## 📖 Deskripsi
Proyek ini adalah inti peladen (*Backend API*) untuk **Sistem Visual Board 5R & Manajemen Inventaris**. Sistem ini menyediakan layanan API tingkat tinggi untuk integrasi lintas platform (*Mobile/Web Kiosk*), sekaligus menyediakan Dasbor Admin (*Filament Panel*) yang elegan untuk pengelolaan data operasional secara terpusat oleh Super Admin dan Manajemen.

## 🏗️ Arsitektur & Teknologi
Sistem ini mematuhi standar *Clean Architecture* dan **Domain-Driven Design (DDD)** untuk skalabilitas dan pemeliharaan jangka panjang.

*   **Framework Utama:** Laravel 13.x
*   **Database:** PostgreSQL (Dioptimalkan untuk tipe data `JSONB`)
*   **Admin Panel:** Filament v5 (dengan Filament Shield untuk *RBAC*)
*   **Dokumentasi API:** Scribe (Auto-generated Postman Collection)
*   **Pengujian (Testing):** Pest & PHPUnit
*   **Analisis Statis:** PHPStan (Level 0/Enterprise)
*   **Pemformatan Kode:** Laravel Pint

### Struktur Domain (DDD)
Sistem dipecah ke dalam beberapa area bisnis utama (`app/Domains/`):
1.  **Core Domain:** Mengelola fondasi otentikasi (Users, Roles, Security).
2.  **Visual Board Domain:** Mengelola entitas operasional 5R (Zones, Workstations, Abnormalities, Monthly Schedules).
3.  **Inventory Domain:** Mengelola pergudangan (Items, Inventory Ledgers, Transactions).
4.  **HR Domain:** Mengelola data kepegawaian (Departments, Employees, Attendances).
5.  **Portal Domain:** Mengelola informasi mading digital (Bulletins/Kiosk).

---

## 🚀 Kebutuhan Sistem (Prerequisites)
Pastikan komputer server/lokal Anda memiliki:
*   PHP >= 8.4
*   Composer >= 2.x
*   PostgreSQL >= 14 (Sangat direkomendasikan karena struktur JSONB)
*   Node.js & NPM (untuk *build asset* Filament jika diperlukan)

---

## ⚙️ Panduan Instalasi Lokal
Ikuti langkah berikut untuk memasang aplikasi di lingkungan pengembangan atau *Local Server* (Intranet):

1. **Kloning Repositori**
   ```bash
   git clone https://github.com/Internal-Audit-Division-PT-Inalum/visual-board-inventory-backend.git
   cd "visual-board-inventory-backend"
   ```

2. **Instalasi Dependensi**
   ```bash
   composer install
   ```

3. **Konfigurasi Environment**
   Salin file `.env.example` menjadi `.env` lalu sesuaikan kredensial *database* (PostgreSQL disarankan) dan konfigurasi SMTP (untuk notifikasi email).
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Migrasi Database & Seeding**
   Perintah ini akan membangun tabel dan memasukkan data tiruan (*dummy*) serta akun *Super Admin*.
   ```bash
   php artisan migrate:fresh --seed
   ```

5. **Menghubungkan Penyimpanan (Storage)**
   ```bash
   php artisan storage:link
   ```

---

## 🛡️ Dasbor Manajemen (Filament Admin)
Setelah instalasi selesai, jalankan server:
```bash
php artisan serve
```
Akses Dasbor Admin melalui: `http://localhost:8000/admin`

**Kredensial Default (Super Admin):**
*   **Email:** `admin@admin.com`
*   **Password:** `password`

---

## 📨 Pekerja Latar Belakang (Queue & Notifications)
Sistem ini menggunakan *Observer* dan *Queue* untuk mengirim notifikasi email (*Targeted Alerts*) kepada PIC Zona secara asinkron agar tidak membebani performa API.
Selama aplikasi berjalan di tahap produksi, pastikan *Queue Worker* selalu aktif:
```bash
php artisan queue:work
```

---

## 📚 Dokumentasi API
Dokumentasi API lengkap digenerate secara otomatis menggunakan **Scribe**.
Untuk melihat dokumentasi (UI HTML):
Akses `http://localhost:8000/docs` di peramban Anda.

Untuk memperbarui dokumentasi setelah ada perubahan pada *Endpoint*:
```bash
php artisan scribe:generate
```

---

## 🧪 Pengujian & Standar Kualitas (Testing & QA)
Sistem ini dikawal ketat oleh pengujian terotomatisasi.
*   **Menjalankan Unit/Feature Test:**
    ```bash
    php artisan test
    ```
*   **Analisis Kode Statis (Mendeteksi potensi *bug* gaib):**
    ```bash
    php vendor/bin/phpstan analyse app/ tests/
    ```
*   **Meratakan Format Kode (Linter):**
    ```bash
    php vendor/bin/pint
    ```
