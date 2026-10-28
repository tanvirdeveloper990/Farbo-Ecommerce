<?php
require __DIR__.'/../vendor/autoload.php';$app=require __DIR__.'/../bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Http\Request;use Illuminate\Support\Facades\{DB,URL};
function check($ok,$message){if(!$ok)throw new RuntimeException($message);echo "PASS: $message\n";}
DB::beginTransaction();try{
$order=App\Models\Order::create(['customer_name'=>'Private fixture','phone'=>'01700000000','address'=>'Fixture address','area'=>'inside_dhaka','payment_method'=>'sslcommerz','payment_status'=>'paid','status'=>'pending','subtotal'=>100,'delivery_fee'=>80,'total'=>180,'stock_reserved'=>true]);
$session=$app->make('session.store');$session->flush();$middleware=new App\Http\Middleware\CleanOrderConfirmation;
function confirmationRequest($url){global $app,$session,$order;$r=Request::create($url,'GET');$app->instance('request',$r);$r->setLaravelSession($session);$route=new Illuminate\Routing\Route('GET','order-success/{order}',fn()=>null);$route->bind($r);$route->setParameter('order',$order);$r->setRouteResolver(fn()=>$route);return $r;}
$url=URL::temporarySignedRoute('orders.success',now()->addHour(),['order'=>$order->id,'payment_result'=>'returned']);
$response=$middleware->handle(confirmationRequest($url),fn()=>response('unexpected'));
check($response->getStatusCode()===303 && !str_contains($response->headers->get('Location'),'signature='),'Signed success URL redirects before tracking loads');
check($session->get('checkout_order_id')===$order->id,'Verified payment retains fresh-purchase signal');
$clean=URL::to('/order-success/'.$order->id);$response=$middleware->handle(confirmationRequest($clean),fn()=>response('confirmation'));check($response->getStatusCode()===200,'Clean confirmation reload is authorized by session');
$session->forget('order_confirmation_access');try{$middleware->handle(confirmationRequest($clean),fn()=>response('unexpected'));throw new RuntimeException('Unauthorized access accepted');}catch(Symfony\Component\HttpKernel\Exception\HttpException $e){check($e->getStatusCode()===403,'Another browser cannot read an unsigned order');}
$session->put('order_confirmation_access',[(string)$order->id=>now()->subMinute()->timestamp]);try{$middleware->handle(confirmationRequest($clean),fn()=>response('unexpected'));throw new RuntimeException('Expired access accepted');}catch(Symfony\Component\HttpKernel\Exception\HttpException $e){check($e->getStatusCode()===403,'Expired confirmation access rejected');}
}finally{DB::rollBack();}
