<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogAttribute extends Model
{
    protected $fillable = ['type', 'name', 'color', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];
}
