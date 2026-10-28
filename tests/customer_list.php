<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:']);
Illuminate\Support\Facades\DB::purge('sqlite');
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
Schema::create('users', function ($t) { $t->id(); $t->string('name'); $t->string('email'); $t->string('phone')->nullable(); $t->string('address')->nullable(); $t->boolean('is_admin')->default(false); $t->boolean('is_super_admin')->default(false); $t->timestamps(); });
Schema::create('orders', function ($t) { $t->id(); $t->integer('customer_id')->nullable(); foreach(['customer_name','phone','email','address','area','payment_method','payment_status','status','customer_note'] as $c) $t->string($c)->nullable(); $t->decimal('total')->default(100); $t->timestamps(); });
Schema::create('order_items', function ($t) { $t->id(); $t->integer('order_id'); $t->string('product_name'); $t->timestamps(); });
$owner=App\Models\User::forceCreate(['name'=>'Owner','email'=>'owner@example.com','is_admin'=>true,'is_super_admin'=>true]);
$staff=App\Models\User::forceCreate(['name'=>'Staff','email'=>'staff@example.com','is_admin'=>true]);
$customer=App\Models\User::forceCreate(['name'=>'Account name','email'=>'account@example.com']);
function orderData($id,$phone,$name) { return ['customer_id'=>$id,'customer_name'=>$name,'phone'=>$phone,'email'=>'checkout@example.com','address'=>'Checkout address','area'=>'outside_dhaka','payment_method'=>'cash_on_delivery','payment_status'=>'unpaid','status'=>'pending','customer_note'=>'Call before delivery','total'=>100]; }
$a=App\Models\Order::create(orderData($customer->id,'01711111111','Checkout old'));
$b=App\Models\Order::create(orderData($customer->id,'01722222222','Checkout latest'));
$g=App\Models\Order::create(orderData(null,'01811111111','Guest old'));
$h=App\Models\Order::create(orderData(null,'01811111111','Guest latest'));
DB::table('order_items')->insert(['order_id'=>$h->id,'product_name'=>'Sample Saree']);
$adminCheckout=App\Models\Order::create(orderData($owner->id,'01922222222','Checkout shopper'));
$controller=new App\Http\Controllers\AdminCustomerController;
function requestFor($search='') { global $app,$owner; $r=Illuminate\Http\Request::create('/admin/customers','GET',['search'=>$search]); $app->instance('request',$r); $r->setUserResolver(fn()=>$owner); return $r; }
function propsFor($response) { return (new ReflectionProperty($response,'props'))->getValue($response); }
function check($condition,$message) { if(!$condition) throw new RuntimeException($message); echo "PASS: $message\n"; }
$p=propsFor($controller->index(requestFor()));
check($p['records']->total()===5,'Customer list includes registered and guest buyers without staff or owner accounts');
$rows=collect($p['records']->items());
check($rows->firstWhere('id',$b->id)->name==='Checkout latest' && $rows->firstWhere('id',$b->id)->orders_count===1,'Registered order row shows its own checkout fields');
check($rows->where('phone','01811111111')->count()===2,'Repeat guest orders have separate rows');
check(propsFor($controller->index(requestFor('Owner')))['records']->total()===0,'Searching cannot expose owner accounts');
check(propsFor($controller->index(requestFor('01722222222')))['records']->total()===1,'Customer search matches checkout phone');
$p=propsFor($controller->show(requestFor(),'account',$customer->id));
check($p['orders']->total()===1 && $p['customer']['account']['name']==='Account name','Customer view contains one order and registered account details');
$p=propsFor($controller->show(requestFor(),'guest',$h->id));
check($p['orders']->total()===1 && $p['orders']->first()->items->first()->product_name==='Sample Saree' && $p['orders']->first()->customer_note==='Call before delivery','Guest view includes order items, checkout information and notes');
try { $controller->show(requestFor(),'account',$owner->id); throw new RuntimeException('Owner account exposed'); } catch(Illuminate\Database\Eloquent\ModelNotFoundException $e) { echo "PASS: Customer view excludes administrator accounts\n"; }
$p=propsFor($controller->show(requestFor(),'guest',$adminCheckout->id));
check($p['customer']['name']==='Checkout shopper' && $p['customer']['account']===null && $p['orders']->total()===1,'Admin-placed order exposes checkout buyer information without administrator account details');
$r=requestFor();$r->setUserResolver(fn()=>$customer);
try { $controller->index($r); throw new RuntimeException('Unauthorized access'); } catch(Symfony\Component\HttpKernel\Exception\HttpException $e) { check($e->getStatusCode()===403,'Customer list requires admin permission'); }

foreach ([$a, $b, $g, $h, $adminCheckout] as $selectedOrder) {
    $p=propsFor($controller->show(requestFor(),'order',$selectedOrder->id));
    check($p['orders']->total()===1 && $p['orders']->first()->id===$selectedOrder->id && $p['customer']['name']===$selectedOrder->customer_name,'Eye view is restricted to order #'.$selectedOrder->id);
}
