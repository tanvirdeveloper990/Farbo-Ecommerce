<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'customer_id',
        'customer_name',
        'phone',
        'email',
        'address',
        'area',
        'payment_method',
        'payment_status',
        'status',
        'courier_provider',
        'customer_note',
        'discount_amount', 'coupon_discount', 'gift_card_discount', 'coupon_code', 'gift_card_code',
        'subtotal',
        'delivery_fee',
        'total',
        'stock_reserved',
    ];

    protected function casts(): array
    {
        return [
            'discount_amount' => 'decimal:2', 'coupon_discount' => 'decimal:2', 'gift_card_discount' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'total' => 'decimal:2',
            'stock_reserved' => 'boolean',
        ];
    }

    public function shipment() { return $this->hasOne(CourierShipment::class); }
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
