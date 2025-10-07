<?php

namespace App\Services;

use App\Models\User;
use App\Models\Task;
use App\Models\Department;

class TaskPermissionService
{
    /**
     * Kiểm tra user có thể tạo task không
     */
    public static function canCreateTask(User $user): bool
    {
        return $user->isAdmin() || $user->isDirector() || $user->isManager();
    }

    /**
     * Kiểm tra user có thể xem task không
     */
    public static function canViewTask(User $user, Task $task): bool
    {
        // Admin và Director có thể xem tất cả
        if ($user->isAdmin() || $user->isDirector()) {
            return true;
        }

        // Manager có thể xem task của phòng ban mình hoặc task mà họ follow
        if ($user->isManager()) {
            return self::isTaskInManagerDepartment($user, $task) ||
                   $task->followers->contains('id', $user->id) ||
                   $task->forwarded_to === $user->id;
        }

        // Employee có thể xem task được assign, tạo, follow, hoặc forward
        if ($user->isEmployee()) {
            return self::isTaskAssignedToUser($user, $task) ||
                   $task->creator_id === $user->id ||
                   $task->followers->contains('id', $user->id) ||
                   $task->forwarded_to === $user->id;
        }

        return false;
    }

    /**
     * Kiểm tra user có thể sửa task không
     */
    public static function canEditTask(User $user, Task $task): bool
    {
        // Admin có thể sửa tất cả
        if ($user->isAdmin()) {
            return true;
        }
        
        // Director có thể sửa tất cả (trừ Admin)
        if ($user->isDirector()) {
            // Kiểm tra xem creator có phải Admin không
            $creator = $task->creator;
            if ($creator && $creator->isAdmin()) {
                return false; // Director không thể edit task của Admin
            }
            return true;
        }

        // Manager chỉ có thể sửa task thuộc thẩm quyền của họ
        if ($user->isManager()) {
            // ❌ KHÔNG được sửa task được giao bởi Director hoặc Admin
            $creator = $task->creator;
            if ($creator && ($creator->isAdmin() || $creator->isDirector())) {
                return false; // Manager không thể sửa task được giao bởi cấp cao hơn
            }
            
            // ✅ Chỉ có thể sửa task được giao bởi Manager khác hoặc Employee
            // và task phải thuộc phòng ban của họ
            return self::isTaskInManagerDepartment($user, $task) ||
                   $task->forwarded_to === $user->id;
        }

        // Employee có thể sửa task được assign hoặc tạo
        if ($user->isEmployee()) {
            return self::isTaskAssignedToUser($user, $task) ||
                   $task->creator_id === $user->id;
        }

        return false;
    }

