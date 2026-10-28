@extends('farbo.layout')
@section('content')
  <!-- ===================== 4. BANNER SLIDER ===================== -->
  <section class="w-full">
    <div>
      <div id="slider" class="relative w-full overflow-hidden group">
        <div id="sliderTrack" class="flex transition-transform duration-700 ease-in-out"> @foreach($banners as $banner)<div class="w-full shrink-0"><picture><source media="(max-width: 639px)" srcset="{{ $banner->image }}"><img src="{{ $banner->image }}" alt="FARBO banner" class="w-full block aspect-[3/2] md:aspect-[1920/700] object-cover"></picture></div>@endforeach </div>

        <!-- Arrows -->
        <button id="prevSlide"
          class="absolute left-2 sm:left-4 top-1/2 -translate-y-1/2 w-8 h-8 sm:w-11 sm:h-11 grid place-items-center rounded-full bg-white/85 text-brand-navy shadow-lg hover:bg-brand-red hover:text-white transition sm:opacity-0 group-hover:opacity-100"
          aria-label="Previous">
          <i class="fa-solid fa-chevron-left text-xs sm:text-base"></i>
        </button>
        <button id="nextSlide"
          class="absolute right-2 sm:right-4 top-1/2 -translate-y-1/2 w-8 h-8 sm:w-11 sm:h-11 grid place-items-center rounded-full bg-white/85 text-brand-navy shadow-lg hover:bg-brand-red hover:text-white transition sm:opacity-0 group-hover:opacity-100"
          aria-label="Next">
          <i class="fa-solid fa-chevron-right text-xs sm:text-base"></i>
        </button>

        <!-- Dots -->
        
      </div>
    </div>
  </section>

<!-- ===================== POPULAR CATEGORIES (GRID) ===================== -->
  <section class="py-10 sm:py-14 bg-brand-light">
    <div class="max-w-7xl mx-auto px-4">

      <!-- Heading -->
      <div class="text-center mb-7 sm:mb-10">
        <p class="text-xs sm:text-sm font-semibold uppercase tracking-[0.2em] text-brand-red">Shop by Category</p>
        <h2 class="mt-1 text-2xl sm:text-3xl lg:text-4xl font-extrabold text-brand-navy uppercase">Popular Categories
        </h2>
        <div class="mt-3 flex items-center justify-center gap-1.5">
          <span class="h-1 w-1.5 rounded-full bg-brand-navy/40"></span>
          <span class="h-1 w-3 rounded-full bg-brand-navy"></span>
          <span class="h-1 w-12 rounded-full bg-brand-red"></span>
          <span class="h-1 w-3 rounded-full bg-brand-navy"></span>
          <span class="h-1 w-1.5 rounded-full bg-brand-navy/40"></span>
        </div>
      </div>

      <!-- Category grid: mobile 4 | tablet 4 | laptop 5 per row -->
      <div id="popularCategoryGrid" class="grid grid-cols-4 lg:grid-cols-5 gap-2 sm:gap-4 lg:gap-5">

          @foreach($popularCategories->take(8) as $category) <a href="{{ url('/categories/'.$category->slug) }}"
            class="cat-card group flex flex-col rounded-lg sm:rounded-xl overflow-hidden bg-white border sm:border-2 border-brand-navy shadow-sm hover:-translate-y-1.5 hover:shadow-xl hover:border-brand-red transition-all duration-300">
            <div class="relative aspect-square bg-brand-light overflow-hidden">
              <img
                src="{{ $category->image ?: asset('farbo/assets/img/logo/logo.jpeg') }}"
                alt="{{ $category->name }}" loading="lazy"
                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" />
              <span
                class="hidden sm:grid absolute top-2 right-2 w-8 h-8 place-items-center rounded-full bg-brand-red text-white text-xs opacity-0 scale-50 group-hover:opacity-100 group-hover:scale-100 transition-all duration-300"><i
                  class="fa-solid fa-arrow-right"></i></span>
            </div>
            <div
              class="flex-1 min-h-[38px] sm:min-h-[64px] px-1 sm:px-2 py-1.5 sm:py-3 grid place-items-center text-center bg-brand-navy group-hover:bg-brand-red transition-colors duration-300">
              <h3 class="text-white text-[9.5px] sm:text-sm font-semibold leading-tight sm:leading-snug">{{ $category->name }}</h3>
            </div>
          </a> @endforeach </div>
      @if($popularCategories->isNotEmpty())
      <div class="mt-7 text-center"><a href="{{ route('popular-categories') }}" class="inline-flex items-center gap-2 h-11 px-6 rounded-lg bg-brand-navy text-white text-sm font-semibold hover:bg-brand-red transition">View All <i class="fa-solid fa-arrow-right"></i></a></div>
      @endif
    </div></section>

