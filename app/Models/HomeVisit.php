<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomeVisit extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'treatment_id',
        'appointment_id',
        'scheduled_at',
        'address',
        'city',
        'postal_code',
        'latitude',
        'longitude',
        'travel_distance_km',
        'travel_time_minutes',
        'travel_cost',
        'notes',
        'status',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'travel_distance_km' => 'decimal:1',
        'travel_time_minutes' => 'integer',
        'travel_cost' => 'decimal:2',
    ];

    // Relationships
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function treatment(): BelongsTo
    {
        return $this->belongsTo(Treatment::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    // Accessors
    public function getFullAddressAttribute(): string
    {
        return "{$this->address}, {$this->postal_code} {$this->city}";
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'scheduled' => 'Gepland',
            'in_transit' => 'Onderweg',
            'arrived' => 'Ter plaatse',
            'completed' => 'Afgerond',
            'cancelled' => 'Geannuleerd',
            default => $this->status,
        };
    }

    // Helper to calculate travel cost
    public function calculateTravelCost(float $minimumCost = 15.00, float $perKmRate = 0.40): float
    {
        if (!$this->travel_distance_km) {
            return $minimumCost;
        }

        return max($minimumCost, $this->travel_distance_km * $perKmRate);
    }

    // Scopes
    public function scopeToday($query)
    {
        return $query->whereDate('scheduled_at', today());
    }

    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_at', '>=', now())
            ->whereNotIn('status', ['completed', 'cancelled']);
    }

    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
}
