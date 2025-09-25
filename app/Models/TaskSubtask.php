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
        'status',
        'priority',
        'assignee_id',
        'deadline',
        'completed_at',
        'completion_note',
        'completed_by',
        'order'
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'completed_at' => 'datetime',
        'is_required' => 'boolean',
    ];

    // Relationships
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function completedBy()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByAssignee($query, $userId)
    {
        return $query->where('assignee_id', $userId);
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

    public function isOverdue(): bool
    {
        return $this->deadline && $this->deadline->isPast() && !$this->isCompleted();
    }

    public function canBeAssignedTo(User $user): bool
    {
        // Chỉ được assign cho user trong danh sách assignees của task chính
        $taskAssignees = collect([
            $this->task->assignee_id,
            $this->task->creator_id
        ])->filter();

        $taskAssignees = $taskAssignees->merge($this->task->assignees()->pluck('users.id'));
        $taskAssignees = $taskAssignees->merge($this->task->followers()->pluck('id'));

        return $taskAssignees->contains($user->id);
    }

    public function getAvailableAssignees()
    {
        // Lấy danh sách user có thể assign (từ task chính)
        $taskAssignees = collect([
            $this->task->assignee_id,
            $this->task->creator_id
        ])->filter();

        $taskAssignees = $taskAssignees->merge($this->task->assignees()->pluck('users.id'));
        $taskAssignees = $taskAssignees->merge($this->task->followers()->pluck('id'));

        return User::whereIn('id', $taskAssignees->unique())
                  ->with('department')
                  ->orderBy('name')
                  ->get();
    }

    public function markAsCompleted(User $user, string $note = null): bool
    {
        $this->status = 'completed';
        $this->completed_at = now();
        $this->completed_by = $user->id;
        $this->completion_note = $note;

        return $this->save();
    }

    public function markAsInProgress(): bool
    {
        $this->status = 'in_progress';
        $this->completed_at = null;
        $this->completed_by = null;
        $this->completion_note = null;

        return $this->save();
    }

    public function markAsTodo(): bool
    {
        $this->status = 'todo';
        $this->completed_at = null;
        $this->completed_by = null;
        $this->completion_note = null;

        return $this->save();
    }

    public function markAsIncomplete(): bool
    {
        return $this->markAsTodo();
    }
}