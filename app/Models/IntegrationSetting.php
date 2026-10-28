<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrationSetting extends Model
{
    protected $fillable = ['provider', 'settings', 'credentials'];

    protected $hidden = ['credentials'];

    protected function casts(): array
    {
        return ['settings' => 'array', 'credentials' => 'encrypted:array'];
    }
}
