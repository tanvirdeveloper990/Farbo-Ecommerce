<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChildCategory extends Model
{
    protected $fillable = ['name', 'slug', 'image', 'is_active', 'serial'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }
}
