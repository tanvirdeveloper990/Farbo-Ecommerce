<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:']);
Illuminate\Support\Facades\DB::purge('sqlite');
use Illuminate\Support\Facades\Schema;
Schema::create('orders',function($t){$t->id();foreach(['status','customer_name','phone','email','address','customer_note','payment_method','payment_status','courier_provider'] as $c)$t->string($c)->nullable();foreach(['subtotal','delivery_fee','total'] as $c)$t->decimal($c)->default(0);$t->timestamps();});
Schema::create('order_items',function($t){$t->id();$t->integer('order_id');$t->string('product_name');$t->string('variant_name')->nullable();$t->integer('quantity');$t->decimal('unit_price');$t->decimal('total');$t->timestamps();});
(require __DIR__.'/../database/migrations/2026_10_04_000002_create_courier_shipments.php')->up();
$order=App\Models\Order::create(['status'=>'pending','customer_name'=>'Private buyer','phone'=>'01711111111','email'=>'private@example.com','address'=>'Private address','customer_note'=>'Private note','payment_method'=>'cash_on_delivery','payment_status'=>'unpaid','subtotal'=>100,'delivery_fee'=>60,'total'=>160]);
$order->items()->create(['product_name'=>'Test saree','quantity'=>2,'unit_price'=>50,'total'=>100]);
$controller=new App\Http\Controllers\OrderTrackingController;
function lookup($number=null){global $app,$controller;$request=Illuminate\Http\Request::create('/track-order','GET',$number===null?[]:['order_id'=>$number]);$app->instance('request',$request);return (new ReflectionProperty($controller->index($request),'props'))->getValue($controller->index($request));}
function check($ok,$message){if(!$ok)throw new RuntimeException($message);echo "PASS: $message\n";}
check(lookup()['searched']===false,'Initial page has no order result');
$p=lookup('#10001');check($p['order']['number']===10001 && $p['order']['items'][0]['name']==='Test saree' && $p['order']['total']===160.0,'Receipt number finds exact order with correct items and totals');
check(!array_intersect(['phone','email','address','customer_name','customer_note','customer_id'],array_keys($p['order'])),'Public tracking excludes personal information');
foreach(['pending','processing','on_the_way','on_hold','courier','completed','cancelled'] as $status){$order->update(['status'=>$status]);check(lookup('10001')['order']['status']===$status,'Current '.$status.' status is returned');}
check(lookup('99999')['searched'] && lookup('99999')['order']===null,'Unknown order has not-found result');
check(lookup('1')['order']===null,'Internal database IDs do not accidentally match receipt numbers');
try{lookup('invalid');throw new RuntimeException('Invalid reference accepted');}catch(Illuminate\Validation\ValidationException $e){echo "PASS: Invalid reference is rejected\n";}

App\Models\CourierShipment::create(['order_id'=>$order->id,'provider'=>'steadfast','state'=>'booked','consignment_id'=>'321','tracking_code'=>'TRACK321','remote_status'=>'pending','cod_amount'=>160]);
$p=lookup('10001');check($p['order']['courier_tracking']==='TRACK321' && $p['order']['courier_status']==='pending','Public tracking includes consignment and courier status');check(!array_key_exists('options',$p['order']),'Public tracking does not expose booking options');
