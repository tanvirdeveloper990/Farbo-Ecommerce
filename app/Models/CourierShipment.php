<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourierShipment extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['options' => 'array', 'cod_amount' => 'decimal:2', 'synced_at' => 'datetime'];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
