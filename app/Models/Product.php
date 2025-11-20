<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'vendor_id',
        'category_id',
        'name',
        'description',
        'price',
        'stock_quantity',
        'low_stock_threshold',
        'is_active',
        'attributes',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'attributes' => 'array',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function inventoryLogs(): HasMany
    {
        return $this->hasMany(InventoryLog::class);
    }

    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->low_stock_threshold;
    }

    public function updateStock(int $quantity): void
    {
        $this->update(['stock_quantity' => $quantity]);
    }
}
