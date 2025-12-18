<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'work_location_id',
        'scheduled_at',
        'duration_minutes',
        'type',
        'status',
        'reason',
        'notes',
        'contact_phone',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'duration_minutes' => 'integer',
    ];

    // Relationships
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function workLocation(): BelongsTo
    {
        return $this->belongsTo(WorkLocation::class);
    }

    // Accessors
    public function getEndTimeAttribute(): \DateTime
    {
        return $this->scheduled_at->addMinutes($this->duration_minutes);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'consult' => 'Consult',
            'checkup' => 'Controle',
            'vaccination' => 'Vaccinatie',
            'surgery' => 'Operatie',
            'dental' => 'Gebit',
            'emergency' => 'Spoed',
            'home_visit' => 'Thuisbezoek',
            'other' => 'Anders',
            default => $this->type,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'scheduled' => 'Gepland',
            'confirmed' => 'Bevestigd',
            'arrived' => 'Aangekomen',
            'in_progress' => 'Bezig',
            'completed' => 'Afgerond',
            'cancelled' => 'Geannuleerd',
            'no_show' => 'Niet verschenen',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'scheduled' => 'gray',
            'confirmed' => 'blue',
            'arrived' => 'yellow',
            'in_progress' => 'orange',
            'completed' => 'green',
            'cancelled' => 'red',
            'no_show' => 'red',
            default => 'gray',
        };
    }

    // Scopes
    public function scopeToday($query)
    {
        return $query->whereDate('scheduled_at', today());
    }

    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_at', '>=', now())
            ->whereNotIn('status', ['completed', 'cancelled', 'no_show']);
    }

    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
}
