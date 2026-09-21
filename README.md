<div align="center">
  <h1>🛡️ Visual Board & Inventory System - Backend API</h1>
  <p><strong>Sistem Manajemen Audit Internal 5R & Inventaris Gudang (PT INALUM)</strong></p>
  <p>Dibangun dengan Arsitektur Skala Enterprise menggunakan pendekatan Domain-Driven Design (DDD)</p>

  [![PHP Version](https://img.shields.io/badge/PHP-8.4+-blue.svg)](https://php.net)
  [![Laravel Version](https://img.shields.io/badge/Laravel-13.x-red.svg)](https://laravel.com)
  [![Filament](https://img.shields.io/badge/Filament-5.x-yellow.svg)](https://filamentphp.com)
  [![PostgreSQL](https://img.shields.io/badge/PostgreSQL-Ready-336791.svg)](https://postgresql.org)
</div>

---

## 📖 Deskripsi Proyek
Repositori ini berisi *source code* untuk layanan peladen (*Backend API*) dari proyek **Sistem Visual Board 5R & Manajemen Inventaris** PT INALUM. 

Sistem ini dirancang untuk melayani dua kebutuhan operasional utama:
1. **API Kiosk / Layar Publik:** Menyediakan *endpoint* JSON yang sangat responsif untuk menampilkan data metrik operasional harian, status kehadiran (*attendance*), tren matriks kelengkapan audit, dokumen panduan/SOP, hingga mading digital (*bulletins*) secara *real-time* di monitor layar pabrik.
2. **Dasbor Manajemen (CMS Backend):** Menyediakan antarmuka admin visual yang elegan untuk mengelola pendataan (*master data*), memantau tren temuan (*abnormalities*), mendistribusikan jadwal audit piket bulanan, serta memproses arus barang logistik di gudang secara rapi.

## 🏗️ Arsitektur & Teknologi
Untuk memastikan sistem bisa dirawat lintas tim tanpa mengubah tatanan *spaghetti code* di kemudian hari, proyek ini menerapkan **Domain-Driven Design (DDD)** yang didukung oleh pola *Repository & Service*.

**Teknologi Utama:**
*   **Framework:** Laravel (PHP 8.4+)
*   **Database:** PostgreSQL (Wajib digunakan karena kami mengandalkan manipulasi tipe data `JSONB` yang ekstensif untuk matriks jadwal).
*   **Dasbor Internal:** Filament PHP (dilengkapi dengan *Filament Shield* untuk hierarki *Role-Based Access Control* tingkat spesifik).
*   **Dokumentasi:** Scribe (Otomatis menghasilkan dokumentasi API dan *Postman Collection*).
*   **Penjamin Mutu (QA):** Pest (Unit/Feature Testing) & PHPStan (Static Analysis).

### Bedah Struktur Domain
Kode tidak lagi digabung dalam satu folder besar `app/Models` secara berantakan. Sistem telah dipecah menjadi beberapa "Domain" bisnis independen di dalam direktori `app/Domains/` dan dikendalikan lewat `app/Services/`:

1. **Visual Board Domain:** Inti fungsionalitas 5R. Menangani hierarki area (Zona & Workstation), pembuatan piket (*Monthly Schedules*), pelaporan temuan (*Abnormalities*), kalkulasi tren kelulusan audit (*Trend Abnormalities*), serta seluruh dokumen terpusat (*General Documents* & Struktur Organisasi).
2. **HR Domain:** Bertanggung jawab atas pengelolaan entitas karyawan, hierarki tingkat jabatan (Manajer/Supervisor/Eksekutor), dan data absen Kiosk.
3. **Portal Domain:** Mengurus fungsi penyampaian informasi massal (*broadcast*), termasuk Mading Pengumuman (*Bulletins*) dan Tautan Eksternal Pintas (*Quick Links*) yang disajikan di layar Kiosk.
4. **Inventory Domain:** Menangani siklus hidup logistik, mulai dari pengaturan rak penyimpanan, data barang (*Items*), kriteria uji kelayakan, hingga jurnal transaksi gudang (*Inventory Ledgers*).
5. **Telemetry (External Service):** Penghubung terintegrasi untuk membaca suplai metrik harian dari sistem eksternal perusahaan (SCADA / SAP / Sistem Keselamatan K3) guna menampilkan statistik kecepatan penyelesaian dan Hari Bebas Kecelakaan (*Safety Streak Days*).

---

## 🚀 Kebutuhan Sistem
Sebelum menekan tombol *clone*, pastikan mesin server atau laptop pengembang sudah terpasang alat-alat berikut:
*   PHP >= 8.4
*   Composer >= 2.x
*   PostgreSQL >= 14
*   Node.js & NPM (Opsional, khusus jika Anda berencana meng-*compile* ulang kustomisasi tema visual CSS dari Filament).

---

## ⚙️ Panduan Menjalankan Aplikasi di Lokal
Panduan langkah demi langkah untuk mempersiapkan lingkungan pengembangan:

1. **Kloning Repositori**
   ```bash
   git clone https://github.com/Internal-Audit-Division-PT-Inalum/visual-board-inventory-backend.git
   cd visual-board-inventory-backend
   ```

2. **Tarik Dependensi (Composer)**
   ```bash
   composer install
   ```

3. **Atur Environment Variables**
   Gandakan file contoh *environment*. Sesuaikan konfigurasi akun *database* PostgreSQL dan kredensial SMTP (jika ingin mencoba fitur notifikasi *email*).
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Siapkan Database (Kosong / Tanpa Dummy)**
   Perintah ini akan menjalankan seluruh *migration* skema tabel, sekaligus mengisi *database* hanya dengan profil akun bawaan (Super Admin) dan *Role/Permissions*. **Sistem ini sengaja dikosongkan dari data tiruan (dummy)** untuk memastikan lingkungan bersih sebelum *input* data aktual secara manual.
   ```bash
   php artisan migrate:fresh --seed
   ```

5. **Buka Akses Penyimpanan Publik (Storage)**
   Jalankan ini agar aset gambar profil, foto lampiran 5R, atau brosur PDF mading bisa diakses melalui URL publik dari internet atau API.
   ```bash
   php artisan storage:link
   ```

---

## 🛡️ Masuk ke Dasbor Admin
Jalankan peladen uji coba bawaan Laravel:
```bash
php artisan serve
```
Buka peramban (browser) dan akses alamat administrasi di: `http://localhost:8000/admin`

**Akses Pengguna Bawaan (Super Admin):**
*   **Email:** `admin@admin.com`
*   **Kata Sandi:** `password`

Gunakan panel ini untuk mengelola hierarki peran (Role & Permissions), mengisi dokumen SOP baru, menambah berita mading, dan memeriksa kinerja audit lintas departemen.

---

## 📚 Membaca Dokumentasi API
Komunikasi data ke aplikasi *Mobile* atau tampilan Web Kiosk *Frontend* menggunakan *RESTful JSON API*. 
Dokumentasi seluruh *endpoint* diproduksi secara dinamis.

*   **Akses UI Dokumentasi HTML:**
    Jalankan server dan buka `http://localhost:8000/docs`.
*   **Perbarui Dokumentasi (Jika Anda menambah fitur):**
    ```bash
    php artisan scribe:generate
    ```

**Beberapa API Kunci:**
*   `GET /api/visual-board/kiosk` - Pusat pengumpulan metrik dasbor layar utama (menarik gabungan matriks tren bulanan, dokumen penting, *abnormality* terakhir, dan telemetri kinerja K3).
*   `GET /api/visual-board/workstations` - Endpoint dinamis untuk menampilkan stasiun kerja dan asosiasi karyawan yang bertugas di dalamnya.
*   `GET /api/portal/bulletins` - Umpan informasi mading *slider* dinamis.

---

## 📨 *Background Workers* (Pemrosesan Asinkron)
Agar antarmuka Kiosk tidak *loading* lama akibat perhitungan kalkulasi kalkulator skor Audit atau proses pengiriman *email* ke manajer zona, sistem ini memindahkan pekerjaan berat ke belakang layar menggunakan *Laravel Queue*.

Jika aplikasi ini sudah mengudara di *server* operasional, pastikan daemon ini berjalan secara stabil (disarankan dibungkus dengan *Supervisor*):
```bash
php artisan queue:work
```

---

## 🧪 Area *Quality Assurance* (Penjaminan Kualitas Kode)
Bagi para *developer*, repositori ini memberlakukan standar kode tertulis. Anda wajib mengecek kondisi kode Anda dengan daftar perintah berikut sebelum membuat *Pull Request* atau *Commit*:

1. **Jalankan *Test Suite* Otomatis:**
   Buktikan bahwa restrukturisasi atau penambahan fitur tidak mematahkan kontrak API yang sudah ada.
   ```bash
   php artisan test
   ```
2. **Scan Struktur & Tipe Data (Static Analysis):**
   Mendeteksi jebakan *fatal error*, *class* hilang, atau pemanggilan *method* gaib.
   ```bash
   php vendor/bin/phpstan analyse app/ tests/ --memory-limit=2G
   ```
3. **Merapikan Standar Pengetikan (Lint/Format):**
   Membersihkan kode dari selipan *space* yang salah format (memenuhi *PSR-12*).
   ```bash
   php vendor/bin/pint
   ```
