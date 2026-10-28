<?php
namespace App\Http\Controllers;
use App\Models\FounderMessage;
use App\Services\MediaLibrary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class FounderMessageController extends Controller
{
    private function authorizeAccess(Request $request): void { abort_unless($request->user()->hasPermission('settings.manage'), 403); }
    public function index(Request $request) {
        $this->authorizeAccess($request);
        return Inertia::render('FounderMessages', ['records' => FounderMessage::orderBy('serial')->orderBy('id')->get()]);
    }
    public function form(Request $request, ?FounderMessage $founderMessage = null) {
        $this->authorizeAccess($request);
        return Inertia::render('FounderMessageForm', ['record' => $founderMessage]);
    }
    public function save(Request $request, MediaLibrary $library, ?FounderMessage $founderMessage = null) {
        $this->authorizeAccess($request);
        $data = $request->validate(['title' => 'required|string|max:180', 'founder_name' => 'required|string|max:150', 'designation' => 'nullable|string|max:180', 'content' => 'required|string|max:200000', 'image_path' => 'nullable|string|max:2048', 'is_active' => 'required|boolean']);
        $config = \HTMLPurifier_Config::createDefault();
        $config->set('Cache.SerializerPath', storage_path('framework/cache'));
        $config->set('HTML.Allowed', 'p[style],br,strong,b,em,i,u,s,strike,span[style],h2[style],h3[style],h4[style],ul,ol,li,blockquote,a[href|title],table,thead,tbody,tr,th,td,hr');
        $config->set('CSS.AllowedProperties', ['text-align', 'color', 'background-color', 'font-size', 'font-weight', 'font-style', 'text-decoration']);
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true, 'tel' => true]);
        $data['content'] = (new \HTMLPurifier($config))->purify($data['content']);
        if (!trim(strip_tags($data['content']))) throw ValidationException::withMessages(['content' => 'Write the founder message.']);
        $old = $founderMessage?->image;
        $data['image'] = $request->filled('image_path') ? $library->selectedImage($data['image_path']) : $old;
        unset($data['image_path']);
        if (!$data['image']) throw ValidationException::withMessages(['image_path' => 'Choose a founder photo.']);
        DB::transaction(function () use ($founderMessage, $data) {
            if ($founderMessage) $founderMessage->update($data);
            else {
                $last = FounderMessage::orderByDesc('serial')->lockForUpdate()->first();
                FounderMessage::create([...$data, 'serial' => ($last?->serial ?? 0) + 1]);
            }
        });
        if ($old && $old !== $data['image']) $library->deleteIfUnused($old);
        return redirect('/admin/founder-messages')->with('success', 'Founder message saved successfully.');
    }
    public function reorder(Request $request) {
        $this->authorizeAccess($request);
        $ids = $request->validate(['ids' => 'required|array|min:1|max:500', 'ids.*' => 'required|integer|distinct|exists:founder_messages,id'])['ids'];
        DB::transaction(function () use ($ids) {
            $current = FounderMessage::lockForUpdate()->pluck('id')->sort()->values()->all();
            $submitted = collect($ids)->map(fn ($id) => (int)$id)->sort()->values()->all();
            if ($current !== $submitted) throw ValidationException::withMessages(['ids' => 'The list changed. Reload before reordering.']);
            foreach ($ids as $serial => $id) FounderMessage::whereKey($id)->update(['serial' => $serial + 1]);
        });
        return back()->with('success', 'Founder message order updated.');
    }
    public function destroy(Request $request, FounderMessage $founderMessage, MediaLibrary $library) {
        $this->authorizeAccess($request);
        $image = $founderMessage->image;
        $founderMessage->delete();
        $library->deleteIfUnused($image);
        return back()->with('success', 'Founder message deleted.');
    }
    public function show() {
        Inertia::setRootView('storefront');
        return Inertia::render('Storefront/FounderMessages', ['messages' => FounderMessage::where('is_active', true)->orderBy('serial')->orderBy('id')->get()]);
    }
}