    /**
     * Kiểm tra user có thể submit task (gửi duyệt) không
     * LOGIC: Manager nhận việc = Employee, Manager giao việc = Director
     */
    public static function canSubmitTask(User $user, Task $task): bool
    {
        // Admin và Director có thể submit bất kỳ task nào
        if ($user->isAdmin() || $user->isDirector()) {
            return true;
        }
        
        // Employee có thể submit task được assign cho họ
        if ($user->isEmployee()) {
            return self::isTaskAssignedToUser($user, $task);
        }
        
        // Manager: 
        // - Nếu được assign (nhận việc) -> có thể submit như Employee
        // - Nếu là creator (giao việc) -> có toàn quyền như Director
        if ($user->isManager()) {
            // Manager nhận việc: có thể submit
            if (self::isTaskAssignedToUser($user, $task)) {
                return true;
            }
            
            // Manager giao việc: có toàn quyền
            if ($task->creator_id === $user->id) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Kiểm tra user có thể xóa task không
     */
    public static function canDeleteTask(User $user, Task $task): bool
    {
        // Admin có thể xóa tất cả
        if ($user->isAdmin()) {
            return true;
        }
        
        // Director có thể xóa tất cả (trừ Admin)
        if ($user->isDirector()) {
            // Kiểm tra xem creator có phải Admin không
            $creator = $task->creator;
            if ($creator && $creator->isAdmin()) {
                return false; // Director không thể delete task của Admin
            }
            return true;
        }

        // Manager chỉ có thể xóa task thuộc thẩm quyền của họ
        if ($user->isManager()) {
            // ❌ KHÔNG được xóa task được giao bởi Director hoặc Admin
            $creator = $task->creator;
            if ($creator && ($creator->isAdmin() || $creator->isDirector())) {
                return false; // Manager không thể xóa task được giao bởi cấp cao hơn
            }
            
            // ✅ Chỉ có thể xóa task được giao bởi Manager khác hoặc Employee
            // và task phải thuộc phòng ban của họ
            return self::isTaskInManagerDepartment($user, $task) ||
                   $task->forwarded_to === $user->id;
        }

        // Employee có thể xóa task được assign hoặc tạo
        if ($user->isEmployee()) {
            return self::isTaskAssignedToUser($user, $task) ||
                   $task->creator_id === $user->id;
        }

        return false;
    }

    /**
     * Kiểm tra user có thể giao task cho user khác không
     */
    public static function canAssignTask(User $user, User $targetUser): bool
    {
        // Admin có thể giao cho tất cả
        if ($user->isAdmin()) {
            return true;
        }

        // Director có thể giao cho tất cả (trừ Admin)
        if ($user->isDirector()) {
            return !$targetUser->isAdmin();
        }

        // Manager chỉ có thể giao cho Employee trong các phòng ban mà Manager quản lý
        if ($user->isManager()) {
            if (!$targetUser->isEmployee()) {
                return false;
            }
            
            // Lấy danh sách phòng ban mà Manager quản lý
            $managerDepartmentIds = $user->departments->pluck('id')->toArray();
            
            // Kiểm tra xem Employee có thuộc bất kỳ phòng ban nào mà Manager quản lý không
            $employeeDepartmentIds = $targetUser->departments->pluck('id')->toArray();
            
            return !empty(array_intersect($managerDepartmentIds, $employeeDepartmentIds));
        }

        // Employee không thể giao task
        return false;
    }

    /**
     * Kiểm tra user có thể giao task cho department không
     */
    public static function canAssignTaskToDepartment(User $user, Department $department): bool
    {
        // Admin có thể giao cho tất cả department
        if ($user->isAdmin()) {
            return true;
        }

        // Director có thể giao cho tất cả department
        if ($user->isDirector()) {
            return true;
        }

        // Manager chỉ có thể giao cho các phòng ban mà Manager quản lý
        if ($user->isManager()) {
            $managerDepartmentIds = $user->departments->pluck('id')->toArray();
            return in_array($department->id, $managerDepartmentIds);
        }

        // Employee không thể giao task
        return false;
    }

    /**
     * Kiểm tra user có thể forward task không
     */
    public static function canForwardTask(User $user, Task $task, User $targetUser): bool
    {
        // Admin và Director có thể forward cho bất kỳ Manager nào
        if ($user->isAdmin() || $user->isDirector()) {
            return $targetUser->isManager() && $targetUser->id !== $user->id;
        }

        // Manager có thể forward cho Manager khác (nếu có quyền truy cập task)
        if ($user->isManager()) {
            return $targetUser->isManager() && 
                   $targetUser->id !== $user->id &&
                   self::canViewTask($user, $task);
        }

        return false;
    }

    /**
     * Kiểm tra user có thể approve task không
     */
    public static function canApproveTask(User $user, Task $task): bool
    {
        // Employee có thể resubmit task bị reject nếu là assignee
        if ($user->role === 'employee' && $task->status === 'rejected') {
            // Load assignees nếu chưa có
            if (!$task->relationLoaded('assignees')) {
                $task->load('assignees');
            }
            
            // Kiểm tra user là assignee (assignee_id hoặc trong danh sách assignees)
            $isAssigned = $task->assignee_id === $user->id || $task->assignees->contains('id', $user->id);
            if ($isAssigned) {
                return true;
            }
        }
        
        // Admin có thể approve tất cả
        if ($user->isAdmin()) {
            return true;
        }
        
        // Director có thể approve tất cả (trừ task của Admin)
        if ($user->isDirector()) {
            $creator = $task->creator;
            if ($creator && $creator->isAdmin()) {
                return false; // Director không thể approve task của Admin
            }
            return true;
        }

        // Manager: 
        // - Nếu là creator (giao việc) -> có toàn quyền approve
        // - Nếu được assign (nhận việc) -> KHÔNG thể approve
        if ($user->isManager()) {
            // Manager giao việc: có toàn quyền approve
            if ($task->creator_id === $user->id) {
                return true;
            }
            
            // Manager nhận việc: KHÔNG thể approve
            if (self::isTaskAssignedToUser($user, $task)) {
                return false;
            }
            
            // Manager approve task khác (thuộc thẩm quyền)
            if (self::isTaskInManagerDepartment($user, $task)) {
                return true;
            }
            
            // Task được forward đến manager này
            if ($task->forwarded_to === $user->id) {
                return true;
            }
            
            return false;
        }

        return false;
    }

    /**
     * Lấy danh sách users mà user hiện tại có thể giao task
     */
    public static function getAssignableUsers(User $user): \Illuminate\Database\Eloquent\Collection
    {
        if ($user->isAdmin()) {
            // Admin có thể giao cho tất cả (trừ Admin khác)
            return User::where('role', '!=', 'admin')
                      ->with('department')
                      ->orderBy('name')
                      ->get();
        }

        if ($user->isDirector()) {
            // Director có thể giao cho tất cả (trừ Admin)
            return User::where('role', '!=', 'admin')
                      ->with('department')
                      ->orderBy('name')
                      ->get();
        }

        if ($user->isManager()) {
            // Manager có thể giao cho Employee trong các phòng ban mà Manager quản lý
            $managerDepartmentIds = $user->departments->pluck('id')->toArray();
            
            return User::where('role', 'employee')
                      ->where('id', '!=', $user->id)
                      ->whereHas('departments', function($query) use ($managerDepartmentIds) {
                          $query->whereIn('department_id', $managerDepartmentIds);
                      })
                      ->with('departments')
                      ->orderBy('name')
                      ->get();
        }

        // Employee không thể giao task
        return collect();
    }

    /**
     * Lấy danh sách departments mà user hiện tại có thể giao task
     */
    public static function getAssignableDepartments(User $user): \Illuminate\Database\Eloquent\Collection
    {
        if ($user->isAdmin() || $user->isDirector()) {
            // Admin và Director có thể giao cho tất cả department
            return Department::orderBy('name')->get();
        }

        if ($user->isManager()) {
            // Manager có thể giao cho các phòng ban mà Manager quản lý
            $managerDepartmentIds = $user->departments->pluck('id')->toArray();
            return Department::whereIn('id', $managerDepartmentIds)->orderBy('name')->get();
        }

        // Employee không thể giao task
        return collect();
    }

    /**
     * Kiểm tra task có thuộc phòng ban của Manager không
     */
    private static function isTaskInManagerDepartment(User $manager, Task $task): bool
    {
        // Lấy danh sách phòng ban mà Manager quản lý
        $managerDepartmentIds = $manager->departments->pluck('id')->toArray();
        
        // Kiểm tra nếu assignee có phòng ban chung với Manager
        if ($task->assignee) {
            $assigneeDepartmentIds = $task->assignee->departments->pluck('id')->toArray();
            if (!empty(array_intersect($managerDepartmentIds, $assigneeDepartmentIds))) {
                return true;
            }
        }

        // Kiểm tra nếu creator có phòng ban chung với Manager
        if ($task->creator) {
            $creatorDepartmentIds = $task->creator->departments->pluck('id')->toArray();
            if (!empty(array_intersect($managerDepartmentIds, $creatorDepartmentIds))) {
                return true;
            }
        }

        // Kiểm tra nếu có assignee nào có phòng ban chung với Manager
        foreach ($task->assignees as $assignee) {
            $assigneeDepartmentIds = $assignee->departments->pluck('id')->toArray();
            if (!empty(array_intersect($managerDepartmentIds, $assigneeDepartmentIds))) {
                return true;
            }
        }

        // Kiểm tra nếu task thuộc phòng ban mà Manager quản lý
        if (in_array($task->department_id, $managerDepartmentIds)) {
            return true;
        }

        // Kiểm tra nếu task là multi-department và có phòng ban của Manager
        if ($task->is_multi_department && $task->departments->whereIn('id', $managerDepartmentIds)->count() > 0) {
            return true;
        }

        // Kiểm tra nếu task đã được forward cho manager này
        if ($task->forwarded_to === $manager->id) {
            return true;
        }

        return false;
    }

    /**
     * Kiểm tra task có được assign cho user không
     */
    private static function isTaskAssignedToUser(User $user, Task $task): bool
    {
        return $task->assignee_id === $user->id || 
               $task->assignees->contains('id', $user->id);
    }

    /**
     * Validate task assignment data
     */
    public static function validateTaskAssignment(User $user, array $data): array
    {
        $errors = [];

        // Kiểm tra assignee_id
        if (isset($data['assignee_id']) && $data['assignee_id']) {
            $assignee = User::find($data['assignee_id']);
            if ($assignee && !self::canAssignTask($user, $assignee)) {
                $errors['assignee_id'] = 'Bạn không có quyền giao việc cho người này.';
            }
        }

        // Kiểm tra assignee_ids (multi-user)
        if (isset($data['assignee_ids']) && is_array($data['assignee_ids'])) {
            foreach ($data['assignee_ids'] as $assigneeId) {
                $assignee = User::find($assigneeId);
                if ($assignee && !self::canAssignTask($user, $assignee)) {
                    $errors['assignee_ids'] = 'Bạn không có quyền giao việc cho một số người trong danh sách.';
                    break;
                }
            }
        }

        // Kiểm tra department_id
        if (isset($data['department_id']) && $data['department_id']) {
            $department = Department::find($data['department_id']);
            if ($department && !self::canAssignTaskToDepartment($user, $department)) {
                $errors['department_id'] = 'Bạn không có quyền giao việc cho phòng ban này.';
            }
        }

        // Kiểm tra department_ids (multi-department)
        if (isset($data['department_ids']) && is_array($data['department_ids'])) {
            foreach ($data['department_ids'] as $departmentId) {
                $department = Department::find($departmentId);
                if ($department && !self::canAssignTaskToDepartment($user, $department)) {
                    $errors['department_ids'] = 'Bạn không có quyền giao việc cho một số phòng ban trong danh sách.';
                    break;
                }
            }
        }

        // Manager không thể tạo task đa phòng ban
        if ($user->isManager() && isset($data['is_multi_department']) && $data['is_multi_department']) {
            $errors['is_multi_department'] = 'Manager không thể tạo công việc đa phòng ban.';
        }

        return $errors;
    }
}
