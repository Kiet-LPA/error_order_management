<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskChecklistCompletion extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'checklist_item_id',
        'user_id',
        'action',
        'note',
        'completed_at'
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    // Relationships
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function checklistItem()
    {
        return $this->belongsTo(TaskChecklistItem::class, 'checklist_item_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('action', 'completed');
    }

    public function scopeUncompleted($query)
    {
        return $query->where('action', 'uncompleted');
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByTask($query, $taskId)
    {
        return $query->where('task_id', $taskId);
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('completed_at', '>=', now()->subDays($days));
    }

    // Helper methods
    public function isCompleted(): bool
    {
        return $this->action === 'completed';
    }

    public function isUncompleted(): bool
    {
        return $this->action === 'uncompleted';
    }

    public function getActionText(): string
    {
        return $this->action === 'completed' ? 'Đã hoàn thành' : 'Đã hủy hoàn thành';
    }

    public function getFormattedCompletedAt(): string
    {
        return $this->completed_at->format('d/m/Y H:i');
    }
}