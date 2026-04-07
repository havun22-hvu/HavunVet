<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Legacy model - work_locations table was dropped in migration 2025_01_02_000030.
 * This class exists only because TreatmentForm still references it.
 */
class WorkLocation extends Model
{
    protected $fillable = [
        'type', 'name', 'address', 'city', 'postal_code',
        'contact_person', 'phone', 'email', 'hourly_rate',
        'contract_notes', 'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
