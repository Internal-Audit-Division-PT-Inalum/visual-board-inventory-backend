# Visual Board & Inventory System — Agent Constitution

Ini adalah rules file utama untuk agent (Gemini/Antigravity) yang bekerja di repo
`visual-board-inventory-backend`. Baca file ini SEBELUM membuat perubahan apa pun.
Untuk perintah operasional (setup, test, lint, definition-of-done), lihat `AGENTS.md`.

## 1. Konteks Proyek

Backend Laravel untuk sistem internal manufaktur (client: PT Inalum, Divisi IIA —
Internal Audit) dengan **lima domain bisnis**:

- **Core** — Fondasi autentikasi, otorisasi (RBAC), dan manajemen pengguna.
- **Visual Board 5R** — papan kepatuhan 5R digital (Ringkas, Rapi, Resik, Rawat, Rajin)
  yang ditampilkan di TV Kiosk pabrik + dashboard tren masalah (Abnormality).
  Mencakup penjadwalan bulanan, ceklis harian, rotasi PIC, dan pelaporan temuan.
- **Inventory ATK** — sistem keluar-masuk barang (consumable & asset) via scan QR
  di smartphone, dengan audit trail lengkap.
- **HR (Human Resources)** — struktur organisasi hierarkis (Departemen), profil
  pegawai, dan pencatatan presensi harian.
- **Portal** — mading digital / papan pengumuman internal untuk siaran informasi
  dan bulletin ke seluruh area pabrik.

Konsumen API: (1) TV Kiosk read-only (auto-refresh tiap 15 detik), (2) Filament
admin panel untuk staff Divisi IIA, (3) React frontend (rencana, belum dibangun).

## 2. Prinsip Arsitektur — Non-Negotiable

Setiap fitur baru WAJIB mengikuti alur bottom-up ini, tanpa loncat layer:

```
Migration → Model (app/Domains/{Domain}/Models) → Repository (Contract + Eloquent)
→ Service (business logic) → Http (FormRequest → Controller tipis → Resource)
```

Aturan tanggung jawab per layer (jangan dilanggar walau demi "kepraktisan"):

- **Repository**: HANYA akses data (CRUD, query builder). Tidak boleh ada validasi
  bisnis, kalkulasi, atau caching di sini.
- **Service**: tempat "otak" aplikasi — validasi aturan bisnis, transaksi database,
  caching, kalkulasi/agregasi, dan **pengiriman notifikasi**. Controller tidak boleh
  punya logika bisnis sendiri.
- **Controller**: tipis. Hanya: terima request tervalidasi → panggil Service →
  bungkus hasil dengan `App\Shared\Responses\ApiResponse` → return.
- **FormRequest**: satu-satunya tempat validasi input client.
- **Resource**: satu-satunya tempat pembentukan format JSON output.
- Operasi yang rawan race condition (pengurangan stok, update record bersamaan)
  WAJIB pakai `DB::transaction()` + `Model::lockForUpdate()`. Contoh referensi yang
  sudah benar: `App\Services\VisualBoard\ScheduleService::updateDailyStatus()`.

## 3. Status Proyek (update manual setiap fase berubah)

| Fase | Status | Catatan |
|---|---|---|
| 1. Fondasi & Arsitektur | ✅ Selesai | Domain dirs, ApiResponse, BaseRepository, Sanctum, Media config sudah ada |
| 2. Domain Inventory | ✅ Selesai | `locations`, `items`, `inventory_ledgers`: Selesai dan ditest. |
| 3. Domain Visual Board | ✅ Selesai | Zone, InspectionCriteria, MonthlySchedule, ScheduleRecord, Abnormality selesai. |
| 4. Backoffice Filament | ✅ Selesai | Panel + Shield terpasang, 12 Resources dibuat untuk semua domain. |
| 4.5 Ekstensi Domain (HR & Portal) | ✅ Selesai | Fondasi HR, Portal, & rotasi PIC terpasang (Migration, Model, Repo, Service, Filament). |
| 4.6 Dummy Data Seeding | ✅ Selesai | Modular Seeders + Factory untuk seluruh domain. Environment Guard aktif. |
| **4.7 Penutupan Gap Konvensional → Digital** | ✅ Selesai | Menyesuaikan arsitektur agar 100% merepresentasikan formulir kertas 5R PT Inalum. |
| 5. API Endpoints & Postman | 🔄 Prioritas Aktif | Scribe sudah siap, endpoint Kiosk belum lengkap karena menunggu Fase 4.7 selesai. |

