@extends('farbo.layout')
@section('title', 'Heavy Duty Alternator | FARBO')
@section('content')
    <!-- single  paje  -->
    <section class="py-6 sm:py-10 bg-brand-light">
        <div class="max-w-7xl mx-auto px-4">

            <!-- Breadcrumb -->
            <nav class="mb-4 sm:mb-6 text-xs sm:text-sm text-gray-500" aria-label="Breadcrumb">
                <ol class="flex flex-wrap items-center gap-x-2 gap-y-1">
                    <li><a href="{{ route('home') }}" class="hover:text-brand-red transition">Home</a></li>
                    <li><i class="fa-solid fa-chevron-right text-[9px] text-gray-400"></i></li>
                    <li><a href="{{ route('product.static') }}" class="hover:text-brand-red transition">Generator Parts</a></li>
                    <li><i class="fa-solid fa-chevron-right text-[9px] text-gray-400"></i></li>
                    <li><a href="{{ route('product.static') }}" class="hover:text-brand-red transition">Alternator</a></li>
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
                        class="pd-thumbs flex sm:flex-col gap-3 overflow-x-auto sm:overflow-visible sm:w-20 lg:w-24 shrink-0">
                        <button
                            class="pd-thumb shrink-0 w-16 h-16 sm:w-20 sm:h-20 lg:w-24 lg:h-24 rounded-xl border-2 border-brand-navy bg-white overflow-hidden transition"
                            data-src="https://images.unsplash.com/photo-1595787142842-7404bc60470d?auto=format&fit=crop&w=900&h=900&q=80"
                            aria-label="Image 1">
                            <img src="https://images.unsplash.com/photo-1595787142842-7404bc60470d?auto=format&fit=crop&w=200&h=200&q=70"
                                alt="" class="w-full h-full object-cover" />
                        </button>
                        <button
                            class="pd-thumb shrink-0 w-16 h-16 sm:w-20 sm:h-20 lg:w-24 lg:h-24 rounded-xl border-2 border-gray-200 bg-white overflow-hidden hover:border-brand-navy/50 transition"
                            data-src="https://images.unsplash.com/photo-1537462715879-360eeb61a0ad?auto=format&fit=crop&w=900&h=900&q=80"
                            aria-label="Image 2">
                            <img src="https://images.unsplash.com/photo-1537462715879-360eeb61a0ad?auto=format&fit=crop&w=200&h=200&q=70"
                                alt="" class="w-full h-full object-cover" />
                        </button>
                        <button
                            class="pd-thumb shrink-0 w-16 h-16 sm:w-20 sm:h-20 lg:w-24 lg:h-24 rounded-xl border-2 border-gray-200 bg-white overflow-hidden hover:border-brand-navy/50 transition"
                            data-src="https://images.unsplash.com/photo-1486262715619-67b85e0b08d3?auto=format&fit=crop&w=900&h=900&q=80"
                            aria-label="Image 3">
                            <img src="https://images.unsplash.com/photo-1486262715619-67b85e0b08d3?auto=format&fit=crop&w=200&h=200&q=70"
                                alt="" class="w-full h-full object-cover" />
                        </button>
                        <button
                            class="pd-thumb shrink-0 w-16 h-16 sm:w-20 sm:h-20 lg:w-24 lg:h-24 rounded-xl border-2 border-gray-200 bg-white overflow-hidden hover:border-brand-navy/50 transition"
                            data-src="https://images.unsplash.com/photo-1544692414-636611c6ae73?auto=format&fit=crop&w=900&h=900&q=80"
                            aria-label="Image 4">
                            <img src="https://images.unsplash.com/photo-1544692414-636611c6ae73?auto=format&fit=crop&w=200&h=200&q=70"
                                alt="" class="w-full h-full object-cover" />
                        </button>
                    </div>

                    <!-- Main image -->
                    <div id="pdMain"
                        class="relative flex-1 aspect-square rounded-2xl border border-gray-200 bg-white overflow-hidden cursor-zoom-in">
                        <img id="pdMainImg"
                            src="https://images.unsplash.com/photo-1595787142842-7404bc60470d?auto=format&fit=crop&w=900&h=900&q=80"
                            alt="Heavy Duty Alternator" class="pd-zoom-img w-full h-full object-cover"
                            onerror="this.src='https://placehold.co/900x900/FFFFFF/0B2A5B?text=Heavy+Duty+Alternator'" />
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

                    <h1 class="mt-3 text-2xl sm:text-3xl lg:text-4xl font-extrabold text-brand-navy leading-tight">Heavy
                        Duty Alternator</h1>

                    <p class="mt-3 text-2xl sm:text-3xl font-extrabold text-brand-navy">
                        <span id="pdPrice">$ 320.00</span>
                    </p>

                    <!-- Options -->
                    <div class="mt-6">
                        <p class="text-sm font-semibold text-brand-navy mb-2.5">Choose Product Option</p>
                        <div id="pdOptions" class="grid grid-cols-3 gap-2.5 sm:gap-3 max-w-md">
                            <button
                                class="pd-opt h-11 rounded-lg border-2 text-sm font-semibold transition bg-brand-navy border-brand-navy text-white"
                                data-price="320.00">Original</button>
                            <button
                                class="pd-opt h-11 rounded-lg border-2 text-sm font-semibold transition bg-blue-50/60 border-blue-200 text-brand-navy hover:border-brand-navy"
                                data-price="210.00">China</button>
                            <button
                                class="pd-opt h-11 rounded-lg border-2 text-sm font-semibold transition bg-blue-50/60 border-blue-200 text-brand-navy hover:border-brand-navy"
                                data-price="150.00">Local</button>
                        </div>
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
                            In Stock
                        </span>
                    </div>

                    <!-- Action buttons -->
                    <div class="mt-6 grid grid-cols-2 gap-3 max-w-xl">
                        <a href="{{ route('product.static') }}"
                            class="group h-12 sm:h-[52px] flex items-center justify-center gap-2.5 rounded-lg bg-brand-red text-white text-sm sm:text-base font-bold shadow-md shadow-brand-red/25 hover:bg-brand-red2 hover:-translate-y-0.5 transition">
                            <i class="fa-solid fa-bolt group-hover:scale-110 transition"></i> Order Now
                        </a>
                        <a href="{{ route('contact.page') }}" id="pdOrderLC"
                            class="group h-12 sm:h-[52px] flex items-center justify-center gap-2.5 rounded-lg bg-brand-navy text-white text-sm sm:text-base font-bold shadow-md shadow-brand-navy/25 hover:bg-brand-navy3 hover:-translate-y-0.5 transition">
                            <i class="fa-solid fa-file-invoice-dollar group-hover:scale-110 transition"></i> <span>Order
                                by LC</span>
                        </a>
                        <a href="https://wa.me/8801927123456?text=Hi%2C%20I%20am%20interested%20in%20Heavy%20Duty%20Alternator"
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
                <div id="pd-desc" class="pd-panel p-5 sm:p-8">
                    <p class="text-sm sm:text-[15px] text-gray-600 leading-relaxed sm:leading-7">
                        The FARBO Heavy Duty Alternator (24V 100A) is engineered for reliable and efficient power
                        generation in diesel
                        generator systems. Manufactured with high-grade materials and precision engineering, this
                        alternator ensures
                        stable voltage output, excellent durability, and long-lasting performance even in harsh
                        operating environments.
                        It is widely used in industrial, commercial, and standby generator applications.
                    </p>

                    <h3 class="mt-6 text-base sm:text-lg font-bold text-brand-navy">Key Features</h3>
                    <ul class="mt-3 grid sm:grid-cols-2 gap-2.5 text-sm text-gray-600">
                        <li class="flex items-start gap-2.5"><i
                                class="fa-solid fa-circle-check text-brand-red mt-0.5"></i> 24V / 100A stable output
                        </li>
                        <li class="flex items-start gap-2.5"><i
                                class="fa-solid fa-circle-check text-brand-red mt-0.5"></i> High-grade copper winding
                        </li>
                        <li class="flex items-start gap-2.5"><i
                                class="fa-solid fa-circle-check text-brand-red mt-0.5"></i> Heat-resistant, durable
                            housing</li>
                        <li class="flex items-start gap-2.5"><i
                                class="fa-solid fa-circle-check text-brand-red mt-0.5"></i> For industrial &amp; standby
                            generators</li>
                    </ul>
                </div>

                <!-- Product Video -->
                <div id="pd-video" class="pd-panel hidden p-5 sm:p-8">
                    <!-- Put your YouTube video ID in data-youtube (e.g. data-youtube="dQw4w9WgXcQ") -->
                    <div id="pdVideo" data-youtube=""
                        class="relative max-w-3xl aspect-video rounded-xl overflow-hidden bg-brand-navy cursor-pointer group">
                        <img src="https://images.unsplash.com/photo-1595787142842-7404bc60470d?auto=format&fit=crop&w=1200&h=675&q=70"
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
                    class="ps-track flex gap-3 cursor-grab select-none sm:gap-4 overflow-x-auto snap-x snap-mandatory scroll-smooth pt-2 pb-4">

                    <!-- Product 1 -->
                    <div
                        class="ps-card group snap-start flex flex-col bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-xl hover:border-brand-navy/30 hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                        <div class="relative aspect-square bg-brand-light overflow-hidden">
                            <img src="https://images.unsplash.com/photo-1577762616603-0be18aaca637?auto=format&fit=crop&w=600&h=600&q=80"
                                alt="Generator Fuel Filter" loading="lazy"
                                class="absolute inset-0 w-full h-full object-cover object-center group-hover:scale-110 transition-transform duration-500"
                                onerror="this.src='https://placehold.co/600x600/FFFFFF/0B2A5B?text=Fuel+Filter'" />
                            <button
                                class="absolute top-2.5 right-2.5 w-8 h-8 grid place-items-center rounded-full bg-white text-brand-navy shadow-md opacity-0 group-hover:opacity-100 hover:bg-brand-red hover:text-white transition"
                                aria-label="Add to wishlist"><i class="fa-regular fa-heart text-xs"></i></button>
                        </div>
                        <div class="flex-1 flex flex-col p-3 sm:p-4">
                            <h3
                                class="line-clamp-2 h-10 sm:h-11 text-[13px] sm:text-[15px] font-semibold text-gray-800 leading-5 sm:leading-[22px] group-hover:text-brand-navy">
                                Generator<br>Fuel Filter</h3>
                            <p class="mt-2 text-base sm:text-lg font-extrabold text-brand-navy">$ 24.00</p>
                            <a href="{{ route('product.static') }}"
                                class="mt-3 h-9 sm:h-10 grid place-items-center rounded-lg bg-brand-navy text-white text-xs sm:text-sm font-semibold hover:bg-brand-red transition">View
                                Details</a>
                        </div>
                    </div>

                    <!-- Product 2 -->
                    <div
                        class="ps-card group snap-start flex flex-col bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-xl hover:border-brand-navy/30 hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                        <div class="relative aspect-square bg-brand-light overflow-hidden">
                            <img src="https://images.unsplash.com/photo-1595787142842-7404bc60470d?auto=format&fit=crop&w=600&h=600&q=80"
                                alt="Heavy Duty Alternator" loading="lazy"
                                class="absolute inset-0 w-full h-full object-cover object-center group-hover:scale-110 transition-transform duration-500"
                                onerror="this.src='https://placehold.co/600x600/FFFFFF/0B2A5B?text=Alternator'" />
                            <button
                                class="absolute top-2.5 right-2.5 w-8 h-8 grid place-items-center rounded-full bg-white text-brand-navy shadow-md opacity-0 group-hover:opacity-100 hover:bg-brand-red hover:text-white transition"
                                aria-label="Add to wishlist"><i class="fa-regular fa-heart text-xs"></i></button>
                        </div>
                        <div class="flex-1 flex flex-col p-3 sm:p-4">
                            <h3
                                class="line-clamp-2 h-10 sm:h-11 text-[13px] sm:text-[15px] font-semibold text-gray-800 leading-5 sm:leading-[22px] group-hover:text-brand-navy">
                                Heavy Duty<br>Alternator</h3>
                            <p class="mt-2 text-base sm:text-lg font-extrabold text-brand-navy">$ 320.00</p>
                            <a href="{{ route('product.static') }}"
                                class="mt-3 h-9 sm:h-10 grid place-items-center rounded-lg bg-brand-navy text-white text-xs sm:text-sm font-semibold hover:bg-brand-red transition">View
                                Details</a>
                        </div>
                    </div>

                    <!-- Product 3 -->
                    <div
                        class="ps-card group snap-start flex flex-col bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-xl hover:border-brand-navy/30 hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                        <div class="relative aspect-square bg-brand-light overflow-hidden">
                            <img src="https://images.unsplash.com/photo-1537462715879-360eeb61a0ad?auto=format&fit=crop&w=600&h=600&q=80"
                                alt="Starter Motor Assembly" loading="lazy"
                                class="absolute inset-0 w-full h-full object-cover object-center group-hover:scale-110 transition-transform duration-500"
                                onerror="this.src='https://placehold.co/600x600/FFFFFF/0B2A5B?text=Starter+Motor'" />
                            <button
                                class="absolute top-2.5 right-2.5 w-8 h-8 grid place-items-center rounded-full bg-white text-brand-navy shadow-md opacity-0 group-hover:opacity-100 hover:bg-brand-red hover:text-white transition"
                                aria-label="Add to wishlist"><i class="fa-regular fa-heart text-xs"></i></button>
                        </div>
                        <div class="flex-1 flex flex-col p-3 sm:p-4">
                            <h3
                                class="line-clamp-2 h-10 sm:h-11 text-[13px] sm:text-[15px] font-semibold text-gray-800 leading-5 sm:leading-[22px] group-hover:text-brand-navy">
                                Starter Motor<br>Assembly</h3>
                            <p class="mt-2 text-base sm:text-lg font-extrabold text-brand-navy">$ 185.00</p>
                            <a href="{{ route('product.static') }}"
                                class="mt-3 h-9 sm:h-10 grid place-items-center rounded-lg bg-brand-navy text-white text-xs sm:text-sm font-semibold hover:bg-brand-red transition">View
                                Details</a>
                        </div>
                    </div>

                    <!-- Product 4 -->
                    <div
                        class="ps-card group snap-start flex flex-col bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-xl hover:border-brand-navy/30 hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                        <div class="relative aspect-square bg-brand-light overflow-hidden">
                            <img src="https://images.unsplash.com/photo-1552195634-fdabf904f26e?auto=format&fit=crop&w=600&h=600&q=80"
                                alt="Diesel Engine Oil Filter" loading="lazy"
                                class="absolute inset-0 w-full h-full object-cover object-center group-hover:scale-110 transition-transform duration-500"
                                onerror="this.src='https://placehold.co/600x600/FFFFFF/0B2A5B?text=Oil+Filter'" />
                            <button
                                class="absolute top-2.5 right-2.5 w-8 h-8 grid place-items-center rounded-full bg-white text-brand-navy shadow-md opacity-0 group-hover:opacity-100 hover:bg-brand-red hover:text-white transition"
                                aria-label="Add to wishlist"><i class="fa-regular fa-heart text-xs"></i></button>
                        </div>
                        <div class="flex-1 flex flex-col p-3 sm:p-4">
                            <h3
                                class="line-clamp-2 h-10 sm:h-11 text-[13px] sm:text-[15px] font-semibold text-gray-800 leading-5 sm:leading-[22px] group-hover:text-brand-navy">
                                Diesel Engine<br>Oil Filter</h3>
                            <p class="mt-2 text-base sm:text-lg font-extrabold text-brand-navy">$ 18.00</p>
                            <a href="{{ route('product.static') }}"
                                class="mt-3 h-9 sm:h-10 grid place-items-center rounded-lg bg-brand-navy text-white text-xs sm:text-sm font-semibold hover:bg-brand-red transition">View
                                Details</a>
                        </div>
                    </div>

                    <!-- Product 5 -->
                    <div
                        class="ps-card group snap-start flex flex-col bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-xl hover:border-brand-navy/30 hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                        <div class="relative aspect-square bg-brand-light overflow-hidden">
                            <img src="https://images.unsplash.com/photo-1538944017952-1cf2e60868b1?auto=format&fit=crop&w=600&h=600&q=80"
                                alt="Cooling Water Pump" loading="lazy"
                                class="absolute inset-0 w-full h-full object-cover object-center group-hover:scale-110 transition-transform duration-500"
                                onerror="this.src='https://placehold.co/600x600/FFFFFF/0B2A5B?text=Water+Pump'" />
                            <button
                                class="absolute top-2.5 right-2.5 w-8 h-8 grid place-items-center rounded-full bg-white text-brand-navy shadow-md opacity-0 group-hover:opacity-100 hover:bg-brand-red hover:text-white transition"
                                aria-label="Add to wishlist"><i class="fa-regular fa-heart text-xs"></i></button>
                        </div>
                        <div class="flex-1 flex flex-col p-3 sm:p-4">
                            <h3
                                class="line-clamp-2 h-10 sm:h-11 text-[13px] sm:text-[15px] font-semibold text-gray-800 leading-5 sm:leading-[22px] group-hover:text-brand-navy">
                                Cooling<br>Water Pump</h3>
                            <p class="mt-2 text-base sm:text-lg font-extrabold text-brand-navy">$ 145.00</p>
                            <a href="{{ route('product.static') }}"
                                class="mt-3 h-9 sm:h-10 grid place-items-center rounded-lg bg-brand-navy text-white text-xs sm:text-sm font-semibold hover:bg-brand-red transition">View
                                Details</a>
                        </div>
                    </div>

                    <!-- Product 6 -->
                    <div
                        class="ps-card group snap-start flex flex-col bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-xl hover:border-brand-navy/30 hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                        <div class="relative aspect-square bg-brand-light overflow-hidden">
                            <img src="https://images.unsplash.com/photo-1777118947168-b6e806cb80cf?auto=format&fit=crop&w=600&h=600&q=80"
                                alt="Generator Air Filter" loading="lazy"
                                class="absolute inset-0 w-full h-full object-cover object-center group-hover:scale-110 transition-transform duration-500"
                                onerror="this.src='https://placehold.co/600x600/FFFFFF/0B2A5B?text=Air+Filter'" />
                            <button
                                class="absolute top-2.5 right-2.5 w-8 h-8 grid place-items-center rounded-full bg-white text-brand-navy shadow-md opacity-0 group-hover:opacity-100 hover:bg-brand-red hover:text-white transition"
                                aria-label="Add to wishlist"><i class="fa-regular fa-heart text-xs"></i></button>
                        </div>
                        <div class="flex-1 flex flex-col p-3 sm:p-4">
                            <h3
                                class="line-clamp-2 h-10 sm:h-11 text-[13px] sm:text-[15px] font-semibold text-gray-800 leading-5 sm:leading-[22px] group-hover:text-brand-navy">
                                Generator<br>Air Filter</h3>
                            <p class="mt-2 text-base sm:text-lg font-extrabold text-brand-navy">$ 32.00</p>
                            <a href="{{ route('product.static') }}"
                                class="mt-3 h-9 sm:h-10 grid place-items-center rounded-lg bg-brand-navy text-white text-xs sm:text-sm font-semibold hover:bg-brand-red transition">View
                                Details</a>
                        </div>
                    </div>

                    <!-- Product 7 -->
                    <div
                        class="ps-card group snap-start flex flex-col bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-xl hover:border-brand-navy/30 hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                        <div class="relative aspect-square bg-brand-light overflow-hidden">
                            <img src="https://images.unsplash.com/photo-1523559094051-53bac879eb80?auto=format&fit=crop&w=600&h=600&q=80"
                                alt="Diesel Generator Engine Set" loading="lazy"
                                class="absolute inset-0 w-full h-full object-cover object-center group-hover:scale-110 transition-transform duration-500"
                                onerror="this.src='https://placehold.co/600x600/FFFFFF/0B2A5B?text=AVR'" />
                            <button
                                class="absolute top-2.5 right-2.5 w-8 h-8 grid place-items-center rounded-full bg-white text-brand-navy shadow-md opacity-0 group-hover:opacity-100 hover:bg-brand-red hover:text-white transition"
                                aria-label="Add to wishlist"><i class="fa-regular fa-heart text-xs"></i></button>
                        </div>
                        <div class="flex-1 flex flex-col p-3 sm:p-4">
                            <h3
                                class="line-clamp-2 h-10 sm:h-11 text-[13px] sm:text-[15px] font-semibold text-gray-800 leading-5 sm:leading-[22px] group-hover:text-brand-navy">
                                Diesel Generator<br>Engine Set</h3>
                            <p class="mt-2 text-base sm:text-lg font-extrabold text-brand-navy">$ 1,250.00</p>
                            <a href="{{ route('product.static') }}"
                                class="mt-3 h-9 sm:h-10 grid place-items-center rounded-lg bg-brand-navy text-white text-xs sm:text-sm font-semibold hover:bg-brand-red transition">View
                                Details</a>
                        </div>
                    </div>

                    <!-- Product 8 -->
                    <div
                        class="ps-card group snap-start flex flex-col bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-xl hover:border-brand-navy/30 hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                        <div class="relative aspect-square bg-brand-light overflow-hidden">
                            <img src="https://images.unsplash.com/photo-1544724569-5f546fd6f2b5?auto=format&fit=crop&w=600&h=600&q=80"
                                alt="Generator Control Panel" loading="lazy"
                                class="absolute inset-0 w-full h-full object-cover object-center group-hover:scale-110 transition-transform duration-500"
                                onerror="this.src='https://placehold.co/600x600/FFFFFF/0B2A5B?text=Control+Panel'" />
                            <button
                                class="absolute top-2.5 right-2.5 w-8 h-8 grid place-items-center rounded-full bg-white text-brand-navy shadow-md opacity-0 group-hover:opacity-100 hover:bg-brand-red hover:text-white transition"
                                aria-label="Add to wishlist"><i class="fa-regular fa-heart text-xs"></i></button>
                        </div>
                        <div class="flex-1 flex flex-col p-3 sm:p-4">
                            <h3
                                class="line-clamp-2 h-10 sm:h-11 text-[13px] sm:text-[15px] font-semibold text-gray-800 leading-5 sm:leading-[22px] group-hover:text-brand-navy">
                                Generator<br>Control Panel</h3>
                            <p class="mt-2 text-base sm:text-lg font-extrabold text-brand-navy">$ 260.00</p>
                            <a href="{{ route('product.static') }}"
                                class="mt-3 h-9 sm:h-10 grid place-items-center rounded-lg bg-brand-navy text-white text-xs sm:text-sm font-semibold hover:bg-brand-red transition">View
                                Details</a>
                        </div>
                    </div>

                </div>
            </div>

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
                document.addEventListener('keydown', e => e.key === 'Escape' && closeLb());

                /* Options -> price */
                const opts = document.querySelectorAll('.pd-opt');
                const active = ['bg-brand-navy', 'border-brand-navy', 'text-white'];
                const idle = ['bg-blue-50/60', 'border-blue-200', 'text-brand-navy'];
                opts.forEach(o => o.addEventListener('click', () => {
                    opts.forEach(x => { x.classList.remove(...active); x.classList.add(...idle); });
                    o.classList.remove(...idle); o.classList.add(...active);
                    document.getElementById('pdPrice').textContent = '$ ' + Number(o.dataset.price).toLocaleString('en-US', { minimumFractionDigits: 2 });
                }));

                /* Add to cart feedback */
                const addBtn = document.getElementById('pdAddCart');
                addBtn.addEventListener('click', () => {
                    const label = addBtn.querySelector('span');
                    label.textContent = 'Added!';
                    addBtn.classList.add('bg-green-600');
                    const badge = document.getElementById('cartBadge');           // header cart badge (if present)
                    if (badge) badge.textContent = (parseInt(badge.textContent) || 0) + 1;
                    setTimeout(() => { label.textContent = 'Add to Cart'; addBtn.classList.remove('bg-green-600'); }, 1500);
                });

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
@endpush
