<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerReview extends Model
{
    protected $fillable = ['name', 'image', 'star', 'description', 'status'];

    protected function casts(): array
    {
        return ['star' => 'integer', 'status' => 'boolean'];
    }
}