Prioritas kerja saat ini: **Fase 5 (API Endpoints & Postman)**
berdasarkan Gap Analysis terhadap 9 dokumen kertas konvensional PT Inalum.

## 4. Batasan & Larangan Keras

- **Jangan** menaruh business logic di Controller atau Repository — pindahkan ke Service.
- **Jangan** menambahkan `auth:sanctum` ke endpoint kiosk (`/v1/visual-board/kiosk`).
  Ini SENGAJA publik read-only untuk TV pabrik, bukan celah keamanan yang perlu ditutup.
- **Jangan** mengubah struktur kolom JSONB (`monthly_schedules.approval_data`,
  `schedule_records.days_data`) menjadi tabel relasional tanpa migration + backfill
  plan yang eksplisit disetujui manusia — ini keputusan desain sadar, bukan kebetulan.
  Lihat §5A untuk kontrak data JSONB yang sah.
- **Jangan** memakai `response()->json()` mentah di controller — selalu lewat
  `App\Shared\Responses\ApiResponse::success()`/`::error()`.
- Perubahan pada skema database (migration baru/ubah kolom) yang bisa memengaruhi
  data produksi harus diberi tahu ke user sebelum dijalankan, bukan langsung `migrate`.
- **Jangan** mengirim notifikasi dari Controller, Observer, atau Middleware.
  Notifikasi hanya boleh dikirim dari **Service layer** (lihat §8A).

## 5. Istilah Domain (jaga konsistensi penamaan di kode & komentar)

### Taksonomi 5R (5S versi Indonesia)

| Kode | Nama | Definisi | Sifat Inspeksi |
|---|---|---|---|
| R-1 | **RINGKAS** | Membuang barang yang tidak diperlukan | Fisik (ceklis per item) |
| R-2 | **RAPI** | Menyusun barang agar mudah diakses; deteksi penyimpangan | Fisik (ceklis per item) |
| R-3 | **RESIK** | Membersihkan area kerja secara menyeluruh | Fisik (ceklis per item) |
| R-4 | **RAWAT** | Menjaga standar kondisi 3R secara konsisten | Perilaku/habitual |
| R-5 | **RAJIN** | Membudayakan kedisiplinan; pencegahan degradasi | Perilaku/habitual |

> R-1 hingga R-3 adalah standar yang **secara langsung bisa diceklis** pada formulir
> Schedule/Check Sheet. R-4 dan R-5 bersifat perilaku dan biasanya dinilai secara
> periodik, bukan per-item harian. Kolom `inspection_criterias.criteria_code` mampu
> menampung semua kode R-1 sampai R-5.

### Simbol Status Harian pada Check Sheet / Schedule Record

| Simbol | Key di JSONB `days_data` | Arti |
|---|---|---|
| ○ | `rencana` | Direncanakan/dijadwalkan |
| ◎ | `ok_tanpa_5r` | Sudah OK, tidak perlu tindakan 5R |
| △ | `ok_dengan_5r` | OK setelah melakukan tindakan 5R |
| ☒ | `abnormal` | Ditemukan temuan abnormality |

### Hierarki Peran Operasional 5R

| Peran | Role RBAC | Tanggung Jawab Utama | Durasi Aktivitas |
|---|---|---|---|
| Petugas/PIC (Pelaksana) | `pelaksana_5r` | Melakukan pengecekan fisik 5R harian | 30 menit |
| Staff (Penyelia) | `staff_penyelia` | Memverifikasi hasil 5R; menindaklanjuti temuan | 15 menit |
| Managerial Staff (MS) | `managerial_staff` | Memonitor di SSM-SEP; koordinasi lintas seksi | Ongoing |
| Administrator Sistem | `super_admin` | Full access ke seluruh panel Filament | — |

