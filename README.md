<div align="center">

<img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="320" alt="Laravel Logo" />

# visual-board-inventory-backend

**RESTful API & Sistem Manajemen — Visual Board 5R & Inventaris Gudang PT INALUM**

[![PHP](https://img.shields.io/badge/PHP-8.3+-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20?logo=laravel&logoColor=white)](https://laravel.com/)
[![Filament](https://img.shields.io/badge/Filament-5.x-FDAE4B?logo=filament&logoColor=white)](https://filamentphp.com/)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-Ready-336791?logo=postgresql&logoColor=white)](https://postgresql.org/)
[![PHPStan](https://img.shields.io/badge/PHPStan-Level%205%20Clean-brightgreen?logo=php)](https://phpstan.org/)
[![Tests](https://img.shields.io/badge/Tests-Pest%20PHP%20100%25-brightgreen?logo=php)](https://pestphp.com/)
[![License](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)

*Sistem peladen skala Enterprise (Backend Monolith) yang menggerakkan platform tata kelola 5R dan Manajemen Inventaris Gudang Divisi Internal Audit PT INALUM — melayani Kiosk REST API, Mobile PWA Offline-First API, dan panel admin Filament dengan kontrol akses granular (RBAC).*

</div>

---

## Table of Contents

- [Overview](#overview)
- [Architecture](#architecture)
- [Features](#features)
- [Tech Stack](#tech-stack)
- [Prerequisites](#prerequisites)
- [Getting Started](#getting-started)
- [Environment Configuration](#environment-configuration)
- [Database](#database)
- [API Reference](#api-reference)
- [Admin Panel](#admin-panel)
- [Roles & Permissions](#roles--permissions)
- [Testing & QA](#testing--qa)
- [API Documentation](#api-documentation)
- [Deployment](#deployment)
- [Project Structure](#project-structure)
- [Contributing](#contributing)
- [Security](#security)

---

## Overview

`visual-board-inventory-backend` adalah tulang punggung peladen (*server-side backbone*) dari ekosistem digital **Sistem Visual Board 5R & Manajemen Inventaris** PT INALUM. Sistem ini melayani dua aplikasi klien sekaligus serta satu dasbor manajemen internal:
1. **Frontend Kiosk SPA (Layar TV Publik)**: Menayangkan metrik kepatuhan 5R, presensi hierarkis, mading digital, dan status ketersediaan barang secara *real-time*.
2. **Mobile PWA Inventaris (Gawai Seluler Auditor)**: Melayani pencatatan transaksi barang (*take*, *add*, *borrow*, *return*) dengan dukungan penuh operasi offline, sinkronisasi antrean outbox, dan pelacakan alat kerja audit.
3. **Filament Admin Panel**: Portal manajemen master data, verifikasi temuan 5R, audit buku besar (*ledger*), koreksi stok fisik, dan manajemen kredensial PIN pegawai.

Aplikasi ini dibangun menggunakan arsitektur **Domain-Driven Design (DDD)** yang ditopang oleh pola **Service–Repository**, berjalan di atas Laravel 13 dengan PHP 8.3+, dan menggunakan **PostgreSQL** untuk manajemen relasional, penguncian pesimistik, dan manipulasi tipe data `JSONB` yang ekstensif.

---

## Architecture

```text
┌────────────────────────────────────────────────────────────────────────┐
│                              Client Layer                              │
│   Frontend Kiosk SPA      Inventory Mobile PWA    Filament Admin Panel │
│    (React 19 / Vite)       (React 19 / Dexie)       (Internal Staff)   │
└────────────┬───────────────────────┬───────────────────────────┬───────┘
             │ REST API (Kiosk)      │ REST API (v1 / Sanctum)   │ Web Panel
┌────────────▼───────────────────────▼───────────────────────────▼───────┐
│                          Laravel Application                           │
│                                                                        │
│  ┌───────────┐  ┌───────────┐  ┌────────────────────────────────────┐  │
│  │   Routes  │  │  Filament │  │          Middleware Stack          │  │
│  │  api.php  │  │  Panel    │  │  (Sanctum ULID, CORS, DateHeader)  │  │
│  └─────┬─────┘  └─────┬─────┘  └────────────────────────────────────┘  │
│        │              │                                                │
│  ┌─────▼──────────────▼─────────────────────────────────────────────┐  │
│  │                     HTTP Controllers (API)                       │  │
│  │   KioskAttendance · VisualBoard · Bulletins · Inventory          │  │
│  │   Auth · Catalog · Transactions · Loans · Reports                │  │
│  └────────────────────────────┬─────────────────────────────────────┘  │
│                               │                                        │
│  ┌────────────────────────────▼─────────────────────────────────────┐  │
│  │                        Service Layer                             │  │
│  │  InventoryService · InventoryAuthService · VisualBoardService    │  │
│  │  Idempotensi (RFC 4122) · Alokasi FIFO · LockForUpdate           │  │
│  └────────────────────────────┬─────────────────────────────────────┘  │
│                               │                                        │
│  ┌────────────────────────────▼─────────────────────────────────────┐  │
│  │                     Repository Layer (Eloquent)                  │  │
│  │  Domains: HR · Inventory · Portal · VisualBoard                  │  │
│  └────────────────────────────┬─────────────────────────────────────┘  │
│                               │                                        │
│  ┌────────────────────────────▼─────────────────────────────────────┐  │
│  │                   Data / Infrastructure Layer                    │  │
│  │  PostgreSQL 14+ · Laravel Queue · File Storage (Public Media)    │  │
│  └──────────────────────────────────────────────────────────────────┘  │
└────────────────────────────────────────────────────────────────────────┘
```

---

## Features

### 📦 Inventory & Mobile PWA Engine (API v1)
- **Autentikasi Pegawai Sanctum ULID** — Autentikasi nir-kata-sandi (*passwordless*) via *Namecode* dan 6-digit numeric PIN, proteksi *timing-attack* via dummy hash, *lockout guard* 15 menit jika 5x gagal, serta kewajiban pembaruan PIN perdana (*MustChangePinRoute*).
- **Idempotensi Transaksi Mutasi (RFC 4122 v4)** — Pencegahan mutasi stok ganda akibat fluktuasi sinyal gudang menggunakan penguncian baris pesimistik (`lockForUpdate()`) dan identifikasi unik `client_uuid`. Replay transaksi sah mengembalikan data asli dengan status aman `meta.replayed: true`.
- **Manajemen Peminjaman Aset & Retur FIFO** — Pelacakan peminjaman alat kerja audit (`asset_loans`), alokasi pengembalian bertahap/parsial secara *strictly First-In, First-Out*, serta validasi batas sisa pinjaman (`LOAN_EXCEEDS_OUTSTANDING`).
- **Sinkronisasi Katalog Diferensial (ETag 304)** — Penghematan *bandwidth* seluler dengan merespons `304 Not Modified` jika data master barang belum berubah.
- **Koreksi Stok Audit (Stock Opname)** — Aksi penyesuaian saldo barang di Filament dengan catatan justifikasi formal wajib audit (*audit trail*).
- **Kiosk Inventaris Publik** — Menyajikan ketersediaan stok fisik dan kuantitas unit yang sedang dipinjam (`/api/v1/inventory/kiosk/locations`) tanpa mengekspos data pribadi peminjam.

### 📊 Public Kiosk API (Visual Board)
- **Visual Board Metriks** — *Endpoint* terpusat untuk menarik gabungan skor audit bulanan, tren 5R, dan *Safety Streak Days* dari Telemetri, dilengkapi algoritma *multi-level sorting*.
- **Manajemen Area (Workstation)** — Menampilkan daftar area kerja pabrik beserta penugasan karyawan penjaganya.
- **Mading Digital (Bulletins)** — Menyuplai daftar pengumuman harian dengan dukungan pembaca PDF bawaan tahan blokir CORS.
- **Presensi Pintar (Kiosk Attendance)** — API status kehadiran (*hadir, sakit, cuti*) yang diurutkan secara hierarkis (Pimpinan ke Pelaksana) lengkap dengan foto profil.
- **Pelaporan Temuan (Abnormalities)** — Pengajuan temuan audit seketika dari lapangan dengan kemampuan unggah gambar kerusakan.

### 🛡️ Admin Panel (Filament)
- **Executive Dashboard** — Dasbor responsif dengan *widget* metrik statistik dinamis yang menghitung langsung dari basis data secara *real-time*.
- **Pusat Kendali Inventaris** — Pengelolaan master barang, rak gudang, pemantauan buku besar mutasi, dan penyesuaian stok fisik.
- **Manajemen Kredensial Pegawai** — Pengaturan PIN awal, reset PIN, buka kunci akun (*unlock*), dan pencabutan sesi token.
- **RBAC Super Ketat** — Hierarki kontrol peran dan izin (Super Admin, Inventory Admin, Manajer Area, Staf) yang dikelola lewat Filament Shield.

---

## Tech Stack

| Layer | Technology | Version |
|---|---|---|
| Language | PHP | ^8.3 |
| Framework | Laravel | ^13.x |
| Admin Panel | Filament | ^5.x |
| Database | PostgreSQL | 14+ |
| Authentication | Laravel Sanctum (ULID) | ^4.x |
| RBAC | Filament Shield | ^4.x |
| Media Storage | Spatie MediaLibrary | ^11.x |
| API Docs | Knuckles Scribe | ^5.x |
| Testing | Pest PHP | ^4.x |
| Static Analysis | PHPStan / Larastan | ^3.x (Level 5) |
| Code Style | Laravel Pint | ^1.x |

---

## Prerequisites

Pastikan peranti lunak berikut telah terpasang sebelum memulai:

| Tool | Minimum Version |
|---|---|
| PHP | 8.3+ |
| Composer | 2.x |
| Node.js | 20.x LTS (Untuk kompilasi aset Filament) |
| PostgreSQL | 14+ |

---

## Getting Started

### 1. Kloning Repositori

```bash
git clone https://github.com/Internal-Audit-Division-PT-Inalum/visual-board-inventory-backend.git
cd visual-board-inventory-backend
```

### 2. Instalasi Dependensi

```bash
composer install
npm install && npm run build
```

### 3. Konfigurasi Lingkungan

Gandakan fail `.env.example` menjadi `.env` dan hasilkan kunci aplikasi:

```bash
cp .env.example .env
php artisan key:generate
```

Sesuaikan konfigurasi database PostgreSQL Anda di dalam fail `.env`.

### 4. Setup Database, Seeding, & Penyimpanan

Jalankan migrasi basis data dan pembuatan tautan penyimpanan publik:

```bash
php artisan migrate:fresh --seed
php artisan storage:link
```

Untuk menyiapkan data demonstrasi dan pengujian UAT lengkap (mencakup 5 auditor UAT, 3 master rak fisik, 11 barang inventaris, dan 4 pinjaman berjalan dengan simulasi *aging* waktu):

```bash
php artisan db:seed --class=InventoryDemoSeeder
```

### 5. Jalankan Development Server

```bash
php artisan serve
```

Aplikasi backend akan aktif di `http://localhost:8000`.

---

## Environment Configuration

Sesuaikan variabel kritikal berikut di fail `.env` Anda:

```dotenv
APP_NAME="Visual Board & Inventory Backend"
APP_ENV=local
APP_KEY=
APP_URL=http://localhost:8000

# Database PostgreSQL
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=visual_board_inventory_db
DB_USERNAME=postgres
DB_PASSWORD=secret

# Storage
FILESYSTEM_DISK=public

# Queue & Cache
QUEUE_CONNECTION=database
CACHE_STORE=database
```

---

## Database

Sistem memecah tabel berdasarkan Domain Bisnis:

| Nama Tabel | Domain | Keterangan |
|---|---|---|
| `employees` | HR | Data karyawan lengkap dengan `hierarchy_level`, `pin`, dan status kunci |
| `items` | Inventory | Master barang gudang (tipe *consumable* vs *asset*, stok minimum) |
| `locations` | Inventory | Master lokasi rak gudang fisik (contoh: `RAK-ATK-01`) |
| `inventory_ledgers` | Inventory | Buku besar mutasi stok lengkap dengan `client_uuid` dan `occurred_at` |
| `asset_loans` | Inventory | Catatan peminjaman aset terbuka lengkap dengan status pelunasan |
| `asset_loan_returns` | Inventory | Riwayat pengembalian bertahap/parsial aset pinjaman |
| `personal_access_tokens` | Auth | Token akses bearer Sanctum berbasis relasi morph ULID |
| `general_documents` | VisualBoard | Dokumen terpusat (SOP, Struktur Organisasi, Peta Area) |
| `workstations` & `workstation_items` | VisualBoard | Pemetaan stasiun kerja dan asetnya |
| `monthly_schedules` | VisualBoard | Matriks jadwal piket 5R |
| `abnormalities` | VisualBoard | Temuan penyimpangan 5R (kondisi tidak normal) |
| `bulletins` | Portal | Berita mading digital Kiosk |

---

## API Reference

Base URL: `http://localhost:8000/api/v1`

### 📦 Inventory & Mobile PWA

| Method | Endpoint | Description |
|---|---|---|
| `POST` | `/auth/login` | Otentikasi auditor via Namecode + PIN (terproteksi *lockout* 15 menit) |
| `POST` | `/auth/logout` | Revokasi token sesi aktif |
| `GET` | `/auth/me` | Informasi profil sesi pegawai terautentikasi |
| `POST` | `/auth/pin/change` | Pembaruan PIN (mandatory pada login awal) |
| `GET` | `/inventory/catalog` | Katalog barang aktif dengan dukungan header ETag 304 |
| `POST` | `/inventory/items/{id}/transact` | Transaksi mutasi stok (*take*, *add*, *borrow*, *return*) dengan proteksi `client_uuid` |
| `GET` | `/inventory/loans/mine` | Daftar pinjaman alat aktif dan riwayat retur bertahap |
| `GET` | `/inventory/kiosk/locations` | Ketersediaan inventaris publik untuk layar TV Kiosk |
| `GET` | `/inventory/reports/low-stock` | Laporan paritas stok rendah untuk verifikasi rekonsiliasi |

### 📊 Visual Board Kiosk

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/visual-board/kiosk` | Mengambil seluruh matriks dan data metrik harian layar publik |
| `GET` | `/visual-board/workstations` | Menampilkan seluruh stasiun kerja dan karyawan yang ditugaskan |
| `POST` | `/visual-board/abnormalities` | Melaporkan temuan kondisi tidak normal (dengan foto) |

### 👥 HR & Attendance

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/hr/kiosk/attendance` | Mengambil data presensi berurut hierarki level kepemimpinan |
| `POST` | `/hr/kiosk/attendance/scan` | Menyimpan hasil pindaian QR Code kehadiran |

### 📢 Portal (Mading)

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/portal/bulletins` | Mendapatkan *carousel* pengumuman mading aktif |
| `GET` | `/portal/quick-links` | Tautan sistem eksternal untuk QR Code akses cepat |

---

## Admin Panel

Panel kontrol Filament dapat diakses melalui rute `/admin`.

**Akses Akun Default (Super Admin):**
- **Email:** `admin@admin.com`
- **Sandi:** `password`

### Fitur Dasbor Utama
- **Branding Korporat Resmi:** Terkonfigurasi dengan identitas resmi Divisi Internal Audit PT INALUM lengkap dengan logo resolusi tinggi dan favicon multi-resolusi.
- **Koreksi Stok Fisik (Stock Opname):** Penyesuaian stok transparan dengan pencatatan delta dan alasan audit wajib.
- **Manajemen PIN Pegawai:** Set PIN, reset PIN, buka kunci akun (*unlock*), dan cabut sesi aktif.
- **Pusat Kendali Dokumen:** Mengunggah file PDF yang langsung dibaca oleh penampil universal di Kiosk.

---

## Testing & QA

Proyek ini menerapkan *automated quality gate* berstandar enterprise sebelum kode digabungkan:

### 1. Jalankan Seluruh Pemeriksaan CI (Lint + Analyse + Test)
```bash
composer run ci
```

### 2. Static Analysis (PHPStan / Larastan Level 5)
```bash
composer run analyse
```

### 3. Code Style Enforcer (Laravel Pint)
```bash
composer run lint
```

### 4. Unit & Feature Testing (Pest PHP)
```bash
composer run test
```

---

## API Documentation

Dokumentasi otomatis menggunakan Scribe:
```bash
php artisan scribe:generate
```
Akses UI interaktif di: `http://localhost:8000/docs`

---

## Project Structure

```text
visual-board-inventory-backend/
├── app/
│   ├── Domains/                 # Pemisahan Entitas Model & Eloquent (DDD)
│   │   ├── HR/                  # Employee, Attendance
│   │   ├── Inventory/           # Item, Location, InventoryLedger, AssetLoan, AssetLoanReturn
│   │   ├── Portal/              # Bulletin, QuickLink
│   │   └── VisualBoard/         # Workstation, Schedule, Abnormality, GeneralDocument
│   ├── Filament/                # Antarmuka Admin Panel, Resources & Widgets
│   ├── Http/
│   │   ├── Controllers/Api/     # Endpoint Controller Khusus Kiosk & Mobile PWA
│   │   ├── Middleware/          # EnsureDateHeader, Sanctum Guards
│   │   ├── Requests/            # Validasi Payload & Idempotency Rules
│   │   └── Resources/           # Pembungkus Standardisasi JSON
│   ├── Repositories/            # Logic Kueri Database Terisolasi
│   └── Services/                # Logika Bisnis (InventoryService, InventoryAuthService)
├── database/
│   ├── migrations/              # Sejarah skema tabel
│   └── seeders/                 # Seeder esensial produksi & InventoryDemoSeeder
├── routes/
│   ├── api.php                  # Rute sentral API
│   ├── api/                     # Pemecahan rute per domain bisnis (inventory, auth, visual-board)
│   └── web.php
└── tests/                       # Repositori Pest PHP Testing (Feature & Unit)
```

---

<div align="center">
Sistem ini dirancang untuk bertahan dalam pengujian industri operasional tinggi. ⚙️ Divisi Internal Audit PT INALUM.
</div>
