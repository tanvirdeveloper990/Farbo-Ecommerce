<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'image',
        'serial',
        'show_in_header',
        'show_on_home',
        'show_on_sidebar',
        'is_popular',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'show_in_header' => 'boolean',
            'show_on_home' => 'boolean',
            'show_on_sidebar' => 'boolean',
            'is_popular' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function subcategories()
    {
        return $this->hasMany(Subcategory::class)->orderBy('serial')->orderBy('id');
    }
}
