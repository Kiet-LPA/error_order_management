<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'status', // 'pending', 'approved', 'rejected', 'forwarded', 'cancelled'
        'priority',
        'attachments',
        'department_id',
        'source_department_id', // phòng ban gốc của yêu cầu
        'requester_id', // người yêu cầu
        'approver_id', // người phê duyệt cuối cùng (giữ lại để tương thích)
        'recipients', // JSON array chứa danh sách người nhận yêu cầu
        'request_type', // 'employee' hoặc 'manager'
        'forwarded_by', // ai đã chuyển tiếp yêu cầu
        'forwarding_reason', // lý do chuyển tiếp
        'rejection_reason',
        'deadline',
        'is_urgent',
    ];

    protected $casts = [
        'attachments' => 'array',
        'recipients' => 'array',
        'deadline' => 'datetime',
        'is_urgent' => 'boolean',
    ];

    // Relationships
    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function sourceDepartment()
    {
        return $this->belongsTo(Department::class, 'source_department_id');
    }

    public function forwardedBy()
    {
        return $this->belongsTo(User::class, 'forwarded_by');
    }

    public function comments()
    {
        return $this->hasMany(SupportRequestComment::class);
    }

    public function followers()
    {
        return $this->hasMany(SupportRequestFollower::class);
    }

    public function activities()
    {
        return $this->hasMany(SupportRequestActivity::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeForwarded($query)
    {
        return $query->where('status', 'forwarded');
    }

    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeBySourceDepartment($query, $departmentId)
    {
        return $query->where('source_department_id', $departmentId);
    }

    public function scopeByRequester($query, $requesterId)
    {
        return $query->where('requester_id', $requesterId);
    }

    public function scopeByApprover($query, $approverId)
    {
        return $query->where('approver_id', $approverId);
    }

    public function scopeByRequestType($query, $type)
    {
        return $query->where('request_type', $type);
    }

    public function scopeEmployeeRequests($query)
    {
        return $query->where('request_type', 'employee');
    }

    public function scopeManagerRequests($query)
    {
        return $query->where('request_type', 'manager');
    }

    // Helper methods
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    public function isForwarded()
    {
        return $this->status === 'forwarded';
    }

    public function isEmployeeRequest()
    {
        return $this->request_type === 'employee';
    }

    public function isManagerRequest()
    {
        return $this->request_type === 'manager';
    }

    public function isUrgent()
    {
        return $this->is_urgent;
    }

    // Kiểm tra quyền phê duyệt
    public function canBeApprovedBy(User $user): bool
    {
        // Admin có thể phê duyệt tất cả
        if ($user->isAdmin()) {
            return true;
        }

        // Director có thể phê duyệt tất cả (như Admin)
        if ($user->isDirector()) {
            return true;
        }

        // Manager chỉ có thể phê duyệt yêu cầu được chỉ định cho họ
        if ($user->isManager()) {
            return $this->isRecipient($user);
        }

        return false;
    }

    // Kiểm tra quyền chuyển tiếp
    public function canBeForwardedBy(User $user): bool
    {
        // Admin có thể chuyển tiếp tất cả
        if ($user->isAdmin()) {
            return true;
        }

        // Director có thể chuyển tiếp tất cả (như Admin)
        if ($user->isDirector()) {
            return true;
        }

        // Manager chỉ có thể chuyển tiếp yêu cầu được chỉ định cho họ
        if ($user->isManager()) {
            return $this->isRecipient($user);
        }

        return false;
    }

    // Lấy danh sách người nhận
    public function getRecipients()
    {
        if (!$this->recipients) {
            return collect();
        }
        return User::whereIn('id', $this->recipients)->get();
    }

    // Kiểm tra user có phải là người nhận không
    public function isRecipient(User $user): bool
    {
        if (!$this->recipients) {
            return false;
        }
        return in_array($user->id, $this->recipients);
    }

    // Lấy trạng thái hiển thị
    public function getStatusLabel(): string
    {
        $labels = [
            'pending' => 'Chờ phê duyệt',
            'approved' => 'Đã phê duyệt',
            'rejected' => 'Bị từ chối',
            'forwarded' => 'Đã chuyển tiếp',
            'cancelled' => 'Đã hủy'
        ];
        
        return $labels[$this->status] ?? $this->status;
    }

    // Lấy màu trạng thái
    public function getStatusColor(): string
    {
        $colors = [
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            'forwarded' => 'info',
            'cancelled' => 'secondary'
        ];
        
        return $colors[$this->status] ?? 'secondary';
    }

    /**
     * Kiểm tra xem yêu cầu có thể được hoàn tác (undo) không
     */
    public function canBeUndone(): bool
    {
        // Chỉ có thể hoàn tác nếu đã được approve hoặc reject
        return in_array($this->status, ['approved', 'rejected']);
    }

    /**
     * Kiểm tra xem Employee có thể hủy yêu cầu không (trong 3 giờ và chưa được xử lý)
     */
    public function canBeCancelledByEmployee(): bool
    {
        // Chỉ có thể hủy nếu:
        // 1. Trạng thái vẫn là pending
        // 2. Đã tạo trong vòng 3 giờ
        // 3. Chưa có ai approve/reject/forward
        if ($this->status !== 'pending') {
            return false;
        }

        $threeHoursAgo = now()->subHours(3);
        return $this->created_at->greaterThan($threeHoursAgo);
    }

    /**
     * Hoàn tác (undo) approve/reject - chuyển về pending
     */
    public function undoApprovalRejection(User $user): bool
    {
        if (!$this->canBeUndone()) {
            return false;
        }

        $oldStatus = $this->status;
        
        $this->update([
            'status' => 'pending',
            'approver_id' => null,
            'rejection_reason' => null,
        ]);

        // Ghi log hoạt động
        SupportRequestActivity::create([
            'support_request_id' => $this->id,
            'user_id' => $user->id,
            'action' => 'undone',
            'meta' => [
                'old_status' => $oldStatus,
                'new_status' => 'pending'
            ]
        ]);

        return true;
    }

    /**
     * Hủy yêu cầu (chỉ Employee có thể làm)
     */
    public function cancelByEmployee(User $user): bool
    {
        if (!$this->canBeCancelledByEmployee()) {
            return false;
        }

        if ($this->requester_id !== $user->id) {
            return false;
        }

        $this->update([
            'status' => 'cancelled',
        ]);

        // Ghi log hoạt động
        SupportRequestActivity::create([
            'support_request_id' => $this->id,
            'user_id' => $user->id,
            'action' => 'cancelled',
            'meta' => ['status' => 'cancelled']
        ]);

        return true;
    }

    /**
     * Kiểm tra xem yêu cầu đã bị hủy chưa
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }
}
