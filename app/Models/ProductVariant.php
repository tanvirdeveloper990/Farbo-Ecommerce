<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    protected $fillable = ['color_id', 'size_id', 'stock', 'price', 'image_path'];

    protected function casts(): array
    {
        return ['price' => 'decimal:2'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(CatalogAttribute::class, 'color_id');
    }

    public function size(): BelongsTo
    {
        return $this->belongsTo(CatalogAttribute::class, 'size_id');
    }
}
