<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\WorkReport;
use App\Models\User;
use App\Models\Department;
use Carbon\Carbon;

class WorkReportTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy các user và department có sẵn
        $users = User::all();
        $departments = Department::all();
        
        if ($users->isEmpty()) {
            $this->command->info('Không có user nào để tạo báo cáo test!');
            return;
        }
        
        // Dữ liệu test cho các tuần khác nhau và nhiều năm
        $testData = [
            // Năm 2025 - Tháng 8-9
            [
                'dates' => ['2025-08-26', '2025-08-27', '2025-08-28', '2025-08-29', '2025-08-30'],
                'year' => 2025,
                'month' => 8,
                'week' => 35,
                'week_of_month' => 5,
                'descriptions' => [
                    'Phát triển tính năng mới cho hệ thống quản lý',
                    'Code review cho các pull request',
                    'Tham gia cuộc họp sprint planning',
                    'Fix bugs và tối ưu hiệu suất',
                    'Viết tài liệu kỹ thuật'
                ]
            ],
            [
                'dates' => ['2025-09-01', '2025-09-02', '2025-09-03', '2025-09-04', '2025-09-05'],
                'year' => 2025,
                'month' => 9,
                'week' => 36,
                'week_of_month' => 1,
                'descriptions' => [
                    'Triển khai tính năng mới lên production',
                    'Kiểm tra và test hệ thống',
                    'Họp với khách hàng về yêu cầu mới',
                    'Cập nhật database schema',
                    'Đào tạo nhân viên mới'
                ]
            ],
            [
                'dates' => ['2025-08-18', '2025-08-19', '2025-08-20', '2025-08-21', '2025-08-22'],
                'year' => 2025,
                'month' => 8,
                'week' => 34,
                'week_of_month' => 4,
                'descriptions' => [
                    'Thiết kế giao diện người dùng',
                    'Tạo wireframes và mockups',
                    'Tham gia brainstorming session',
                    'Cập nhật design system',
                    'Review feedback từ khách hàng'
                ]
            ],
            [
                'dates' => ['2025-08-11', '2025-08-12', '2025-08-13', '2025-08-14', '2025-08-15'],
                'year' => 2025,
                'month' => 8,
                'week' => 33,
                'week_of_month' => 3,
                'descriptions' => [
                    'Phân tích yêu cầu dự án',
                    'Lập kế hoạch phát triển',
                    'Thiết lập môi trường development',
                    'Tạo database design',
                    'Viết API documentation'
                ]
            ],
            [
                'dates' => ['2025-08-04', '2025-08-05', '2025-08-06', '2025-08-07', '2025-08-08'],
                'year' => 2025,
                'month' => 8,
                'week' => 32,
                'week_of_month' => 2,
                'descriptions' => [
                    'Khảo sát công nghệ mới',
                    'Tham gia workshop về AI/ML',
                    'Nghiên cứu best practices',
                    'Chuẩn bị presentation',
                    'Họp team retrospective'
                ]
            ],
            [
                'dates' => ['2025-07-28', '2025-07-29', '2025-07-30', '2025-07-31', '2025-08-01'],
                'year' => 2025,
                'month' => 7,
                'week' => 31,
                'week_of_month' => 5,
                'descriptions' => [
                    'Maintenance hệ thống',
                    'Backup dữ liệu',
                    'Cập nhật security patches',
                    'Kiểm tra performance',
                    'Chuẩn bị báo cáo tháng'
                ]
            ],
            
            // Năm 2024 - Tháng 12
            [
                'dates' => ['2024-12-23', '2024-12-24', '2024-12-25', '2024-12-26', '2024-12-27'],
                'year' => 2024,
                'month' => 12,
                'week' => 52,
                'week_of_month' => 4,
                'descriptions' => [
                    'Chuẩn bị báo cáo cuối năm',
                    'Tổng kết dự án',
                    'Lập kế hoạch năm mới',
                    'Dọn dẹp workspace',
                    'Backup toàn bộ dữ liệu'
                ]
            ],
            [
                'dates' => ['2024-12-16', '2024-12-17', '2024-12-18', '2024-12-19', '2024-12-20'],
                'year' => 2024,
                'month' => 12,
                'week' => 51,
                'week_of_month' => 3,
                'descriptions' => [
                    'Hoàn thiện tính năng cuối năm',
                    'Test toàn bộ hệ thống',
                    'Chuẩn bị demo cho khách hàng',
                    'Cập nhật documentation',
                    'Họp team cuối năm'
                ]
            ],
            
            // Năm 2024 - Tháng 11
            [
                'dates' => ['2024-11-25', '2024-11-26', '2024-11-27', '2024-11-28', '2024-11-29'],
                'year' => 2024,
                'month' => 11,
                'week' => 48,
                'week_of_month' => 5,
                'descriptions' => [
                    'Phát triển module mới',
                    'Tối ưu database queries',
                    'Tham gia training session',
                    'Code review cho team',
                    'Chuẩn bị presentation'
                ]
            ],
            [
                'dates' => ['2024-11-18', '2024-11-19', '2024-11-20', '2024-11-21', '2024-11-22'],
                'year' => 2024,
                'month' => 11,
                'week' => 47,
                'week_of_month' => 4,
                'descriptions' => [
                    'Thiết kế UI/UX mới',
                    'Tạo prototype',
                    'User testing',
                    'Cập nhật design guidelines',
                    'Họp với stakeholders'
                ]
            ],
            
            // Năm 2024 - Tháng 10
            [
                'dates' => ['2024-10-28', '2024-10-29', '2024-10-30', '2024-10-31', '2024-11-01'],
                'year' => 2024,
                'month' => 10,
                'week' => 44,
                'week_of_month' => 5,
                'descriptions' => [
                    'Triển khai tính năng mới',
                    'Monitoring hệ thống',
                    'Fix critical bugs',
                    'Cập nhật security',
                    'Chuẩn bị release'
                ]
            ],
            [
                'dates' => ['2024-10-21', '2024-10-22', '2024-10-23', '2024-10-24', '2024-10-25'],
                'year' => 2024,
                'month' => 10,
                'week' => 43,
                'week_of_month' => 4,
                'descriptions' => [
                    'Phân tích yêu cầu mới',
                    'Lập kế hoạch sprint',
                    'Thiết lập môi trường test',
                    'Viết test cases',
                    'Họp planning'
                ]
            ],
            
            // Năm 2023 - Tháng 12
            [
                'dates' => ['2023-12-25', '2023-12-26', '2023-12-27', '2023-12-28', '2023-12-29'],
                'year' => 2023,
                'month' => 12,
                'week' => 52,
                'week_of_month' => 5,
                'descriptions' => [
                    'Bảo trì hệ thống cuối năm',
                    'Chuẩn bị báo cáo tổng kết',
                    'Dọn dẹp codebase',
                    'Archive dự án cũ',
                    'Lập kế hoạch 2024'
                ]
            ],
            [
                'dates' => ['2023-12-18', '2023-12-19', '2023-12-20', '2023-12-21', '2023-12-22'],
                'year' => 2023,
                'month' => 12,
                'week' => 51,
                'week_of_month' => 4,
                'descriptions' => [
                    'Hoàn thiện dự án cuối năm',
                    'Final testing',
                    'Chuẩn bị handover',
                    'Training nhân viên mới',
                    'Họp tổng kết team'
                ]
            ],
            
            // Năm 2023 - Tháng 6
            [
                'dates' => ['2023-06-26', '2023-06-27', '2023-06-28', '2023-06-29', '2023-06-30'],
                'year' => 2023,
                'month' => 6,
                'week' => 26,
                'week_of_month' => 5,
                'descriptions' => [
                    'Phát triển tính năng core',
                    'Tối ưu performance',
                    'Security audit',
                    'Database optimization',
                    'Chuẩn bị release v2.0'
                ]
            ],
            [
                'dates' => ['2023-06-19', '2023-06-20', '2023-06-21', '2023-06-22', '2023-06-23'],
                'year' => 2023,
                'month' => 6,
                'week' => 25,
                'week_of_month' => 4,
                'descriptions' => [
                    'Thiết kế architecture mới',
                    'Code refactoring',
                    'Unit testing',
                    'Integration testing',
                    'Code review session'
                ]
            ],
            
            // Năm 2023 - Tháng 3
            [
                'dates' => ['2023-03-27', '2023-03-28', '2023-03-29', '2023-03-30', '2023-03-31'],
                'year' => 2023,
                'month' => 3,
                'week' => 13,
                'week_of_month' => 5,
                'descriptions' => [
                    'Khởi tạo dự án mới',
                    'Setup development environment',
                    'Tạo project structure',
                    'Cài đặt dependencies',
                    'Lập kế hoạch sprint đầu tiên'
                ]
            ],
            [
                'dates' => ['2023-03-20', '2023-03-21', '2023-03-22', '2023-03-23', '2023-03-24'],
                'year' => 2023,
                'month' => 3,
                'week' => 12,
                'week_of_month' => 4,
                'descriptions' => [
                    'Phân tích yêu cầu dự án',
                    'Thiết kế database schema',
                    'Tạo API specifications',
                    'Lập timeline dự án',
                    'Họp kickoff với khách hàng'
                ]
            ],
            
            // Năm 2022 - Tháng 12
            [
                'dates' => ['2022-12-26', '2022-12-27', '2022-12-28', '2022-12-29', '2022-12-30'],
                'year' => 2022,
                'month' => 12,
                'week' => 52,
                'week_of_month' => 5,
                'descriptions' => [
                    'Bảo trì hệ thống legacy',
                    'Migration dữ liệu cũ',
                    'Cập nhật documentation',
                    'Chuẩn bị báo cáo năm',
                    'Lập kế hoạch 2023'
                ]
            ],
            [
                'dates' => ['2022-12-19', '2022-12-20', '2022-12-21', '2022-12-22', '2022-12-23'],
                'year' => 2022,
                'month' => 12,
                'week' => 51,
                'week_of_month' => 4,
                'descriptions' => [
                    'Hoàn thiện dự án cuối năm',
                    'Final testing và deployment',
                    'Training end users',
                    'Chuẩn bị handover',
                    'Họp tổng kết'
                ]
            ]
        ];
        
        $createdCount = 0;
        
        foreach ($testData as $data) {
            foreach ($users as $user) {
                // Tạo 2-3 báo cáo cho mỗi user trong mỗi tuần
                $numReports = rand(2, 3);
                
                // Lấy ngẫu nhiên các ngày khác nhau để tránh trùng lặp
                $availableDates = $data['dates'];
                shuffle($availableDates);
                $selectedDates = array_slice($availableDates, 0, $numReports);
                
                foreach ($selectedDates as $date) {
                    $randomDescription = $data['descriptions'][array_rand($data['descriptions'])];
                    
                    // Thêm một số biến thể cho mô tả
                    $variations = [
                        'Hoàn thành ' . $randomDescription,
                        'Tiếp tục ' . $randomDescription,
                        'Bắt đầu ' . $randomDescription,
                        'Review ' . $randomDescription,
                        'Test ' . $randomDescription
                    ];
                    
                    $finalDescription = $variations[array_rand($variations)];
                    
                    WorkReport::create([
                        'user_id' => $user->id,
                        'department_id' => $user->department_id,
                        'report_date' => $date,
                        'daily_work' => $finalDescription,
                        'year' => $data['year'],
                        'month' => $data['month'],
                        'week' => $data['week'],
                        'week_of_month' => $data['week_of_month'],
                        'created_at' => Carbon::parse($date),
                        'updated_at' => Carbon::parse($date)
                    ]);
                    
                    $createdCount++;
                }
            }
        }
        
        $this->command->info("Đã tạo {$createdCount} báo cáo test thành công!");
        $this->command->info("Dữ liệu bao gồm:");
        $this->command->info("- 4 năm: 2022, 2023, 2024, 2025");
        $this->command->info("- 8 tháng: 3, 6, 10, 11, 12 (2022-2024) và 7, 8, 9 (2025)");
        $this->command->info("- 18 tuần khác nhau từ tuần 12-52");
        $this->command->info("- Mỗi user có 2-3 báo cáo/tuần");
        $this->command->info("- Auto-classification theo ngày báo cáo");
        $this->command->info("- Dữ liệu trải dài từ 2022-2025 để test filter theo năm");
    }
}