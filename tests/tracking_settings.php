<?php
require __DIR__.'/../vendor/autoload.php';$app=require __DIR__.'/../bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;use Illuminate\Http\Request;
DB::beginTransaction();try{
$admin=App\Models\User::forceCreate(['name'=>'Tracking fixture','email'=>'tracking-'.bin2hex(random_bytes(5)).'@example.test','password'=>bin2hex(random_bytes(16)),'is_admin'=>true,'is_super_admin'=>true]);
function settingsRequest($data){global $app,$admin;$r=Request::create('/admin/settings','POST',$data);$app->instance('request',$r);$r->setLaravelSession($app->make('session.store'));$r->setUserResolver(fn()=>$admin);return $r;}
$controller=new App\Http\Controllers\StoreContentController;$media=$app->make(App\Services\MediaLibrary::class);$data=['company_name'=>'Tracking fixture','meta_pixel_id'=>'123456789012345','gtm_container_id'=>'GTM-TEST123','meta_pixel_enabled'=>true,'gtm_enabled'=>true];$controller->saveSettings(settingsRequest($data),$media);$saved=App\Models\SiteSetting::current();if($saved['meta_pixel_id']!==$data['meta_pixel_id'] || $saved['gtm_container_id']!==$data['gtm_container_id'])throw new RuntimeException('Tracking settings not saved');echo "PASS: Admin tracking settings save\n";
foreach(['meta_pixel_id'=>'<script>','gtm_container_id'=>'https://bad.example'] as $key=>$value){try{$controller->saveSettings(settingsRequest(array_replace($data,[$key=>$value])),$media);throw new RuntimeException('Invalid tracking ID accepted');}catch(Illuminate\Validation\ValidationException $e){echo "PASS: Reject invalid $key\n";}}
}finally{DB::rollBack();}
