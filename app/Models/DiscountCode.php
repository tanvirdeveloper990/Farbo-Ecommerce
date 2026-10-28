<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class DiscountCode extends Model{protected $fillable=['type','name','code','amount','status'];protected function casts():array{return ['amount'=>'decimal:2','status'=>'boolean'];}}
