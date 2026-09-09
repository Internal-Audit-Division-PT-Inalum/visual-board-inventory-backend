# AGENTS.md — Visual Board & Inventory Backend

Panduan operasional untuk agent coding (Antigravity/Gemini, Claude Code, atau tool
AGENTS.md-compatible lain) yang bekerja di repo ini. Untuk prinsip arsitektur, batasan
keras, status fase, dan istilah domain, baca `GEMINI.md` terlebih dahulu — file ini
fokus ke "bagaimana caranya", bukan "kenapa".

## Panduan Dasar
Selalu patuhi pedoman yang tertulis di dalam file ini dan `GEMINI.md` saat bekerja.

## Setup Environment

```sh
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed # Akan meng-generate tabel, permission (Filament Shield), dan Super Admin (admin@admin.com / password)
npm install && npm run build
```

## Command yang Wajib Dijalankan Sebelum Commit

```sh
./vendor/bin/pint --dirty        # format kode (Laravel Pint)
./vendor/bin/phpstan analyse     # static analysis (Larastan)
./vendor/bin/pest                # test suite
```

Untuk regenerate dokumentasi API setelah endpoint berubah bentuk:

```sh
php artisan scribe:generate
```

## Stack & Package Kunci

- Laravel 13, PHP 8.3
- Filament 5 + `bezhansalleh/filament-shield` (RBAC di admin panel)
- Sanctum (auth API)
- Spatie: `laravel-permission`, `laravel-medialibrary`, `laravel-activitylog`,
  `laravel-model-states` (sudah ter-install, **belum dipakai** — jangan asumsikan
  ada state machine aktif di model manapun saat ini), `laravel-query-builder`
- Pest 4 + `pest-plugin-arch` + `pest-plugin-laravel`, Larastan, Pint

> **PERINGATAN KRUSIAL SPATIE PERMISSION & SHIELD:**
> 1. Tabel `model_has_roles` dan `model_has_permissions` pada _migration_ bawaan Spatie telah **dimodifikasi secara kustom** untuk menggunakan tipe `$table->ulid('model_id')`. Jangan pernah menjalankan ulang `php artisan vendor:publish --tag="permission-migrations"` atau `shield:setup` karena akan merusak skema ini (kembali ke bigint) dan membuat sistem *crash* (Postgres Type Mismatch).
> 2. Kofigurasi *Super Admin* Filament Shield menggunakan `define_via_gate = true`. Hak akses penuh diberikan otomatis melalui Laravel Gate. **Dilarang** memanggil `Artisan::call('shield:generate')` di dalam `DatabaseSeeder.php`. Pembentukan *Super Admin* di seeder murni memakai OOP Eloquent sederhana.

## Konvensi Kode (ikuti pola yang sudah ada, jangan buat gaya baru)

- **Primary key**: ULID via trait `App\Shared\Concerns\HasUlid`, bukan auto-increment.
  Migration pakai `$table->ulid('id')->primary()`.
- **Response envelope**: selalu lewat `App\Shared\Responses\ApiResponse::success()`
  atau `::error()`. Tidak ada `response()->json()` mentah di controller.
- **Routing**: `routes/api.php` cuma me-require file per-domain di bawah prefix
  `v1`. Domain baru = buat `routes/api/{domain}.php` baru dan daftarkan di sana.
- **SoftDeletes**: dipakai konsisten di `Zone`, `InspectionCriteria`,
  `MonthlySchedule`, `Abnormality`. Tabel pivot/record granular seperti
  `ScheduleRecord` sengaja TIDAK pakai SoftDeletes — ikuti pola ini per tabel baru
  (tanyakan ke user kalau ragu, jangan menebak).
- **Kolom JSONB**: dipakai untuk data semi-terstruktur yang berubah-ubah shape-nya
  (`days_data`, `approval_data`). Jangan diubah jadi tabel relasional tanpa persetujuan.
- **Repository binding**: didaftarkan manual di `App\Providers\AppServiceProvider::register()`.
  Repository baru = interface di `Repositories/Contracts`, implementasi di
  `Repositories/Eloquent`, lalu `$this->app->bind(...)` di AppServiceProvider.
- **Race condition**: pola wajib adalah `DB::transaction(fn () => Model::where(...)
  ->lockForUpdate()->first() ...)`. Lihat `ScheduleService::updateDailyStatus()`
  sebagai referensi yang sudah benar dan konsisten di seluruh codebase.
- **Migration naming**: gunakan timestamp otomatis dari `php artisan make:migration`.
  Jangan edit timestamp migration yang sudah di-commit. Nama deskriptif:
  `create_{table}_table`, `add_{column}_to_{table}_table`,
  `alter_{column}_on_{table}_table`.
- **Error handling**: Service boleh melempar custom domain exception
  (lihat `GEMINI.md` §6). Controller TIDAK boleh menangkap exception sendiri —
  biarkan Global Exception Handler di `bootstrap/app.php` yang memformat response.
  Satu-satunya pengecualian: `try-catch` di Controller diperbolehkan jika Service
  melempar exception umum (`\Exception`) yang memang perlu diterjemahkan ke HTTP
  status spesifik secara kontekstual.
- **Logging**: hanya di Service layer. Gunakan `Log::info()` untuk operasi bisnis
  penting, `Log::warning()` untuk anomali, `Log::error()` untuk kegagalan. Selalu
  sertakan array konteks: `Log::info('message', ['key' => 'value'])`.
  Lihat `GEMINI.md` §8 untuk detail lengkap.
