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
        'position',
        'social_insurance_number',
        'health_insurance_number',
        'personal_identification_number',
    ];
    
    protected $hidden = ['password','remember_token'];
    protected $casts = ['email_verified_at' => 'datetime'];
    
    // Relationships
    public function department()
    {
        return $this->belongsTo(Department::class);
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
    public function isAdmin()
    { 
        return strtolower(trim($this->role)) === 'admin'; 
    }
    
    public function isDirector()
    { 
        return strtolower(trim($this->role)) === 'director'; 
    }
    
    public function isManager()
    { 
        return strtolower(trim($this->role)) === 'manager'; 
    }
    
    public function isEmployee()
    { 
        return $this->role === 'employee' || $this->employee_type === 'new'; 
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
     * Kiểm tra user có thể giao việc cho user khác không
     */
    public function canAssignTaskTo(User $targetUser): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if ($this->isDirector()) {
            // Director có thể giao việc cho tất cả user (như Admin), chỉ không thể chỉ định vào Admin
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
}