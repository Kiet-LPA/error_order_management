<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'department_id',
        'employee_type', // new, official
        'account_status', // active, inactive
        'position',
        'social_insurance_number',
        'health_insurance_number',
        'personal_identification_number',
        'avatar',
        'checkin_region_id',
        'is_active',
        'can_manage_cars',
    ];
    
    protected $hidden = ['password','remember_token'];
    protected $casts = ['email_verified_at' => 'datetime'];
    
    // Relationships
    public function department()
    {
        // Giữ lại để backward compatibility - trả về phòng ban chính
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function departments()
    {
        return $this->belongsToMany(Department::class, 'user_departments')
                    ->withTimestamps();
    }

    public function contracts()
    {
        return $this->hasMany(EmployeeContract::class);
    }

    public function activeContract()
    {
        return $this->hasOne(EmployeeContract::class)->where('status', 'active')->latest();
    }

    public function salary()
    {
        return $this->hasOne(EmployeeSalary::class)->where('status', 'active');
    }

    public function salaries()
    {
        return $this->hasMany(EmployeeSalary::class);
    }

    // Task relationships
    public function assignedTasks()
    { 
        return $this->hasMany(Task::class, 'assignee_id'); 
    }
    
    public function createdTasks()
    { 
        return $this->hasMany(Task::class, 'creator_id'); 
    }

    // Multi-assignments
    public function taskAssignments()
    {
        return $this->hasMany(TaskAssignee::class);
    }

    public function multiAssignedTasks()
    {
        return $this->belongsToMany(Task::class, 'task_assignees', 'user_id', 'task_id')
                    ->withTimestamps();
    }

    // Task Followers relationships
    public function followedTasks()
    {
        return $this->hasMany(TaskFollower::class);
    }

    public function tasksFollowing()
    {
        return $this->belongsToMany(Task::class, 'task_followers');
    }

    public function isFollowingTask(Task $task): bool
    {
        return $this->followedTasks()->where('task_id', $task->id)->exists();
    }

    // Work Report relationships
    public function workReports()
    {
        return $this->hasMany(WorkReport::class);
    }

    // Role methods
    /**
     * Chuẩn hóa role để tránh lỗi khoảng trắng hoặc ký tự ẩn
     */
    public function normalizedRole(): string
    {
        return strtolower(trim(preg_replace('/\s+/', '', $this->role ?? '')));
    }
    
    public function isAdmin()
    { 
        return $this->normalizedRole() === 'admin'; 
    }
    
    public function isDirector()
    { 
        return $this->normalizedRole() === 'director'; 
    }
    
    public function isManager()
    { 
        return $this->normalizedRole() === 'manager'; 
    }
    
    public function isEmployee()
    { 
        return $this->normalizedRole() === 'employee'; 
    }

    // Account status methods
    public function isAccountActive(): bool
    {
        return $this->account_status === 'active';
    }

    public function isAccountInactive(): bool
    {
        return $this->account_status === 'inactive';
    }

    public function activateAccount(): bool
    {
        return $this->update(['account_status' => 'active']);
    }

    public function deactivateAccount(): bool
    {
        return $this->update(['account_status' => 'inactive']);
    }

    /**
     * Kiểm tra user có quyền tạo task không
     */
    public function canCreateTask(): bool
    {
        return \App\Services\TaskPermissionService::canCreateTask($this);
    }

    /**
     * Kiểm tra user có quyền xem task không
     */
    public function canViewTask(Task $task): bool
    {
        return \App\Services\TaskPermissionService::canViewTask($this, $task);
    }

    /**
     * Kiểm tra user có quyền sửa task không
     */
    public function canEditTask(Task $task): bool
    {
        return \App\Services\TaskPermissionService::canEditTask($this, $task);
    }

    /**
     * Kiểm tra user có quyền xóa task không
     */
    public function canDeleteTask(Task $task): bool
    {
        return \App\Services\TaskPermissionService::canDeleteTask($this, $task);
    }

    /**
     * Kiểm tra user có quyền giao task cho user khác không
     */
    public function canAssignTaskTo(User $targetUser): bool
    {
        return \App\Services\TaskPermissionService::canAssignTask($this, $targetUser);
    }

    /**
     * Kiểm tra user có quyền giao task cho department không
     */
    public function canAssignTaskToDepartment(Department $department): bool
    {
        return \App\Services\TaskPermissionService::canAssignTaskToDepartment($this, $department);
    }

    /**
     * Kiểm tra user có quyền forward task không
     */
    public function canForwardTask(Task $task, User $targetUser): bool
    {
        return \App\Services\TaskPermissionService::canForwardTask($this, $task, $targetUser);
    }

    /**
     * Kiểm tra user có quyền approve task không
     */
    public function canApproveTask(Task $task): bool
    {
        return \App\Services\TaskPermissionService::canApproveTask($this, $task);
    }

    /**
     * Lấy danh sách users có thể giao task
     */
    public function getAssignableUsers(): \Illuminate\Database\Eloquent\Collection
    {
        return \App\Services\TaskPermissionService::getAssignableUsers($this);
    }

    /**
     * Lấy danh sách departments có thể giao task
     */
    public function getAssignableDepartments(): \Illuminate\Database\Eloquent\Collection
    {
        return \App\Services\TaskPermissionService::getAssignableDepartments($this);
    }

    /**
     * Kiểm tra user có thể quản lý user khác không
     */
    public function canManageUser(User $targetUser): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if ($this->isDirector()) {
            // Director có thể quản lý tất cả user (như Admin), chỉ không thể chỉ định vào Admin
            if ($targetUser->isAdmin()) {
                return false;
            }
            return true;
        }

        if ($this->isManager()) {
            return $targetUser->department_id === $this->department_id;
        }

        return false;
    }


    /**
     * Kiểm tra Director có thể quản lý phòng ban này không
     */
    public function canManageDepartment($departmentId): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if ($this->isDirector()) {
            // Director có thể quản lý tất cả phòng ban (như Admin)
            return true;
        }

        if ($this->isManager()) {
            return $this->department_id === $departmentId;
        }

        return false;
    }

    /**
     * Lấy danh sách phòng ban mà Director được quản lý
     */
    public function managedDepartments()
    {
        return $this->belongsToMany(Department::class, 'user_departments');
    }

    /**
     * Scope để lọc user theo phòng ban
     */
    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    /**
     * Scope để lọc user theo role
     */
    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope để tìm kiếm user
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
        });
    }

    /**
     * Kiểm tra xem Manager có quản lý nhiều phòng ban không
     */
    public function isMultiManager(): bool
    {
        return $this->isManager() && $this->departments->count() > 1;
    }

    /**
     * Lấy tên role hiển thị (bao gồm Multi Manager)
     */
    public function getDisplayRoleAttribute(): string
    {
        if ($this->isMultiManager()) {
            return 'Quản lý đa phòng ban';
        }
        
        return match($this->role) {
            'admin' => 'Quản trị viên',
            'director' => 'Giám đốc',
            'manager' => 'Quản lý',
            'employee' => 'Nhân viên',
            default => ucfirst($this->role)
        };
    }

    /**
     * Get the avatar URL
     */
    public function getAvatarUrlAttribute()
    {
        if ($this->avatar) {
            // Kiểm tra file tồn tại trong storage
            $storagePath = 'avatars/' . $this->avatar;
            
            if (\Storage::disk('public')->exists($storagePath)) {
                return asset('storage/avatars/' . $this->avatar);
            }
        }
        
        // Tạo SVG avatar đẹp với chữ cái đầu
        return $this->generateDefaultAvatar();
    }
    
    /**
     * Generate beautiful SVG avatar with user's initial
     */
    private function generateDefaultAvatar()
    {
        $name = $this->name ?? 'User';
        $initial = strtoupper(mb_substr($name, 0, 1));
        
        // Tạo màu dựa trên ID để mỗi user có màu riêng
        $colors = [
            '#667eea', '#764ba2', '#f093fb', '#4facfe',
            '#43e97b', '#fa709a', '#fee140', '#30cfd0',
            '#a8edea', '#fed6e3', '#89f7fe', '#66a6ff',
            '#f38181', '#aa076b', '#61045f', '#eecda3',
            '#ef629f', '#42e695', '#3bb2b8', '#fa8231'
        ];
        
        $colorIndex = $this->id % count($colors);
        $color1 = $colors[$colorIndex];
        $color2 = $colors[($colorIndex + 1) % count($colors)];
        
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="120" height="120" viewBox="0 0 120 120">' .
               '<defs>' .
               '<linearGradient id="grad-' . $this->id . '" x1="0%" y1="0%" x2="100%" y2="100%">' .
               '<stop offset="0%" style="stop-color:' . $color1 . ';stop-opacity:1" />' .
               '<stop offset="100%" style="stop-color:' . $color2 . ';stop-opacity:0.8" />' .
               '</linearGradient>' .
               '</defs>' .
               '<rect width="120" height="120" fill="url(#grad-' . $this->id . ')"/>' .
               '<text x="50%" y="50%" dominant-baseline="central" text-anchor="middle" ' .
               'font-family="Arial, sans-serif" font-size="48" fill="white" font-weight="bold">' .
               $initial .
               '</text>' .
               '</svg>';
        
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    // Checkin relationships
    public function checkins()
    {
        return $this->hasMany(Checkin::class);
    }

    public function gpsRequests()
    {
        return $this->hasMany(GpsRequest::class);
    }

    /**
     * Lấy department để checkin (department chính của user)
     */
    public function getCheckinDepartment()
    {
        return $this->department;
    }

    // Rental relationships
    public function rentals()
    {
        return $this->hasMany(Rental::class);
    }

    public function activeRental()
    {
        return $this->hasOne(Rental::class)->where('status', 'active');
    }

    public function approvedExtensions()
    {
        return $this->hasMany(RentalExtension::class, 'approved_by');
    }

    // Rental methods
    public function canManageCars(): bool
    {
        return $this->can_manage_cars || $this->isAdmin() || $this->isDirector();
    }

    public function hasActiveRental(): bool
    {
        return $this->activeRental()->exists();
    }
}