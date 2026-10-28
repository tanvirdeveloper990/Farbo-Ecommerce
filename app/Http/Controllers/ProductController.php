<?php

namespace App\Http\Controllers;

use App\Models\CatalogAttribute;
use App\Models\Category;
use App\Models\Product;
use App\Models\Subcategory;
use App\Services\MediaLibrary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class ProductController extends Controller
{
    private function authorizeAccess(Request $request): void
    {
        abort_unless($request->user()->hasPermission('products.manage'), 403);
    }

    public function index(Request $request)
    {
        $this->authorizeAccess($request);
        $filters = $request->validate([
            'search' => 'nullable|string|max:200',
            'category_id' => 'nullable|integer|exists:categories,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $records = Product::with([
            'category:id,name',
            'subcategory:id,name',
            'childCategory:id,name',
            'brand:id,name',
            'images:id,product_id,path',
        ])
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where('name', 'like', '%'.$search.'%'))
            ->when($filters['category_id'] ?? null, fn ($query, $categoryId) => $query->where('category_id', $categoryId))
            ->when($filters['start_date'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['end_date'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '<=', $date))
            ->latest('id')
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('Products', [
            'records' => $records,
            'categories' => Category::orderBy('serial')->orderBy('id')->get(['id', 'name']),
            'filters' => $filters,
        ]);
    }

    public function form(Request $request, ?Product $product = null)
    {
        $this->authorizeAccess($request);
        $product?->load(['images', 'variants.color', 'variants.size']);
        $brandIds = $product?->brand_id ? [$product->brand_id] : [];
        $colorIds = $product?->variants->pluck('color_id')->filter()->all() ?? [];
        $sizeIds = $product?->variants->pluck('size_id')->filter()->all() ?? [];

        return Inertia::render('ProductForm', [
            'product' => $product,
            'categories' => Category::with('subcategories.children:id,subcategory_id,name')
                ->orderBy('serial')
                ->orderBy('id')
                ->get(['id', 'name']),
            'brands' => $this->attributeOptions('brands', $brandIds, ['id', 'name', 'is_active']),
            'colors' => $this->attributeOptions('colors', $colorIds, ['id', 'name', 'color', 'is_active']),
            'sizes' => $this->attributeOptions('sizes', $sizeIds, ['id', 'name', 'is_active']),
        ]);
    }

    public function save(Request $request, MediaLibrary $library, ?Product $product = null)
    {
        $this->authorizeAccess($request);
        $data = $request->validate([
            'name' => 'required|string|max:200',
            'category_id' => 'required|integer|exists:categories,id',
            'subcategory_id' => 'nullable|integer|exists:subcategories,id',
            'child_category_id' => 'nullable|integer|exists:child_categories,id',
            'brand_id' => ['nullable', 'integer', Rule::exists('catalog_attributes', 'id')->where('type', 'brands')],
            'regular_price' => 'nullable|numeric|min:0|max:9999999999.99',
            'sale_price' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99', Rule::when($request->filled('regular_price'), 'lte:regular_price')],
            'unit' => 'nullable|string|max:40',
            'stock' => 'required|integer|min:0|max:4294967295',
            'description' => 'nullable|string|max:50000',
            'new_arrival' => 'required|boolean',
            'status' => 'required|boolean',
            'featured_image_one' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'featured_image_two' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'featured_image_one_path' => 'nullable|string|max:2048',
            'featured_image_two_path' => 'nullable|string|max:2048',
            'remove_featured_image_one' => 'sometimes|boolean',
            'remove_featured_image_two' => 'sometimes|boolean',
            'product_images' => 'nullable|array|max:10',
            'product_images.*' => 'image|mimes:jpg,jpeg,png,webp|max:10240',
            'product_image_paths' => 'nullable|array|max:10',
            'product_image_paths.*' => 'required|string|max:2048',
            'remove_product_image_ids' => 'nullable|array',
            'remove_product_image_ids.*' => 'integer|distinct',
            'variants' => 'nullable|array|max:100',
            'variants.*.color_id' => ['nullable', 'integer', Rule::exists('catalog_attributes', 'id')->where('type', 'colors')],
            'variants.*.size_id' => ['nullable', 'integer', Rule::exists('catalog_attributes', 'id')->where('type', 'sizes')],
            'variants.*.stock' => 'required|integer|min:0|max:4294967295',
            'variants.*.price' => [Rule::requiredIf($request->filled('regular_price')), 'nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'variants.*.image_path' => 'nullable|string|max:2048',
        ]);
        $data['description'] = $this->sanitizeDescription($data['description'] ?? '');
        $hasPrice = isset($data['regular_price']) && $data['regular_price'] !== '';
        if (! $hasPrice) {
            $data['regular_price'] = null;
            $data['sale_price'] = null;
        }

        if (! empty($data['subcategory_id']) && ! Category::findOrFail($data['category_id'])->subcategories()->whereKey($data['subcategory_id'])->exists()) {
            throw ValidationException::withMessages(['subcategory_id' => 'Choose a subcategory belonging to the selected category.']);
        }

        if (! empty($data['child_category_id']) &&
            (empty($data['subcategory_id']) ||
                ! Subcategory::findOrFail($data['subcategory_id'])->children()->whereKey($data['child_category_id'])->exists())) {
            throw ValidationException::withMessages(['child_category_id' => 'Choose a child category belonging to the selected subcategory.']);
        }

        if (! empty($data['brand_id']) &&
            ! CatalogAttribute::whereKey($data['brand_id'])->where('is_active', true)->exists() &&
            (int) $product?->brand_id !== (int) $data['brand_id']) {
            throw ValidationException::withMessages(['brand_id' => 'Choose an active brand.']);
        }

        $existingVariants = $product?->variants()->get(['color_id', 'size_id']) ?? collect();
        $existingColorIds = $existingVariants->pluck('color_id')->filter()->map(fn ($id) => (int) $id)->all();
        $existingSizeIds = $existingVariants->pluck('size_id')->filter()->map(fn ($id) => (int) $id)->all();
        foreach ($data['variants'] ?? [] as $index => $variant) {
            if (! empty($variant['color_id']) &&
                ! CatalogAttribute::whereKey($variant['color_id'])->where('is_active', true)->exists() &&
                ! in_array((int) $variant['color_id'], $existingColorIds, true)) {
                throw ValidationException::withMessages(["variants.$index.color_id" => 'Choose an active color.']);
            }
            if (! empty($variant['size_id']) &&
                ! CatalogAttribute::whereKey($variant['size_id'])->where('is_active', true)->exists() &&
                ! in_array((int) $variant['size_id'], $existingSizeIds, true)) {
                throw ValidationException::withMessages(["variants.$index.size_id" => 'Choose an active size.']);
            }
        }

        if ($product) {
            $removeIds = $data['remove_product_image_ids'] ?? [];
            $remainingImages = $product->images()->whereNotIn('id', $removeIds)->count();
        } else {
            $remainingImages = 0;
        }
        if ($remainingImages + count($request->file('product_images', []) ?? []) + count($data['product_image_paths'] ?? []) > 10) {
            throw ValidationException::withMessages(['product_images' => 'A product can have no more than 10 gallery images.']);
        }

        $variantCombinations = [];
        foreach ($data['variants'] ?? [] as $index => $variant) {
            if (empty($variant['color_id']) && empty($variant['size_id'])) {
                throw ValidationException::withMessages(["variants.$index.size_id" => 'Choose a color or size for this variant.']);
            }
            $combination = ($variant['color_id'] ?? '').'-'.($variant['size_id'] ?? '');
            if (isset($variantCombinations[$combination])) {
                throw ValidationException::withMessages(["variants.$index.size_id" => 'Each color and size combination must be unique.']);
            }
            $variantCombinations[$combination] = true;
        }

        $galleryToRemove = $product
            ? $product->images()->whereIn('id', $data['remove_product_image_ids'] ?? [])->get()
            : collect();
        if ($product && $galleryToRemove->count() !== count($data['remove_product_image_ids'] ?? [])) {
            throw ValidationException::withMessages(['remove_product_image_ids' => 'One or more selected images do not belong to this product.']);
        }

        $newFiles = [];
        $oldFiles = $product?->variants()->whereNotNull('image_path')->pluck('image_path')->all() ?? [];
        $selectedImages = [];
        $variants = $request->input('variants', []);
        $product ??= new Product;

        try {
            foreach (['featured_image_one_path', 'featured_image_two_path'] as $key) {
                if (! empty($data[$key])) {
                    $selectedImages[$key] = $library->selectedImage($data[$key]);
                }
            }
            foreach ($data['product_image_paths'] ?? [] as $index => $url) {
                $selectedImages['product_image_paths'][$index] = $library->selectedImage($url);
            }
            foreach ($data['variants'] ?? [] as $index => $variant) {
                if (! empty($variant['image_path'])) {
                    $selectedImages['variants'][$index] = $library->selectedImage($variant['image_path']);
                }
            }

            foreach (['featured_image_one', 'featured_image_two'] as $key) {
                $oldPath = $product?->$key;
                if ($request->hasFile($key)) {
                    $asset = $library->store($request->file($key), $request->user()->id);
                    $storedUrl = '/storage/'.$asset->path;
                    $newFiles[] = $storedUrl;
                    $data[$key] = $storedUrl;
                } elseif ($request->boolean('remove_'.$key)) {
                    $data[$key] = null;
                } elseif (isset($selectedImages[$key.'_path'])) {
                    $data[$key] = $selectedImages[$key.'_path'];
                } else {
                    $data[$key] = $oldPath;
                }
                if ($oldPath && $oldPath !== $data[$key]) {
                    $oldFiles[] = $oldPath;
                }
            }

            $galleryPaths = [];
            foreach ($request->file('product_images', []) ?? [] as $file) {
                $asset = $library->store($file, $request->user()->id);
                $url = '/storage/'.$asset->path;
                $galleryPaths[] = $url;
                $newFiles[] = $url;
            }
            $galleryPaths = [...$galleryPaths, ...array_values($selectedImages['product_image_paths'] ?? [])];

            unset(
                $data['remove_featured_image_one'],
                $data['remove_featured_image_two'],
                $data['featured_image_one_path'],
                $data['featured_image_two_path'],
                $data['product_images'],
                $data['product_image_paths'],
                $data['remove_product_image_ids'],
                $data['variants'],
            );

            DB::transaction(function () use ($product, $data, $hasPrice, $galleryToRemove, $galleryPaths, $variants, $selectedImages, &$oldFiles) {
                $product->fill($data)->save();

                foreach ($galleryToRemove as $image) {
                    $oldFiles[] = $image->path;
                    $image->delete();
                }
                foreach ($galleryPaths as $path) {
                    $product->images()->create(['path' => $path]);
                }

                $product->variants()->delete();
                foreach ($variants as $index => $variant) {
                    $product->variants()->create([
                        'color_id' => $variant['color_id'] ?: null,
                        'size_id' => $variant['size_id'] ?: null,
                        'stock' => $variant['stock'],
                        'price' => $hasPrice ? $variant['price'] : null,
                        'image_path' => $selectedImages['variants'][$index] ?? null,
                    ]);
                }
            });
        } catch (\Throwable $exception) {
            $this->deleteUploads($newFiles, $library);
            throw $exception;
        }

        $this->deleteUploads($oldFiles, $library);

        return redirect('/admin/products')->with('success', 'Product saved successfully.');
    }

    public function destroy(Request $request, Product $product, MediaLibrary $library)
    {
        $this->authorizeAccess($request);
        $paths = [
            $product->featured_image_one,
            $product->featured_image_two,
            ...$product->images()->pluck('path')->all(),
            ...$product->variants()->whereNotNull('image_path')->pluck('image_path')->all(),
        ];
        $product->delete();
        $this->deleteUploads($paths, $library);

        return redirect('/admin/products')->with('success', 'Product deleted successfully.');
    }

    private function deleteUploads(array $paths, MediaLibrary $library): void
    {
        foreach (array_unique(array_filter($paths)) as $path) {
            $library->deleteIfUnused($path);
        }
    }

    private function attributeOptions(string $type, array $selectedIds, array $columns)
    {
        return CatalogAttribute::where('type', $type)
            ->where(function ($query) use ($selectedIds) {
                $query->where('is_active', true);
                if ($selectedIds) {
                    $query->orWhereIn('id', $selectedIds);
                }
            })
            ->orderBy('name')
            ->get($columns);
    }

    private function sanitizeDescription(string $description): string
    {
        $config = \HTMLPurifier_Config::createDefault();
        $config->set('Cache.SerializerPath', storage_path('framework/cache'));
        $config->set('HTML.Allowed', 'p[style],br,strong,b,em,i,u,s,strike,span[style],h2[style],h3[style],h4[style],ul,ol,li,blockquote,a[href|title],table,thead,tbody,tr,th,td,hr');
        $config->set('CSS.AllowedProperties', ['text-align', 'color', 'background-color', 'font-size']);
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true, 'tel' => true]);

        return (new \HTMLPurifier($config))->purify($description);
    }
}
