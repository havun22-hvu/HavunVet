<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Owner extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'phone2',
        'address',
        'house_number',
        'postal_code',
        'city',
        'notes',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    // Relationships
    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class);
    }

    public function treatments(): HasMany
    {
        return $this->hasManyThrough(Treatment::class, Patient::class);
    }

    // Accessors
    public function getFullAddressAttribute(): string
    {
        $street = $this->address;
        if ($this->house_number) {
            $street .= ' ' . $this->house_number;
        }

        return collect([
            $street,
            trim($this->postal_code . ' ' . $this->city),
        ])->filter()->implode(', ');
    }

    public function getPatientsCountAttribute(): int
    {
        return $this->patients()->count();
    }

    public function getActivePatientsCountAttribute(): int
    {
        return $this->patients()->whereNull('deceased_at')->count();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhere('city', 'like', "%{$search}%");
        });
    }
}
