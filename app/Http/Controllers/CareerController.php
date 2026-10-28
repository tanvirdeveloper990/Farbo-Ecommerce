<?php
namespace App\Http\Controllers;
use App\Models\CareerApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
class CareerController extends Controller {
    private function authorizeAccess(Request $request): void { abort_unless($request->user()->hasPermission('contacts.manage'),403); }
    public function page() { Inertia::setRootView('storefront'); return Inertia::render('Storefront/Career'); }
    public function store(Request $request) {
        $data=$request->validate([
            'name'=>'required|string|max:160','email'=>'required|email|max:255',
            'phone'=>['required','string','min:7','max:40','regex:/^[+0-9()\s.\-]+$/'],
            'address'=>'required|string|max:2000','years_of_experience'=>'required|numeric|min:0|max:80',
            'expected_salary'=>'required|numeric|min:0|max:99999999', 'motivation'=>'required|string|max:20000',
            'attachment'=>'nullable|file|max:10240|mimes:pdf,doc,docx,txt,rtf,jpg,jpeg,png,odt',
        ]);
        unset($data['attachment']); $path=null;
        try {
            if($file=$request->file('attachment')) {
                $path=$file->store('career-applications','local');
                if(!$path) throw new \RuntimeException('Unable to save attachment.');
                $data['attachment_path']=$path;
                $data['attachment_name']=mb_substr(basename(str_replace('\\','/',$file->getClientOriginalName())),0,255);
                $data['attachment_size']=$file->getSize();
            }
            CareerApplication::create($data);
        } catch(\Throwable $exception) { if($path) Storage::disk('local')->delete($path); throw $exception; }
        return redirect('/career')->with('success','Your application has been submitted successfully. Thank you for your interest in joining us.');
    }
    public function index(Request $request) {
        $this->authorizeAccess($request);
        $filters=$request->validate(['search'=>'nullable|string|max:200']);
        $records=CareerApplication::when($filters['search']??null,fn($q,$s)=>$q->where(fn($q)=>$q->where('name','like','%'.$s.'%')->orWhere('email','like','%'.$s.'%')->orWhere('phone','like','%'.$s.'%')))->latest('id')->paginate(30)->withQueryString();
        return Inertia::render('CareerApplications',['records'=>$records,'filters'=>$filters]);
    }
    public function show(Request $request,CareerApplication $careerApplication) { $this->authorizeAccess($request); return Inertia::render('CareerApplicationDetails',['application'=>$careerApplication]); }
    public function attachment(Request $request,CareerApplication $careerApplication) {
        $this->authorizeAccess($request);
        abort_unless($careerApplication->attachment_path && Storage::disk('local')->exists($careerApplication->attachment_path),404);
        return Storage::disk('local')->download($careerApplication->attachment_path,$careerApplication->attachment_name,['X-Content-Type-Options'=>'nosniff','Cache-Control'=>'private, no-store']);
    }
}
