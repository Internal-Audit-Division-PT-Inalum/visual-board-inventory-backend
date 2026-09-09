# Visual Board & Inventory System — Agent Constitution

Ini adalah rules file utama untuk agent (Gemini/Antigravity) yang bekerja di repo
`visual-board-inventory-backend`. Baca file ini SEBELUM membuat perubahan apa pun.
Untuk perintah operasional (setup, test, lint, definition-of-done), lihat `AGENTS.md`.

## 1. Konteks Proyek

Backend Laravel untuk sistem internal manufaktur (client: PT Inalum) dengan dua domain:

- **Visual Board 5R** — papan kepatuhan 5R digital (Ringkas, Rapi, Resik, Rawat, Rajin)
  yang ditampilkan di TV Kiosk pabrik + dashboard tren masalah (Abnormality).
- **Inventory ATK** — sistem keluar-masuk barang (consumable & asset) via scan QR
  di smartphone, dengan audit trail lengkap.

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
  caching, kalkulasi/agregasi. Controller tidak boleh punya logika bisnis sendiri.
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
| 1. Fondasi & Arsitektur | Selesai | Domain dirs, ApiResponse, BaseRepository, Sanctum, Media config sudah ada |
| 2. Domain Inventory | Selesai | `locations`, `items`, `inventory_ledgers`: Model, Migration, Repository, Service (dengan lockForUpdate), Controller, dan Route sudah selesai dan ditest. |
| 3. Domain Visual Board | Inti selesai | Zone, InspectionCriteria, MonthlySchedule, ScheduleRecord, Abnormality: Model+Migration+Repository+Service+Controller lengkap |
| 4. Backoffice Filament | Sebagian | Panel + Shield terpasang, belum ada satupun Filament Resource |
| 5. Optimasi & Dokumentasi | Sebagian | Caching kiosk (60s) & Scribe sudah jalan; exception handling terpusat sudah diimplementasi & diverifikasi |

Prioritas kerja saat ini: **Fase 4 (Filament Resources)**
adalah yang paling tertinggal dari rencana.

## 4. Batasan & Larangan Keras

- **Jangan** menaruh business logic di Controller atau Repository — pindahkan ke Service.
- **Jangan** menambahkan `auth:sanctum` ke endpoint kiosk (`/v1/visual-board/kiosk`).
  Ini SENGAJA publik read-only untuk TV pabrik, bukan celah keamanan yang perlu ditutup.
- **Jangan** mengubah struktur kolom JSONB (`monthly_schedules.approval_data`,
  `schedule_records.days_data`) menjadi tabel relasional tanpa migration + backfill
  plan yang eksplisit disetujui manusia — ini keputusan desain sadar, bukan kebetulan.
- **Jangan** menimpa `AGENTS.md`/`CLAUDE.md` begitu saja — keduanya saat ini berisi
  stub instalasi Laravel Boost. Lihat catatan penanganan di `AGENTS.md`.
- **Jangan** memakai `response()->json()` mentah di controller — selalu lewat
  `App\Shared\Responses\ApiResponse::success()`/`::error()`.
- Perubahan pada skema database (migration baru/ubah kolom) yang bisa memengaruhi
  data produksi harus diberi tahu ke user sebelum dijalankan, bukan langsung `migrate`.

## 5. Istilah Domain (jaga konsistensi penamaan di kode & komentar)

- **5R** = versi Indonesia dari 5S (Ringkas, Rapi, Resik, Rawat, Rajin).
- Status harian pada `ScheduleRecord.days_data`: `rencana`, `ok`, `ok_5r`, `abnormal`, `libur`.
- **PIC** = Person In Charge. Hierarki approval jadwal: PIC → Staf → M → VP.
- **Mading** = papan informasi fisik pabrik, direpresentasikan sebagai gambar/poster
  di layar kiosk (dikelola lewat Spatie Media Library dari Filament).
- **Abnormality** = temuan masalah di lapangan, dilacak dari `open` → `in_progress` → `resolved`
  dengan `progress_percentage` 0–100. Jika `is_kaizen = true`, temuan ini dianggap perbaikan
  berkelanjutan yang layak dilaporkan sebagai Kaizen Report.
- **Ledger** (Fase 2, akan datang) = jejak audit setiap pengambilan/penambahan barang
  di Inventory — siapa mengambil apa, kapan, berapa banyak.

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

## 9. Kapan Harus Berhenti dan Bertanya ke User

- Sebelum menjalankan migration yang mengubah/menghapus kolom pada tabel yang sudah
  berisi data (bukan tabel baru).
- Sebelum mengubah kontrak `RepositoryInterface`/`ServiceInterface` yang sudah dipakai
  di lebih dari satu tempat.
- Sebelum menambah dependency composer/npm baru.
- Ketika instruksi di dokumen roadmap (yang dilampirkan user) tampak bertentangan
  dengan kondisi kode yang sudah ada — laporkan selisihnya, jangan diam-diam pilih salah satu.
