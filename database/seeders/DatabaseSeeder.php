<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(CreateAdminUserSeeder::class);
        $this->call(MenusTableSeeder::class);
        $this->call(KategoriSeeder::class);
        $this->call(UnitSeeder::class);
        $this->call(IndikatorSeeder::class);
        $this->call(OpsiJawabanSeeder::class);
    }
}
