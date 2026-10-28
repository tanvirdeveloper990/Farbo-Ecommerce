@extends('farbo.layout')
@section('title', $product->name.' | FARBO')
@section('content')
@php
    $price = (float)($product->sale_price > 0 ? $product->sale_price : $product->regular_price);
    $gallery = collect([$product->featured_image_one, $product->featured_image_two])->merge($product->images->pluck('path'))->filter()->unique()->values();
    $variants = $product->variants->map(fn ($variant) => ['id' => $variant->id, 'price' => (float)$variant->price, 'stock' => $variant->stock, 'image' => $variant->image_path, 'label' => collect([$product->brand?->name, $variant->color?->name, $variant->size?->name])->filter()->implode(' / ')])->values();
    $selectedVariant = $variants->firstWhere('stock', '>', 0) ?? $variants->first();
    $price = $selectedVariant['price'] ?? $price;
    $availableStock = $selectedVariant['stock'] ?? $product->stock;
    $hasPrice = $product->hasPrice();
    $whatsappUrl = $farboSettings['whatsapp'] ?? '';
    $whatsappUrl = preg_match('#^https?://#i', $whatsappUrl) ? $whatsappUrl : route('contact.page');
    $cartProduct = ['id' => $product->id, 'name' => $product->name, 'category' => $product->category?->name, 'image' => $selectedVariant['image'] ?? $gallery->first(), 'price' => $price, 'stock' => $availableStock, 'key' => $product->id.'-'.($selectedVariant['id'] ?? 'default'), 'variant_id' => $selectedVariant['id'] ?? null, 'variant_label' => $selectedVariant['label'] ?? $product->brand?->name ?? ''];
