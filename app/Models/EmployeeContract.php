<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeContract extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'probation_salary',
        'probation_period',
        'start_date',
        'end_date',
        'status', // active, completed, terminated
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'probation_salary' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function images()
    {
        return $this->hasMany(ContractImage::class);
    }
}
