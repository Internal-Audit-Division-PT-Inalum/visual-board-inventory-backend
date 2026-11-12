<?php

namespace Database\Seeders;

use App\Domains\Core\Models\User;
use App\Domains\HR\Models\Department;
use App\Domains\HR\Models\Employee;
use App\Domains\Inventory\Models\AssetLoan;
use App\Domains\Inventory\Models\InventoryLedger;
use App\Domains\Inventory\Models\Item;
use App\Domains\Inventory\Models\Location;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * Seeder Data Demo & UAT Inventaris Internal Audit PT INALUM (Task P7.4)
 *
 * Karakteristik Enterprise:
 * 1. Environment Guard: Terproteksi dari eksekusi di lingkungan produksi.
 * 2. Idempotent: Menggunakan firstOrCreate/updateOrCreate sehingga aman dijalankan berulang.
 * 3. Non-Destructive: Tidak melakukan TRUNCATE atau menghapus data riil Visual Board.
 * 4. Realistic Dataset: Menyediakan akun auditor, lokasi rak fisik, katalog barang berbagai kondisi,
 *    dan riwayat pinjaman berstatus aktif serta overdue untuk pengujian aging.
 */
class InventoryDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command->error('InventoryDemoSeeder DILARANG dijalankan di lingkungan produksi!');

            return;
        }

        $this->command->info('Memulai seeding data demo & UAT Inventaris PT INALUM...');

        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $pelaksanaRole = Role::firstOrCreate(['name' => 'pelaksana_5r', 'guard_name' => 'web']);

        $superAdmin = User::where('email', 'admin@admin.com')->first();
        if ($superAdmin && ! $superAdmin->hasRole('super_admin')) {
            $superAdmin->assignRole($superAdminRole);
        }

        $dept = Department::where('name', 'like', '%Internal Audit%')->first()
            ?? Department::first()
            ?? Department::create([
                'name' => 'SIA (Smelter Internal Audit)',
                'division' => 'IIA (Inalum Internal Audit)',
                'is_active' => true,
            ]);

        $existingEmployees = Employee::all();
        foreach ($existingEmployees as $existingEmp) {
            $cleanNamecode = strtoupper(trim($existingEmp->namecode));
            $user = User::firstOrCreate(
                ['email' => strtolower($cleanNamecode) . '@inalum.id'],
                [
                    'name' => $existingEmp->name,
                    'password' => Hash::make('password123'),
                    'email_verified_at' => now(),
                ]
            );

            if ($user->roles()->count() === 0) {
                $user->assignRole($pelaksanaRole);
            }

            $existingEmp->update([
                'user_id' => $user->id,
                'pin' => Hash::make('123456'),
                'pin_set_at' => now(),
                'must_change_pin' => false,
                'is_active' => true,
            ]);
        }

        $uatAuditors = [
            [
                'namecode' => '101001',
                'name' => 'Muhammad Ridwan Auditor',
                'email' => '101001@inalum.id',
                'position_title' => 'Lead Auditor Operasional',
                'hierarchy_level' => 2,
            ],
            [
                'namecode' => '101002',
                'name' => 'Rudyanto',
                'email' => '101002@inalum.id',
                'position_title' => 'Senior Auditor Fabrikasi',
                'hierarchy_level' => 3,
            ],
            [
                'namecode' => '101003',
                'name' => 'Bambang Setiawan',
                'email' => '101003@inalum.id',
                'position_title' => 'Staff Auditor Gudang & Logistik',
                'hierarchy_level' => 4,
            ],
            [
                'namecode' => '101004',
                'name' => 'Siti Fatimah',
                'email' => '101004@inalum.id',
                'position_title' => 'Staff Auditor Keuangan & IT',
                'hierarchy_level' => 4,
            ],
            [
                'namecode' => '101005',
                'name' => 'Auditor Demo / Training',
                'email' => '101005@inalum.id',
                'position_title' => 'Auditor Tamu UAT',
                'hierarchy_level' => 5,
            ],
        ];

        $employeeMap = [];
        foreach ($uatAuditors as $auditorData) {
            $user = User::firstOrCreate(
                ['email' => $auditorData['email']],
                [
                    'name' => $auditorData['name'],
                    'password' => Hash::make('password123'),
                    'email_verified_at' => now(),
                ]
            );

            if ($user->roles()->count() === 0) {
                $user->assignRole($pelaksanaRole);
            }

            $employee = Employee::updateOrCreate(
                ['namecode' => $auditorData['namecode']],
                [
                    'user_id' => $user->id,
                    'department_id' => $dept->id,
                    'name' => $auditorData['name'],
                    'employee_code' => $auditorData['name'] . ' / ' . $auditorData['namecode'],
                    'position_title' => $auditorData['position_title'],
                    'hierarchy_level' => $auditorData['hierarchy_level'],
                    'is_active' => true,
                    'pin' => Hash::make('123456'),
                    'pin_set_at' => now(),
                    'must_change_pin' => false,
                ]
            );

            $employeeMap[$auditorData['namecode']] = $employee;
        }

        $locations = [
            'RAK-ATK-01' => [
                'name' => 'RAK-ATK-01',
                'code' => 'RAK-ATK-01',
                'description' => 'Lemari ATK Utama (Gudang Internal Audit Gedung Kantor Paritohan)',
            ],
            'RAK-ELK-02' => [
                'name' => 'RAK-ELK-02',
                'code' => 'RAK-ELK-02',
                'description' => 'Rak Elektronik & Perangkat IT Audit (Gedung Utama Paritohan)',
            ],
            'RAK-K3-03' => [
                'name' => 'RAK-K3-03',
                'code' => 'RAK-K3-03',
                'description' => 'Lemari Alat Pelindung Diri & K3 Lapangan (Smelter Kuala Tanjung)',
            ],
        ];

        $locationMap = [];
        foreach ($locations as $code => $data) {
            $locationMap[$code] = Location::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'is_active' => true,
                ]
            );
        }

        $itemsData = [
            [
                'sku' => 'ATK-KTS-A4',
                'name' => 'Kertas HVS A4 80gsm PaperOne',
                'type' => 'consumable',
                'unit' => 'Rim',
                'location_code' => 'RAK-ATK-01',
                'current_stock' => 35,
                'minimum_stock' => 10,
                'description' => 'Kertas cetak dokumen laporan audit internal A4 80 gram',
            ],
            [
                'sku' => 'ATK-SPD-001',
                'name' => 'Spidol Snowman Whiteboard Hitam',
                'type' => 'consumable',
                'unit' => 'Pcs',
                'location_code' => 'RAK-ATK-01',
                'current_stock' => 24,
                'minimum_stock' => 5,
                'description' => 'Spidol whiteboard warna hitam isi ulang untuk rapat audit',
            ],
            [
                'sku' => 'ELK-BAT-AA',
                'name' => 'Baterai AA Alkaline ABC',
                'type' => 'consumable',
                'unit' => 'Pack',
                'location_code' => 'RAK-ELK-02',
                'current_stock' => 3, // Kondisi Stok Menipis (3 <= 5)
                'minimum_stock' => 5,
                'description' => 'Baterai daya alat ukur dan pointer presentasi audit',
            ],
            [
                'sku' => 'ELK-TNT-BRT',
                'name' => 'Tinta Printer Brother BT-D60BK Hitam',
                'type' => 'consumable',
                'unit' => 'Botol',
                'location_code' => 'RAK-ELK-02',
                'current_stock' => 1, // Kondisi Stok Kritis (1 <= 2)
                'minimum_stock' => 2,
                'description' => 'Botol tinta refill printer printer audit room',
            ],
            [
                'sku' => 'ATK-ORD-F4',
                'name' => 'Ordner Bantex Folio 7cm Biru',
                'type' => 'consumable',
                'unit' => 'Pcs',
                'location_code' => 'RAK-ATK-01',
                'current_stock' => 0, // Kondisi Stok Habis (0)
                'minimum_stock' => 5,
                'description' => 'Map binder tebal pengarsipan berkas kertas kerja audit',
            ],
            [
                'sku' => 'K3-MSK-N95',
                'name' => 'Masker Partikulat N95 3M 8210',
                'type' => 'consumable',
                'unit' => 'Box',
                'location_code' => 'RAK-K3-03',
                'current_stock' => 15,
                'minimum_stock' => 10,
                'description' => 'Masker perlindungan debu & partikel area smelter reduksi',
            ],
            [
                'sku' => 'K3-SRG-LAS',
                'name' => 'Sarung Tangan Safety Kulit Las Heavy Duty',
                'type' => 'consumable',
                'unit' => 'Pasang',
                'location_code' => 'RAK-K3-03',
                'current_stock' => 8,
                'minimum_stock' => 10,
                'description' => 'Sarung tangan pelindung panas inspeksi area potline',
            ],
            [
                'sku' => 'AST-LPT-001',
                'name' => 'Laptop ThinkPad L14 Gen 4 Core i7 16GB',
                'type' => 'asset',
                'unit' => 'Unit',
                'location_code' => 'RAK-ELK-02',
                'current_stock' => 2,
                'minimum_stock' => 1,
                'description' => 'Laptop kerja dinas audit investigasi dan lapangan',
            ],
            [
                'sku' => 'AST-PRJ-001',
                'name' => 'Proyektor Portable Epson EB-E01 HDMI',
                'type' => 'asset',
                'unit' => 'Unit',
                'location_code' => 'RAK-ELK-02',
                'current_stock' => 1,
                'minimum_stock' => 1,
                'description' => 'Proyektor presentasi exit meeting audit pabrik',
            ],
            [
                'sku' => 'AST-LSR-001',
                'name' => 'Laser Distance Meter Bosch GLM 50-27 CG',
                'type' => 'asset',
                'unit' => 'Unit',
                'location_code' => 'RAK-ELK-02',
                'current_stock' => 2,
                'minimum_stock' => 1,
                'description' => 'Alat ukur jarak presisi digital audit stock opname',
            ],
            [
                'sku' => 'AST-MLT-001',
                'name' => 'Digital Multimeter Fluke 115 True-RMS',
                'type' => 'asset',
                'unit' => 'Unit',
                'location_code' => 'RAK-ELK-02',
                'current_stock' => 1,
                'minimum_stock' => 1,
                'description' => 'Alat uji kelistrikan fasilitas smelter dan gardu induk',
            ],
        ];

        $itemMap = [];
        $adminUser = User::where('email', '101001@inalum.id')->first() ?? $superAdmin;

        foreach ($itemsData as $data) {
            $location = $locationMap[$data['location_code']];
            $item = Item::updateOrCreate(
                ['sku' => $data['sku']],
                [
                    'location_id' => $location->id,
                    'name' => $data['name'],
                    'type' => $data['type'],
                    'unit' => $data['unit'],
                    'current_stock' => $data['current_stock'],
                    'minimum_stock' => $data['minimum_stock'],
                    'description' => $data['description'],
                    'is_active' => true,
                ]
            );

            $itemMap[$data['sku']] = $item;

            $existingLedger = InventoryLedger::where('item_id', $item->id)->first();
            if (! $existingLedger && $adminUser) {
                InventoryLedger::create([
                    'item_id' => $item->id,
                    'user_id' => $adminUser->id,
                    'client_uuid' => (string) Str::uuid(),
                    'type' => 'in',
                    'quantity' => $data['current_stock'],
                    'stock_before' => 0,
                    'stock_after' => $data['current_stock'],
                    'notes' => 'Saldo awal data master inventaris demo UAT PT INALUM',
                    'reference_number' => 'INIT-' . $data['sku'],
                    'occurred_at' => now()->subDays(14),
                ]);
            }
        }

        $loansData = [
            [
                'sku' => 'AST-LPT-001',
                'namecode' => '101001',
                'quantity' => 1,
                'borrowed_at' => now()->subDay(),
                'notes' => 'Pinjam untuk audit kepatuhan pengadaan Kuala Tanjung',
            ],
            [
                'sku' => 'AST-LPT-001',
                'namecode' => '101002',
                'quantity' => 1,
                'borrowed_at' => now()->subDays(5),
                'notes' => 'Peminjaman audit stock opname logistik dermaga',
            ],
            [
                'sku' => 'AST-PRJ-001',
                'namecode' => '101003',
                'quantity' => 1,
                'borrowed_at' => now()->subDays(9),
                'notes' => 'Peminjaman presentasi interim exit meeting pabrik',
            ],
            [
                'sku' => 'AST-MLT-001',
                'namecode' => '101004',
                'quantity' => 1,
                'borrowed_at' => now()->subDays(2),
                'notes' => 'Pemeriksaan instrumen panel gardu induk PLTA Sigura-gura',
            ],
        ];

        foreach ($loansData as $lData) {
            $item = $itemMap[$lData['sku']];
            $employee = $employeeMap[$lData['namecode']];

            if (! $employee->user_id) {
                continue;
            }

            $existingLoan = AssetLoan::where('item_id', $item->id)
                ->where('employee_id', $employee->id)
                ->whereNull('fully_returned_at')
                ->first();

            if (! $existingLoan) {
                $borrowLedger = InventoryLedger::create([
                    'item_id' => $item->id,
                    'user_id' => $employee->user_id,
                    'client_uuid' => (string) Str::uuid(),
                    'type' => 'borrow',
                    'quantity' => $lData['quantity'],
                    'stock_before' => $item->current_stock + $lData['quantity'],
                    'stock_after' => $item->current_stock,
                    'notes' => $lData['notes'],
                    'reference_number' => 'LOAN-' . strtoupper(Str::random(6)),
                    'occurred_at' => $lData['borrowed_at'],
                ]);

                AssetLoan::create([
                    'item_id' => $item->id,
                    'employee_id' => $employee->id,
                    'quantity' => $lData['quantity'],
                    'returned_quantity' => 0,
                    'borrowed_at' => $lData['borrowed_at'],
                    'fully_returned_at' => null,
                    'borrow_ledger_id' => $borrowLedger->id,
                ]);
            }
        }

        $this->command->info('InventoryDemoSeeder berhasil dieksekusi 100%!');
        $this->command->table(
            ['Tipe Data', 'Jumlah Berhasil Diseed'],
            [
                ['Master Lokasi Rak', count($locations)],
                ['Master Barang Inventaris', count($itemsData)],
                ['Akun Pegawai UAT', count($uatAuditors)],
                ['Pinjaman Aset Aktif', count($loansData)],
            ]
        );
    }
}
