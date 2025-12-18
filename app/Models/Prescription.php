<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Prescription extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'treatment_id',
        'medication_id',
        'medication_name',
        'dosage',
        'frequency',
        'duration_days',
        'instructions',
        'dispensed_quantity',
        'dispensed_unit',
        'dispensed_at',
        'prescribed_by',
    ];

    protected $casts = [
        'dispensed_at' => 'datetime',
        'dispensed_quantity' => 'decimal:2',
        'duration_days' => 'integer',
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

    public function medication(): BelongsTo
    {
        return $this->belongsTo(Medication::class);
    }

    // Accessors
    public function getIsDispensedAttribute(): bool
    {
        return $this->dispensed_at !== null;
    }

    public function getDosageInstructionsAttribute(): string
    {
        $parts = [
            $this->medication_name,
            $this->dosage,
            $this->frequency,
        ];

        if ($this->duration_days) {
            $parts[] = "gedurende {$this->duration_days} dagen";
        }

        return implode(', ', $parts);
    }
}
