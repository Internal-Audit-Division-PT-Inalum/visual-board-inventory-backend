<?php

namespace Database\Seeders;

use App\Domains\HR\Models\OrganizationDocument;
use Illuminate\Database\Seeder;

class OrganizationDocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $doc1 = OrganizationDocument::create([
            'title' => 'Struktur Organisasi 5R Divisi IIA Periode 2026-2027',
            'description' => 'Bagan kepengurusan 5R di lingkungan Divisi IIA beserta area tanggung jawab.',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $path1 = 'C:\Users\LENOVO\.gemini\antigravity-ide\brain\73cff94d-7e06-44e2-9a7d-f0bd9b64589c\.user_uploaded\media_1789005488160.jpg';
        if (file_exists($path1)) {
            $doc1->addMedia($path1)->preservingOriginal()->toMediaCollection('document');
        }

        $doc2 = OrganizationDocument::create([
            'title' => 'Struktur Pegawai & Organisasi Departemen IIA',
            'description' => 'Susunan kepegawaian dan kepemimpinan di tingkat departemen sesuai SK Direksi.',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $path2 = 'C:\Users\LENOVO\.gemini\antigravity-ide\brain\73cff94d-7e06-44e2-9a7d-f0bd9b64589c\.user_uploaded\media_1789005488167.jpg';
        if (file_exists($path2)) {
            $doc2->addMedia($path2)->preservingOriginal()->toMediaCollection('document');
        }
    }
}
