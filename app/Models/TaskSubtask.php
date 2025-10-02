<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskSubtask extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'title',
        'description',
        'assignee_id',
        'status',
        'completed_at',
        'order',
        'deadline',
        'completion_note',
        'completed_by',
        'priority'
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'deadline' => 'datetime',
    ];

    // Relationships
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'todo');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('created_at');
    }

    // Helper methods
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isPending(): bool
    {
        return $this->status === 'todo';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now()
        ]);
    }

    public function markAsPending(): void
    {
        $this->update([
            'status' => 'todo',
            'completed_at' => null
        ]);
    }

    public function markAsInProgress(): void
    {
        $this->update([
            'status' => 'in_progress'
        ]);
    }

    // Check if user can be assigned to this subtask
    public function canAssignUser(User $user): bool
    {
        // User must be assigned to the parent task
        $task = $this->task;
        
        // Check if user is assignee of parent task
        if ($task->assignee_id === $user->id) {
            return true;
        }
        
        // Check if user is in multi-assignees of parent task
        if ($task->assignees->contains('id', $user->id)) {
            return true;
        }
        
        return false;
    }

    // Check if user can complete this subtask
    public function canBeCompletedBy(User $user): bool
    {
        // Kiểm tra user có được assign trực tiếp vào subtask không
        if ($this->assignee_id === $user->id) {
            return true;
        }
        
        // Kiểm tra user có phải là assignee của task chính không
        $task = $this->task;
        if ($task->assignee_id === $user->id) {
            return true;
        }
        
        // Kiểm tra user có trong danh sách multi-assignees của task không
        if ($task->assignees->contains('id', $user->id)) {
            return true;
        }
        
        return false;
    }
}