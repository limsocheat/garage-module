<?php

namespace Modules\Garage\Database\Seeders;

use Illuminate\Database\Seeder;

class GarageDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            PermissionTableSeeder::class,
            // Must run after permissions — the bay roles are granted them.
            RoleTableSeeder::class,
        ]);
    }
}
