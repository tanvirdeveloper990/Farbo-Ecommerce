@if($renderForInertia ?? false)
@yield('content')
@stack('scripts')
@else
<!DOCTYPE html>
<html lang="en">
<head>
  @php($farboSettings = \App\Models\SiteSetting::current())
  @php($farboAccountUrl = auth()->user()?->is_admin ? url('/admin/dashboard') : (auth()->check() ? url('/my-account') : route('login')))
  <link rel="icon" href="{{ $farboSettings['favicon'] ?? asset('farbo/assets/img/logo/logo.jpeg') }}">
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title inertia>@yield('title', 'FARBO – Everything You Need, One Place')</title>

  @unless(View::hasSection('inertia'))
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            brand: {
              navy: '#0B2A5B',
              navy2: '#071E44',
              navy3: '#123A75',
              red: '#E30613',
              red2: '#C10510',
              light: '#F3F6FB'
            }
          },
          fontFamily: { sans: ['Poppins', 'sans-serif'] }
        }
      }
    }
  </script>

  @endunless
  @stack('head')
  <!-- Google Font -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap"
    rel="stylesheet" />
  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />

  <link rel="stylesheet" href="{{ asset('farbo/assets/css/main.css') }}">
</head>
<body class="bg-white text-gray-800 overflow-x-hidden">
@include('farbo.partials.header')
<main>@yield('content')</main>
@include('farbo.partials.footer')


  <!-- ===================== 6. FLOATING CALL & WHATSAPP ===================== -->
  <div class="fixed right-4 bottom-6 sm:bottom-10 z-30 flex flex-col items-end gap-3">
    <!-- WhatsApp -->
    @if(!empty($farboSettings['whatsapp']))<a href="{{ $farboSettings['whatsapp'] ?? '' }}" target="_blank" rel="noopener"
      class="group flex flex-row-reverse items-center h-12 sm:h-14 rounded-full bg-[#25D366] text-white shadow-xl shadow-green-900/30 hover:shadow-2xl transition-all duration-300">
      <span
        class="relative ring-pulse w-12 h-12 sm:w-14 sm:h-14 shrink-0 grid place-items-center rounded-full bg-[#25D366]">
        <i class="fa-brands fa-whatsapp text-2xl sm:text-3xl"></i>
      </span>
      <span
        class="max-w-0 overflow-hidden whitespace-nowrap font-semibold text-sm transition-all duration-500 group-hover:max-w-[180px] group-hover:pl-5">
        Chat on WhatsApp
      </span>
    </a>@endif
    <!-- Call -->
    @if(!empty($farboSettings['phone_one']))<a href="tel:{{ $farboSettings['phone_one'] ?? '' }}"
      class="group flex flex-row-reverse items-center h-12 sm:h-14 rounded-full bg-brand-red text-white shadow-xl shadow-red-900/30 hover:shadow-2xl transition-all duration-300">
      <span
        class="relative ring-pulse w-12 h-12 sm:w-14 sm:h-14 shrink-0 grid place-items-center rounded-full bg-brand-red">
        <i class="fa-solid fa-phone-volume text-lg sm:text-xl"></i>
      </span>
      <span
        class="max-w-0 overflow-hidden whitespace-nowrap font-semibold text-sm transition-all duration-500 group-hover:max-w-[180px] group-hover:pl-5">
        Call: {{ $farboSettings['phone_one'] ?? '' }}
      </span>
    </a>@endif
  </div>


  @include('farbo.partials.menu-data')
<script>window.farboHomeUrl = @json(route('home')); window.farboLogoUrl = @json($farboSettings['header_logo'] ?? asset('farbo/assets/img/logo/logo.jpeg'));</script>
  <script src="{{ asset('farbo/assets/js/main.js') }}"></script>
<script src="{{ asset('farbo/assets/js/cart.js') }}"></script>

@stack('scripts')
</body>
</html>
@endif
