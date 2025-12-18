<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TreatmentItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'treatment_id',
        'description',
        'quantity',
        'unit',
        'unit_price',
        'vat_rate',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'vat_rate' => 'decimal:2',
    ];

    // Relationships
    public function treatment(): BelongsTo
    {
        return $this->belongsTo(Treatment::class);
    }

    // Accessors
    public function getTotalAttribute(): float
    {
        return $this->quantity * $this->unit_price;
    }

    public function getVatAmountAttribute(): float
    {
        return $this->total * ($this->vat_rate / 100);
    }

    public function getTotalWithVatAttribute(): float
    {
        return $this->total + $this->vat_amount;
    }
}
