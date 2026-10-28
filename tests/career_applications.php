<?php
require __DIR__.'/../vendor/autoload.php';
$app=require __DIR__.'/../bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use App\Models\{CareerApplication,User};
use Illuminate\Http\{Request,UploadedFile};
use Illuminate\Support\Facades\{DB,Storage};
function careerCheck($ok,$text){if(!$ok)throw new RuntimeException($text);echo "PASS: $text\n";}
DB::beginTransaction();$attachmentPath=null;
try{
 $controller=app(App\Http\Controllers\CareerController::class);
 $tag='career-test-'.bin2hex(random_bytes(4));
 $data=['name'=>$tag,'email'=>$tag.'@example.test','phone'=>'+8801700000000','address'=>'Test address','years_of_experience'=>'2.5','expected_salary'=>'45000','motivation'=>'Temporary application for verification.'];
 $r=Request::create('/career','POST',$data,[],['attachment'=>UploadedFile::fake()->createWithContent('test-cv.pdf',"%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF")]);
 $r->setLaravelSession($app->make('session.store'));$controller->store($r);
 $record=CareerApplication::where('name',$tag)->firstOrFail();$attachmentPath=$record->attachment_path;
 careerCheck(Storage::disk('local')->exists($attachmentPath) && !Storage::disk('public')->exists($attachmentPath),'Application and attachment saved privately');
 careerCheck($record->email===$data['email'] && (float)$record->years_of_experience===2.5 && (float)$record->expected_salary===45000.0,'Applicant fields saved correctly');
 $admin=User::where('is_super_admin',true)->firstOrFail();$r->setUserResolver(fn()=>$admin);
 careerCheck($controller->show($r,$record) instanceof Inertia\Response,'Admin can view application');
 $response=$controller->attachment($r,$record);careerCheck($response->getStatusCode()===200 && str_contains($response->headers->get('Content-Disposition'),'attachment'),'Admin can download attachment');
 $r->setUserResolver(fn()=>new User(['name'=>'Unauthorized']));
 try{$controller->attachment($r,$record);throw new RuntimeException('Unauthorized download allowed');}catch(Symfony\Component\HttpKernel\Exception\HttpException $e){careerCheck($e->getStatusCode()===403,'Unauthorized attachment access blocked');}
 $invalid=Request::create('/career','POST',[...$data,'email'=>'invalid','years_of_experience'=>-1]);
 try{$controller->store($invalid);throw new RuntimeException('Invalid form accepted');}catch(Illuminate\Validation\ValidationException $e){careerCheck(isset($e->errors()['email'],$e->errors()['years_of_experience']),'Invalid email and experience rejected');}
 $bad=Request::create('/career','POST',$data,[],['attachment'=>UploadedFile::fake()->createWithContent('run.php','<?php echo 1;')]);
 try{$controller->store($bad);throw new RuntimeException('Executable upload accepted');}catch(Illuminate\Validation\ValidationException $e){careerCheck(isset($e->errors()['attachment']),'Executable attachment rejected');}
}finally{DB::rollBack();if($attachmentPath)Storage::disk('local')->delete($attachmentPath);}
echo "PASS: temporary application and file removed.\n";
