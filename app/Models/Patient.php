<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'name',
        'species',
        'breed',
        'date_of_birth',
        'gender',
        'neutered',
        'chip_number',
        'weight',
        'color',
        'allergies',
        'notes',
        'photo_path',
        'deceased_at',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'deceased_at' => 'date',
        'neutered' => 'boolean',
        'weight' => 'decimal:2',
        'allergies' => 'array',
    ];

    // Relationships
    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class);
    }

    public function treatments(): HasMany
    {
        return $this->hasMany(Treatment::class);
    }

    public function vaccinations(): HasMany
    {
        return $this->hasMany(Vaccination::class);
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function homeVisits(): HasMany
    {
        return $this->hasMany(HomeVisit::class);
    }

    // Accessors
    public function getAgeAttribute(): ?string
    {
        if (!$this->date_of_birth) {
            return null;
        }

        $years = $this->date_of_birth->diffInYears(now());
        $months = $this->date_of_birth->diffInMonths(now()) % 12;

        if ($years === 0) {
            return "{$months} maanden";
        }

        return $months > 0 ? "{$years} jaar, {$months} maanden" : "{$years} jaar";
    }

    public function getGenderLabelAttribute(): string
    {
        return match ($this->gender) {
            'male' => $this->neutered ? 'Gecastreerd' : 'Reu/Kater',
            'female' => $this->neutered ? 'Gesteriliseerd' : 'Teef/Poes',
            default => 'Onbekend',
        };
    }

    // Scopes
    public function scopeAlive($query)
    {
        return $query->whereNull('deceased_at');
    }

    public function scopeSpecies($query, string $species)
    {
        return $query->where('species', $species);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('chip_number', 'like', "%{$search}%")
              ->orWhereHas('owner', function ($ownerQuery) use ($search) {
                  $ownerQuery->where('name', 'like', "%{$search}%");
              });
        });
    }
}
