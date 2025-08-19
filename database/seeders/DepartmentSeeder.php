<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['HR','Sales','Production','IT'] as $name) {
            Department::firstOrCreate(['name' => $name]);
        }
    }
}
