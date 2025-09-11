<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalForm extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_type',
        'form_name',
        'description',
        'form_fields',
        'validation_rules',
        'approval_workflow',
        'is_active'
    ];

    protected $casts = [
        'form_fields' => 'array',
        'validation_rules' => 'array',
        'approval_workflow' => 'array',
        'is_active' => 'boolean'
    ];

    public function approvalRequests()
    {
        return $this->hasMany(ApprovalRequest::class, 'form_type', 'form_type');
    }

    public function getFormConfig()
    {
        return $this;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
