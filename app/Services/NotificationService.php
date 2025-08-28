<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Task;
use App\Models\User;
use App\Models\WorkReport;

class NotificationService
{
    /**
     * Gửi thông báo task được giao
     */
    public static function taskAssigned(Task $task, User $assigner, User $assignee)
    {
        Notification::create([
            'user_id' => $assignee->id,
            'type' => 'task_assigned',
            'title' => 'Công việc mới được giao',
            'message' => "Bạn nhận được thông báo mời từ {$assigner->name} giao công việc: {$task->title}",
            'data' => [
                'task_id' => $task->id,
                'assigner_id' => $assigner->id,
                'assigner_name' => $assigner->name
            ]
        ]);
    }

    /**
     * Gửi thông báo task được cập nhật
     */
    public static function taskUpdated(Task $task, User $updater)
    {
        // Thông báo cho assignees
        $assignees = $task->assignees;
        foreach ($assignees as $assignee) {
            if ($assignee->id !== $updater->id) {
                Notification::create([
                    'user_id' => $assignee->id,
                    'type' => 'task_updated',
                    'title' => 'Công việc được cập nhật',
                    'message' => "Bạn nhận được thông báo mời từ {$updater->name} cập nhật công việc: {$task->title}",
                    'data' => [
                        'task_id' => $task->id,
                        'updater_id' => $updater->id,
                        'updater_name' => $updater->name
                    ]
                ]);
            }
        }

        // Thông báo cho followers
        $followers = $task->followers;
        foreach ($followers as $follower) {
            if ($follower->id !== $updater->id) {
                Notification::create([
                    'user_id' => $follower->id,
                    'type' => 'task_updated',
                    'title' => 'Công việc được cập nhật',
                    'message' => "Bạn nhận được thông báo mời từ {$updater->name} cập nhật công việc: {$task->title}",
                    'data' => [
                        'task_id' => $task->id,
                        'updater_id' => $updater->id,
                        'updater_name' => $updater->name
                    ]
                ]);
            }
        }
    }

    /**
     * Gửi thông báo work report được submit
     */
    public static function workReportSubmitted(WorkReport $report, User $submitter)
    {
        // Thông báo cho Admin
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'type' => 'work_report_submitted',
                'title' => 'Báo cáo công việc mới',
                'message' => "Bạn nhận được thông báo mời từ {$submitter->name} gửi báo cáo công việc",
                'data' => [
                    'report_id' => $report->id,
                    'submitter_id' => $submitter->id,
                    'submitter_name' => $submitter->name
                ]
            ]);
        }

        // Thông báo cho Manager của phòng ban
        if ($submitter->department && $submitter->department->manager) {
            $manager = $submitter->department->manager;
            if ($manager->id !== $submitter->id) {
                Notification::create([
                    'user_id' => $manager->id,
                    'type' => 'work_report_submitted',
                    'title' => 'Báo cáo công việc mới',
                    'message' => "Bạn nhận được thông báo mời từ {$submitter->name} gửi báo cáo công việc",
                    'data' => [
                        'report_id' => $report->id,
                        'submitter_id' => $submitter->id,
                        'submitter_name' => $submitter->name
                    ]
                ]);
            }
        }
    }

    /**
     * Gửi thông báo task được follow
     */
    public static function taskFollowed(Task $task, User $follower)
    {
        // Thông báo cho creator của task
        if ($task->creator && $task->creator->id !== $follower->id) {
            Notification::create([
                'user_id' => $task->creator->id,
                'type' => 'task_followed',
                'title' => 'Có người theo dõi công việc',
                'message' => "Bạn nhận được thông báo mời từ {$follower->name} theo dõi công việc: {$task->title}",
                'data' => [
                    'task_id' => $task->id,
                    'follower_id' => $follower->id,
                    'follower_name' => $follower->name
                ]
            ]);
        }
    }

    /**
     * Lấy số thông báo chưa đọc
     */
    public static function getUnreadCount(User $user)
    {
        return Notification::where('user_id', $user->id)
                          ->where('is_read', false)
                          ->count();
    }

    /**
     * Lấy danh sách thông báo
     */
    public static function getNotifications(User $user, $limit = 10)
    {
        return Notification::where('user_id', $user->id)
                          ->orderBy('created_at', 'desc')
                          ->limit($limit)
                          ->get();
    }

    /**
     * Đánh dấu thông báo đã đọc
     */
    public static function markAsRead($notificationId, User $user)
    {
        $notification = Notification::where('id', $notificationId)
                                   ->where('user_id', $user->id)
                                   ->first();
        
        if ($notification) {
            $notification->markAsRead();
            return true;
        }
        
        return false;
    }

    /**
     * Đánh dấu tất cả thông báo đã đọc
     */
    public static function markAllAsRead(User $user)
    {
        return Notification::where('user_id', $user->id)
                          ->where('is_read', false)
                          ->update([
                              'is_read' => true,
                              'read_at' => now()
                          ]);
    }
}