### Glosarium Umum

- **5R** = versi Indonesia dari 5S. Lihat tabel taksonomi di atas.
- **PIC** = Person In Charge. Pada rotasi harian 5R, terdapat **PIC Utama** dan
  **PIC Pengganti**. PIC Utama di-assign per Zona (`zones.pic_utama_id`) dan bisa
  di-override per hari melalui `schedule_pics`.
- **Abnormality** = temuan masalah di lapangan, dilacak dari `open` → `in_progress`
  → `resolved` dengan target penyelesaian dan aktualisasi (`target_date`,
  `actual_resolution_date`). Memiliki dua peran terkait:
  - `pic_id` = penanggung jawab **penyelesaian** temuan
  - `reported_by_id` = orang yang **menemukan** temuan di lapangan
- **Kaizen** = Perbaikan berkelanjutan. Jika `is_kaizen = true`, temuan dianggap
  sebagai peluang improvement. Konten laporan disimpan di `kaizen_report`.
- **Check Sheet** = formulir ceklis harian yang dibawa Pelaksana saat pengecekan
  fisik. Secara data merupakan **subset harian** dari `schedule_records.days_data`.
- **Schedule Bulanan** = tabel grid 31 hari yang menampilkan perencanaan dan
  realisasi per kriteria inspeksi per zona per bulan.
- **Departemen** = Struktur organisasi hierarkis. Entitas ini mengelompokkan Karyawan.
- **Mading / Portal Hub** = papan informasi digital untuk karyawan, berisi
  *Attendance* dan *Bulletin*.
- **Ledger** = jejak audit setiap pengambilan/penambahan barang di Inventory.
- **Akses Dashboard Admin**: Sistem wajib menggunakan integrasi `spatie/laravel-permission`
  (melalui `bezhanSalleh/filament-shield`). Pembuatan user `Super Admin`, role operasional
  (`pelaksana_5r`, `staff_penyelia`, `managerial_staff`), dan seluruh permission Resource
  mutlak dilakukan secara otomatis saat eksekusi `php artisan migrate:fresh --seed` via
  `DatabaseSeeder`. Dilarang meminta User menjalankan command seeding manual.
- **Dilarang memakai raw SQL** untuk data bisnis, gunakan Eloquent/Repository.
- **Single Source of Truth**: Dokumentasi ini (GEMINI.md dan AGENTS.md) adalah acuan
  utama. Dilarang mengubah aturan tanpa persetujuan arsitek/User.

## 5A. Kontrak Data JSONB (Data Shape Contracts)

Kolom JSONB digunakan untuk data semi-terstruktur yang sifatnya dinamis. Berikut
adalah **kontrak wajib** untuk setiap kolom JSONB yang ada — agent dilarang mengubah
shape ini tanpa persetujuan:

### `schedule_records.days_data`
```json
{
    "1": "rencana",
    "2": "ok_tanpa_5r",
    "8": "abnormal",
    "15": "ok_dengan_5r"
}
```
- Key: string tanggal "1" sampai "31"
- Value: salah satu dari 4 status — `rencana`, `ok_tanpa_5r`, `ok_dengan_5r`, `abnormal`
- Tanggal yang tidak diisi berarti **tidak ada aktivitas** di hari itu (libur/tidak dijadwalkan)

### `monthly_schedules.approval_data`
```json
{
    "pic_signed":     { "user_id": "01abc...", "signed_at": "2026-09-10T08:00:00Z" },
    "staff_signed":   { "user_id": "01def...", "signed_at": "2026-09-10T09:00:00Z" },
    "manager_signed": { "user_id": "01ghi...", "signed_at": "2026-09-10T10:00:00Z" },
    "vp_signed":      { "user_id": "01jkl...", "signed_at": "2026-09-10T11:00:00Z" }
}
```
- Setiap level approval bersifat **opsional** (nullable) sampai ditandatangani
- Urutan approval: PIC → Staff → Manager → VP
- `user_id` = ULID user yang menandatangani, `signed_at` = timestamp ISO-8601

