<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = ['name'];

    // Relationships
    public function users()
    { 
        return $this->hasMany(User::class); 
    }
    
    public function tasks()
    { 
        return $this->hasMany(Task::class); 
    }

    // Multi-department tasks
    public function departmentTasks()
    {
        return $this->hasMany(DepartmentTask::class);
    }

    public function multiDepartmentTasks()
    {
        return $this->belongsToMany(Task::class, 'department_tasks', 'department_id', 'task_id')
                    ->withTimestamps();
    }

    // Work Report relationships
    public function workReports()
    {
        return $this->hasMany(WorkReport::class);
    }

    /**
     * Scope để tìm kiếm department
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', "%{$search}%");
    }

    /**
     * Lấy tất cả tasks của department (bao gồm cả multi-department)
     */
    public function getAllTasks()
    {
        return Task::where(function($query) {
            $query->where('department_id', $this->id)
                  ->orWhereHas('departments', function($q) {
                      $q->where('department_id', $this->id);
                  });
        });
    }
}

