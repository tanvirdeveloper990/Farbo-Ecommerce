<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;

class FarboStorefrontController extends Controller
{
    private function template(string $view, array $data, string $title)
    {
        \Inertia\Inertia::setRootView('storefront');
        return \Inertia\Inertia::render('Storefront/Template', [
            'html' => view($view, [...$data, 'renderForInertia' => true])->render(),
            'title' => $title,
        ]);
    }
    public function popularCategories()
    {
        return $this->template('farbo.popular-categories', [
            'popularCategories' => Category::where('is_active', true)->where('is_popular', true)->orderBy('serial')->orderBy('id')->get(),
        ], 'Popular Categories | FARBO');
    }

    public function home(\Illuminate\Http\Request $request)
    {
        $search = $request->validate(['q' => 'nullable|string|max:200'])['q'] ?? '';
        $sectionCategories = Category::where('is_active', true)->where('show_on_home', true)->orderBy('serial')->orderBy('id')->get();
        $sectionProducts = [];
        foreach ($sectionCategories as $category) {
            $sectionProducts[$category->id] = Product::with('category', 'brand', 'images')->where('status', true)
                ->where('category_id', $category->id)
                ->when($search, fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
                ->latest('id')->take(16)->get();
        }
        return $this->template('farbo.home', [
            'sectionProducts' => $sectionProducts,
            'farboSettings' => \App\Models\SiteSetting::current(),
            'banners' => Banner::orderBy('serial')->orderBy('id')->get(),
            'popularCategories' => Category::where('is_active', true)->where('is_popular', true)->orderBy('serial')->get(),
            'homeCategories' => $sectionCategories,
            'products' => Product::with('category', 'brand', 'images')->where('status', true)
                ->when($search, fn ($query) => $query->where('name', 'like', '%'.$search.'%'))->latest('id')->take(16)->get(),
        ], 'FARBO – Everything You Need, One Place');
    }

    public function product(?Product $product = null)
    {
        $product ??= Product::where('status', true)->where('name', 'Heavy Duty Alternator')->firstOrFail();
        abort_unless($product->status, 404);
        $product->load('category', 'brand', 'images', 'variants.color', 'variants.size');
        $options = Product::with('brand')->where('status', true)->where('name', $product->name)
            ->whereHas('brand', fn ($query) => $query->where('type', 'brands')->where('is_active', true))->get();
        return $this->template('farbo.dynamic-product', [
            'farboSettings' => \App\Models\SiteSetting::current(),
            'product' => $product,
            'options' => $options,
            'relatedProducts' => Product::with('category', 'brand', 'images')->where('status', true)
                ->where('category_id', $product->category_id)->where('id', '!=', $product->id)->take(8)->get(),
        ], $product->name.' | FARBO');
    }
}
