<?php

namespace Database\Seeders;

use App\Domains\HR\Models\Employee;
use App\Domains\HR\Models\Position;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $positions = [
            [
                'name' => 'Dewan Direksi',
                'hierarchy_level' => 1,
                'description' => 'Pucuk pimpinan eksekutif pengelola perusahaan',
            ],
            [
                'name' => 'Dewan Komisaris',
                'hierarchy_level' => 1,
                'description' => 'Dewan pengawas independen tata kelola korporat',
            ],
            [
                'name' => 'Kepala Grup',
                'hierarchy_level' => 2,
                'description' => 'Pimpinan unit operasional tingkat grup',
            ],
            [
                'name' => 'Kepala Divisi',
                'hierarchy_level' => 3,
                'description' => 'Pimpinan unit kerja divisi (contoh: Internal Audit)',
            ],
            [
                'name' => 'Kepala Departemen',
                'hierarchy_level' => 4,
                'description' => 'Pimpinan departemen teknis operasional',
            ],
            [
                'name' => 'Kepala Seksi',
                'hierarchy_level' => 5,
                'description' => 'Pimpinan seksi operasional lapangan',
            ],
            [
                'name' => 'Staff',
                'hierarchy_level' => 6,
                'description' => 'Tenaga analis, auditor pelaksana, dan profesional',
            ],
            [
                'name' => 'Operator',
                'hierarchy_level' => 7,
                'description' => 'Pelaksana teknis operasional lapangan',
            ],
            [
                'name' => 'Administrasi',
                'hierarchy_level' => 7,
                'description' => 'Tenaga pendukung kesekretariatan dan ketatausahaan',
            ],
        ];

        $positionMap = [];
        foreach ($positions as $item) {
            $position = Position::firstOrCreate(
                ['name' => $item['name']],
                [
                    'hierarchy_level' => $item['hierarchy_level'],
                    'description' => $item['description'],
                    'is_active' => true,
                ]
            );
            $positionMap[$item['name']] = $position;
        }

        $unlinkedEmployees = Employee::whereNull('position_id')->get();

        foreach ($unlinkedEmployees as $emp) {
            $title = $emp->position_title ?? '';
            $matchedPosition = $this->resolveMatchedPosition($title, $positionMap);

            $emp->position_id = $matchedPosition->id;
            $emp->save();
        }
    }

    /**
     * Resolves appropriate Position model based on existing free-text title.
     *
     * @param  array<string, Position>  $positionMap
     */
    private function resolveMatchedPosition(string $title, array $positionMap): Position
    {
        $normalized = Str::lower($title);

        if (Str::contains($normalized, ['komisaris'])) {
            return $positionMap['Dewan Komisaris'];
        }

        if (Str::contains($normalized, ['direksi', 'direktur'])) {
            return $positionMap['Dewan Direksi'];
        }

        if (Str::contains($normalized, ['grup', 'group'])) {
            return $positionMap['Kepala Grup'];
        }

        if (Str::contains($normalized, ['divisi', 'division'])) {
            return $positionMap['Kepala Divisi'];
        }

        if (Str::contains($normalized, ['departemen', 'department', 'manager'])) {
            return $positionMap['Kepala Departemen'];
        }

        if (Str::contains($normalized, ['seksi', 'section', 'asmen'])) {
            return $positionMap['Kepala Seksi'];
        }

        if (Str::contains($normalized, ['operator', 'teknisi'])) {
            return $positionMap['Operator'];
        }

        if (Str::contains($normalized, ['admin', 'sekretaris', 'tata usaha'])) {
            return $positionMap['Administrasi'];
        }

        return $positionMap['Staff'];
    }
}
