<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = [
        'name',
        'latitude',
        'longitude',
        'radius_meters',
        'address'
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'radius_meters' => 'integer',
    ];

    // Relationships
    public function users()
    { 
        return $this->hasMany(User::class); 
    }

    public function departmentUsers()
    {
        return $this->belongsToMany(User::class, 'user_departments')
                    ->withTimestamps();
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

    // Manager relationship
    public function manager()
    {
        return $this->hasOne(User::class)->where('role', 'manager');
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
     * Kiểm tra xem department có cấu hình GPS không
     */
    public function hasGpsConfig()
    {
        return !is_null($this->latitude) && !is_null($this->longitude);
    }
}

