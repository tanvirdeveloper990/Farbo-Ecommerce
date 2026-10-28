<?php

namespace App\Http\Controllers;

use App\Models\Policy;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PolicyController extends Controller
{
    public function editAbout(Request $request)
    {
        return $this->edit($request, Policy::where('slug', 'about-us')->firstOrFail());
    }
    public function about()
    {
        return $this->show(Policy::where('slug', 'about-us')->firstOrFail());
    }
    public function edit(Request $request, Policy $policy)
    {
        abort_unless($request->user()->hasPermission('policies.manage'), 403);

        return Inertia::render('PolicyEdit', ['policy' => $policy]);
    }

    public function update(Request $request, Policy $policy)
    {
        abort_unless($request->user()->hasPermission('policies.manage'), 403);
        $data = $request->validate(['content' => 'nullable|string|max:200000']);
        $config = \HTMLPurifier_Config::createDefault();
        $config->set('Cache.SerializerPath', storage_path('framework/cache'));
        $config->set('HTML.Allowed', 'p[style],br,strong,b,em,i,u,s,strike,span[style],h2[style],h3[style],h4[style],ul,ol,li,blockquote,a[href|title],table,thead,tbody,tr,th,td,hr');
        $config->set('CSS.AllowedProperties', ['text-align', 'color', 'background-color', 'font-size']);
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true, 'tel' => true]);
        $clean = (new \HTMLPurifier($config))->purify($data['content'] ?? '');
        $policy->update(['content' => $clean]);

        return back()->with('success', $policy->title.' updated successfully.');
    }

    public function show(Policy $policy)
    {
        Inertia::setRootView('storefront');
        if ($policy->slug === 'about-us') return Inertia::render('Storefront/About', ['policy' => $policy]);

        return Inertia::render('Storefront/Home', ['banners' => [], 'newArrivals' => [], 'settings' => SiteSetting::current(), 'policy' => $policy]);
    }
}
