<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'approval_request_id',
        'user_id',
        'action',
        'note',
        'action_at'
    ];

    protected $casts = [
        'action_at' => 'datetime'
    ];

    public function approvalRequest()
    {
        return $this->belongsTo(ApprovalRequest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getActionText()
    {
        return match($this->action) {
            'approve' => 'Phê duyệt',
            'reject' => 'Từ chối'
        };
    }

    public function getActionClass()
    {
        return match($this->action) {
            'approve' => 'success',
            'reject' => 'danger'
        };
    }
}
