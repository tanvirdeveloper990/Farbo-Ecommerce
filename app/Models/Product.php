<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'name',
        'category_id',
        'subcategory_id',
        'child_category_id',
        'brand_id',
        'regular_price',
        'sale_price',
        'unit',
        'stock',
        'description',
        'new_arrival',
        'featured_image_one',
        'featured_image_two',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'regular_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'new_arrival' => 'boolean',
            'status' => 'boolean',
        ];
    }

    // Products saved without a price are enquiry-only: no cart, checkout, or order buttons.
    public function hasPrice(): bool
    {
        return (float) ($this->sale_price > 0 ? $this->sale_price : $this->regular_price) > 0;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function childCategory(): BelongsTo
    {
        return $this->belongsTo(ChildCategory::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(CatalogAttribute::class, 'brand_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }
}
