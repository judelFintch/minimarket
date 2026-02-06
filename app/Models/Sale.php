<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sale extends Model
{
    protected $fillable = [
        'user_id',
        'reference',
        'customer_name',
        'total_amount',
        'subtotal_amount',
        'discount_rate',
        'discount_amount',
        'tax_rate',
        'tax_amount',
        'status',
        'sold_at',
    ];

    protected $casts = [
        'sold_at' => 'datetime',
        'total_amount' => 'decimal:2',
        'subtotal_amount' => 'decimal:2',
        'discount_rate' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }
}
