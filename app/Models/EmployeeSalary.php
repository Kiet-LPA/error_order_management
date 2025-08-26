<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeSalary extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'gross_salary',
        'basic_salary',
        'allowance',
        'bonus',
        'deduction',
        'insurance',
        'tax',
        'net_salary',
        'effective_date',
        'status', // active, inactive
    ];

    protected $casts = [
        'effective_date' => 'datetime',
        'gross_salary' => 'decimal:2',
        'basic_salary' => 'decimal:2',
        'allowance' => 'decimal:2',
        'bonus' => 'decimal:2',
        'deduction' => 'decimal:2',
        'insurance' => 'decimal:2',
        'tax' => 'decimal:2',
        'net_salary' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
