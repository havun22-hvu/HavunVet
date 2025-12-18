<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Treatment extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'work_location_id',
        'date',
        'complaint',
        'anamnesis',
        'examination',
        'diagnosis',
        'treatment_description',
        'follow_up_needed',
        'follow_up_date',
        'veterinarian',
        'havunadmin_invoice_id',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
        'follow_up_date' => 'date',
        'follow_up_needed' => 'boolean',
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

    public function items(): HasMany
    {
        return $this->hasMany(TreatmentItem::class);
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    public function vaccinations(): HasMany
    {
        return $this->hasMany(Vaccination::class);
    }

    public function homeVisit(): HasMany
    {
        return $this->hasMany(HomeVisit::class);
    }

    // Accessors
    public function getTotalAttribute(): float
    {
        return $this->items->sum(fn ($item) => $item->total);
    }

    public function getTotalWithVatAttribute(): float
    {
        return $this->items->sum(fn ($item) => $item->total_with_vat);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Concept',
            'completed' => 'Afgerond',
            'invoiced' => 'Gefactureerd',
            default => $this->status,
        };
    }

    // Scopes
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeNeedsFollowUp($query)
    {
        return $query->where('follow_up_needed', true)
            ->whereNotNull('follow_up_date')
            ->where('follow_up_date', '<=', now()->addDays(7));
    }
}
