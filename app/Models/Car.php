<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    use HasFactory;

    protected $fillable = [
        'license_plate',
        'weight',
        'car_type',
        'color',
        'description',
        'status',
        'available_from',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'available_from' => 'datetime',
    ];

    // Relationships
    public function rentals()
    {
        return $this->hasMany(Rental::class);
    }

    public function activeRental()
    {
        return $this->hasOne(Rental::class)->where('status', 'active');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeRented($query)
    {
        return $query->where('status', 'rented');
    }

    // Accessors
    public function getIsAvailableAttribute()
    {
        return $this->status === 'active' && 
               ($this->available_from === null || $this->available_from <= now());
    }

    // Methods
    public function canBeRented()
    {
        return $this->status === 'active' && $this->is_available;
    }

    public function setRented($rentalEnd)
    {
        $this->update([
            'status' => 'rented',
            'available_from' => $rentalEnd->addHours(6) // 6 hours rest time
        ]);
    }

    public function setAvailable()
    {
        $this->update([
            'status' => 'active',
            'available_from' => null
        ]);
    }
}
