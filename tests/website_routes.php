<?php
require __DIR__.'/../vendor/autoload.php';
$app=require __DIR__.'/../bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;use Illuminate\Support\Facades\Auth;use Illuminate\Http\Request;use Inertia\Inertia;
DB::beginTransaction();$failed=[];$passed=0;$report=[];
try {
 $tag='audit-'.bin2hex(random_bytes(5));
 $admin=App\Models\User::forceCreate(['name'=>'Audit Admin','email'=>$tag.'-admin@example.test','password'=>bin2hex(random_bytes(16)),'is_admin'=>true,'is_super_admin'=>true]);
 $customer=App\Models\User::forceCreate(['name'=>'Audit Customer','email'=>$tag.'@example.test','password'=>bin2hex(random_bytes(16))]);
 $public=['/','/login','/register','/contact','/how-to-buy','/new-arrivals','/customer-reviews','/track-order','/checkout','/?q=Silk','/?wishlist=1&wish_ids=5,6'];
 $public[]='/popular-categories';$public[]='/single-page';$public[]='/about-us';$public[]='/founder-message';$public[]='/career';
 foreach(App\Models\Category::where('is_active',true)->pluck('slug') as $slug)$public[]='/categories/'.$slug;
 foreach(App\Models\Product::where('status',true)->pluck('id') as $id)$public[]='/product/'.$id;
 foreach(App\Models\Policy::pluck('slug') as $slug)$public[]='/policies/'.$slug;
 $adminUrls=['/admin/dashboard','/admin/profile','/admin/customers','/admin/management/users','/admin/management/roles','/admin/management/permissions','/admin/categories','/admin/categories/create','/admin/products','/admin/products/create','/admin/brands','/admin/colors','/admin/sizes','/admin/customer-reviews','/admin/customer-reviews/create','/admin/banners','/admin/contacts','/admin/smtp','/admin/payments','/admin/courier','/admin/faqs','/admin/faqs/create','/admin/settings','/admin/media','/admin/media/library','/admin/orders','/admin/shipping'];
 $adminUrls[]='/admin/why-choose-us';$adminUrls[]='/admin/founder-messages';$adminUrls[]='/admin/founder-messages/create';$adminUrls[]='/admin/career';
 foreach(['category'=>App\Models\Category::first(),'subcategory'=>App\Models\Subcategory::first(),'child'=>App\Models\ChildCategory::first()] as $kind=>$record)if($record)$adminUrls[]='/admin/categories/records/'.$kind.'/'.$record->id.'/edit';
 if($p=App\Models\Product::first())$adminUrls[]='/admin/products/'.$p->id.'/edit';
 foreach(App\Models\Policy::pluck('slug') as $slug)$adminUrls[]='/admin/policies/'.$slug;
 foreach(['pending','processing','on_the_way','on_hold','courier','completed','cancelled'] as $s)$adminUrls[]='/admin/orders?status='.$s;
 foreach([[$public,null],[['/my-account','/my-orders','/change-password'],$customer],[$adminUrls,$admin]] as [$urls,$user])foreach($urls as $url){
   Auth::forgetGuards();if($user)Auth::guard()->setUser($user);Inertia::setRootView('app');
   $r=Request::create($url,'GET',[],[],[],['HTTP_X_INERTIA_VERSION'=>$app->make(App\Http\Middleware\HandleInertiaRequests::class)->version(Request::create($url)), 'HTTP_X_INERTIA'=>'true','HTTP_X_REQUESTED_WITH'=>'XMLHttpRequest','HTTP_ACCEPT'=>'text/html, application/xhtml+xml']);
   $res=$app->make(Illuminate\Contracts\Http\Kernel::class)->handle($r);$status=$res->getStatusCode();
   if($status!==200){$failed[]="$url => $status";echo "FAIL: $url ($status)\n";}else $passed++;
   $report[]=['url'=>$url,'status'=>$status];
 }
} finally {DB::rollBack();Auth::forgetGuards();}
file_put_contents(__DIR__.'/../storage/app/website-audit-routes.json',json_encode(['passed'=>$passed,'failed'=>$failed,'routes'=>$report],JSON_PRETTY_PRINT));
echo "Route audit: $passed passed; ".count($failed)." failed.\n";exit(count($failed)?1:0);
