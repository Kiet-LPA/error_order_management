<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ApprovalRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_type',
        'form_data',
        'status',
        'discussion_status',
        'edit_status',
        'approval_status',
        'created_by_id',
        'current_approver_id',
        'approved_by_id',
        'rejected_by_id',
        'approval_signatures',
        'approvers',
        'approved_at',
        'rejected_at'
    ];

    protected $casts = [
        'form_data' => 'array',
        'approval_signatures' => 'array',
        'approvers' => 'array',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function currentApprover()
    {
        return $this->belongsTo(User::class, 'current_approver_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }

    public function forwardedRequests()
    {
        return $this->hasMany(ForwardRequest::class);
    }

    public function forwardedToUsers()
    {
        return $this->belongsToMany(User::class, 'forward_requests', 'approval_request_id', 'forwarded_to_id')
                    ->withPivot(['forwarded_by_id', 'message', 'forwarded_at'])
                    ->withTimestamps();
    }

    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by_id');
    }

    public function forwards()
    {
        return $this->hasMany(ForwardRequest::class);
    }

    public function approvalActions()
    {
        return $this->hasMany(ApprovalAction::class);
    }

    public function comments()
    {
        return $this->hasMany(ApprovalComment::class);
    }

    public function approvalForm()
    {
        return $this->belongsTo(ApprovalForm::class, 'form_type', 'form_type');
    }

    public function canEdit($userId)
    {
        $user = User::find($userId);
        
        // Admin có thể edit tất cả
        if ($user && $user->isAdmin()) {
            return true;
        }
        
        // Director có thể edit tất cả (trừ Admin)
        if ($user && $user->isDirector()) {
            $creator = $this->creator;
            if ($creator && $creator->isAdmin()) {
                return false; // Director không thể edit approval request của Admin
            }
            return true;
        }
        
        // Người tạo có thể edit nếu edit_status là editable
        return $this->created_by_id === $userId && $this->edit_status === 'editable';
    }

    public function canChangeStatus($userId)
    {
        return $this->created_by_id === $userId;
    }

    /**
     * Kiểm tra xem user có thể xóa approval request này không
     */
    public function canBeDeletedBy($user)
    {
        if (!$user) return false;
        
        // Admin và Director có thể xóa bất kỳ approval request nào
        if ($user->isAdmin() || $user->isDirector()) {
            return true;
        }
        
        // Manager có thể xóa approval request của Employee trong cùng phòng ban
        if ($user->isManager()) {
            $creator = $this->creator;
            if ($creator && $creator->isEmployee() && $creator->department_id === $user->department_id) {
                return true;
            }
        }
        
        // Người tạo có thể xóa approval request của chính mình (chỉ khi chưa được xử lý)
        if ($this->created_by_id === $user->id && $this->approval_status === 'pending') {
            return true;
        }
        
        return false;
    }

    public function getApprovalStatusText()
    {
        return match($this->approval_status) {
            'pending' => 'Chờ phê duyệt',
            'approved' => 'Đã phê duyệt',
            'rejected' => 'Đã từ chối',
            'cancelled' => 'Đã hủy'
        };
    }

    public function getApprovalSignaturesText()
    {
        if (!$this->approval_signatures) return '';
        
        $signatures = [];
        foreach ($this->approval_signatures as $signature) {
            $role = $signature['role'] === 'manager' ? 'Quản lý' : 'Người điều hành';
            $action = $signature['action'] === 'approve' ? 'đã phê duyệt' : 'đã từ chối';
            $signatures[] = "{$role} {$action}";
        }
        
        return implode(', ', $signatures);
    }

    public function getFormConfig()
    {
        return ApprovalForm::where('form_type', $this->form_type)->first();
    }

    public function scopeByUser($query, $userId)
    {
        $user = \App\Models\User::find($userId);
        
        // Admin và Director có thể xem tất cả
        if ($user && in_array($user->role, ['admin', 'director'])) {
            return $query;
        }
        
        // User thường chỉ xem requests của mình hoặc được assign
        return $query->where('created_by_id', $userId)
                    ->orWhere('current_approver_id', $userId);
    }

    public function scopePendingApproval($query, $userId)
    {
        return $query->where('current_approver_id', $userId)
                    ->where('approval_status', 'pending');
    }

    public function getFormFieldValue($fieldName, $fieldConfig)
    {
        $value = $this->form_data[$fieldName] ?? null;
        
        if (!$value || $value === '') return null;
        
        // Xử lý field department
        if ($fieldName === 'department' && $fieldConfig['type'] === 'select') {
            $department = \App\Models\Department::find($value);
            return $department ? $department->name : 'Không tìm thấy phòng ban';
        }
        
        // Xử lý field manager (người phê duyệt)
        if ($fieldName === 'manager' && $fieldConfig['type'] === 'select') {
            $user = \App\Models\User::find($value);
            if ($user) {
                $roleText = match($user->role) {
                    'manager' => 'Quản lý',
                    'director' => 'Giám đốc',
                    'employee' => 'Nhân viên',
                    default => ucfirst($user->role)
                };
                return $user->name . ' (' . $roleText . ')';
            }
            return 'Không tìm thấy người phê duyệt';
        }
        
        // Xử lý các field select khác
        if ($fieldConfig['type'] === 'select' && isset($fieldConfig['options'])) {
            foreach ($fieldConfig['options'] as $option) {
                if ($option['value'] == $value) {
                    return $option['label'];
                }
            }
        }
        
        return $value;
    }

    /**
     * Kiểm tra quyền phê duyệt
     */
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

        // Manager có thể phê duyệt nếu là current approver
        if ($user->isManager()) {
            return $this->current_approver_id === $user->id;
        }

        return false;
    }

}