@endphp
    <!-- single  paje  -->
    <section class="py-6 sm:py-10 bg-brand-light">
        <div class="max-w-7xl mx-auto px-4">

            <!-- Breadcrumb -->
            <nav class="mb-4 sm:mb-6 text-xs sm:text-sm text-gray-500" aria-label="Breadcrumb">
                <ol class="flex flex-wrap items-center gap-x-2 gap-y-1">
                    <li><a href="{{ route('home') }}" class="hover:text-brand-red transition">Home</a></li>
                    <li><i class="fa-solid fa-chevron-right text-[9px] text-gray-400"></i></li>
                    <li><a href="{{ url('/categories/'.$product->category?->slug) }}" class="hover:text-brand-red transition">{{ $product->category?->name }}</a></li>
                    <li><i class="fa-solid fa-chevron-right text-[9px] text-gray-400"></i></li>
                    <li><a href="{{ url('/product/'.$product->id) }}" class="hover:text-brand-red transition">{{ $product->name }}</a></li>
                    <li><i class="fa-solid fa-chevron-right text-[9px] text-gray-400"></i></li>
                    <li class="font-semibold text-brand-navy">Product Details</li>
                </ol>
            </nav>

            <!-- ===== Top: Gallery + Info ===== -->
            <div
                class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 sm:p-6 lg:p-8 grid gap-6 lg:gap-10 lg:grid-cols-2">

                <!-- Gallery -->
                <div class="flex flex-col-reverse sm:flex-row gap-3 sm:gap-4">

                    <!-- Thumbnails (left on desktop, below on mobile) -->
                    <div id="pdThumbs"
                        class="pd-thumbs flex sm:flex-col gap-3 overflow-x-auto sm:overflow-visible sm:w-20 lg:w-24 shrink-0"> @foreach($gallery as $image)<button class="pd-thumb shrink-0 w-16 h-16 sm:w-20 sm:h-20 lg:w-24 lg:h-24 rounded-xl border-2 {{ $loop->first ? 'border-brand-navy' : 'border-gray-200' }} bg-white overflow-hidden transition" data-src="{{ $image }}"><img src="{{ $image }}" alt="{{ $product->name }}" class="w-full h-full object-cover"></button>@endforeach </div>

                    <!-- Main image -->
                    <div id="pdMain"
                        class="relative flex-1 aspect-square rounded-2xl border border-gray-200 bg-white overflow-hidden cursor-zoom-in">
                        <img id="pdMainImg"
                            src="{{ $gallery->first() ?: asset('farbo/assets/img/logo/logo.jpeg') }}"
                            alt="{{ $product->name }}" class="pd-zoom-img w-full h-full object-cover" />
                        <button id="pdZoomBtn"
                            class="absolute bottom-3 right-3 w-10 h-10 grid place-items-center rounded-full bg-white text-brand-navy shadow-lg hover:bg-brand-navy hover:text-white transition"
                            aria-label="View full image">
                            <i class="fa-solid fa-magnifying-glass-plus"></i>
                        </button>
                    </div>
                </div>

                <!-- Info -->
                <div class="flex flex-col">
                    <span
                        class="self-start px-3 py-1 rounded-full bg-blue-50 text-brand-navy text-xs font-bold tracking-wide border border-brand-navy/10">FARBO</span>

                    <h1 class="mt-3 text-2xl sm:text-3xl lg:text-4xl font-extrabold text-brand-navy leading-tight">{{ $product->name }}</h1>

                    @if($hasPrice)
                    <p class="mt-3 text-2xl sm:text-3xl font-extrabold text-brand-navy">
                        <span id="pdPrice">৳{{ number_format($price, 2) }}</span>
                    </p>
                    @endif

                    <!-- Options -->
                    <div class="mt-6">
                        <p class="text-sm font-semibold text-brand-navy mb-2.5">Choose Product Option</p>
                        <div id="pdOptions" class="grid grid-cols-3 gap-2.5 sm:gap-3 max-w-md"> @foreach($options as $option)<a href="{{ url('/product/'.$option->id) }}" class="pd-opt h-11 rounded-lg border-2 text-sm font-semibold transition flex items-center justify-center {{ $option->id === $product->id ? 'bg-brand-navy border-brand-navy text-white' : 'bg-blue-50/60 border-blue-200 text-brand-navy hover:border-brand-navy' }}">{{ $option->brand->name }}</a>@endforeach </div>
                    </div>

                    <!-- Stock -->
                    <div class="mt-5">
                        <span
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm font-bold">
                            <span class="relative flex w-2.5 h-2.5">
                                <span
                                    class="absolute inline-flex w-full h-full rounded-full bg-green-500 opacity-60 animate-ping"></span>
                                <span class="relative inline-flex w-2.5 h-2.5 rounded-full bg-green-600"></span>
                            </span>
                            {{ $availableStock > 0 ? 'In Stock' : 'Out of Stock' }}
                        </span>
                    </div>

                    @if($variants->isNotEmpty())<div class="mt-6"><label for="productVariant" class="block mb-2 text-sm font-semibold text-brand-navy">Select variation</label><select id="productVariant" class="w-full border border-gray-300 rounded-lg px-3 py-3">@foreach($variants as $variant)<option value="{{ $variant['id'] }}" @selected($variant['id'] === $selectedVariant['id']) @disabled($variant['stock'] < 1)>{{ $variant['label'] }}{{ $variant['stock'] < 1 ? ' — Out of stock' : '' }}</option>@endforeach</select></div>@endif @if($hasPrice)<div class="mt-5 flex items-center gap-3"><label for="productQuantity" class="text-sm font-semibold text-brand-navy">Quantity</label><input id="productQuantity" type="number" min="1" max="{{ min(100,max(1,$availableStock)) }}" value="1" class="w-20 border border-gray-200 rounded-lg px-3 py-2"><button id="productAddCart" type="button" class="h-11 px-5 rounded-lg bg-brand-navy text-white text-sm font-semibold"><i class="fa-solid fa-cart-plus mr-2"></i>Add to Cart</button></div><p id="productCartMessage" role="status" class="mt-2 text-sm text-brand-navy"></p>@endif<!-- Action buttons -->
                    <div class="mt-6 grid grid-cols-2 gap-3 max-w-xl">
                        @if($hasPrice)
                        <button type="button" id="productBuyNow" @disabled($availableStock < 1)
                            class="group h-12 sm:h-[52px] flex items-center justify-center gap-2.5 rounded-lg bg-brand-red text-white text-sm sm:text-base font-bold shadow-md shadow-brand-red/25 hover:bg-brand-red2 hover:-translate-y-0.5 transition">
                            <i class="fa-solid fa-bolt group-hover:scale-110 transition"></i> Order Now
                        </button>
                        <a href="{{ route('contact.page') }}" id="pdOrderLC"
                            class="group h-12 sm:h-[52px] flex items-center justify-center gap-2.5 rounded-lg bg-brand-navy text-white text-sm sm:text-base font-bold shadow-md shadow-brand-navy/25 hover:bg-brand-navy3 hover:-translate-y-0.5 transition">
                            <i class="fa-solid fa-file-invoice-dollar group-hover:scale-110 transition"></i> <span>Order
                                by LC</span>
                        </a>
                        @endif
                        <a href="{{ $whatsappUrl }}"
                            target="_blank" rel="noopener"
                            class="group h-12 sm:h-[52px] flex items-center justify-center gap-2.5 rounded-lg bg-[#25D366] text-white text-sm sm:text-base font-bold shadow-md shadow-green-700/20 hover:bg-[#1EBE5A] hover:-translate-y-0.5 transition">
                            <i class="fa-brands fa-whatsapp text-xl group-hover:scale-110 transition"></i> WhatsApp
                        </a>
                        <a href="{{ route('contact.page') }}" target="_blank" rel="noopener"
                            class="group h-12 sm:h-[52px] flex items-center justify-center gap-2.5 rounded-lg bg-[#0084FF] text-white text-sm sm:text-base font-bold shadow-md shadow-blue-700/20 hover:bg-[#0073E0] hover:-translate-y-0.5 transition">
                            <i class="fa-brands fa-facebook-messenger text-xl group-hover:scale-110 transition"></i>
                            Messenger
                        </a>
                    </div>

                    <!-- Small trust row -->
                    <div
                        class="mt-6 pt-5 border-t border-gray-100 grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs sm:text-[13px] text-gray-600">
                        <div class="flex items-center gap-2"><i class="fa-solid fa-shield-halved text-brand-navy"></i>
                            Quality Checked</div>
                        <div class="flex items-center gap-2"><i class="fa-solid fa-truck-fast text-brand-navy"></i> Fast
                            Delivery</div>
                        <div class="flex items-center gap-2"><i class="fa-solid fa-headset text-brand-navy"></i> Expert
                            Support</div>
                    </div>
                </div>
            </div>

            <!-- ===== Tabs: Description / Product Video ===== -->
            <div class="mt-6 sm:mt-8 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="flex border-b border-gray-200 bg-blue-50/40 overflow-x-auto" role="tablist">
                    <button
                        class="pd-tab relative shrink-0 px-5 sm:px-8 py-4 text-sm sm:text-base font-bold text-brand-navy transition"
                        data-tab="desc" role="tab">
                        Description
                        <span
                            class="pd-tab-line absolute left-4 right-4 bottom-0 h-[3px] rounded-full bg-brand-red"></span>
                    </button>
                    <button
                        class="pd-tab relative shrink-0 px-5 sm:px-8 py-4 text-sm sm:text-base font-semibold text-gray-600 hover:text-brand-navy transition flex items-center gap-2"
                        data-tab="video" role="tab">
                        <i class="fa-regular fa-circle-play text-lg"></i> Product Video
                        <span
                            class="pd-tab-line absolute left-4 right-4 bottom-0 h-[3px] rounded-full bg-brand-red hidden"></span>
                    </button>
                </div>

                <!-- Description -->
                <div id="pd-desc" class="pd-panel p-5 sm:p-8"><div class="text-sm sm:text-[15px] text-gray-600 leading-relaxed sm:leading-7">{!! $product->description !!}</div></div>

                <!-- Product Video -->
                <div id="pd-video" class="pd-panel hidden p-5 sm:p-8">
                    <!-- Put your YouTube video ID in data-youtube (e.g. data-youtube="dQw4w9WgXcQ") -->
                    <div id="pdVideo" data-youtube=""
                        class="relative max-w-3xl aspect-video rounded-xl overflow-hidden bg-brand-navy cursor-pointer group">
                        <img src="{{ $farboSettings['product_video_poster'] ?? 'https://images.unsplash.com/photo-1595787142842-7404bc60470d?auto=format&fit=crop&w=1200&h=675&q=70' }}"
                            alt="" class="absolute inset-0 w-full h-full object-cover opacity-60" />
                        <div class="absolute inset-0 flex flex-col items-center justify-center text-white">
                            <span
                                class="w-16 h-16 sm:w-20 sm:h-20 grid place-items-center rounded-full bg-brand-red shadow-xl group-hover:scale-110 transition">
                                <i class="fa-solid fa-play text-2xl sm:text-3xl ml-1"></i>
                            </span>
                            <p id="pdVideoMsg" class="mt-3 text-sm font-semibold">Watch Product Video</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Lightbox (full image) -->
    <div id="pdLightbox" class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/85 p-4">
        <button id="pdLbClose"
            class="absolute top-4 right-4 w-11 h-11 grid place-items-center rounded-full bg-white/10 text-white hover:bg-brand-red transition"
            aria-label="Close"><i class="fa-solid fa-xmark text-xl"></i></button>
        <img id="pdLbImg" src="" alt="" class="max-w-full max-h-[85vh] rounded-xl shadow-2xl object-contain" />
    </div>


    <!--product section  -->

    <section class="product-slider py-10 sm:py-14 bg-white">
        <div class="max-w-7xl mx-auto px-4">

            <!-- Heading -->
            <div class="flex items-center justify-between gap-4 pb-4 mb-6 sm:mb-8 border-b border-gray-200 relative">
                <div>
                    <h2 class="text-xl sm:text-2xl lg:text-3xl font-extrabold text-brand-navy uppercase tracking-wide">
                        Related Product</h2>
                    <span class="absolute -bottom-px left-0 h-[3px] w-24 sm:w-32 bg-brand-red rounded-full"></span>
                </div>
                <a href="{{ route('home') }}#products"
                    class="shrink-0 inline-flex items-center gap-2 h-9 sm:h-10 px-4 sm:px-5 rounded-lg bg-brand-navy text-white text-xs sm:text-sm font-semibold hover:bg-brand-red transition">
                    View All <i class="fa-solid fa-arrow-right text-[11px]"></i>
                </a>
            </div>

            <!-- Slider wrapper -->
            <div class="relative">

                <!-- Track -->
                <div
                    class="ps-track flex gap-3 cursor-grab select-none sm:gap-4 overflow-x-auto snap-x snap-mandatory scroll-smooth pt-2 pb-4"> @foreach($relatedProducts as $item) @include('farbo.partials.product-card') @endforeach </div></div>

            <!-- Dots -->
            
        </div>
    </section>

    <!-- ===================== 5. FOOTER ===================== -->
    
