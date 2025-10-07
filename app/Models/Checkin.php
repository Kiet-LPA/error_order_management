<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Checkin extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'department_id',
        'checkin_region_id',
        'checkin_date',
        'session',
        'checkin_time',
        'latitude',
        'longitude',
        'distance_meters',
        'ip_address',
        'status',
        'notes',
    ];

    protected $casts = [
        'checkin_date' => 'date',
        'checkin_time' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'distance_meters' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
