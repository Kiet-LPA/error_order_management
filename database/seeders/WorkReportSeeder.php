<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WorkReport;
use App\Models\User;
use App\Models\Department;
use Carbon\Carbon;

class WorkReportSeeder extends Seeder
{
    public function run(): void
    {
        // Lấy một số users để tạo báo cáo mẫu
        $users = User::where('role', 'employee')->take(5)->get();
        
        if ($users->isEmpty()) {
            $this->command->info('Không có employee nào để tạo báo cáo mẫu.');
            return;
        }

        $currentYear = now()->year;
        $currentMonth = now()->month;
        
        foreach ($users as $user) {
            // Tạo báo cáo cho tuần hiện tại
            $this->createWeekReports($user, $currentYear, $currentMonth, 1);
            
            // Tạo báo cáo cho tuần trước
            $this->createWeekReports($user, $currentYear, $currentMonth - 1, 4);
            
            // Tạo báo cáo cho tháng trước
            $this->createWeekReports($user, $currentYear, $currentMonth - 1, 1);
        }

        $this->command->info('Đã tạo báo cáo công việc mẫu thành công.');
    }

    private function createWeekReports($user, $year, $month, $week)
    {
        // Tạo báo cáo cho 5 ngày trong tuần (thứ 2 đến thứ 6)
        for ($day = 1; $day <= 5; $day++) {
            // Tính ngày trong tuần
            $date = Carbon::create($year, $month, 1)->startOfWeek()->addDays($day - 1);
            
            // Nếu ngày không thuộc tháng đang xét, bỏ qua
            if ($date->month !== $month) {
                continue;
            }

            // Tạo nội dung báo cáo mẫu
            $dailyWork = $this->getSampleDailyWork($user->department->name ?? 'IT');
            $difficulties = $this->getSampleDifficulties();
            $comments = $this->getSampleComments();

            WorkReport::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'report_date' => $date->format('Y-m-d')
                ],
                [
                    'department_id' => $user->department_id,
                    'year' => $year,
                    'month' => $month,
                    'week' => $week,
                    'daily_work' => $dailyWork,
                    'difficulties' => $difficulties,
                    'comments' => $comments,
                    'custom_fields' => $this->getSampleCustomFields($user->department->name ?? 'IT')
                ]
            );
        }
    }

    private function getSampleDailyWork($department)
    {
        $samples = [
            'IT' => [
                'Phát triển tính năng mới cho hệ thống quản lý',
                'Sửa lỗi bug trong module báo cáo',
                'Code review cho các pull request',
                'Tham gia cuộc họp sprint planning',
                'Cập nhật documentation cho API'
            ],
            'HR' => [
                'Phỏng vấn ứng viên cho vị trí Developer',
                'Xử lý hồ sơ nhân viên mới',
                'Tổ chức buổi đào tạo về quy định công ty',
                'Giải quyết vấn đề nhân viên',
                'Cập nhật chính sách nhân sự'
            ],
            'Finance' => [
                'Xử lý báo cáo tài chính tháng',
                'Kiểm tra và phê duyệt hóa đơn',
                'Phân tích ngân sách dự án',
                'Làm việc với kiểm toán viên',
                'Cập nhật sổ sách kế toán'
            ]
        ];

        $departmentSamples = $samples[$department] ?? $samples['IT'];
        return $departmentSamples[array_rand($departmentSamples)];
    }

    private function getSampleDifficulties()
    {
        $difficulties = [
            'Gặp khó khăn trong việc tích hợp API bên thứ 3',
            'Cần thêm thời gian để nghiên cứu công nghệ mới',
            'Thiếu tài liệu kỹ thuật chi tiết',
            'Cần hỗ trợ từ team khác để hoàn thành task',
            'Gặp vấn đề về performance khi xử lý dữ liệu lớn'
        ];

        return $difficulties[array_rand($difficulties)];
    }

    private function getSampleComments()
    {
        $comments = [
            'Task hoàn thành đúng tiến độ',
            'Cần cải thiện quy trình làm việc',
            'Đề xuất sử dụng công nghệ mới để tối ưu',
            'Cần training thêm về kỹ năng mới',
            'Làm việc hiệu quả với team'
        ];

        return $comments[array_rand($comments)];
    }

    private function getSampleCustomFields($department)
    {
        $customFields = [
            'IT' => [
                'projects_worked_on' => 'Hệ thống quản lý nhân viên',
                'bugs_fixed' => rand(1, 5),
                'code_reviews' => rand(2, 8),
                'meetings_attended' => 'Sprint planning, Daily standup'
            ],
            'HR' => [
                'candidates_interviewed' => rand(1, 3),
                'contracts_processed' => rand(2, 5),
                'training_sessions' => 'Quy định công ty, An toàn lao động',
                'employee_issues' => 'Giải quyết vấn đề nhân viên về lương thưởng'
            ],
            'Finance' => [
                'transactions_processed' => rand(10, 50),
                'reports_generated' => rand(3, 8),
                'budget_reviews' => 'Đánh giá ngân sách Q1',
                'audit_tasks' => 'Chuẩn bị tài liệu cho kiểm toán'
            ]
        ];

        return $customFields[$department] ?? $customFields['IT'];
    }
}
