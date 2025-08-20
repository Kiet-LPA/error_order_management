<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_contract_id',
        'image_path',
        'page_number',
        'description',
    ];

    public function contract()
    {
        return $this->belongsTo(EmployeeContract::class, 'employee_contract_id');
    }
}