@endsection
@push('scripts')
<script>
            (function () {
                const mainImg = document.getElementById('pdMainImg');
                const main = document.getElementById('pdMain');

                /* Thumbnails */
                const thumbs = document.querySelectorAll('.pd-thumb');
                thumbs.forEach(t => t.addEventListener('click', () => {
                    thumbs.forEach(x => { x.classList.remove('border-brand-navy'); x.classList.add('border-gray-200'); });
                    t.classList.add('border-brand-navy'); t.classList.remove('border-gray-200');
                    mainImg.style.opacity = 0;
                    setTimeout(() => { mainImg.src = t.dataset.src; mainImg.style.opacity = 1; }, 150);
                }));
                mainImg.style.transition = 'opacity .2s, transform .25s';

                /* Hover zoom (desktop) */
                main.addEventListener('mousemove', e => {
                    if (window.innerWidth < 1024) return;
                    const r = main.getBoundingClientRect();
                    mainImg.style.transformOrigin = `${((e.clientX - r.left) / r.width) * 100}% ${((e.clientY - r.top) / r.height) * 100}%`;
                    mainImg.style.transform = 'scale(1.8)';
                });
                main.addEventListener('mouseleave', () => { mainImg.style.transform = 'scale(1)'; });

                /* Lightbox */
                const lb = document.getElementById('pdLightbox');
                const openLb = () => { document.getElementById('pdLbImg').src = mainImg.src; lb.classList.remove('hidden'); lb.classList.add('flex'); };
                const closeLb = () => { lb.classList.add('hidden'); lb.classList.remove('flex'); };
                document.getElementById('pdZoomBtn').addEventListener('click', e => { e.stopPropagation(); openLb(); });
                main.addEventListener('click', () => { if (window.innerWidth < 1024) openLb(); });
                document.getElementById('pdLbClose').onclick = closeLb;
                lb.addEventListener('click', e => { if (e.target === lb) closeLb(); });
                document.addEventListener('keydown', e => e.key === 'Escape' && closeLb(), {signal: window.farboPageSignal});

                /* Tabs */
                const tabs = document.querySelectorAll('.pd-tab');
                tabs.forEach(t => t.addEventListener('click', () => {
                    tabs.forEach(x => {
                        x.classList.remove('font-bold', 'text-brand-navy'); x.classList.add('font-semibold', 'text-gray-600');
                        x.querySelector('.pd-tab-line').classList.add('hidden');
                    });
                    t.classList.add('font-bold', 'text-brand-navy'); t.classList.remove('font-semibold', 'text-gray-600');
                    t.querySelector('.pd-tab-line').classList.remove('hidden');
                    document.querySelectorAll('.pd-panel').forEach(p => p.classList.add('hidden'));
                    document.getElementById('pd-' + t.dataset.tab).classList.remove('hidden');
                }));

                /* Video: click poster -> load YouTube */
                const vid = document.getElementById('pdVideo');
                vid.addEventListener('click', () => {
                    const id = vid.dataset.youtube.trim();
                    if (!id) { document.getElementById('pdVideoMsg').textContent = 'Video coming soon'; return; }
                    vid.innerHTML = `<iframe class="absolute inset-0 w-full h-full" src="https://www.youtube.com/embed/${id}?autoplay=1&rel=0"
        title="Product video" frameborder="0" allow="autoplay; encrypted-media; picture-in-picture" allowfullscreen></iframe>`;
                });
            })();
    </script>
