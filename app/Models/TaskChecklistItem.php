<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskChecklistItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'title',
        'description',
        'is_required',
        'assignee_id',
        'order',
        'is_completed',
        'completed_at',
        'completed_by',
        'completion_note'
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
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

    public function completions()
    {
        return $this->hasMany(TaskChecklistCompletion::class, 'checklist_item_id');
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }

    public function scopePending($query)
    {
        return $query->where('is_completed', false);
    }

    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
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

    public function toggleCompletion(User $user, string $note = null): bool
    {
        $this->is_completed = !$this->is_completed;
        
        if ($this->is_completed) {
            $this->completed_at = now();
            $this->completed_by = $user->id;
            $this->completion_note = $note;
        } else {
            $this->completed_at = null;
            $this->completed_by = null;
            $this->completion_note = null;
        }

        // Ghi log completion
        $this->completions()->create([
            'task_id' => $this->task_id,
            'user_id' => $user->id,
            'action' => $this->is_completed ? 'completed' : 'uncompleted',
            'note' => $note,
            'completed_at' => now()
        ]);

        return $this->save();
    }

    public function markAsCompleted(User $user, string $note = null): bool
    {
        if ($this->is_completed) {
            return true;
        }

        return $this->toggleCompletion($user, $note);
    }

    public function markAsPending(User $user, string $note = null): bool
    {
        if (!$this->is_completed) {
            return true;
        }

        return $this->toggleCompletion($user, $note);
    }

    public function getCompletionHistory()
    {
        return $this->completions()
                   ->with('user')
                   ->orderBy('completed_at', 'desc')
                   ->get();
    }

    public function getLastCompletion()
    {
        return $this->completions()
                   ->with('user')
                   ->orderBy('completed_at', 'desc')
                   ->first();
    }

    public function markAsIncomplete(): bool
    {
        if (!$this->is_completed) {
            return true;
        }

        $this->is_completed = false;
        $this->completed_at = null;
        $this->completed_by = null;
        $this->completion_note = null;

        return $this->save();
    }
}