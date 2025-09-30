<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Rental extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'car_id',
        'rental_start',
        'rental_end',
        'status',
        'notes',
    ];

    protected $casts = [
        'rental_start' => 'datetime',
        'rental_end' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    public function extensions()
    {
        return $this->hasMany(RentalExtension::class);
    }

    public function pendingExtension()
    {
        return $this->hasOne(RentalExtension::class)->where('status', 'pending');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'active')
                    ->where('rental_end', '<', now());
    }

    // Accessors
    public function getIsActiveAttribute()
    {
        return $this->status === 'active';
    }

    public function getIsOverdueAttribute()
    {
        return $this->is_active && $this->rental_end < now();
    }

    public function getTimeRemainingAttribute()
    {
        if (!$this->is_active) {
            return null;
        }

        $remaining = $this->rental_end->diffInMinutes(now());
        
        if ($remaining <= 0) {
            return 'Quá hạn';
        }

        $hours = floor($remaining / 60);
        $minutes = $remaining % 60;

        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }

        return "{$minutes}m";
    }

    // Methods
    public function canRequestExtension()
    {
        return $this->is_active && !$this->pendingExtension;
    }

    public function complete()
    {
        $this->update(['status' => 'completed']);
        $this->car->setAvailable();
    }

    public function cancel()
    {
        $this->update(['status' => 'cancelled']);
        $this->car->setAvailable();
    }

    public function extendRental($newEndTime, $reason)
    {
        $this->update(['rental_end' => $newEndTime]);
        $this->car->setRented($newEndTime);
    }
}