## 6. Error Handling Strategy

Service layer adalah satu-satunya tempat yang boleh melempar *domain exception*.
Controller dan Repository **tidak boleh** melempar exception bisnis sendiri.

- Buat custom exception per domain di `app/Domains/{Domain}/Exceptions/`.
  Contoh: `InsufficientStockException`, `ScheduleAlreadyLockedException`.
- Custom exception harus meng-extend base class `RuntimeException` atau
  `DomainException` PHP bawaan. Tidak perlu membuat base exception sendiri
  kecuali ada kebutuhan shared behavior lintas domain.
- Service melempar exception, **Global Exception Handler** di `bootstrap/app.php`
  yang menangkap dan mengubahnya menjadi `ApiResponse::error()` dengan HTTP
  status code yang sesuai.
- Pola wajib di Service:
  ```php
  // Service — melempar exception
  if ($item->current_stock < $quantity) {
      throw new InsufficientStockException(
          "Stok {$item->name} tidak mencukupi (sisa: {$item->current_stock})."
      );
  }
  ```
- Pola wajib di `bootstrap/app.php`:
  ```php
  // Handler — menangkap dan memformat
  $exceptions->render(function (InsufficientStockException $e, Request $request) {
      if ($request->is('api/*') || $request->expectsJson()) {
          return ApiResponse::error($e->getMessage(), [], 422);
      }
  });
  ```
- **Jangan** menelan (*swallow*) exception dengan `try-catch` kosong di manapun.
  Jika harus di-catch, selalu log lalu re-throw atau kembalikan error response.

## 7. Caching Convention

Caching hanya boleh terjadi di **Service layer**. Repository dan Controller
dilarang menyentuh `Cache` facade secara langsung.

### Penamaan Cache Key (Wajib Konsisten)

Format: `{domain}:{resource}:{identifier}:{optional_qualifier}`

Contoh:
```
visual-board:kiosk:dashboard          → data agregat kiosk
visual-board:abnormality:summary:2026-09  → ringkasan bulanan
inventory:item:{ulid}:stock            → stok real-time item
```

### Strategi Invalidasi

- **TTL-based (Time To Live)**: digunakan untuk data agregat read-heavy yang
  tidak perlu real-time sempurna. Contoh: dashboard kiosk, 60 detik.
- **Event-based flush**: digunakan ketika data HARUS langsung berubah setelah
  operasi tulis. Service yang melakukan mutasi data bertanggung jawab memanggil
  `Cache::forget('cache-key')` di akhir operasinya.
- Pilihan strategi per kasus harus didokumentasikan dalam komentar di Service
  yang bersangkutan. Contoh:
  ```php
  // TTL 60s — data kiosk boleh terlambat maksimal 1 menit
  return Cache::remember('visual-board:kiosk:dashboard', 60, fn () => ...);
  ```

### Larangan

- Jangan cache data per-user (session-specific) di shared cache.
- Jangan cache hasil query yang sudah di-paginate — cache hanya data agregat
  atau data master yang jarang berubah.

## 8. Logging & Observability

Gunakan Laravel `Log` facade dengan level yang tepat. Tujuan: ketika ada
insiden di produksi pabrik, tim bisa melacak kronologi kejadian tanpa harus
membaca kode sumber.

### Kapan Harus Menulis Log

| Level | Kapan Digunakan | Contoh |
|---|---|---|
| `Log::info()` | Operasi bisnis penting berhasil | Barang diambil dari inventory, jadwal dikunci/di-approve |
| `Log::warning()` | Situasi tidak normal tapi bukan error | Stok item mendekati nol, percobaan akses endpoint tanpa auth |
| `Log::error()` | Kegagalan yang memerlukan tindakan | Exception tertangkap, transaksi DB gagal |

### Format Log (Structured Logging)

Selalu sertakan konteks terstruktur sebagai parameter kedua:
```php
Log::info('Inventory: item taken', [
    'item_id'   => $item->id,
    'user_id'   => auth()->id(),
    'quantity'  => $quantity,
    'remaining' => $item->current_stock - $quantity,
]);
```

