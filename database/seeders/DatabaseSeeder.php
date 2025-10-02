<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DepartmentSeeder::class,
            AdminSeeder::class,
            ManagerAccountsSeeder::class,
            DirectorAccountsSeeder::class,
            CarSeeder::class,
        ]);
    }
}
