<div align="center">

<img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="320" alt="Laravel Logo" />

# visual-board-inventory-backend

**RESTful API & Sistem Manajemen — Visual Board 5R & Inventaris Gudang PT INALUM**

[![PHP](https://img.shields.io/badge/PHP-8.4+-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20?logo=laravel&logoColor=white)](https://laravel.com/)
[![Filament](https://img.shields.io/badge/Filament-5.x-FDAE4B?logo=filament&logoColor=white)](https://filamentphp.com/)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-Ready-336791?logo=postgresql&logoColor=white)](https://postgresql.org/)
[![Tests](https://img.shields.io/badge/Tests-Pest%20PHP-brightgreen?logo=php)](https://pestphp.com/)
[![License](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)

*Sistem peladen skala Enterprise (Backend Monolith) yang menggerakkan platform tata kelola 5R PT INALUM — melayani Kiosk REST API yang sangat cepat dan panel admin Filament dengan kontrol akses granular (RBAC).*

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

`visual-board-inventory-backend` adalah tulang punggung peladen (*server-side backbone*) dari proyek **Sistem Visual Board 5R & Manajemen Inventaris** PT INALUM. Sistem ini mengekspos *RESTful API* berversi yang dikonsumsi oleh aplikasi layar publik (*Frontend Kiosk*) dan menyediakan dasbor manajemen berbasis *Filament* yang digunakan oleh staf internal untuk mengelola master data, dokumen SOP, jadwal audit, dan inventaris gudang.

Aplikasi ini dibangun menggunakan arsitektur **Domain-Driven Design (DDD)** yang ditopang oleh pola **Service–Repository**, berjalan di atas Laravel 13 dengan PHP 8.4+, dan menggunakan **PostgreSQL** untuk manajemen relasional dan manipulasi tipe data `JSONB` yang ekstensif.

---

## Architecture

```text
┌────────────────────────────────────────────────────────────┐
│                     Client Layer                           │
│        Frontend Kiosk SPA        Filament Admin Panel      │
│        (React 19 / Vite)           (Internal Staff)        │
└────────────────┬───────────────────────┬───────────────────┘
                 │ REST API (v1)         │ Web Panel
┌────────────────▼───────────────────────▼───────────────────┐
│                     Laravel Application                     │
│                                                             │
│  ┌───────────┐  ┌───────────┐  ┌────────────────────────┐  │
│  │   Routes  │  │  Filament │  │   Middleware Stack     │  │
│  │  api.php  │  │  Panel    │  │  (Sanctum, CORS, dll.) │  │
│  └─────┬─────┘  └─────┬─────┘  └────────────────────────┘  │
│        │              │                                      │
│  ┌─────▼──────────────▼──────────────────────────────────┐  │
│  │                HTTP Controllers (API)                  │  │
│  │   KioskAttendance · KioskWorkstation · VisualBoard     │  │
│  │   PortalBulletins · InventoryLedger                    │  │
│  └──────────────────────┬────────────────────────────────┘  │
│                         │                                    │
│  ┌──────────────────────▼────────────────────────────────┐  │
│  │               Service Layer                           │  │
│  │  Business logic, validasi orkestrasi, dan             │  │
│  │  kalkulasi metrik dasbor.                             │  │
│  └──────────────────────┬────────────────────────────────┘  │
│                         │                                    │
│  ┌──────────────────────▼────────────────────────────────┐  │
│  │            Repository Layer (Eloquent)                 │  │
│  │  Mengimplementasikan RepositoryInterface per domain.  │  │
│  │  Kueri spesifik & isolasi akses ke Database.          │  │
│  └──────────────────────┬────────────────────────────────┘  │
│                         │                                    │
│  ┌──────────────────────▼────────────────────────────────┐  │
│  │             Data / Infrastructure Layer                │  │
│  │  PostgreSQL · Laravel Queue · File Storage (Public)    │  │
│  └────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
```

---

## Features

### Public Kiosk API (Frontend)
- **Visual Board Metriks** — *Endpoint* terpusat untuk menarik gabungan skor audit bulanan, tren 5R, dan *Safety Streak Days* dari Telemetri.
- **Manajemen Area (Workstation)** — *Endpoint* spesifik untuk menampilkan daftar area kerja pabrik beserta penugasan karyawan penjaganya.
- **Mading Digital (Bulletins)** — Menyuplai daftar *carousel/slider* pengumuman harian dengan dukungan pembaca PDF bawaan.
- **Presensi Pintar (Kiosk Attendance)** — API pelaporan status kehadiran (*hadir, sakit, cuti*) yang diurutkan secara hierarkis (dari Pimpinan ke Pelaksana) lengkap dengan URL foto profil.
- **Pelaporan Temuan (Abnormalities)** — Pengajuan temuan audit seketika dari lapangan dengan kemampuan unggah gambar kerusakan.

### Admin Panel (Filament)
- **Executive Dashboard** — Dasbor responsif dengan *widget* metrik statistik dinamis yang menghitung langsung dari pangkalan data secara *real-time*.
- **Manajemen Dokumen Tunggal (General Documents)** — Pengelolaan file PDF tersentralisasi untuk semua kebutuhan (SOP, Struktur Organisasi, Peta Area).
- **Penjadwalan 5R (Monthly Schedules)** — Matriks alokasi piket harian yang kompleks.
- **Inventaris Gudang (Inventory)** — Pencatatan logistik keluar-masuk barang, penataan rak, dan ambang batas ketersediaan barang.
- **RBAC Super Ketat** — Hierarki kontrol peran dan izin (Super Admin, Manajer Area, Staf) yang dikelola lewat Filament Shield.

### Platform & Infrastructure
- **Domain-Driven Design (DDD)** — Pemecahan kode ke dalam modul: `HR`, `VisualBoard`, `Portal`, dan `Inventory`.
- **API Resources** — Pembentukan respons JSON yang konsisten, aman, dan versi terkendali.
- **Background Jobs** — *Laravel Queue* untuk pemrosesan asinkron (kalkulasi tren 5R berbeban tinggi atau notifikasi).

---

## Tech Stack

| Layer | Technology | Version |
|---|---|---|
| Language | PHP | ^8.4 |
| Framework | Laravel | ^13.x |
| Admin Panel | Filament | ^5.x |
| Database | PostgreSQL | — |
| Authentication | Laravel Sanctum | ^4.x |
| RBAC | Filament Shield | ^3.x |
| API Docs | Knuckles Scribe | ^4.x |
| Testing | Pest PHP | ^3.x |
| Static Analysis | PHPStan | ^2.x |
| Code Style | Laravel Pint | ^1.x |

---

## Prerequisites

Pastikan peranti lunak berikut telah terpasang sebelum memulai:

| Tool | Minimum Version |
|---|---|
| PHP | 8.4+ |
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

Gandakan fail `.env.example` menjadi `.env` dan hasilkan kunci aplikasi.

```bash
cp .env.example .env
php artisan key:generate
```

Ubah konfigurasi *database* PostgreSQL Anda di dalam fail `.env`.

### 4. Setup Database & Penyimpanan

Sistem ini dirancang untuk beroperasi di lingkungan produksi yang steril. Perintah ini **TIDAK** akan memasukkan data tiruan (dummy data), melainkan hanya struktur tabel dan kredensial dasar Super Admin.

```bash
php artisan migrate:fresh --seed
php artisan storage:link
```

### 5. Jalankan Development Server

```bash
php artisan serve
```

---

## Environment Configuration

Sesuaikan variabel kritikal berikut di fail `.env` Anda:

```dotenv
APP_NAME="Visual Board Backend"
APP_ENV=local
APP_KEY=
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=inalum_visual_board
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

Sistem memecah tabel berdasarkan Domain Bisnis. Beberapa tabel krusial meliputi:

| Nama Tabel | Domain | Keterangan |
|---|---|---|
| `employees` | HR | Data karyawan lengkap dengan `hierarchy_level` |
| `general_documents` | VisualBoard | Dokumen terpusat (SOP, Struktur, Peta Area) |
| `workstations` & `workstation_items` | VisualBoard | Pemetaan stasiun kerja dan asetnya |
| `monthly_schedules` | VisualBoard | Matriks jadwal piket 5R |
| `abnormalities` | VisualBoard | Temuan penyimpangan 5R (kondisi tidak normal) |
| `bulletins` | Portal | Berita mading digital Kiosk |
| `inventory_items` | Inventory | Daftar logistik gudang |

---

## API Reference

Base URL: `http://localhost:8000/api`

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

*(Dokumentasi API interaktif penuh dapat di-generate melalui Scribe).*

---

## Admin Panel

Panel kontrol Filament dapat diakses melalui rute `/admin`.

**Akses Akun Default (Super Admin):**
- **Email:** `admin@admin.com`
- **Sandi:** `password`

### Fitur Dasbor Utama
- **Executive Stat Cards:** Metrik dihitung secara dinamis dari tabel `MonthlySchedule` (Misal: "Jadwal Inspeksi Disetujui").
- **Manajemen Hierarki Karyawan:** Pemeringkatan level pemimpin agar API merespons dengan presisi.
- **Pusat Kendali Dokumen (General Documents):** Mengunggah file .pdf yang langsung dibaca oleh penampil universal di Frontend.

---

## Testing & QA

Proyek ini menjunjung tinggi jaminan kualitas (QA) sebelum rilis (*Production-ready*).

### 1. Static Analysis (PHPStan)
Mendeteksi *fatal error* atau inkonsistensi tipe data:
```bash
php vendor/bin/phpstan analyse app/ tests/ --memory-limit=2G
```

### 2. Code Style Enforcer (Laravel Pint)
Memaksa seluruh basis kode sesuai standar PSR-12 secara piksel sempurna:
```bash
php vendor/bin/pint
```

---

## API Documentation

Dokumentasi otomatis menggunakan Scribe:
```bash
php artisan scribe:generate
```
Akses UI di: `http://localhost:8000/docs`

---

## Project Structure

```text
visual-board-inventory-backend/
├── app/
│   ├── Domains/                 # Pemisahan Entitas Model & Eloquent (DDD)
│   │   ├── HR/
│   │   ├── Inventory/
│   │   ├── Portal/
│   │   └── VisualBoard/
│   ├── Filament/                # Antarmuka Admin Panel & Widgets
│   ├── Http/
│   │   ├── Controllers/Api/     # Endpoint Controller Khusus Kiosk
│   │   ├── Requests/            # Validasi Payload
│   │   └── Resources/           # Pembungkus Standardisasi JSON
│   ├── Repositories/            # Logic Kueri Database Terisolasi (Contracts & Eloquent)
│   └── Services/                # Logika Bisnis & Kalkulasi Metrik
├── database/
│   ├── migrations/              # Sejarah skema tabel
│   └── seeders/                 # Hanya data esensial produksi (Super Admin)
├── routes/
│   ├── api.php                  # Rute sentral API
│   ├── api/                     # Pemecahan rute per domain bisnis
│   └── web.php
└── tests/                       # Repositori Pest PHP Testing
```

---

<div align="center">
Sistem ini dirancang untuk bertahan dalam pengujian industri operasional tinggi. ⚙️ PT INALUM Internal Audit.
</div>
