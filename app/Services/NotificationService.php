<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Task;
use App\Models\User;
use App\Models\WorkReport;
use App\Models\SupportRequest;

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
     * Gửi thông báo yêu cầu hỗ trợ được tạo
     */
    public static function supportRequestCreated(SupportRequest $supportRequest, User $requester)
    {
        // Thông báo cho người phê duyệt
        if ($supportRequest->approver && $supportRequest->approver->id !== $requester->id) {
            Notification::create([
                'user_id' => $supportRequest->approver->id,
                'type' => 'support_request_created',
                'title' => 'Yêu cầu hỗ trợ mới',
                'message' => "Bạn nhận được yêu cầu hỗ trợ mới từ {$requester->name}: {$supportRequest->title}",
                'data' => [
                    'support_request_id' => $supportRequest->id,
                    'requester_id' => $requester->id,
                    'requester_name' => $requester->name,
                    'priority' => $supportRequest->priority,
                    'is_urgent' => $supportRequest->is_urgent
                ]
            ]);
        }

        // Thông báo cho Admin
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            if ($admin->id !== $requester->id) {
                Notification::create([
                    'user_id' => $admin->id,
                    'type' => 'support_request_created',
                    'title' => 'Yêu cầu hỗ trợ mới',
                    'message' => "Có yêu cầu hỗ trợ mới từ {$requester->name}: {$supportRequest->title}",
                    'data' => [
                        'support_request_id' => $supportRequest->id,
                        'requester_id' => $requester->id,
                        'requester_name' => $requester->name,
                        'priority' => $supportRequest->priority,
                        'is_urgent' => $supportRequest->is_urgent
                    ]
                ]);
            }
        }

        // Thông báo cho Director
        $directors = User::where('role', 'director')->get();
        foreach ($directors as $director) {
            if ($director->id !== $requester->id) {
                Notification::create([
                    'user_id' => $director->id,
                    'type' => 'support_request_created',
                    'title' => 'Yêu cầu hỗ trợ mới',
                    'message' => "Có yêu cầu hỗ trợ mới từ {$requester->name}: {$supportRequest->title}",
                    'data' => [
                        'support_request_id' => $supportRequest->id,
                        'requester_id' => $requester->id,
                        'requester_name' => $requester->name,
                        'priority' => $supportRequest->priority,
                        'is_urgent' => $supportRequest->is_urgent
                    ]
                ]);
            }
        }
    }

    /**
     * Gửi thông báo yêu cầu hỗ trợ được phê duyệt
     */
    public static function supportRequestApproved(SupportRequest $supportRequest, User $approver)
    {
        // Thông báo cho người yêu cầu
        if ($supportRequest->requester && $supportRequest->requester->id !== $approver->id) {
            Notification::create([
                'user_id' => $supportRequest->requester->id,
                'type' => 'support_request_approved',
                'title' => 'Yêu cầu hỗ trợ được phê duyệt',
                'message' => "Yêu cầu hỗ trợ của bạn đã được {$approver->name} phê duyệt: {$supportRequest->title}",
                'data' => [
                    'support_request_id' => $supportRequest->id,
                    'approver_id' => $approver->id,
                    'approver_name' => $approver->name
                ]
            ]);
        }
    }

    /**
     * Gửi thông báo yêu cầu hỗ trợ bị từ chối
     */
    public static function supportRequestRejected(SupportRequest $supportRequest, User $approver, $reason = null)
    {
        // Thông báo cho người yêu cầu
        if ($supportRequest->requester && $supportRequest->requester->id !== $approver->id) {
            $message = "Yêu cầu hỗ trợ của bạn đã bị {$approver->name} từ chối: {$supportRequest->title}";
            if ($reason) {
                $message .= " - Lý do: {$reason}";
            }

            Notification::create([
                'user_id' => $supportRequest->requester->id,
                'type' => 'support_request_rejected',
                'title' => 'Yêu cầu hỗ trợ bị từ chối',
                'message' => $message,
                'data' => [
                    'support_request_id' => $supportRequest->id,
                    'approver_id' => $approver->id,
                    'approver_name' => $approver->name,
                    'reason' => $reason
                ]
            ]);
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

    /**
     * Xóa thông báo
     */
    public static function deleteNotification($notificationId, User $user)
    {
        $notification = Notification::where('id', $notificationId)
                                   ->where('user_id', $user->id)
                                   ->first();
        
        if ($notification) {
            $notification->delete();
            return true;
        }
        
        return false;
    }

    /**
     * Gửi thông báo task được forward
     */
    public static function taskForwarded(Task $task, User $forwarder, User $forwardedTo)
    {
        Notification::create([
            'user_id' => $forwardedTo->id,
            'type' => 'task_forwarded',
            'title' => 'Task được chuyển tiếp',
            'message' => "Bạn nhận được task được chuyển tiếp từ {$forwarder->name}: {$task->title}",
            'data' => [
                'task_id' => $task->id,
                'forwarder_id' => $forwarder->id,
                'forwarder_name' => $forwarder->name,
                'forward_reason' => $task->forward_reason
            ]
        ]);
    }

    /**
     * Gửi thông báo yêu cầu hỗ trợ đã được hoàn tác (undo)
     */
    public static function supportRequestUndone(SupportRequest $supportRequest, User $user, $oldStatus)
    {
        // Thông báo cho người yêu cầu
        if ($supportRequest->requester && $supportRequest->requester->id !== $user->id) {
            $actionText = $oldStatus === 'approved' ? 'phê duyệt' : 'từ chối';
            $message = "Yêu cầu hỗ trợ của bạn đã được {$user->name} hoàn tác {$actionText}: {$supportRequest->title}";

            Notification::create([
                'user_id' => $supportRequest->requester->id,
                'type' => 'support_request_undone',
                'title' => 'Yêu cầu hỗ trợ đã được hoàn tác',
                'message' => $message,
                'data' => [
                    'support_request_id' => $supportRequest->id,
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'old_status' => $oldStatus
                ]
            ]);
        }
    }

    /**
     * Gửi thông báo yêu cầu hỗ trợ đã bị hủy
     */
    public static function supportRequestCancelled(SupportRequest $supportRequest, User $user)
    {
        // Thông báo cho tất cả người nhận yêu cầu
        $recipients = $supportRequest->getRecipients();
        
        foreach ($recipients as $recipientId) {
            if ($recipientId !== $user->id) {
                $recipient = User::find($recipientId);
                if ($recipient) {
                    $message = "Yêu cầu hỗ trợ đã được {$user->name} hủy: {$supportRequest->title}";

                    Notification::create([
                        'user_id' => $recipientId,
                        'type' => 'support_request_cancelled',
                        'title' => 'Yêu cầu hỗ trợ đã bị hủy',
                        'message' => $message,
                        'data' => [
                            'support_request_id' => $supportRequest->id,
                            'cancelled_by_id' => $user->id,
                            'cancelled_by_name' => $user->name
                        ]
                    ]);
                }
            }
        }
    }

    /**
     * Gửi thông báo yêu cầu hỗ trợ đã được chuyển tiếp
     */
    public static function supportRequestForwarded(SupportRequest $supportRequest, User $forwarder)
    {
        // Thông báo cho người nhận mới
        if ($supportRequest->recipients) {
            $recipientIds = is_string($supportRequest->recipients) 
                ? json_decode($supportRequest->recipients, true) 
                : $supportRequest->recipients;
                
            if (is_array($recipientIds)) {
                foreach ($recipientIds as $recipientId) {
                    if ($recipientId !== $forwarder->id) {
                        $recipient = User::find($recipientId);
                        if ($recipient) {
                            Notification::create([
                                'user_id' => $recipientId,
                                'type' => 'support_request_forwarded',
                                'title' => 'Yêu cầu hỗ trợ được chuyển tiếp',
                                'message' => "Bạn nhận được yêu cầu hỗ trợ được chuyển tiếp từ {$forwarder->name}: {$supportRequest->title}",
                                'data' => [
                                    'support_request_id' => $supportRequest->id,
                                    'forwarder_id' => $forwarder->id,
                                    'forwarder_name' => $forwarder->name,
                                    'forwarding_reason' => $supportRequest->forwarding_reason
                                ]
                            ]);
                        }
                    }
                }
            }
        }

        // Thông báo cho người yêu cầu gốc
        if ($supportRequest->requester && $supportRequest->requester->id !== $forwarder->id) {
            Notification::create([
                'user_id' => $supportRequest->requester->id,
                'type' => 'support_request_forwarded',
                'title' => 'Yêu cầu hỗ trợ đã được chuyển tiếp',
                'message' => "Yêu cầu hỗ trợ của bạn đã được {$forwarder->name} chuyển tiếp: {$supportRequest->title}",
                'data' => [
                    'support_request_id' => $supportRequest->id,
                    'forwarder_id' => $forwarder->id,
                    'forwarder_name' => $forwarder->name,
                    'forwarding_reason' => $supportRequest->forwarding_reason
                ]
            ]);
        }
    }
}