### Larangan

- **Jangan** log data sensitif: password, token, nomor KTP.
- **Jangan** log di dalam loop tight (ribuan iterasi) — ini akan membanjiri
  storage. Log di awal dan akhir batch operation, bukan per iterasi.
- **Jangan** log di Repository layer — logging hanya terjadi di Service layer
  (konsisten dengan prinsip: Repository murni akses data).

## 8A. Notifikasi & Eskalasi

Notifikasi antar-role dipicu **hanya dari Service layer** menggunakan
`Filament\Notifications\Notification::make()->sendToDatabase()`.

### Skenario Notifikasi yang Sah

| Pemicu | Penerima | Deskripsi |
|---|---|---|
| Pelaksana melaporkan abnormality | `staff_penyelia` | Temuan baru perlu diverifikasi |
| Staff memverifikasi temuan sulit | `managerial_staff` | Eskalasi — perlu koordinasi lintas seksi |
| Abnormality resolved | Pelaksana (`reported_by_id`) | Temuan yang ia temukan telah diselesaikan |
| Jadwal bulanan di-approve | `pelaksana_5r` pada zona terkait | Jadwal baru siap dieksekusi |

### Larangan Notifikasi

- **Jangan** mengirim notifikasi dari Controller, Observer, atau Middleware.
- **Jangan** mengirim notifikasi di dalam loop — batch-kan jika perlu.
- **Jangan** mengirim notifikasi untuk operasi CRUD biasa (create/update/delete)
  kecuali ada implikasi bisnis yang eksplisit (lihat tabel di atas).

## 9. Kapan Harus Berhenti dan Bertanya ke User

- Sebelum menjalankan migration yang mengubah/menghapus kolom pada tabel yang sudah
  berisi data (bukan tabel baru).
- Sebelum mengubah kontrak `RepositoryInterface`/`ServiceInterface` yang sudah dipakai
  di lebih dari satu tempat.
- Sebelum menambah dependency composer/npm baru.
- Sebelum mengubah kontrak data JSONB yang didefinisikan di §5A.
- Ketika instruksi di dokumen roadmap (yang dilampirkan user) tampak bertentangan
  dengan kondisi kode yang sudah ada — laporkan selisihnya, jangan diam-diam pilih salah satu.

## 10. Acuan Dokumen Konvensional PT Inalum

Sistem ini dibangun untuk mendigitalisasi 9 dokumen kertas konvensional berikut.
Setiap perubahan arsitektur harus **mempertahankan atau meningkatkan** kesesuaian
pemetaan terhadap dokumen-dokumen ini:

| # | Nama Dokumen | Domain Digital | Entitas Utama |
|---|---|---|---|
| 1 | Schedule Aktifitas 5R (Zona 1) | VisualBoard | `monthly_schedules` + `schedule_records` |
| 2 | Schedule Aktifitas 5R (Zona 2) | VisualBoard | `monthly_schedules` + `schedule_records` |
| 3 | Schedule Aktifitas 5R (Zona 3) | VisualBoard | `monthly_schedules` + `schedule_records` |
| 4 | Laporan & Progress Temuan Abnormality | VisualBoard | `abnormalities` |
| 5 | Trend Abnormality 5S SIA-SCQ | VisualBoard | `abnormalities` (agregat) |
| 6 | Basic Rule Pelaksanaan 5R | Core (RBAC) | `roles`, `users` |
| 7 | Check Sheet Aktifitas 5R (Zona 1&2) | VisualBoard | `schedule_records.days_data` (subset harian) |
| 8 | Check Sheet Aktifitas 5R (Zona 3&4) | VisualBoard | `schedule_records.days_data` (subset harian) |
| 9 | Flow Proses Aktifitas 5R | Lintas Domain | Workflow MS → Staff → Pelaksana |

> Dokumen analisis gap lengkap tersedia sebagai artifact proyek. Referensikan
> dokumen tersebut sebelum merancang fitur baru yang menyentuh domain VisualBoard.
