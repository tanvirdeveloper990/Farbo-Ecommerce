@if($popularCategories->isNotEmpty())
<section class="py-10 sm:py-14 bg-brand-light"><div class="max-w-7xl mx-auto px-4">
    <div class="text-center mb-8"><p class="text-sm font-semibold uppercase tracking-widest text-brand-red">Shop by Category</p><h2 class="mt-2 text-2xl sm:text-3xl font-bold text-brand-navy">Popular Categories</h2></div>
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-5 gap-4">
        @foreach($popularCategories as $category)
        <a href="{{ url('/categories/'.$category->slug) }}" class="bg-white border border-gray-200 rounded-2xl p-4 text-center hover:shadow-md hover:border-brand-red transition">
            @if($category->image)<img src="{{ $category->image }}" alt="{{ $category->name }}" class="w-full aspect-square object-contain rounded-lg" loading="lazy">@else<div class="aspect-square grid place-items-center text-4xl text-brand-navy bg-brand-light rounded-lg"><i class="fa-solid fa-layer-group"></i></div>@endif
            <h3 class="mt-3 font-semibold text-sm text-brand-navy">{{ $category->name }}</h3>
        </a>
        @endforeach
    </div>
</div></section>
@endif
