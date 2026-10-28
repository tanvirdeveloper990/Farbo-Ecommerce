@extends('farbo.layout')
@section('title', 'Popular Categories | FARBO')
@section('content')
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

          @foreach($popularCategories as $category) <a href="{{ url('/categories/'.$category->slug) }}"
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
          </a> @endforeach @if($popularCategories->isEmpty())<p class="col-span-5 text-center text-gray-500">No popular categories available.</p>@endif </div>

    </div></section>
@endsection
