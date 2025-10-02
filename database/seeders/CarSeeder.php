<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Car;

class CarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cars = [
            [
                'license_plate' => '30A-12345',
                'weight' => 1500.00,
                'car_type' => 'Sedan',
                'color' => 'Trắng',
                'description' => 'Xe sedan 4 chỗ, tiết kiệm nhiên liệu, phù hợp đi công tác',
                'status' => 'active',
            ],
            [
                'license_plate' => '30A-67890',
                'weight' => 2000.00,
                'car_type' => 'SUV',
                'color' => 'Đen',
                'description' => 'Xe SUV 7 chỗ, phù hợp gia đình và nhóm đông người',
                'status' => 'active',
            ],
            [
                'license_plate' => '30A-11111',
                'weight' => 1200.00,
                'car_type' => 'Hatchback',
                'color' => 'Xanh',
                'description' => 'Xe hatchback nhỏ gọn, dễ lái trong thành phố',
                'status' => 'active',
            ],
            [
                'license_plate' => '30A-22222',
                'weight' => 1800.00,
                'car_type' => 'Sedan',
                'color' => 'Bạc',
                'description' => 'Xe sedan cao cấp, đầy đủ tiện nghi',
                'status' => 'active',
            ],
            [
                'license_plate' => '30A-33333',
                'weight' => 2200.00,
                'car_type' => 'SUV',
                'color' => 'Trắng',
                'description' => 'Xe SUV 4WD, mạnh mẽ, phù hợp địa hình khó',
                'status' => 'active',
            ],
            [
                'license_plate' => '30A-44444',
                'weight' => 1600.00,
                'car_type' => 'Crossover',
                'color' => 'Đỏ',
                'description' => 'Xe crossover hiện đại, kết hợp sedan và SUV',
                'status' => 'active',
            ],
            [
                'license_plate' => '30A-55555',
                'weight' => 1400.00,
                'car_type' => 'Compact',
                'color' => 'Vàng',
                'description' => 'Xe compact tiết kiệm nhiên liệu, dễ đỗ xe',
                'status' => 'active',
            ],
            [
                'license_plate' => '30A-66666',
                'weight' => 1900.00,
                'car_type' => 'Pickup',
                'color' => 'Xám',
                'description' => 'Xe pickup chở hàng, phù hợp vận chuyển và công việc',
                'status' => 'active',
            ],
        ];

        foreach ($cars as $car) {
            Car::create($car);
        }
    }
}
