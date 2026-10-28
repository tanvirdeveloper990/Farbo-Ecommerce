<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class FounderMessage extends Model
{
    protected $fillable = ['title', 'founder_name', 'designation', 'image', 'content', 'serial', 'is_active'];
    protected function casts(): array { return ['is_active' => 'boolean', 'serial' => 'integer']; }
}
