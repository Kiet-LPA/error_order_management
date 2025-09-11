<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForwardRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'approval_request_id',
        'forwarded_by_id',
        'forwarded_to_id',
        'message',
        'forwarded_at'
    ];

    protected $casts = [
        'forwarded_at' => 'datetime',
    ];

    public function approvalRequest()
    {
        return $this->belongsTo(ApprovalRequest::class);
    }

    public function forwardedBy()
    {
        return $this->belongsTo(User::class, 'forwarded_by_id');
    }

    public function forwardedTo()
    {
        return $this->belongsTo(User::class, 'forwarded_to_id');
    }

}
