<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = ['image', 'serial'];

    protected function casts(): array
    {
        return ['serial' => 'integer'];
    }
}
