<?php

namespace App\Http\Controllers;

use App\Models\CatalogAttribute;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class CatalogAttributeController extends Controller
{
    private const TYPES = [
        'brands' => ['title' => 'Brands', 'singular' => 'brand'],
        'colors' => ['title' => 'Colors', 'singular' => 'color'],
        'sizes' => ['title' => 'Sizes', 'singular' => 'size'],
    ];

    public function index(Request $request, string $type)
    {
        abort_unless(isset(self::TYPES[$type]), 404);
        $this->authorizeAccess($request, $type);

        return Inertia::render('ProductAttributes', [
            'type' => $type,
            'title' => self::TYPES[$type]['title'],
            'records' => CatalogAttribute::where('type', $type)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request, string $type)
    {
        abort_unless(isset(self::TYPES[$type]), 404);
        $this->authorizeAccess($request, $type);

        $attribute = $this->validated($request, $type);
        CatalogAttribute::create(['type' => $type, ...$attribute]);

        return back()->with('success', self::TYPES[$type]['title'].' entry created successfully.');
    }

    public function update(Request $request, string $type, CatalogAttribute $attribute)
    {
        abort_unless(isset(self::TYPES[$type]) && $attribute->type === $type, 404);
        $this->authorizeAccess($request, $type);

        $attribute->update($this->validated($request, $type, $attribute));

        return back()->with('success', self::TYPES[$type]['title'].' entry updated successfully.');
    }

    public function destroy(Request $request, string $type, CatalogAttribute $attribute)
    {
        abort_unless(isset(self::TYPES[$type]) && $attribute->type === $type, 404);
        $this->authorizeAccess($request, $type);

        $attribute->delete();

        return back()->with('success', self::TYPES[$type]['title'].' entry deleted successfully.');
    }

    private function validated(Request $request, string $type, ?CatalogAttribute $attribute = null): array
    {
        $rules = [
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('catalog_attributes', 'name')
                    ->where(fn ($query) => $query->where('type', $type))
                    ->ignore($attribute?->id),
            ],
            'is_active' => ['required', 'boolean'],
        ];

        if ($type === 'colors') {
            $rules['color'] = ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'];
        }

        return $request->validate($rules);
    }

    private function authorizeAccess(Request $request, string $type): void
    {
        abort_unless($request->user()->hasPermission($type.'.manage'), 403);
    }
}
