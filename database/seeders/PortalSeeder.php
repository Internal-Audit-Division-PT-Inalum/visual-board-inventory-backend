<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domains\Portal\Models\Bulletin;

class PortalSeeder extends Seeder
{
    public function run(): void
    {
        Bulletin::factory(15)->create();
    }
}
