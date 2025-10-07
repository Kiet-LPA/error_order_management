<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'user_id',
        'submitted_at',
        'undone_at',
        'status'
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'undone_at' => 'datetime',
    ];

    /**
     * Relationship với Task
     */
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Relationship với User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: Lấy submissions đã submit
     */
    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    /**
     * Scope: Lấy submissions chưa submit
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Lấy submissions đã hoàn tác
     */
    public function scopeUndone($query)
    {
        return $query->where('status', 'undone');
    }

    /**
     * Kiểm tra user đã submit chưa
     */
    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    /**
     * Kiểm tra user có thể hoàn tác không (trong 3 tiếng)
     */
    public function canUndo(): bool
    {
        if (!$this->isSubmitted() || !$this->submitted_at) {
            return false;
        }

        $threeHoursAgo = now()->subHours(3);
        return $this->submitted_at->gt($threeHoursAgo);
    }
}