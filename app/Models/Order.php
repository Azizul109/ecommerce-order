<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'customer_id',
        'total_amount',
        'status',
        'shipping_address',
        'billing_address',
        'customer_email',
        'customer_phone',
        'notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'shipping_address' => 'array',
        'billing_address' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (!$order->order_number) {
                $order->order_number = 'ORD-' . date('Ymd') . '-' . strtoupper(uniqid());
            }
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'processing']);
    }

    public function markAsProcessing(): void
    {
        $this->update(['status' => 'processing']);
    }

    public function markAsShipped(): void
    {
        $this->update(['status' => 'shipped']);
    }

    public function markAsDelivered(): void
    {
        $this->update(['status' => 'delivered']);
    }

    public function markAsCancelled(): void
    {
        $this->update(['status' => 'cancelled']);
    }
}
