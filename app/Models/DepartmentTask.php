<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepartmentTask extends Model
{
    protected $fillable = ['task_id', 'department_id'];

    // Relationships
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
