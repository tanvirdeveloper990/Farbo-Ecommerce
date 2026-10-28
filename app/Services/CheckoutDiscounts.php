<?php
namespace App\Services;
use App\Models\DiscountCode;use Illuminate\Validation\ValidationException;
class CheckoutDiscounts {
public function calculate(float $subtotal,?string $coupon,?string $gift,bool $lock=false):array{
$remaining=(int)round($subtotal*100);$result=['coupon_code'=>null,'gift_card_code'=>null,'coupon_discount'=>0,'gift_card_discount'=>0,'discount_amount'=>0];
foreach(['coupon'=>$coupon,'gift_card'=>$gift] as $type=>$input){$code=strtoupper(trim($input??''));if($code==='')continue;$query=DiscountCode::where('type',$type)->where('code',$code);if($lock)$query->lockForUpdate();$record=$query->first();if(!$record||!$record->status)throw ValidationException::withMessages([$type.'_code'=>'This '.str_replace('_',' ',$type).' code is invalid or inactive.']);$cents=min($remaining,(int)round((float)$record->amount*100));$remaining-=$cents;$result[$type.'_code']=$code;$result[$type.'_discount']=$cents/100;}
$result['discount_amount']=$result['coupon_discount']+$result['gift_card_discount'];return $result;
}
}
