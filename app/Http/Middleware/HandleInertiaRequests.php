<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\Category;
use App\Models\SiteSetting;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        $search = fn () => $request->is('admin*') ? [] : \App\Models\Product::with('category:id,name')
            ->where('status', true)->get(['id', 'name', 'category_id', 'featured_image_one', 'regular_price', 'sale_price'])
            ->map(fn ($product) => ['id' => $product->id, 'name' => $product->name,
                'category' => $product->category?->name, 'image' => $product->featured_image_one,
                'price' => (float) ($product->sale_price > 0 ? $product->sale_price : $product->regular_price)]);
        return [...parent::share($request), 'catalogSearchProducts' => $search, 'auth' => ['user' => $request->user()?->load('role.permissions')], 'flash' => ['success' => fn () => $request->session()->get('success'), 'error' => fn () => $request->session()->get('error')], 'storeCategories' => fn () => $request->is('admin*') ? [] : Category::with('subcategories.children')->where('is_active', true)->where('show_in_header', true)->orderBy('serial')->orderBy('id')->get(['id', 'name', 'slug', 'image']), 'sidebarCategories' => fn () => $request->is('admin*') ? [] : Category::with('subcategories.children')->where('is_active', true)->where('show_on_sidebar', true)->orderBy('serial')->orderBy('id')->get(['id', 'name', 'image']), 'footerCategories' => fn () => $request->is('admin*') ? [] : Category::where('is_active', true)->orderBy('serial')->orderBy('id')->limit(5)->get(['id', 'name', 'slug']), 'storeSettings' => fn () => $request->is('admin*') ? [] : array_merge(SiteSetting::current(), ['shipping_inside_dhaka' => SiteSetting::current()['shipping_inside_dhaka'] ?? 80, 'shipping_outside_dhaka' => SiteSetting::current()['shipping_outside_dhaka'] ?? 150])];
    }
}
