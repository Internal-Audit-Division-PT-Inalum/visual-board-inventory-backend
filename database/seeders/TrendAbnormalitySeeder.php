<?php

namespace Database\Seeders;

use App\Domains\VisualBoard\Models\TrendAbnormality;
use Illuminate\Database\Seeder;

class TrendAbnormalitySeeder extends Seeder
{
    public function run(): void
    {
        $zones = ['Z-1', 'Z-2', 'Z-3'];
        $year = 2026;

        // Data realistik per zona per bulan (Jan-Sep 2026)
        $data = [
            // Format: [zone, month, temuan, tindak_lanjut, belum_selesai]
            ['Z-1', 1, 3, 3, 0], ['Z-2', 1, 2, 2, 0], ['Z-3', 1, 1, 1, 0],
            ['Z-1', 2, 4, 3, 1], ['Z-2', 2, 3, 3, 0], ['Z-3', 2, 2, 1, 1],
            ['Z-1', 3, 2, 2, 0], ['Z-2', 3, 5, 4, 1], ['Z-3', 3, 3, 3, 0],
            ['Z-1', 4, 5, 4, 1], ['Z-2', 4, 2, 2, 0], ['Z-3', 4, 4, 3, 1],
            ['Z-1', 5, 3, 3, 0], ['Z-2', 5, 4, 4, 0], ['Z-3', 5, 2, 2, 0],
            ['Z-1', 6, 6, 5, 1], ['Z-2', 6, 3, 2, 1], ['Z-3', 6, 1, 1, 0],
            ['Z-1', 7, 2, 2, 0], ['Z-2', 7, 4, 4, 0], ['Z-3', 7, 3, 2, 1],
            ['Z-1', 8, 4, 3, 1], ['Z-2', 8, 2, 2, 0], ['Z-3', 8, 5, 4, 1],
            ['Z-1', 9, 3, 2, 1], ['Z-2', 9, 3, 3, 0], ['Z-3', 9, 2, 1, 1],
        ];

        foreach ($data as [$zone, $month, $temuan, $tl, $bs]) {
            TrendAbnormality::updateOrCreate(
                ['year' => $year, 'month' => $month, 'zone_label' => $zone],
                ['temuan' => $temuan, 'tindak_lanjut' => $tl, 'belum_selesai' => $bs]
            );
        }
    }
}
