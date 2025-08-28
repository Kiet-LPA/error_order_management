<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Notification;
use App\Models\User;
use App\Models\Task;

class NotificationTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo một số thông báo test
        $users = User::all();
        $tasks = Task::all();

        if ($users->count() > 0 && $tasks->count() > 0) {
            foreach ($users as $user) {
                // Tạo thông báo task assigned
                Notification::create([
                    'user_id' => $user->id,
                    'type' => 'task_assigned',
                    'title' => 'Công việc mới được giao',
                    'message' => 'Bạn nhận được thông báo mời từ Admin giao công việc: Test Task',
                    'data' => [
                        'task_id' => $tasks->first()->id,
                        'assigner_id' => $users->where('role', 'admin')->first()->id ?? $users->first()->id,
                        'assigner_name' => 'Admin'
                    ],
                    'is_read' => false
                ]);

                // Tạo thông báo task updated
                Notification::create([
                    'user_id' => $user->id,
                    'type' => 'task_updated',
                    'title' => 'Công việc được cập nhật',
                    'message' => 'Bạn nhận được thông báo mời từ Manager cập nhật công việc: Test Task Update',
                    'data' => [
                        'task_id' => $tasks->first()->id,
                        'updater_id' => $users->where('role', 'manager')->first()->id ?? $users->first()->id,
                        'updater_name' => 'Manager'
                    ],
                    'is_read' => false
                ]);

                // Tạo thông báo work report
                Notification::create([
                    'user_id' => $user->id,
                    'type' => 'work_report_submitted',
                    'title' => 'Báo cáo công việc mới',
                    'message' => 'Bạn nhận được thông báo mời từ Employee gửi báo cáo công việc',
                    'data' => [
                        'report_id' => 1,
                        'submitter_id' => $users->where('role', 'employee')->first()->id ?? $users->first()->id,
                        'submitter_name' => 'Employee'
                    ],
                    'is_read' => true
                ]);
            }
        }
    }
}