<script>
const currentProduct = @json($cartProduct);
const productHasPrice = @json($hasPrice);
const availableVariants = @json($variants);
document.getElementById('productVariant')?.addEventListener('change', event => {
    const variant = availableVariants.find(variant => variant.id === Number(event.target.value));
    if (!variant) return;
    Object.assign(currentProduct, {price: variant.price, stock: variant.stock, variant_id: variant.id, variant_label: variant.label, key: currentProduct.id + '-' + variant.id});
    if (variant.image) { currentProduct.image = variant.image; document.getElementById('pdMainImg').src = variant.image; }
    if (!productHasPrice) return;
    document.getElementById('pdPrice').textContent = '৳' + variant.price.toLocaleString('en-BD', {minimumFractionDigits:2});
    document.getElementById('productQuantity').max = Math.min(100, variant.stock);
    document.getElementById('productQuantity').value = 1;
    document.getElementById('productCartMessage').textContent = '';
});
document.querySelectorAll('.product-thumbnail').forEach(button => button.addEventListener('click', () => { document.getElementById('pdMainImg').src = button.dataset.image; }));
function addCurrentProduct() {
    const quantity = Number(document.getElementById('productQuantity').value);
    const message = document.getElementById('productCartMessage');
    if (!Number.isInteger(quantity) || quantity < 1 || quantity > Math.min(100, currentProduct.stock)) { message.textContent = 'Choose an available quantity.'; return false; }
    if (!window.farboCart.add(currentProduct, quantity)) { message.textContent = 'The selected quantity is not available.'; return false; }
    message.textContent = 'Added to your cart.';
    return true;
}
document.getElementById('productAddCart')?.addEventListener('click', () => { if (addCurrentProduct()) document.getElementById('openCart').click(); });
document.getElementById('productBuyNow')?.addEventListener('click', () => { if (addCurrentProduct()) window.farboNavigate(@json(route('checkout.show'))); });
</script>
@endpush
