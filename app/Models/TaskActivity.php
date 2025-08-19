<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskActivity extends Model
{
    protected $fillable = ['task_id','user_id','action','meta'];
    public function task(){ return $this->belongsTo(Task::class); }
    public function user(){ return $this->belongsTo(User::class); }
}

