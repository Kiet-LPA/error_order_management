<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CheckinRegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('checkin_regions')->insert([
            [
                'name' => 'Khu vực Kiến Thành',
                'latitude' => 10.0259,
                'longitude' => 105.7692,
                'radius_meters' => 200,
                'address' => '19 Đường Châu Văn Liêm, Tân An, Ninh Kiều, Cần Thơ, Việt Nam',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Văn phòng Hà Nội',
                'latitude' => 21.0285,
                'longitude' => 105.8542,
                'radius_meters' => 200,
                'address' => 'Hà Nội, Việt Nam',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Văn phòng TP.HCM',
                'latitude' => 10.8231,
                'longitude' => 106.6297,
                'radius_meters' => 200,
                'address' => 'TP. Hồ Chí Minh, Việt Nam',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
