<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Task;
use App\Models\User;
use App\Models\Department;
use Carbon\Carbon;

class TaskSampleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy một số user và department để tạo task
        $users = User::take(5)->get();
        $departments = Department::take(3)->get();
        
        if ($users->isEmpty() || $departments->isEmpty()) {
            $this->command->info('Không có user hoặc department nào. Vui lòng chạy seeder khác trước.');
            return;
        }

        $sampleTasks = [
            [
                'title' => 'Phát triển tính năng đăng nhập OAuth',
                'description' => 'Tích hợp Google và Facebook login cho ứng dụng',
                'status' => 'in_progress',
                'priority' => 'high',
                'deadline' => Carbon::now()->addDays(7),
            ],
            [
                'title' => 'Tối ưu hóa database queries',
                'description' => 'Cải thiện performance của các query phức tạp',
                'status' => 'completed',
                'priority' => 'medium',
                'deadline' => Carbon::now()->addDays(3),
            ],
            [
                'title' => 'Thiết kế giao diện mobile responsive',
                'description' => 'Cải thiện UX/UI cho thiết bị di động',
                'status' => 'pending_approval',
                'priority' => 'high',
                'deadline' => Carbon::now()->addDays(5),
            ],
            [
                'title' => 'Viết unit tests cho API endpoints',
                'description' => 'Tạo test cases cho tất cả API endpoints',
                'status' => 'in_progress',
                'priority' => 'medium',
                'deadline' => Carbon::now()->addDays(10),
            ],
            [
                'title' => 'Cập nhật documentation',
                'description' => 'Viết lại tài liệu hướng dẫn sử dụng',
                'status' => 'rejected',
                'priority' => 'low',
                'deadline' => Carbon::now()->subDays(2),
            ],
            [
                'title' => 'Tích hợp hệ thống thanh toán',
                'description' => 'Kết nối với VNPay và MoMo',
                'status' => 'overdue',
                'priority' => 'high',
                'deadline' => Carbon::now()->subDays(1),
            ],
            [
                'title' => 'Thiết kế logo và branding',
                'description' => 'Tạo bộ nhận diện thương hiệu mới',
                'status' => 'pending_approval',
                'priority' => 'medium',
                'deadline' => Carbon::now()->addDays(4),
            ],
            [
                'title' => 'Cài đặt monitoring và logging',
                'description' => 'Thiết lập hệ thống giám sát ứng dụng',
                'status' => 'in_progress',
                'priority' => 'high',
                'deadline' => Carbon::now()->addDays(6),
            ],
            [
                'title' => 'Tối ưu hóa hình ảnh và assets',
                'description' => 'Nén và tối ưu hóa các file hình ảnh',
                'status' => 'completed',
                'priority' => 'low',
                'deadline' => Carbon::now()->addDays(2),
            ],
            [
                'title' => 'Thiết lập CI/CD pipeline',
                'description' => 'Tự động hóa quy trình deploy',
                'status' => 'overdue',
                'priority' => 'high',
                'deadline' => Carbon::now()->subDays(3),
            ],
        ];

        foreach ($sampleTasks as $index => $taskData) {
            $task = Task::create([
                'title' => $taskData['title'],
                'description' => $taskData['description'],
                'status' => $taskData['status'],
                'priority' => $taskData['priority'],
                'deadline' => $taskData['deadline'],
                'assignee_id' => $users->random()->id,
                'creator_id' => $users->random()->id,
                'department_id' => $departments->random()->id,
                'created_at' => Carbon::now()->subDays(rand(1, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 5)),
            ]);

            // Thêm một số task có nhiều phòng ban
            if ($index % 3 == 0) {
                $additionalDepartments = $departments->where('id', '!=', $task->department_id)->take(2);
                foreach ($additionalDepartments as $dept) {
                    $task->departments()->attach($dept->id);
                }
            }
        }

        $this->command->info('Đã tạo 10 công việc mẫu thành công!');
    }
}