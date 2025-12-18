<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vaccination extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'treatment_id',
        'vaccine_name',
        'vaccine_type',
        'batch_number',
        'manufacturer',
        'administered_at',
        'next_due_date',
        'administered_by',
        'notes',
    ];

    protected $casts = [
        'administered_at' => 'datetime',
        'next_due_date' => 'date',
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

    // Accessors
    public function getIsDueAttribute(): bool
    {
        return $this->next_due_date && $this->next_due_date->isPast();
    }

    public function getIsDueSoonAttribute(): bool
    {
        return $this->next_due_date &&
               $this->next_due_date->isBetween(now(), now()->addMonth());
    }

    // Scopes
    public function scopeDue($query)
    {
        return $query->whereNotNull('next_due_date')
            ->where('next_due_date', '<=', now());
    }

    public function scopeDueSoon($query)
    {
        return $query->whereNotNull('next_due_date')
            ->whereBetween('next_due_date', [now(), now()->addMonth()]);
    }
}
