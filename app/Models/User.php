<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = ['name','email','phone','password','role','department_id'];
    protected $hidden = ['password','remember_token'];
    protected $casts = ['email_verified_at' => 'datetime'];
    
    public function department(){ return $this->belongsTo(Department::class); }
    public function assignedTasks(){ return $this->hasMany(Task::class, 'assignee_id'); }
    public function createdTasks(){ return $this->hasMany(Task::class, 'creator_id'); }

    public function isAdmin(){ return $this->role === 'admin'; }
    public function isManager(){ return $this->role === 'manager'; }
    public function isEmployee(){ return $this->role === 'employee'; }

}