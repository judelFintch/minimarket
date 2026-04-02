<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    use HasFactory;

    public const UNIT_OPTIONS = [
        'piece' => 'Piece',
        'kg' => 'Kilogramme (kg)',
        'g' => 'Gramme (g)',
        'litre' => 'Litre (L)',
        'ml' => 'Millilitre (ml)',
        'carton' => 'Carton',
        'sachet' => 'Sachet',
        'paquet' => 'Paquet',
        'bouteille' => 'Bouteille',
        'boite' => 'Boite',
    ];

    protected $fillable = [
        'category_id',
        'name',
        'sku',
        'barcode',
        'image_url',
        'unit',
        'cost_price',
        'sale_price',
        'promo_label',
        'promo_price',
        'currency',
        'min_stock',
        'reorder_qty',
        'archived_at',
    ];

    protected $casts = [
        'promo_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'min_stock' => 'integer',
        'reorder_qty' => 'integer',
        'archived_at' => 'datetime',
    ];

    public static function unitOptions(): array
    {
        return self::UNIT_OPTIONS;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('archived_at');
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->whereNotNull('archived_at');
    }

    public function unitLabel(): string
    {
        return self::UNIT_OPTIONS[$this->unit] ?? (string) ($this->unit ?: 'piece');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function stock(): HasOne
    {
        return $this->hasOne(Stock::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function favoriteUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorite_products')
            ->withTimestamps();
    }
}
