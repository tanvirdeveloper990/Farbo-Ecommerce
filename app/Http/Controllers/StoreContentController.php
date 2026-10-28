<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\SiteSetting;
use App\Services\MediaLibrary;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StoreContentController extends Controller
{
    private function authorizeAccess(Request $request, string $permission): void
    {
        abort_unless($request->user()->hasPermission($permission), 403);
    }

    private function deleteUpload(?string $url, MediaLibrary $library): void
    {
        if ($url) {
            $library->deleteIfUnused($url);
        }
    }

    public function banners(Request $request)
    {
        $this->authorizeAccess($request, 'banners.manage');

        return Inertia::render('Banners', ['banners' => Banner::orderBy('serial')->orderBy('id')->get()]);
    }

    public function saveBanner(Request $request, MediaLibrary $library, ?Banner $banner = null)
    {
        $this->authorizeAccess($request, 'banners.manage');
        $data = $request->validate([
            'serial' => 'required|integer|min:0|max:999999',
            'image' => [$banner?->exists ? 'nullable' : 'required_without:image_path', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'image_path' => [$banner?->exists ? 'nullable' : 'required_without:image', 'nullable', 'string', 'max:2048'],
        ]);
        $banner ??= new Banner;
        $old = $banner->image;
        $newlyUploaded = null;
        $banner->serial = $data['serial'];
        if ($request->hasFile('image')) {
            $asset = $library->store($request->file('image'), $request->user()->id);
            $banner->image = '/storage/'.$asset->path;
            $newlyUploaded = $banner->image;
        } elseif ($request->filled('image_path')) {
            $banner->image = $library->selectedImage($request->input('image_path'));
        }
        try {
            $banner->save();
        } catch (\Throwable $exception) {
            if ($newlyUploaded) {
                $library->deleteIfUnused($newlyUploaded);
            }
            throw $exception;
        }
        if ($old !== $banner->image) {
            $this->deleteUpload($old, $library);
        }

        return back()->with('success', 'Banner saved successfully.');
    }

    public function deleteBanner(Request $request, Banner $banner, MediaLibrary $library)
    {
        $this->authorizeAccess($request, 'banners.manage');
        $old = $banner->image;
        $banner->delete();
        $this->deleteUpload($old, $library);

        return back()->with('success', 'Banner deleted successfully.');
    }

    public function settings(Request $request)
    {
        $this->authorizeAccess($request, 'settings.manage');

        return Inertia::render('Settings', ['settings' => SiteSetting::current()]);
    }

    public function whyChooseUs(Request $request)
    {
        $this->authorizeAccess($request, 'settings.manage');
        return Inertia::render('WhyChooseUs', ['settings' => SiteSetting::current()]);
    }

    public function saveWhyChooseUs(Request $request, MediaLibrary $library)
    {
        $this->authorizeAccess($request, 'settings.manage');
        $data = $request->validate([
            'why_title' => 'required|string|max:250',
            'why_summary' => 'required|string|max:50000',
            'why_image_path' => 'nullable|string|max:2048',
        ]);
        $config = \HTMLPurifier_Config::createDefault();
        $config->set('Cache.SerializerPath', storage_path('framework/cache'));
        $config->set('HTML.Allowed', 'p[style],br,strong,b,em,i,u,s,strike,span[style],h2[style],h3[style],h4[style],ul,ol,li,blockquote,a[href|title],table,thead,tbody,tr,th,td,hr');
        $config->set('CSS.AllowedProperties', ['text-align', 'color', 'background-color', 'font-size']);
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true, 'tel' => true]);
        $data['why_summary'] = (new \HTMLPurifier($config))->purify($data['why_summary']);
        $current = SiteSetting::current();
        $image = $request->filled('why_image_path') ? $library->selectedImage($data['why_image_path']) : ($current['why_image'] ?? null);
        SiteSetting::updateOrCreate(['id' => 1], ['data' => array_merge($current, [
            'why_title' => $data['why_title'], 'why_summary' => $data['why_summary'], 'why_image' => $image,
        ])]);
        if (($current['why_image'] ?? null) !== $image) $this->deleteUpload($current['why_image'] ?? null, $library);
        return back()->with('success', 'Why Choose Us updated successfully.');
    }

    public function saveSettings(Request $request, MediaLibrary $library)
    {
        $this->authorizeAccess($request, 'settings.manage');
        $rules = ['company_name' => 'required|string|max:150', 'address' => 'nullable|string|max:1000',
            'footer_description' => 'nullable|string|max:1500',
            'topbar_text' => 'nullable|string|max:150', 'topbar_highlight' => 'nullable|string|max:100',
            'copyright' => 'nullable|string|max:300', 'marquee' => 'nullable|string|max:1000'];
        $rules['meta_pixel_id'] = ['nullable', 'string', 'regex:/^[0-9]{5,25}$/'];
        $rules['gtm_container_id'] = ['nullable', 'string', 'regex:/^GTM-[A-Z0-9]{4,20}$/'];
        $rules['meta_pixel_enabled'] = 'sometimes|boolean';
        $rules['gtm_enabled'] = 'sometimes|boolean';
        foreach (['phone_one', 'phone_two'] as $key) {
            $rules[$key] = ['nullable', 'string', 'max:30', 'regex:/^[+0-9()\s.\-]+$/'];
        }
        foreach (['email_one', 'email_two'] as $key) {
            $rules[$key] = 'nullable|email|max:255';
        }
        foreach (['maps', 'facebook', 'linkedin', 'youtube', 'whatsapp'] as $key) {
            $rules[$key] = 'nullable|url:http,https|max:2048';
        }
        foreach (['header_logo', 'footer_logo', 'favicon'] as $key) {
            $rules[$key] = 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240';
            $rules[$key.'_path'] = 'nullable|string|max:2048';
            $rules['remove_'.$key] = 'sometimes|boolean';
        }
        $data = $request->validate($rules);
        foreach (['meta_pixel_enabled', 'gtm_enabled'] as $trackingFlag) {
            if (array_key_exists($trackingFlag, $data)) $data[$trackingFlag] = $request->boolean($trackingFlag);
        }
        $current = SiteSetting::current();
        $oldFiles = [];
        $newFiles = [];
        try {
            foreach (['header_logo', 'footer_logo', 'favicon'] as $key) {
                unset($data['remove_'.$key]);
                unset($data[$key.'_path']);
                if ($request->hasFile($key)) {
                    $asset = $library->store($request->file($key), $request->user()->id);
                    $data[$key] = '/storage/'.$asset->path;
                    $newFiles[] = $data[$key];
                    $oldFiles[] = $current[$key] ?? null;
                } elseif ($request->filled($key.'_path')) {
                    $data[$key] = $library->selectedImage($request->input($key.'_path'));
                    if (($current[$key] ?? null) !== $data[$key]) {
                        $oldFiles[] = $current[$key] ?? null;
                    }
                } elseif ($request->boolean('remove_'.$key)) {
                    $data[$key] = null;
                    $oldFiles[] = $current[$key] ?? null;
                } else {
                    $data[$key] = $current[$key] ?? null;
                }
            }
            SiteSetting::updateOrCreate(['id' => 1], ['data' => array_merge($current, $data)]);
        } catch (\Throwable $exception) {
            foreach ($newFiles as $file) {
                $library->deleteIfUnused($file);
            }
            throw $exception;
        }
        foreach ($oldFiles as $file) {
            $this->deleteUpload($file, $library);
        }

        return back()->with('success', 'Company settings updated successfully.');
    }
}
