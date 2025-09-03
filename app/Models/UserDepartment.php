<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDepartment extends Model
{
    use HasFactory;

    protected $table = 'user_departments';

    protected $fillable = [
        'user_id',
        'department_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
