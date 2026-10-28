<section id="products" class="py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-center justify-between mb-6"><h2 class="text-2xl sm:text-3xl font-bold text-brand-navy">Our Products</h2><a href="{{ url('/categories') }}" class="text-brand-red font-semibold text-sm">View All →</a></div>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
            @forelse($products as $item) @include('farbo.partials.product-card') @empty <p>No products available yet.</p> @endforelse
        </div>
    </div>
</section>
@foreach($homeCategories as $homeCategory)
    @php($sectionProducts = \App\Models\Product::with('category', 'brand', 'images')->where('status', true)->where('category_id', $homeCategory->id)->take(8)->get())
    @if($sectionProducts->isNotEmpty())
    <section class="py-10 bg-brand-light"><div class="max-w-7xl mx-auto px-4">
        <div class="flex items-center justify-between mb-6"><h2 class="text-xl sm:text-2xl font-bold text-brand-navy">{{ $homeCategory->name }}</h2><a href="{{ url('/categories/'.$homeCategory->slug) }}" class="text-brand-red text-sm font-semibold">View All →</a></div>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">@foreach($sectionProducts as $item) @include('farbo.partials.product-card') @endforeach</div>
    </div></section>
    @endif
@endforeach
