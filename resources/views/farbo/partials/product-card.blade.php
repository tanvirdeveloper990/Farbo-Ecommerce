@php
$image = $item->featured_image_one ?: $item->images->first()?->path ?: asset('farbo/assets/img/logo/logo.jpeg');
$price = $item->sale_price > 0 ? $item->sale_price : $item->regular_price;
@endphp
<div
            class="ps-card group snap-start flex flex-col bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-xl hover:border-brand-navy/30 hover:-translate-y-1 transition-all duration-300 overflow-hidden">
            <div class="relative aspect-square bg-brand-light overflow-hidden">
              <a href="{{ url('/product/'.$item->id) }}">
                <img
                  src="{{ $image }}"
                  alt="{{ $item->name }}" loading="lazy"
                  class="absolute inset-0 w-full h-full object-cover object-center group-hover:scale-110 transition-transform duration-500" />
                <button
                  class="absolute top-2.5 right-2.5 w-8 h-8 grid place-items-center rounded-full bg-white text-brand-navy shadow-md opacity-0 group-hover:opacity-100 hover:bg-brand-red hover:text-white transition"
                  type="button" data-wish-product="{{ $item->id }}" aria-label="Add to wishlist"><i class="fa-regular fa-heart text-xs"></i></button>
              </a>
            </div>
            <div class="flex-1 flex flex-col p-3 sm:p-4">
              <h3
                class="line-clamp-2 h-10 sm:h-11 text-[13px] sm:text-[15px] font-semibold text-gray-800 leading-5 sm:leading-[22px] group-hover:text-brand-navy">{{ $item->name }}</h3>
              @if($item->hasPrice())<p class="mt-2 text-base sm:text-lg font-extrabold text-brand-navy">৳{{ number_format($price, 2) }}</p>@endif
              <a href="{{ url('/product/'.$item->id) }}"
                class="mt-3 h-9 sm:h-10 grid place-items-center rounded-lg bg-brand-navy text-white text-xs sm:text-sm font-semibold hover:bg-brand-red transition">View
                Details</a>
            </div>
          </div>
