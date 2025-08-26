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

    // Work Report relationships
    public function workReports()
    {
        return $this->hasMany(WorkReport::class);
    }

    // Role methods
    public function isAdmin()
    { 
        return $this->role === 'admin'; 
    }
    
    public function isManager()
    { 
        return $this->role === 'manager'; 
    }
    
    public function isEmployee()
    { 
        return $this->role === 'employee'; 
    }

    /**
     * Kiểm tra user có thể quản lý user khác không
     */
    public function canManageUser(User $targetUser): bool
    {
        if ($this->isAdmin()) {
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

        if ($this->isManager()) {
            return $targetUser->department_id === $this->department_id;
        }

        return false;
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