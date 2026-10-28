@php
    $navCategories = \App\Models\Category::with('subcategories.children')->where('is_active', true)->where('show_in_header', true)->orderBy('serial')->orderBy('id')->get();
    $sidebarCategories = \App\Models\Category::with('subcategories.children')->where('is_active', true)->where('show_on_sidebar', true)->orderBy('serial')->orderBy('id')->get();
    $makeMenu = function ($categories) {
        return $categories->map(function ($category) {
            $url = url('/categories/'.$category->slug);
            $items = [];
            foreach ($category->subcategories->where('is_active', true) as $sub) {
                $items[] = [$sub->name, 'fa-solid fa-angle-right', $url.'?subcategory='.$sub->id];
                foreach ($sub->children->where('is_active', true) as $child) {
                    $items[] = ['↳ '.$child->name, 'fa-solid fa-angle-right', $url.'?subcategory='.$sub->id.'&child_category='.$child->id];
                }
            }
            return ['title' => $category->name, 'icon' => 'fa-solid fa-layer-group', 'url' => $url, 'items' => $items];
        })->values();
    };
    $desktopMenu = $makeMenu($navCategories);
    $moreMenu = collect();
    $sidebarMenu = $makeMenu($sidebarCategories);
@endphp
<script>window.farboMenu = @json($desktopMenu); window.farboMoreMenu = @json($moreMenu); window.farboSidebarMenu = @json($sidebarMenu);</script>