<div id="products"></div>
@foreach($homeCategories->take(2) as $homeCategory)
@include('farbo.partials.category-products')
@endforeach

  <!-- why choose us -->
  <div id="about"></div>

  <section class="py-10 sm:py-14 bg-brand-light">
    <div class="max-w-7xl mx-auto px-4">
      <div
        class="relative overflow-hidden rounded-2xl bg-white shadow-[0_10px_40px_-12px_rgba(11,42,91,0.25)] border border-gray-100 lg:min-h-[500px]">

        <!-- Image (right on desktop, top on mobile) -->
        <div class="relative h-56 sm:h-72 md:h-80 lg:absolute lg:inset-y-0 lg:right-0 lg:h-auto lg:w-[58%]">
          <img
            src="{{ $farboSettings['why_image'] ?? '' }}"
            alt="FARBO global supply – port and containers" loading="lazy"
            class="absolute inset-0 w-full h-full object-cover object-center"
            onerror="this.src='https://placehold.co/1400x1000/0B2A5B/FFFFFF?text=FARBO+Global+Supply'" />
          <!-- soft overlay for depth -->
          <div
            class="absolute inset-0 bg-gradient-to-t from-brand-navy/30 via-transparent to-transparent lg:bg-gradient-to-r lg:from-brand-navy/10">
          </div>
        </div>

        <!-- Content panel -->
        <div
          class="wcu-panel relative z-10 bg-white lg:w-[52%] px-5 py-8 sm:px-10 sm:py-10 lg:pl-12 lg:pr-[9%] lg:py-14">

          <!-- Eyebrow -->
          <div class="flex items-center gap-3">
            <span class="text-xs sm:text-sm font-bold uppercase tracking-[0.18em] text-brand-navy">Why Choose Us</span>
            <span class="h-0.5 w-12 sm:w-16 bg-brand-navy"></span>
          </div>

          <!-- Heading -->
          <h2 class="mt-4 text-2xl sm:text-3xl lg:text-[2.35rem] font-extrabold text-brand-navy leading-tight">
            {{ $farboSettings['why_title'] ?? '' }}
          </h2>

          <!-- Text -->
          <div class="why-summary mt-4 text-sm sm:text-[15px] text-gray-600 leading-relaxed max-w-xl">
            {!! $farboSettings['why_summary'] ?? '' !!}
          </div>

          <!-- Button -->
          <a href="{{ url('/categories') }}"
            class="group mt-8 inline-flex items-center gap-3 h-12 px-7 rounded-lg bg-brand-navy text-white text-sm font-semibold shadow-lg shadow-brand-navy/20 hover:bg-brand-red hover:shadow-brand-red/25 transition">
            Learn More
            <i class="fa-solid fa-arrow-right text-xs group-hover:translate-x-1 transition-transform"></i>
          </a>
        </div>

      </div>
    </div>
  </section>

@foreach($homeCategories->skip(2) as $homeCategory)
@include('farbo.partials.category-products')
@endforeach

@endsection
