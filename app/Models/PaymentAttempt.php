<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PaymentAttempt extends Model {
    protected $guarded = [];
    protected $hidden = ['credentials'];
    protected function casts(): array { return ['credentials' => 'encrypted:array', 'amount' => 'decimal:2']; }
    public function order() { return $this->belongsTo(Order::class); }
}
