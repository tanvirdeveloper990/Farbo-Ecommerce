<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ChildCategory;
use App\Models\Product;
use App\Models\Subcategory;
use App\Services\MediaLibrary;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class CategoryController extends Controller
{
    private function authorizeAccess(Request $r): void
    {
        abort_unless($r->user()->hasPermission('categories.manage'), 403);
    }

    private function deleteImage(?string $path, MediaLibrary $library): void
    {
        if ($path) {
            $library->deleteIfUnused($path);
        }
    }

    public function index(Request $r)
    {
        $this->authorizeAccess($r);

        $filters = $r->validate([
            'search' => 'nullable|string|max:150',
            'type' => 'nullable|in:category,subcategory,child',
        ]);
        $items = $this->categoryRows()
            ->when($filters['type'] ?? null, fn (Collection $items, string $type) => $items->where('type', $type))
            ->when($filters['search'] ?? null, fn (Collection $items, string $search) => $items->filter(
                fn (array $item) => str_contains(mb_strtolower($item['name']), mb_strtolower($search)) ||
                    str_contains(mb_strtolower($item['slug']), mb_strtolower($search)) ||
                    str_contains(mb_strtolower($item['parent'] ?? ''), mb_strtolower($search)),
            ))
            ->values();
        $perPage = 50;
        $page = LengthAwarePaginator::resolveCurrentPage();
        $paginatedItems = new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => $r->query()],
        );
        $query = Category::with('subcategories.children')
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where('name', 'like', '%'.$search.'%'))
            ->orderBy('serial')
            ->orderBy('id');
        $records = $query->paginate(50)->withQueryString();
        $offset = ($records->currentPage() - 1) * $perPage;

        return Inertia::render('Categories', [
            'records' => $records,
            'items' => $paginatedItems,
            'filters' => $filters,
            'previousId' => empty($filters['search']) && $offset > 0 ? Category::orderBy('serial')->orderBy('id')->skip($offset - 1)->value('id') : null,
            'nextId' => empty($filters['search']) && $records->hasMorePages() ? Category::orderBy('serial')->orderBy('id')->skip($offset + $perPage)->value('id') : null,
        ]);
    }

    public function form(Request $r, ?Category $category = null)
    {
        $this->authorizeAccess($r);

        return Inertia::render('CategoryForm', $this->formProps(
            $category,
            $category ? 'category' : $r->query('type', 'category'),
        ));
    }

    public function formRecord(Request $r, string $type, int $id)
    {
        $this->authorizeAccess($r);
        $record = $this->findRecord($type, $id);

        return Inertia::render('CategoryForm', $this->formProps($record, $type));
    }

    public function storeRecord(Request $r, MediaLibrary $library)
    {
        $this->authorizeAccess($r);
        $data = $r->validate(['type' => 'required|in:category,subcategory,child']);

        return $this->persistRecord($r, $library, $data['type']);
    }

    public function updateRecord(Request $r, string $type, int $id, MediaLibrary $library)
    {
        $this->authorizeAccess($r);

        return $this->persistRecord($r, $library, $type, $this->findRecord($type, $id));
    }

    public function destroyRecord(Request $r, string $type, int $id, MediaLibrary $library)
    {
        $this->authorizeAccess($r);
        $record = $this->findRecord($type, $id);
        $images = collect([$record->image]);
        if ($type === 'category') {
            $record->load('subcategories.children');
            $images = $images
                ->concat($record->subcategories->pluck('image'))
                ->concat($record->subcategories->flatMap(fn (Subcategory $sub) => $sub->children->pluck('image')));
        } elseif ($type === 'subcategory') {
            $record->load('children');
            $images = $images->concat($record->children->pluck('image'));
        }
        $parentColumn = match ($type) {
            'subcategory' => 'category_id',
            'child' => 'subcategory_id',
            default => null,
        };
        $parentId = $parentColumn ? $record->{$parentColumn} : null;
        $record->delete();
        foreach ($images->filter()->unique() as $image) {
            $this->deleteImage($image, $library);
        }
        if ($parentColumn) {
            $model = $type === 'subcategory' ? Subcategory::class : ChildCategory::class;
            $model::where($parentColumn, $parentId)->orderBy('serial')->orderBy('id')
                ->get()->each(fn ($sibling, $index) => $sibling->update(['serial' => $index + 1]));
        } else {
            Category::orderBy('serial')->orderBy('id')
                ->get()->each(fn ($sibling, $index) => $sibling->update(['serial' => $index + 1]));
        }

        return redirect('/admin/categories')->with('success', 'Category deleted successfully.');
    }

    private function categoryRows(): Collection
    {
        $rows = collect();
        foreach (Category::with('subcategories.children')->orderBy('serial')->orderBy('id')->get() as $category) {
            $rows->push($this->rowData($category, 'category', null, [$category->serial, 0, 0]));
            foreach ($category->subcategories as $subcategory) {
                $rows->push($this->rowData($subcategory, 'subcategory', $category->name, [$category->serial, $subcategory->serial, 0]));
                foreach ($subcategory->children as $child) {
                    $rows->push($this->rowData($child, 'child', $subcategory->name, [$category->serial, $subcategory->serial, $child->serial]));
                }
            }
        }

        return $rows->sortBy(fn (array $row) => $row['_order'])->map(function (array $row) {
            unset($row['_order']);

            return $row;
        })->values();
    }

    private function rowData(Category|Subcategory|ChildCategory $record, string $type, ?string $parent, array $order): array
    {
        return [
            'id' => $record->id,
            'type' => $type,
            'name' => $record->name,
            'slug' => $record->slug,
            'image' => $record->image,
            'is_active' => (bool) $record->is_active,
            'parent' => $parent,
            '_order' => sprintf('%010d-%010d-%010d', ...$order),
        ];
    }

    private function formProps(Category|Subcategory|ChildCategory|null $record, string $type): array
    {
        abort_unless(in_array($type, ['category', 'subcategory', 'child'], true), 404);

        return [
            'record' => $record ? [
                'id' => $record->id,
                'type' => $type,
                'name' => $record->name,
                'slug' => $record->slug,
                'image' => $record->image,
                'is_active' => (bool) $record->is_active,
                ...($type === 'category' ? [
                    'show_in_header' => (bool) $record->show_in_header,
                    'show_on_home' => (bool) $record->show_on_home,
                    'show_on_sidebar' => (bool) $record->show_on_sidebar,
                    'is_popular' => (bool) $record->is_popular,
                ] : []),
                'parent_id' => match ($type) {
                    'subcategory' => $record->category_id,
                    'child' => $record->subcategory_id,
                    default => null,
                },
            ] : null,
            'type' => $type,
            'categories' => Category::orderBy('serial')->orderBy('id')->get(['id', 'name']),
            'subcategories' => Subcategory::with('category:id,name')->orderBy('serial')->orderBy('id')->get()
                ->map(fn (Subcategory $subcategory) => [
                    'id' => $subcategory->id,
                    'name' => $subcategory->name,
                    'category_id' => $subcategory->category_id,
                    'category_name' => $subcategory->category->name,
                ]),
        ];
    }

    private function findRecord(string $type, int $id): Category|Subcategory|ChildCategory
    {
        $model = match ($type) {
            'category' => Category::class,
            'subcategory' => Subcategory::class,
            'child' => ChildCategory::class,
            default => abort(404),
        };

        return $model::findOrFail($id);
    }

    private function persistRecord(Request $r, MediaLibrary $library, string $type, Category|Subcategory|ChildCategory|null $record = null)
    {
        $parentRequired = $type !== 'category';
        $data = $r->validate([
            'name' => 'required|string|max:150',
            'parent_id' => [$parentRequired ? 'required' : 'nullable', 'integer'],
            'image_path' => 'nullable|string|max:2048',
            'remove_image' => 'sometimes|boolean',
            'is_active' => 'required|boolean',
            'show_in_header' => 'sometimes|boolean',
            'show_on_home' => 'sometimes|boolean',
            'show_on_sidebar' => 'sometimes|boolean',
            'is_popular' => 'sometimes|boolean',
        ]);
        $data['name'] = trim($data['name']);

        if ($type === 'subcategory') {
            Category::findOrFail($data['parent_id']);
        } elseif ($type === 'child') {
            Subcategory::findOrFail($data['parent_id']);
        }

        $parentColumn = match ($type) {
            'subcategory' => 'category_id',
            'child' => 'subcategory_id',
            default => null,
        };
        $oldParentId = $record && $parentColumn ? $record->{$parentColumn} : null;
        $newParentId = $parentColumn ? (int) $data['parent_id'] : null;
        $oldImage = $record?->image;
        $newImage = null;
        if ($r->filled('image_path')) {
            $newImage = $library->selectedImage($r->input('image_path'));
        }

        $model = match ($type) {
            'category' => Category::class,
            'subcategory' => Subcategory::class,
            'child' => ChildCategory::class,
        };
        $record ??= new $model;
        $baseSlug = Str::slug($data['name']) ?: 'category';
        $slug = $baseSlug;
        $suffix = 2;
        while (true) {
            $slugQuery = $model::where('slug', $slug);
            if ($record->exists) {
                $slugQuery->whereKeyNot($record->getKey());
            }
            if (! $slugQuery->exists()) {
                break;
            }
            $slug = $baseSlug.'-'.$suffix++;
        }

        DB::transaction(function () use ($record, $data, $type, $parentColumn, $newParentId, $oldParentId, $newImage, $slug) {
            if ($parentColumn) {
                $record->{$parentColumn} = $newParentId;
                if (! $record->exists) {
                    $record->serial = (int) (DB::table($record->getTable())->where($parentColumn, $newParentId)->max('serial') ?? 0) + 1;
                } elseif ((int) $oldParentId !== $newParentId) {
                    $record->serial = (int) (DB::table($record->getTable())->where($parentColumn, $newParentId)->max('serial') ?? 0) + 1;
                }
            } elseif (! $record->exists) {
                $record->serial = (int) (Category::max('serial') ?? 0) + 1;
            }
            if ($type === 'category') {
                $record->is_popular = $data['is_popular'] ?? $record->is_popular ?? false;
            }
            $record->name = $data['name'];
            $record->slug = $slug;
            $record->is_active = $data['is_active'];
            if ($type === 'category') {
                foreach (['show_in_header', 'show_on_home', 'show_on_sidebar'] as $option) {
                    $record->{$option} = $data[$option] ?? $record->{$option} ?? true;
                }
            }
            if ($newImage) {
                $record->image = $newImage;
            } elseif (($data['remove_image'] ?? false) && $record->exists) {
                $record->image = null;
            }
            $record->save();

            if ($parentColumn && $oldParentId && $oldParentId !== $newParentId) {
                if ($type === 'subcategory') {
                    Product::where('subcategory_id', $record->id)->update(['category_id' => $newParentId]);
                } else {
                    $parent = Subcategory::findOrFail($newParentId);
                    Product::where('child_category_id', $record->id)->update([
                        'subcategory_id' => $parent->id,
                        'category_id' => $parent->category_id,
                    ]);
                }
            }
        });

        if ($oldParentId && $oldParentId !== $newParentId) {
            $siblingModel = $type === 'subcategory' ? Subcategory::class : ChildCategory::class;
            $siblingModel::where($parentColumn, $oldParentId)->orderBy('serial')->orderBy('id')
                ->get()->each(fn ($sibling, $index) => $sibling->update(['serial' => $index + 1]));
        }
        if ($oldImage && $oldImage !== $record->image) {
            $this->deleteImage($oldImage, $library);
        }

        return redirect('/admin/categories')->with('success', ucfirst($type).' saved successfully.');
    }

    public function save(Request $r, MediaLibrary $library, ?Category $category = null)
    {
        $this->authorizeAccess($r);
        if (is_string($r->input('subcategories'))) {
            $r->merge(['subcategories' => json_decode($r->input('subcategories'), true)]);
        }
        foreach (['show_in_header' => true, 'show_on_home' => true, 'is_popular' => false] as $key => $default) {
            if (! $r->exists($key)) {
                $r->merge([$key => $category?->getAttribute($key) ?? $default]);
            }
        }
        $nested = $r->input('subcategories');
        if (is_array($nested)) {
            array_walk_recursive($nested, function (&$value, $key) {
                if ($key === 'name' && is_string($value)) {
                    $value = trim($value);
                }
            });
            $r->merge(['subcategories' => $nested]);
        }
        $data = $r->validate([
            'name' => 'required|string|max:150', 'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240', 'image_path' => 'nullable|string|max:2048', 'remove_image' => 'sometimes|boolean',
            'show_in_header' => 'boolean', 'show_on_home' => 'boolean', 'is_popular' => 'boolean',
            'subcategories' => 'present|array|max:100', 'subcategories.*.id' => 'nullable|integer|distinct', 'subcategories.*.name' => 'required|string|max:150',
            'subcategories.*.children' => 'present|array|max:100', 'subcategories.*.children.*.id' => 'nullable|integer|distinct', 'subcategories.*.children.*.name' => 'required|string|max:150',
        ]);
        $category ??= new Category;
        // Verify ownership before accepting nested IDs; never move another category's data.
        foreach ($data['subcategories'] as $i => $sub) {
            $record = isset($sub['id']) ? $category->subcategories()->find($sub['id']) : null;
            if (isset($sub['id']) && ! $record) {
                throw ValidationException::withMessages(["subcategories.$i.name" => 'This subcategory does not belong to this category. Reload and try again.']);
            }
            foreach ($sub['children'] as $j => $child) {
                if (isset($child['id']) && (! $record || ! $record->children()->whereKey($child['id'])->exists())) {
                    throw ValidationException::withMessages(["subcategories.$i.children.$j.name" => 'This child does not belong to this subcategory.']);
                }
            }
        }
        $old = $category->image;
        $new = null;
        $newlyUploaded = false;
        if ($r->hasFile('image')) {
            $asset = $library->store($r->file('image'), $r->user()->id);
            $new = '/storage/'.$asset->path;
            $newlyUploaded = true;
        } elseif ($r->filled('image_path')) {
            $new = $library->selectedImage($r->input('image_path'));
        }
        try {
            DB::transaction(function () use ($category, $data, $r, $new) {
                $ordered = Category::orderBy('id')->lockForUpdate()->get();
                $category->name = $data['name'];
                $category->slug = $this->uniqueSlug($data['name'], $category);
                $category->show_in_header = $data['show_in_header'];
                $category->show_on_home = $data['show_on_home'];
                $category->is_popular = $data['is_popular'];
                if (! $category->exists) {
                    $category->serial = ($ordered->max('serial') ?? 0) + 1;
                }
                if ($new) {
                    $category->image = $new;
                } elseif ($r->boolean('remove_image')) {
                    $category->image = null;
                }
                unset($data['image_path'], $data['image']);
                $category->save();
                $keep = [];
                foreach ($data['subcategories'] as $i => $sub) {
                    $record = isset($sub['id']) ? $category->subcategories()->findOrFail($sub['id']) : $category->subcategories()->make();
                    $record->fill(['name' => $sub['name'], 'serial' => $i + 1])->save();
                    $keep[] = $record->id;
                    $childKeep = [];
                    foreach ($sub['children'] as $j => $child) {
                        $item = isset($child['id']) ? $record->children()->findOrFail($child['id']) : $record->children()->make();
                        $item->fill(['name' => $child['name'], 'serial' => $j + 1])->save();
                        $childKeep[] = $item->id;
                    }
                    $record->children()->whereNotIn('id', $childKeep)->delete();
                }
                $category->subcategories()->whereNotIn('id', $keep)->delete();
            });
        } catch (\Throwable $e) {
            if ($newlyUploaded) {
                $this->deleteImage($new, $library);
            }
            throw $e;
        }
        if ($old !== $category->image) {
            $this->deleteImage($old, $library);
        }

        return redirect('/admin/categories')->with('success', 'Category and all subcategories saved successfully.');
    }

    private function uniqueSlug(string $name, Category $category): string
    {
        $base = Str::slug($name) ?: 'category';
        $slug = $base;
        $suffix = 2;

        while (Category::where('slug', $slug)->whereKeyNot($category->getKey())->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    public function destroy(Request $r, Category $category, MediaLibrary $library)
    {
        $this->authorizeAccess($r);
        $image = $category->image;
        DB::transaction(function () use ($category) {
            Category::orderBy('id')->lockForUpdate()->get();
            $category->delete();
            Category::orderBy('serial')->orderBy('id')->get()->each(fn ($row, $i) => $row->update(['serial' => $i + 1]));
        });
        $this->deleteImage($image, $library);

        return redirect('/admin/categories')->with('success', 'Category and its subcategories deleted.');
    }

    public function reorder(Request $r)
    {
        $this->authorizeAccess($r);
        $data = $r->validate(['id' => 'required|integer|exists:categories,id', 'target_id' => 'required|integer|different:id|exists:categories,id', 'position' => 'required|in:before,after']);
        DB::transaction(function () use ($data) {
            $rows = Category::orderBy('id')->lockForUpdate()->get()->sortBy([['serial', 'asc'], ['id', 'asc']]);
            $ids = $rows->pluck('id')->reject(fn ($id) => $id == $data['id'])->values()->all();
            $index = array_search((int) $data['target_id'], $ids, true);
            abort_if($index === false, 409, 'The list changed. Reload and try again.');
            array_splice($ids, $index + ($data['position'] === 'after' ? 1 : 0), 0, [(int) $data['id']]);
            foreach ($ids as $i => $id) {
                Category::whereKey($id)->update(['serial' => $i + 1]);
            }
        });

        return back()->with('success', 'Category order updated.');
    }

    public function moveHierarchy(Request $r)
    {
        $this->authorizeAccess($r);
        $data = $r->validate([
            'type' => 'required|in:subcategory,child',
            'id' => 'required|integer',
            'parent_id' => 'required|integer',
            'target_id' => 'nullable|integer',
            'position' => 'nullable|in:before,after',
        ]);

        DB::transaction(function () use ($data) {
            $model = $data['type'] === 'subcategory' ? Subcategory::class : ChildCategory::class;
            $parentColumn = $data['type'] === 'subcategory' ? 'category_id' : 'subcategory_id';
            $parentModel = $data['type'] === 'subcategory' ? Category::class : Subcategory::class;

            $item = $model::query()->lockForUpdate()->findOrFail($data['id']);
            $parent = $parentModel::query()->lockForUpdate()->findOrFail($data['parent_id']);
            $oldParentId = $item->{$parentColumn};
            $targetId = $data['target_id'] ?? null;

            if ($targetId !== null) {
                if ((int) $targetId === (int) $item->id) {
                    throw ValidationException::withMessages(['target_id' => 'Choose a different drop target.']);
                }

                $target = $model::query()
                    ->where($parentColumn, $data['parent_id'])
                    ->lockForUpdate()
                    ->find($targetId);
                if (! $target) {
                    throw ValidationException::withMessages(['target_id' => 'The drop target is not inside this parent.']);
                }
            }

            $item->{$parentColumn} = $data['parent_id'];
            $item->save();
            if ($data['type'] === 'subcategory') {
                Product::where('subcategory_id', $item->id)->update([
                    'category_id' => $parent->id,
                ]);
            } else {
                Product::where('child_category_id', $item->id)->update([
                    'category_id' => $parent->category_id,
                    'subcategory_id' => $parent->id,
                ]);
            }

            $siblings = $model::query()
                ->where($parentColumn, $data['parent_id'])
                ->whereKeyNot($item->id)
                ->orderBy('serial')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();
            $ids = $siblings->pluck('id')->all();

            if ($targetId === null) {
                $ids[] = $item->id;
            } else {
                $index = array_search((int) $targetId, array_map('intval', $ids), true);
                array_splice($ids, $index + (($data['position'] ?? 'after') === 'after' ? 1 : 0), 0, [$item->id]);
            }

            foreach ($ids as $index => $id) {
                $model::whereKey($id)->update(['serial' => $index + 1]);
            }

            if ((int) $oldParentId !== (int) $data['parent_id']) {
                $model::query()
                    ->where($parentColumn, $oldParentId)
                    ->orderBy('serial')
                    ->orderBy('id')
                    ->get()
                    ->each(fn ($sibling, $index) => $sibling->update(['serial' => $index + 1]));
            }
        });

        return back()->with('success', 'Category hierarchy updated.');
    }
}
