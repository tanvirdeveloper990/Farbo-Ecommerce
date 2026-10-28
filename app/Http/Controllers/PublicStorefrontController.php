<?php

namespace App\Http\Controllers;

use App\Models\CatalogAttribute;
use App\Models\Category;
use App\Models\ChildCategory;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PublicStorefrontController extends Controller
{
    private function productData(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'category' => $product->category?->name,
            'categoryId' => $product->category_id,
            'categorySlug' => $product->category?->slug,
            'subcategory' => $product->subcategory?->name,
            'childCategory' => $product->childCategory?->name,
            'brand' => $product->brand?->name,
            'description' => $product->description,
            'image' => $product->featured_image_one ?: $product->images->first()?->path ?: $product->featured_image_two,
            'images' => collect([$product->featured_image_one, $product->featured_image_two])
                ->merge($product->images->pluck('path'))
                ->filter()
                ->unique()
                ->values(),
            'price' => (float) ($product->sale_price !== null && $product->sale_price > 0 ? $product->sale_price : $product->regular_price),
            'oldPrice' => $product->sale_price !== null && $product->sale_price > 0 ? (float) $product->regular_price : 0,
            'stock' => $product->stock,
            'unit' => $product->unit,
            'newArrival' => (bool) $product->new_arrival,
            'variants' => $product->variants->map(fn (ProductVariant $variant) => [
                'id' => $variant->id,
                'stock' => $variant->stock,
                'price' => (float) $variant->price,
                'colorId' => $variant->color_id,
                'color' => $variant->color?->name,
                'colorCode' => $variant->color?->color,
                'sizeId' => $variant->size_id,
                'size' => $variant->size?->name,
                'image' => $variant->image_path,
                'label' => collect([$variant->color?->name, $variant->size?->name])->filter()->implode(' / '),
            ])->values(),
        ];
    }

    private function productsQuery()
    {
        return Product::with([
            'category:id,name,slug',
            'subcategory:id,name,slug',
            'childCategory:id,name',
            'brand:id,name',
            'images:id,product_id,path',
            'variants.color:id,name,color',
            'variants.size:id,name',
        ])->where('status', true);
    }

    public function category(Request $request, ?string $category = null)
    {
        Inertia::setRootView('storefront');
        $allCategories = $category === null;
        $key = $category;
        $category = $allCategories ? null : Category::where('slug', $key)->where('is_active', true)->first();
        if (! $allCategories && ! $category && ctype_digit($key)) {
            $category = Category::where('is_active', true)->findOrFail((int) $key);
            return redirect('/categories/'.rawurlencode($category->slug).($request->getQueryString() ? '?'.$request->getQueryString() : ''), 301);
        }
        abort_unless($allCategories || $category, 404);
        $category?->load('subcategories.children');
        $filters = $request->validate([
            'q' => 'nullable|string|max:200',
            'wishlist' => 'nullable|boolean',
            'wish_ids' => ['nullable', 'string', 'max:2000', 'regex:/^(?:\d+(?:,\d+)*)?$/'],
            'subcategory' => 'nullable|integer|exists:subcategories,id',
            'child_category' => 'nullable|integer|exists:child_categories,id',
            'sort' => 'nullable|in:price_asc,price_desc',
            'min_price' => 'nullable|numeric|min:0|max:99999999',
            'max_price' => 'nullable|numeric|min:0|max:99999999',
            'brands' => 'sometimes|array',
            'brands.*' => 'integer|exists:catalog_attributes,id',
            'colors' => 'sometimes|array',
            'colors.*' => 'integer|exists:catalog_attributes,id',
            'sizes' => 'sometimes|array',
            'sizes.*' => 'integer|exists:catalog_attributes,id',
        ]);
        $subcategoryId = $filters['subcategory'] ?? null;
        $childCategoryId = $filters['child_category'] ?? null;
        if ($category && $subcategoryId && ! $category->subcategories()->whereKey($subcategoryId)->exists()) {
            abort(404);
        }
        if ($childCategoryId) {
            $childQuery = ChildCategory::whereKey($childCategoryId)
                ->when($category, fn ($query) => $query->whereHas('subcategory', fn ($query) => $query->when($category, fn ($query) => $query->where('category_id', $category->id))));
            if ($subcategoryId) {
                $childQuery->where('subcategory_id', $subcategoryId);
            }
            abort_unless($childQuery->exists(), 404);
        }

        $categoryProductIds = Product::where('status', true)
            ->when($category, fn ($query) => $query->where('category_id', $category->id))
            ->select('id');
        $filterOptions = [
            'brands' => CatalogAttribute::where('type', 'brands')->where('is_active', true)
                ->whereIn('id', Product::whereIn('id', $categoryProductIds)->whereNotNull('brand_id')->select('brand_id'))
                ->orderBy('name')->get(['id', 'name']),
            'colors' => CatalogAttribute::where('type', 'colors')->where('is_active', true)
                ->whereIn('id', ProductVariant::whereIn('product_id', $categoryProductIds)->whereNotNull('color_id')->select('color_id'))
                ->orderBy('name')->get(['id', 'name', 'color']),
            'sizes' => CatalogAttribute::where('type', 'sizes')->where('is_active', true)
                ->whereIn('id', ProductVariant::whereIn('product_id', $categoryProductIds)->whereNotNull('size_id')->select('size_id'))
                ->orderBy('name')->get(['id', 'name']),
        ];
        $priceRange = Product::where('status', true)
            ->when($category, fn ($query) => $query->where('category_id', $category->id))
            ->selectRaw('MIN(CASE WHEN sale_price > 0 THEN sale_price ELSE regular_price END) as minimum')
            ->selectRaw('MAX(CASE WHEN sale_price > 0 THEN sale_price ELSE regular_price END) as maximum')
            ->first();
        $applyPriceFilter = function ($query, string $operator, float $amount): void {
            $query->where(function ($query) use ($operator, $amount) {
                $query->where(function ($query) use ($operator, $amount) {
                    $query->whereNotNull('sale_price')
                        ->where('sale_price', '>', 0)
                        ->where('sale_price', $operator, $amount);
                })->orWhere(function ($query) use ($operator, $amount) {
                    $query->where(function ($query) {
                        $query->whereNull('sale_price')->orWhere('sale_price', '<=', 0);
                    })->where('regular_price', $operator, $amount);
                });
            });
        };

        $productsQuery = $this->productsQuery()
            ->when($request->boolean('wishlist'), fn ($query) => $query->whereIn('id', array_filter(explode(',', $filters['wish_ids'] ?? ''))))
            ->when($filters['q'] ?? '', fn ($query, $search) => $query->where(fn ($query) => $query->where('name', 'like', '%'.$search.'%')->orWhereHas('category', fn ($category) => $category->where('name', 'like', '%'.$search.'%'))))
            ->when($category, fn ($query) => $query->where('category_id', $category->id))
            ->when($subcategoryId, fn ($query) => $query->where('subcategory_id', $subcategoryId))
            ->when($childCategoryId, fn ($query) => $query->where('child_category_id', $childCategoryId))
            ->when($filters['brands'] ?? [], fn ($query, $brands) => $query->whereIn('brand_id', $brands))
            ->when($filters['colors'] ?? [], fn ($query, $colors) => $query->whereHas('variants', fn ($variants) => $variants->whereIn('color_id', $colors)))
            ->when($filters['sizes'] ?? [], fn ($query, $sizes) => $query->whereHas('variants', fn ($variants) => $variants->whereIn('size_id', $sizes)))
            ->when(isset($filters['min_price']), fn ($query) => $applyPriceFilter($query, '>=', (float) $filters['min_price']))
            ->when(isset($filters['max_price']), fn ($query) => $applyPriceFilter($query, '<=', (float) $filters['max_price']));

        if (($filters['sort'] ?? null) === 'price_asc') {
            $productsQuery->orderByRaw('CASE WHEN sale_price > 0 THEN sale_price ELSE regular_price END ASC');
        } elseif (($filters['sort'] ?? null) === 'price_desc') {
            $productsQuery->orderByRaw('CASE WHEN sale_price > 0 THEN sale_price ELSE regular_price END DESC');
        } else {
            $productsQuery->latest('id');
        }

        $products = $productsQuery
            ->paginate(24)
            ->withQueryString()
            ->through(fn (Product $product) => $this->productData($product));

        return Inertia::render('Storefront/Category', [
            'category' => $category ?? ['id' => null, 'name' => 'All products', 'slug' => null, 'image' => null, 'subcategories' => []],
            'allCategories' => $allCategories,
            'products' => $products,
            'selectedSubcategory' => $subcategoryId,
            'selectedChildCategory' => $childCategoryId,
            'filters' => [
                'sort' => $filters['sort'] ?? '',
                'min_price' => $filters['min_price'] ?? '',
                'max_price' => $filters['max_price'] ?? '',
                'brands' => array_map('strval', $filters['brands'] ?? []),
                'colors' => array_map('strval', $filters['colors'] ?? []),
                'sizes' => array_map('strval', $filters['sizes'] ?? []),
            ],
            'filterOptions' => $filterOptions,
            'priceRange' => [
                'minimum' => (float) ($priceRange->minimum ?? 0),
                'maximum' => (float) ($priceRange->maximum ?? 0),
            ],
        ]);
    }

    public function newArrivals()
    {
        Inertia::setRootView('storefront');
        return Inertia::render('Storefront/NewArrivals', [
            'products' => $this->productsQuery()->where('new_arrival', true)
                ->latest('updated_at')->latest('id')->paginate(24)
                ->through(fn (Product $product) => $this->productData($product)),
        ]);
    }

    public function reviews()
    {
        Inertia::setRootView('storefront');
        return Inertia::render('Storefront/Reviews', [
            'reviews' => \App\Models\CustomerReview::where('status', true)
                ->latest('id')->get(['id', 'name', 'image', 'star', 'description']),
        ]);
    }

    public function product(Product $product)
    {
        abort_unless($product->status, 404);
        Inertia::setRootView('storefront');
        $product->load([
            'category:id,name,slug',
            'subcategory:id,name,slug',
            'childCategory:id,name',
            'brand:id,name',
            'images:id,product_id,path',
            'variants.color:id,name,color',
            'variants.size:id,name',
        ]);
        $relatedProducts = $this->productsQuery()
            ->whereKeyNot($product->id)
            ->when($product->child_category_id, fn ($query, $id) => $query->where('child_category_id', $id), function ($query) use ($product) {
                if ($product->subcategory_id) {
                    $query->where('subcategory_id', $product->subcategory_id);
                } elseif ($product->category_id) {
                    $query->where('category_id', $product->category_id);
                }
            })
            ->latest('id')
            ->take(8)
            ->get()
            ->map(fn (Product $related) => $this->productData($related));

        return Inertia::render('Storefront/ProductDetails', [
            'product' => $this->productData($product),
            'relatedProducts' => $relatedProducts,
        ]);
    }
}
