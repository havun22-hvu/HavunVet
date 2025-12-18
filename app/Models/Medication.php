<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medication extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'active_ingredient',
        'dosage_form',
        'strength',
        'stock_quantity',
        'stock_unit',
        'min_stock_level',
        'expiry_date',
        'batch_number',
        'supplier',
        'purchase_price',
        'selling_price',
        'prescription_required',
        'notes',
    ];

    protected $casts = [
        'stock_quantity' => 'decimal:2',
        'min_stock_level' => 'decimal:2',
        'expiry_date' => 'date',
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'prescription_required' => 'boolean',
    ];

    // Relationships
    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    // Accessors
    public function getIsLowStockAttribute(): bool
    {
        return $this->stock_quantity <= $this->min_stock_level;
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function getIsExpiringSoonAttribute(): bool
    {
        return $this->expiry_date && $this->expiry_date->isBetween(now(), now()->addMonths(3));
    }

    public function getFullNameAttribute(): string
    {
        $parts = [$this->name];
        if ($this->strength) {
            $parts[] = $this->strength;
        }
        if ($this->dosage_form) {
            $parts[] = "({$this->dosage_form})";
        }
        return implode(' ', $parts);
    }

    // Scopes
    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock_quantity', '<=', 'min_stock_level');
    }

    public function scopeExpiringSoon($query)
    {
        return $query->whereBetween('expiry_date', [now(), now()->addMonths(3)]);
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now());
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('active_ingredient', 'like', "%{$search}%");
        });
    }
}