- **Cache key naming**: format `{domain}:{resource}:{identifier}`. Contoh:
  `visual-board:kiosk:dashboard`, `inventory:item:{ulid}:stock`.
  Cache hanya di Service layer. Lihat `GEMINI.md` §7 untuk strategi invalidasi.
- **Aturan Filament 5.7 (Backoffice UI)**:
  - **Separation of Concerns**: Jangan deklarasikan array `form()` dan `table()` di dalam class `...Resource.php` jika sudah ada direktori `Schemas/` dan `Tables/`. Lakukan modifikasi hanya di file form/table yang bersangkutan.
  - **Lokalisasi Harga Mati**: Seluruh komponen (form, tabel, filter) **WAJIB** memakai modifier `->label('Terjemahan Bahasa Indonesia')`. Tidak boleh ada bahasa Inggris untuk kolom kecuali ID.
  - **UX Tabel**: Kolom utama (seperti nama, kode, status) wajib memakai modifier `->searchable()` dan `->sortable()` untuk mempermudah pencarian oleh Admin.
  - **Pengelolaan Media/File**: Dilarang membuat sistem upload manual. Seluruh upload gambar/dokumen pada form Filament WAJIB menggunakan integrasi bawaan `\Filament\Forms\Components\SpatieMediaLibraryFileUpload`, dan Model bersangkutan wajib mengimplementasikan interface `Spatie\MediaLibrary\HasMedia` beserta trait `InteractsWithMedia`.

## Peta Direktori (taruh file baru di tempat yang tepat)

```
app/Domains/{Domain}/Models/          # Eloquent model per domain
app/Domains/{Domain}/Exceptions/      # Custom domain exceptions (§6 GEMINI.md)
app/Repositories/Contracts/           # Interface repository
app/Repositories/Eloquent/            # Implementasi repository
app/Services/{Domain}/                # Business logic
app/Http/Controllers/Api/{Domain}/    # Controller tipis
app/Http/Requests/{Domain}/           # FormRequest validasi
app/Http/Resources/Api/{Domain}/      # JSON Resource
app/Shared/                           # Concerns & Responses lintas-domain
database/migrations/                  # urut kronologis, jangan edit migration lama yang sudah jalan
database/factories/                   # satu factory per model
routes/api/{domain}.php               # route per domain
tests/Feature/Api/{Domain}/           # Pest feature test per domain
```

## Definition of Done — Setiap Modul/Endpoint Baru

1. Migration + Model + relasi Eloquent dibuat.
2. Repository interface + implementasi dibuat dan di-bind di `AppServiceProvider`.
3. Service berisi aturan bisnis (termasuk transaction/lock kalau relevan).
4. FormRequest untuk validasi input + Resource untuk output + Controller tipis.
5. Route terdaftar di `routes/api/{domain}.php` yang sesuai.
6. Pest feature test hijau di `tests/Feature/Api/{Domain}/`.
7. `pint --dirty` dan `phpstan analyse` bersih.
8. `php artisan scribe:generate` dijalankan ulang kalau bentuk endpoint berubah.

## Tugas Konkret yang Belum Dikerjakan (Fase 5 — API Endpoints)

Prioritas berikutnya adalah membangun API endpoints terpusat untuk melayani aplikasi frontend dan Kiosk:

1. **API Domain HR / Organization**:
   - Pembuatan FormRequests, Resources, dan API Controllers untuk Entitas Department, Employee, dan EmployeeAttendance.
   - Pembuatan routing (contoh: `routes/api/hr.php`).
2. **API Domain Portal**:
   - Endpoint read-only untuk mengambil data `Bulletin` yang aktif guna ditampilkan di mading digital.
   - Pembuatan routing (contoh: `routes/api/portal.php`).
3. **API Domain Visual Board 5R (Refaktor & Kiosk)**:
   - Endpoint (read) untuk menampilkan matriks harian 5R (berbasis kolom JSONB `days_data` pada `ScheduleRecord`) dan jadwal rotasi PIC.
   - Endpoint (write) untuk memvalidasi/melaporkan temuan (Abnormality) dari Kiosk dengan penyertaan tanggal target.
4. **Security, Caching, & Dokumentasi**:
   - Penambahan Custom Exceptions per domain di direktori `app/Domains/{Domain}/Exceptions`.
   - Penerapan Caching Strategy pada Service layer (terutama untuk query berat mading/Kiosk).
   - Pembuatan Feature Test komprehensif di `tests/Feature/Api/{Domain}`.
   - Eksekusi `php artisan scribe:generate` secara berkala untuk update dokumentasi Postman otomatis.

## Testing

- Setiap endpoint baru butuh Feature test, bukan cuma Unit test.
- Logika dengan race condition (`takeItem`, `updateDailyStatus`) butuh test yang
  memverifikasi lock/transaction bekerja (minimal assert stok tidak pernah minus
  setelah beberapa kali pemanggilan berurutan yang disimulasikan).
- Pakai factory (`database/factories/`), jangan insert manual ke DB di dalam test.

## Larangan Operasional

- Jangan jalankan `migrate:fresh` atau `migrate:rollback` di environment yang bukan lokal/testing.
- Jangan commit `.env`.
- Jangan menambah package composer/npm baru tanpa konfirmasi ke user.
