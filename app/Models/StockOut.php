<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockOut extends Model
{
    /** @use HasFactory<\Database\Factories\StockOutFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reference',
        'reason',
        'total_quantity',
        'occurred_at',
        'notes',
    ];

    protected $casts = [
        'total_quantity' => 'decimal:2',
        'occurred_at' => 'date',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(StockOutItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
