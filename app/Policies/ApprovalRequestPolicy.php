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
        // Người tạo, người được forward, hoặc cấp trên có quyền xem
        return $approvalRequest->created_by_id === $user->id ||
               $approvalRequest->current_approver_id === $user->id ||
               in_array($user->role, ['manager', 'director']);
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
        // Chỉ người tạo mới có thể cập nhật
        return $approvalRequest->created_by_id === $user->id;
    }

    /**
     * Determine whether the user can approve the model.
     */
    public function approve(User $user, ApprovalRequest $approvalRequest): bool
    {
        // Chỉ manager và director mới có quyền phê duyệt
        return in_array($user->role, ['manager', 'director']) && 
               $approvalRequest->approval_status === 'pending';
    }

    /**
     * Determine whether the user can forward the model.
     */
    public function forward(User $user, ApprovalRequest $approvalRequest): bool
    {
        // Người tạo hoặc người hiện tại cần phê duyệt có thể forward
        return $approvalRequest->created_by_id === $user->id ||
               $approvalRequest->current_approver_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ApprovalRequest $approvalRequest): bool
    {
        // Chỉ người tạo mới có thể xóa
        return $approvalRequest->created_by_id === $user->id;
    }
}
