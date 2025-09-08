<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskForward extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'forwarded_to',
        'forwarded_by',
        'forward_reason',
        'forwarded_at',
    ];

    protected $casts = [
        'forwarded_at' => 'datetime',
    ];

    // Relationships
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function forwardedTo()
    {
        return $this->belongsTo(User::class, 'forwarded_to');
    }

    public function forwardedBy()
    {
        return $this->belongsTo(User::class, 'forwarded_by');
    }
}