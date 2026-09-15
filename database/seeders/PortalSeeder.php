<?php

namespace Database\Seeders;

use App\Domains\Portal\Models\Bulletin;
use Illuminate\Database\Seeder;

class PortalSeeder extends Seeder
{
    public function run(): void
    {
        Bulletin::factory(5)->create();
    }
}
