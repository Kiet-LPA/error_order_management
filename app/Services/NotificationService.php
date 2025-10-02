<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Task;
use App\Models\User;
use App\Models\WorkReport;
use App\Models\SupportRequest;
use App\Models\TaskApproval;

class NotificationService
{
    /**
     * Gửi thông báo cho tất cả admin và director
     */
    private static function notifyAdminsAndDirectors($type, $title, $message, $data = [], $excludeUserIds = [])
    {
        // Gửi cho tất cả admin
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            if (!in_array($admin->id, $excludeUserIds)) {
                Notification::create([
                    'user_id' => $admin->id,
                    'type' => $type,
                    'title' => $title,
                    'message' => $message,
                    'data' => $data
                ]);
            }
        }

        // Gửi cho tất cả director
        $directors = User::where('role', 'director')->get();
        foreach ($directors as $director) {
            if (!in_array($director->id, $excludeUserIds)) {
                Notification::create([
                    'user_id' => $director->id,
                    'type' => $type,
                    'title' => $title,
                    'message' => $message,
                    'data' => $data
                ]);
            }
        }
    }

    /**
     * Gửi thông báo cho admin và director có liên quan
     */
    private static function notifyRelevantAdminsAndDirectors($type, $title, $message, $data = [], $excludeUserIds = [], $relatedObject = null)
    {
        // Gửi cho tất cả admin
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            if (!in_array($admin->id, $excludeUserIds)) {
                Notification::create([
                    'user_id' => $admin->id,
                    'type' => $type,
                    'title' => $title,
                    'message' => $message,
                    'data' => $data
                ]);
            }
        }

        // Gửi cho director có liên quan
        $directors = User::where('role', 'director')->get();
        foreach ($directors as $director) {
            if (!in_array($director->id, $excludeUserIds) && self::isDirectorRelevant($director, $relatedObject, $data)) {
                Notification::create([
                    'user_id' => $director->id,
                    'type' => $type,
                    'title' => $title,
                    'message' => $message,
                    'data' => $data
                ]);
            }
        }
    }

    /**
     * Kiểm tra director có liên quan đến object không
     */
    private static function isDirectorRelevant(User $director, $relatedObject = null, $data = [])
    {
        // Nếu không có object liên quan, không gửi notification cho director
        if (!$relatedObject && empty($data)) {
            return false;
        }

        // Kiểm tra theo loại object
        if ($relatedObject instanceof Task) {
            return self::isDirectorRelevantToTask($director, $relatedObject);
        } elseif ($relatedObject instanceof SupportRequest) {
            return self::isDirectorRelevantToSupportRequest($director, $relatedObject);
        } elseif ($relatedObject instanceof \App\Models\ApprovalRequest) {
            return self::isDirectorRelevantToApprovalRequest($director, $relatedObject);
        }

        // Nếu có data chứa thông tin liên quan
        if (!empty($data)) {
            return self::isDirectorRelevantToData($director, $data);
        }

        return false;
    }

    /**
     * Kiểm tra director có liên quan đến task không
     */
    private static function isDirectorRelevantToTask(User $director, Task $task)
    {
        // Director liên quan nếu:
        // 1. Là creator của task
        // 2. Là assignee của task
        // 3. Là follower của task
        // 4. Task thuộc phòng ban mà director quản lý
        return $task->creator_id == $director->id ||
               $task->assignee_id == $director->id ||
               $task->followers()->where('user_id', $director->id)->exists() ||
               $task->assignees()->where('user_id', $director->id)->exists() ||
               self::isDirectorOfTaskDepartment($director, $task);
    }

    /**
     * Kiểm tra director có liên quan đến support request không
     */
    private static function isDirectorRelevantToSupportRequest(User $director, SupportRequest $supportRequest)
    {
        // Director liên quan nếu:
        // 1. Là requester
        // 2. Là approver
        // 3. Là follower
        // 4. Là recipient
        return $supportRequest->requester_id == $director->id ||
               $supportRequest->approver_id == $director->id ||
               $supportRequest->followers()->where('user_id', $director->id)->exists() ||
               $supportRequest->isRecipient($director);
    }

    /**
     * Kiểm tra director có liên quan đến approval request không
     */
    private static function isDirectorRelevantToApprovalRequest(User $director, \App\Models\ApprovalRequest $approvalRequest)
    {
        // Director liên quan nếu:
        // 1. Là creator
        // 2. Là current approver
        // 3. Là approved by
        return $approvalRequest->created_by_id == $director->id ||
               $approvalRequest->current_approver_id == $director->id ||
               $approvalRequest->approved_by_id == $director->id;
    }

    /**
     * Kiểm tra director có liên quan đến data không
     */
    private static function isDirectorRelevantToData(User $director, array $data)
    {
        // Kiểm tra các trường thông thường trong data
        $relevantFields = ['creator_id', 'assigner_id', 'approver_id', 'requester_id', 'current_approver_id'];
        
        foreach ($relevantFields as $field) {
            if (isset($data[$field]) && $data[$field] == $director->id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Kiểm tra director có phải là director của phòng ban task không
     */
    private static function isDirectorOfTaskDepartment(User $director, Task $task)
    {
        // Kiểm tra phòng ban chính
        if ($task->department_id && $director->department_id == $task->department_id) {
            return true;
        }

        // Kiểm tra các phòng ban multi-department
        if ($task->is_multi_department) {
            $taskDepartmentIds = $task->departments()->pluck('departments.id')->toArray();
            return in_array($director->department_id, $taskDepartmentIds);
        }

        return false;
    }
    /**
     * Gửi thông báo task được giao
     */
    public static function taskAssigned(Task $task, User $assigner, User $assignee)
    {
        // Thông báo cho người được giao
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

        // Thông báo cho admin và director có liên quan
        self::notifyRelevantAdminsAndDirectors(
            'task_assigned',
            'Công việc mới được giao',
            "{$assigner->name} đã giao công việc '{$task->title}' cho {$assignee->name}",
            [
                'task_id' => $task->id,
                'assigner_id' => $assigner->id,
                'assigner_name' => $assigner->name,
                'assignee_id' => $assignee->id,
                'assignee_name' => $assignee->name
            ],
            [],
            $task
        );
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

        // Thông báo cho admin và director có liên quan
        self::notifyRelevantAdminsAndDirectors(
            'task_updated',
            'Công việc được cập nhật',
            "{$updater->name} đã cập nhật công việc '{$task->title}'",
            [
                'task_id' => $task->id,
                'updater_id' => $updater->id,
                'updater_name' => $updater->name
            ],
            [],
            $task
        );
    }

    /**
     * Gửi thông báo work report được submit
     */
    public static function workReportSubmitted(WorkReport $report, User $submitter)
    {
        // Thông báo cho Admin và Director có liên quan
        self::notifyRelevantAdminsAndDirectors(
            'work_report_submitted',
            'Báo cáo công việc mới',
            "Bạn nhận được thông báo mời từ {$submitter->name} gửi báo cáo công việc",
            [
                'report_id' => $report->id,
                'submitter_id' => $submitter->id,
                'submitter_name' => $submitter->name
            ]
        );

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
        // Thông báo cho recipients (những người được chọn để nhận yêu cầu)
        if ($supportRequest->recipients) {
            $recipientIds = is_string($supportRequest->recipients) 
                ? json_decode($supportRequest->recipients, true) 
                : $supportRequest->recipients;
                
            if (is_array($recipientIds)) {
                foreach ($recipientIds as $recipientId) {
                    if ($recipientId !== $requester->id) {
                        $recipient = User::find($recipientId);
                        if ($recipient) {
                            Notification::create([
                                'user_id' => $recipientId,
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
                    }
                }
            }
        }

        // Thông báo cho người phê duyệt (nếu có và khác với recipients)
        if ($supportRequest->approver && $supportRequest->approver->id !== $requester->id) {
            // Kiểm tra xem approver đã được thông báo chưa (qua recipients)
            $recipientIds = is_string($supportRequest->recipients) 
                ? json_decode($supportRequest->recipients, true) 
                : $supportRequest->recipients;
                
            $isApproverNotified = is_array($recipientIds) && in_array($supportRequest->approver->id, $recipientIds);
            
            if (!$isApproverNotified) {
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
        }

        // Thông báo cho Admin và Director có liên quan
        self::notifyRelevantAdminsAndDirectors(
            'support_request_created',
            'Yêu cầu hỗ trợ mới',
            "Có yêu cầu hỗ trợ mới từ {$requester->name}: {$supportRequest->title}",
            [
                'support_request_id' => $supportRequest->id,
                'requester_id' => $requester->id,
                'requester_name' => $requester->name,
                'priority' => $supportRequest->priority,
                'is_urgent' => $supportRequest->is_urgent
            ],
            [$requester->id],
            $supportRequest
        );
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

    /**
     * Gửi thông báo khi có đề xuất phê duyệt mới
     */
    public static function approvalRequestCreated(TaskApproval $approval, User $creator)
    {
        // Thông báo cho manager cần phê duyệt
        Notification::create([
            'user_id' => $approval->manager_id,
            'type' => 'approval_request_created',
            'title' => 'Có đề xuất phê duyệt mới',
            'message' => "Bạn có đề xuất phê duyệt mới từ {$creator->name} cho công việc: {$approval->task->title}",
            'data' => [
                'approval_id' => $approval->id,
                'task_id' => $approval->task_id,
                'creator_id' => $creator->id,
                'creator_name' => $creator->name,
                'department_id' => $approval->department_id,
                'department_name' => $approval->department->name ?? 'N/A'
            ]
        ]);
    }

    /**
     * Gửi thông báo khi đề xuất được phê duyệt
     */
    public static function approvalRequestApproved(TaskApproval $approval, User $approver)
    {
        // Thông báo cho người tạo task
        if ($approval->task->creator && $approval->task->creator->id !== $approver->id) {
            Notification::create([
                'user_id' => $approval->task->creator->id,
                'type' => 'approval_request_approved',
                'title' => 'Đề xuất phê duyệt được chấp nhận',
                'message' => "Đề xuất phê duyệt của bạn đã được {$approver->name} chấp nhận cho công việc: {$approval->task->title}",
                'data' => [
                    'approval_id' => $approval->id,
                    'task_id' => $approval->task_id,
                    'approver_id' => $approver->id,
                    'approver_name' => $approver->name,
                    'department_id' => $approval->department_id,
                    'department_name' => $approval->department->name ?? 'N/A',
                    'comment' => $approval->comment
                ]
            ]);
        }

        // Thông báo cho tất cả assignees của task
        foreach ($approval->task->assignees as $assignee) {
            if ($assignee->id !== $approver->id && $assignee->id !== $approval->task->creator->id) {
                Notification::create([
                    'user_id' => $assignee->id,
                    'type' => 'approval_request_approved',
                    'title' => 'Công việc được phê duyệt',
                    'message' => "Công việc {$approval->task->title} đã được {$approver->name} phê duyệt và có thể tiếp tục thực hiện",
                    'data' => [
                        'approval_id' => $approval->id,
                        'task_id' => $approval->task_id,
                        'approver_id' => $approver->id,
                        'approver_name' => $approver->name,
                        'department_id' => $approval->department_id,
                        'department_name' => $approval->department->name ?? 'N/A'
                    ]
                ]);
            }
        }
    }

    /**
     * Gửi thông báo khi đề xuất bị từ chối
     */
    public static function approvalRequestRejected(TaskApproval $approval, User $approver)
    {
        // Thông báo cho người tạo task
        if ($approval->task->creator && $approval->task->creator->id !== $approver->id) {
            $message = "Đề xuất phê duyệt của bạn đã bị {$approver->name} từ chối cho công việc: {$approval->task->title}";
            if ($approval->comment) {
                $message .= " - Lý do: {$approval->comment}";
            }

            Notification::create([
                'user_id' => $approval->task->creator->id,
                'type' => 'approval_request_rejected',
                'title' => 'Đề xuất phê duyệt bị từ chối',
                'message' => $message,
                'data' => [
                    'approval_id' => $approval->id,
                    'task_id' => $approval->task_id,
                    'approver_id' => $approver->id,
                    'approver_name' => $approver->name,
                    'department_id' => $approval->department_id,
                    'department_name' => $approval->department->name ?? 'N/A',
                    'comment' => $approval->comment
                ]
            ]);
        }

        // Thông báo cho tất cả assignees của task
        foreach ($approval->task->assignees as $assignee) {
            if ($assignee->id !== $approver->id && $assignee->id !== $approval->task->creator->id) {
                $message = "Công việc {$approval->task->title} đã bị {$approver->name} từ chối phê duyệt";
                if ($approval->comment) {
                    $message .= " - Lý do: {$approval->comment}";
                }

                Notification::create([
                    'user_id' => $assignee->id,
                    'type' => 'approval_request_rejected',
                    'title' => 'Công việc bị từ chối phê duyệt',
                    'message' => $message,
                    'data' => [
                        'approval_id' => $approval->id,
                        'task_id' => $approval->task_id,
                        'approver_id' => $approver->id,
                        'approver_name' => $approver->name,
                        'department_id' => $approval->department_id,
                        'department_name' => $approval->department->name ?? 'N/A',
                        'comment' => $approval->comment
                    ]
                ]);
            }
        }
    }

    /**
     * Gửi thông báo khi task được phê duyệt hoàn toàn (tất cả departments)
     */
    public static function taskFullyApproved(Task $task, User $lastApprover)
    {
        // Thông báo cho người tạo task
        if ($task->creator && $task->creator->id !== $lastApprover->id) {
            Notification::create([
                'user_id' => $task->creator->id,
                'type' => 'task_fully_approved',
                'title' => 'Công việc được phê duyệt hoàn toàn',
                'message' => "Công việc {$task->title} đã được phê duyệt hoàn toàn và có thể tiếp tục thực hiện",
                'data' => [
                    'task_id' => $task->id,
                    'last_approver_id' => $lastApprover->id,
                    'last_approver_name' => $lastApprover->name
                ]
            ]);
        }

        // Thông báo cho tất cả assignees
        foreach ($task->assignees as $assignee) {
            if ($assignee->id !== $lastApprover->id && $assignee->id !== $task->creator->id) {
                Notification::create([
                    'user_id' => $assignee->id,
                    'type' => 'task_fully_approved',
                    'title' => 'Công việc được phê duyệt hoàn toàn',
                    'message' => "Công việc {$task->title} đã được phê duyệt hoàn toàn và có thể tiếp tục thực hiện",
                    'data' => [
                        'task_id' => $task->id,
                        'last_approver_id' => $lastApprover->id,
                        'last_approver_name' => $lastApprover->name
                    ]
                ]);
            }
        }
    }

    /**
     * Gửi thông báo khi tạo approval request mới (ApprovalRequest model)
     */
    public static function approvalRequestCreatedNew($approvalRequest, User $creator)
    {
        $notificationData = [
            'approval_request_id' => $approvalRequest->id,
            'creator_id' => $creator->id,
            'creator_name' => $creator->name,
            'form_type' => $approvalRequest->form_type
        ];

        // Thông báo cho current approver (nếu có)
        if ($approvalRequest->current_approver_id && $approvalRequest->current_approver_id !== $creator->id) {
            $approver = User::find($approvalRequest->current_approver_id);
            if ($approver) {
                Notification::create([
                    'user_id' => $approvalRequest->current_approver_id,
                    'type' => 'approval_request_created',
                    'title' => 'Có đề xuất phê duyệt mới',
                    'message' => "Bạn có đề xuất phê duyệt mới từ {$creator->name} cần xử lý",
                    'data' => $notificationData
                ]);
            }
        }

        // Thông báo cho Admin và Director có liên quan (trừ current approver đã được thông báo)
        $excludeUserIds = [];
        if ($approvalRequest->current_approver_id) {
            $excludeUserIds[] = $approvalRequest->current_approver_id;
        }
        
        self::notifyRelevantAdminsAndDirectors(
            'approval_request_created',
            'Có đề xuất phê duyệt mới',
            "Có đề xuất phê duyệt mới từ {$creator->name} cần xử lý",
            $notificationData,
            $excludeUserIds,
            $approvalRequest
        );
    }

    /**
     * Gửi thông báo khi approval request bị hủy
     */
    public static function approvalRequestCancelled($approvalRequest, User $canceller)
    {
        // Thông báo cho current approver (nếu có)
        if ($approvalRequest->current_approver_id && $approvalRequest->current_approver_id !== $canceller->id) {
            Notification::create([
                'user_id' => $approvalRequest->current_approver_id,
                'type' => 'approval_request_cancelled',
                'title' => 'Đề xuất phê duyệt đã bị hủy',
                'message' => "Đề xuất phê duyệt đã được {$canceller->name} hủy",
                'data' => [
                    'approval_request_id' => $approvalRequest->id,
                    'canceller_id' => $canceller->id,
                    'canceller_name' => $canceller->name,
                    'form_type' => $approvalRequest->form_type
                ]
            ]);
        }

        // Thông báo cho Admin
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            if ($admin->id !== $canceller->id) {
                Notification::create([
                    'user_id' => $admin->id,
                    'type' => 'approval_request_cancelled',
                    'title' => 'Đề xuất phê duyệt đã bị hủy',
                    'message' => "Đề xuất phê duyệt đã được {$canceller->name} hủy",
                    'data' => [
                        'approval_request_id' => $approvalRequest->id,
                        'canceller_id' => $canceller->id,
                        'canceller_name' => $canceller->name,
                        'form_type' => $approvalRequest->form_type
                    ]
                ]);
            }
        }

        // Thông báo cho Director có liên quan
        self::notifyRelevantAdminsAndDirectors(
            'approval_request_cancelled',
            'Đề xuất phê duyệt đã bị hủy',
            "Đề xuất phê duyệt đã được {$canceller->name} hủy",
            [
                'approval_request_id' => $approvalRequest->id,
                'canceller_id' => $canceller->id,
                'canceller_name' => $canceller->name,
                'form_type' => $approvalRequest->form_type
            ],
            [$canceller->id],
            $approvalRequest
        );
    }

    /**
     * Gửi thông báo khi approval request được phê duyệt (ApprovalRequest model)
     */
    public static function approvalRequestApprovedNew($approvalRequest, User $approver)
    {
        // Thông báo cho người tạo
        if ($approvalRequest->created_by_id && $approvalRequest->created_by_id !== $approver->id) {
            Notification::create([
                'user_id' => $approvalRequest->created_by_id,
                'type' => 'approval_request_approved',
                'title' => 'Đề xuất đã được phê duyệt',
                'message' => "Đề xuất của bạn đã được {$approver->name} phê duyệt",
                'data' => [
                    'approval_request_id' => $approvalRequest->id,
                    'approver_id' => $approver->id,
                    'approver_name' => $approver->name,
                    'form_type' => $approvalRequest->form_type
                ]
            ]);
        }

        // Thông báo cho Admin và Director có liên quan
        self::notifyRelevantAdminsAndDirectors(
            'approval_request_approved',
            'Đề xuất đã được phê duyệt',
            "Đề xuất đã được {$approver->name} phê duyệt",
            [
                'approval_request_id' => $approvalRequest->id,
                'approver_id' => $approver->id,
                'approver_name' => $approver->name,
                'form_type' => $approvalRequest->form_type
            ],
            [],
            $approvalRequest
        );
    }

    /**
     * Gửi thông báo khi approval request bị từ chối (ApprovalRequest model)
     */
    public static function approvalRequestRejectedNew($approvalRequest, User $approver)
    {
        // Thông báo cho người tạo
        if ($approvalRequest->created_by_id && $approvalRequest->created_by_id !== $approver->id) {
            Notification::create([
                'user_id' => $approvalRequest->created_by_id,
                'type' => 'approval_request_rejected',
                'title' => 'Đề xuất bị từ chối',
                'message' => "Đề xuất của bạn đã bị {$approver->name} từ chối",
                'data' => [
                    'approval_request_id' => $approvalRequest->id,
                    'approver_id' => $approver->id,
                    'approver_name' => $approver->name,
                    'form_type' => $approvalRequest->form_type
                ]
            ]);
        }

        // Thông báo cho Admin và Director có liên quan
        self::notifyRelevantAdminsAndDirectors(
            'approval_request_rejected',
            'Đề xuất bị từ chối',
            "Đề xuất đã bị {$approver->name} từ chối",
            [
                'approval_request_id' => $approvalRequest->id,
                'approver_id' => $approver->id,
                'approver_name' => $approver->name,
                'form_type' => $approvalRequest->form_type
            ],
            [],
            $approvalRequest
        );
    }
}
