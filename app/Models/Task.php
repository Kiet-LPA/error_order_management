<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = ['title','description','status','priority','attachments','department_id','assignee_id','creator_id','rejection_reason','finish_note','deadline'];
    
    protected $casts = [
        'deadline' => 'datetime',
        'attachments' => 'array',
    ];
    public function department(){ return $this->belongsTo(Department::class); }
    public function assignee(){ return $this->belongsTo(User::class, 'assignee_id'); }
    public function creator(){ return $this->belongsTo(User::class, 'creator_id'); }
    public function activities(){ return $this->hasMany(TaskActivity::class); }
}

