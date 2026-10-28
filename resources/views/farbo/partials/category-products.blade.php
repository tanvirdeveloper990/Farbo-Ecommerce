<section class="product-slider category-product-section py-10 sm:py-14 bg-white" data-category-id="{{ $homeCategory->id }}">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-center justify-between gap-4 pb-4 mb-6 sm:mb-8 border-b border-gray-200 relative">
            <div>
                <h2 class="text-xl sm:text-2xl lg:text-3xl font-extrabold text-brand-navy uppercase tracking-wide">{{ $homeCategory->name }}</h2>
                <span class="absolute -bottom-px left-0 h-[3px] w-24 sm:w-32 bg-brand-red rounded-full"></span>
            </div>
            <a href="{{ url('/categories/'.$homeCategory->slug) }}" class="shrink-0 inline-flex items-center gap-2 h-9 sm:h-10 px-4 sm:px-5 rounded-lg bg-brand-navy text-white text-xs sm:text-sm font-semibold hover:bg-brand-red transition">View All <i class="fa-solid fa-arrow-right text-[11px]"></i></a>
        </div>
        <div class="relative">
            <div class="ps-track flex gap-3 cursor-grab select-none sm:gap-4 overflow-x-auto snap-x snap-mandatory scroll-smooth pt-2 pb-4">
                @forelse($sectionProducts[$homeCategory->id] ?? [] as $item)
                    @include('farbo.partials.product-card')
                @empty
                    <p class="py-6 text-sm text-gray-500">No products available in this category yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</section>
