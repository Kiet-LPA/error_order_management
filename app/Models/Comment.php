<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'user_id',
        'parent_id',
        'content',
        'reactions',
        'is_edited',
        'edited_at'
    ];

    protected $casts = [
        'reactions' => 'array',
        'is_edited' => 'boolean',
        'edited_at' => 'datetime',
    ];

    // Relationships
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(CommentAttachment::class);
    }

    // Scopes
    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeWithReplies($query)
    {
        return $query->with(['replies.user', 'replies.attachments']);
    }

    // Methods
    public function addReaction($type, $userId)
    {
        $reactions = $this->reactions ?? [];
        
        if (!isset($reactions[$type])) {
            $reactions[$type] = [];
        }
        
        if (!in_array($userId, $reactions[$type])) {
            $reactions[$type][] = $userId;
        }
        
        $this->update(['reactions' => $reactions]);
    }

    public function removeReaction($type, $userId)
    {
        $reactions = $this->reactions ?? [];
        
        if (isset($reactions[$type])) {
            $reactions[$type] = array_diff($reactions[$type], [$userId]);
        }
        
        $this->update(['reactions' => $reactions]);
    }

    public function hasReaction($type, $userId): bool
    {
        $reactions = $this->reactions ?? [];
        return isset($reactions[$type]) && in_array($userId, $reactions[$type]);
    }

    public function getReactionCount($type): int
    {
        $reactions = $this->reactions ?? [];
        return isset($reactions[$type]) ? count($reactions[$type]) : 0;
    }

    public function canEdit($user): bool
    {
        return $user->id === $this->user_id || $user->isAdmin();
    }

    public function canDelete($user): bool
    {
        return $user->id === $this->user_id || $user->isAdmin();
    }

    public function markAsEdited()
    {
        $this->update([
            'is_edited' => true,
            'edited_at' => now()
        ]);
    }
}
