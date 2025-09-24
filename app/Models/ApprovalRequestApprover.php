<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalRequestApprover extends Model
{
    use HasFactory;

    protected $fillable = [
        'approval_request_id',
        'user_id',
        'status',
        'comment',
        'approved_at',
        'rejected_at'
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime'
    ];

    // Relationships
    public function approvalRequest()
    {
        return $this->belongsTo(ApprovalRequest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Status methods
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    // Action methods
    public function approve($comment = null)
    {
        // Chỉ cho phép Manager approve
        $user = $this->user;
        if ($user->role !== 'manager') {
            throw new \Exception('Chỉ Manager mới có thể phê duyệt');
        }
        
        $this->update([
            'status' => 'approved',
            'comment' => $comment,
            'approved_at' => now(),
            'rejected_at' => null
        ]);
    }

    public function reject($comment)
    {
        // Chỉ cho phép Manager reject
        $user = $this->user;
        if ($user->role !== 'manager') {
            throw new \Exception('Chỉ Manager mới có thể từ chối');
        }
        
        $this->update([
            'status' => 'rejected',
            'comment' => $comment,
            'rejected_at' => now(),
            'approved_at' => null
        ]);
    }

    public function getStatusText()
    {
        return match($this->status) {
            'approved' => 'Đã phê duyệt',
            'rejected' => 'Đã từ chối',
            'pending' => 'Chờ phê duyệt',
            default => 'Không xác định'
        };
    }

    public function getStatusClass()
    {
        return match($this->status) {
            'approved' => 'success',
            'rejected' => 'danger',
            'pending' => 'warning',
            default => 'secondary'
        };
    }
}
