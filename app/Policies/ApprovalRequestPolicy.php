<?php

namespace App\Policies;

use App\Models\ApprovalRequest;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ApprovalRequestPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Tất cả user đều có thể xem danh sách
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ApprovalRequest $approvalRequest): bool
    {
        // Admin có toàn quyền xem tất cả
        if ($user->isAdmin()) {
            return true;
        }
        
        // Director có toàn quyền xem tất cả (trừ Admin)
        if ($user->isDirector()) {
            return true;
        }
        
        // Người tạo, người được forward, hoặc manager có quyền xem
        return $approvalRequest->created_by_id === $user->id ||
               $approvalRequest->current_approver_id === $user->id ||
               $user->role === 'manager';
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // Tất cả user đều có thể tạo đề xuất
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ApprovalRequest $approvalRequest): bool
    {
        // Admin có toàn quyền cập nhật
        if ($user->isAdmin()) {
            return true;
        }
        
        // Director có toàn quyền cập nhật (trừ Admin)
        if ($user->isDirector()) {
            // Kiểm tra xem người tạo có phải Admin không
            $creator = $approvalRequest->creator;
            if ($creator && $creator->isAdmin()) {
                return false; // Director không thể edit approval request của Admin
            }
            return true;
        }
        
        // Chỉ người tạo mới có thể cập nhật
        return $approvalRequest->created_by_id === $user->id;
    }

    /**
     * Determine whether the user can approve the model.
     */
    public function approve(User $user, ApprovalRequest $approvalRequest): bool
    {
        // Admin có toàn quyền phê duyệt
        if ($user->isAdmin()) {
            return true;
        }
        
        // Director có toàn quyền phê duyệt (trừ Admin)
        if ($user->isDirector()) {
            // Kiểm tra xem người tạo có phải Admin không
            $creator = $approvalRequest->creator;
            if ($creator && $creator->isAdmin()) {
                return false; // Director không thể approve approval request của Admin
            }
            return true;
        }
        
        // Manager có quyền phê duyệt nếu status là pending
        return $user->role === 'manager' && $approvalRequest->approval_status === 'pending';
    }

    /**
     * Determine whether the user can forward the model.
     */
    public function forward(User $user, ApprovalRequest $approvalRequest): bool
    {
        // Admin có toàn quyền forward
        if ($user->isAdmin()) {
            return true;
        }
        
        // Director có toàn quyền forward (trừ Admin)
        if ($user->isDirector()) {
            // Kiểm tra xem người tạo có phải Admin không
            $creator = $approvalRequest->creator;
            if ($creator && $creator->isAdmin()) {
                return false; // Director không thể forward approval request của Admin
            }
            return true;
        }
        
        // Người tạo hoặc người hiện tại cần phê duyệt có thể forward
        return $approvalRequest->created_by_id === $user->id ||
               $approvalRequest->current_approver_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ApprovalRequest $approvalRequest): bool
    {
        // Admin có toàn quyền xóa
        if ($user->isAdmin()) {
            return true;
        }
        
        // Director có toàn quyền xóa (trừ Admin)
        if ($user->isDirector()) {
            // Kiểm tra xem người tạo có phải Admin không
            $creator = $approvalRequest->creator;
            if ($creator && $creator->isAdmin()) {
                return false; // Director không thể xóa approval request của Admin
            }
            return true;
        }
        
        // Chỉ người tạo mới có thể xóa
        return $approvalRequest->created_by_id === $user->id;
    }
}
