

  <!-- ===================== 1. TOP BAR ===================== -->
  <div class="farbo-topbar bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 py-2 flex items-center justify-between gap-3">
      <p class="text-xs sm:text-sm font-bold text-brand-navy whitespace-nowrap">
        {{ $farboSettings['topbar_text'] ?? 'Everything You Need,' }} <span class="text-brand-red">{{ $farboSettings['topbar_highlight'] ?? 'One Place' }}</span>
      </p>
      <div class="flex items-center gap-3 sm:gap-5 text-xs sm:text-sm text-gray-600">
        <a href="tel:{{ $farboSettings['phone_one'] ?? '' }}" class="flex items-center gap-2 hover:text-brand-red transition">
          <i class="fa-solid fa-phone text-brand-red"></i>
          <span class="hidden sm:inline">{{ $farboSettings['phone_one'] ?? '' }}</span>
        </a>
        <span class="hidden sm:block h-4 w-px bg-gray-300"></span>
        <a href="mailto:{{ $farboSettings['email_one'] ?? '' }}" class="flex items-center gap-2 hover:text-brand-red transition">
          <i class="fa-solid fa-envelope text-brand-red"></i>
          <span class="hidden sm:inline">{{ $farboSettings['email_one'] ?? '' }}</span>
        </a>
      </div>
    </div>
  </div>

  <!-- ===================== STICKY HEADER + NAV ===================== -->
  <div id="stickyHeader" class="sticky top-0 z-30 bg-white transition-shadow duration-300">

    <!-- ===== 2. MAIN HEADER ===== -->
    <header class="max-w-7xl mx-auto px-4 py-3 lg:py-4 flex items-center justify-between gap-4">
      <!-- Left: hamburger (mobile) + logo -->
      <div class="flex items-center gap-3">
        <button id="openMenu"
          class="lg:hidden w-10 h-10 grid place-items-center rounded-lg bg-brand-light text-brand-navy hover:bg-brand-navy hover:text-white transition"
          aria-label="Open menu">
          <i class="fa-solid fa-bars text-lg"></i>
        </button>
        <a id="farboHeaderLogo" href="{{ route('home') }}" class="block shrink-0 lg:relative lg:h-14 lg:w-[220px]">
          <img src="{{ $farboSettings['header_logo'] ?? asset('farbo/assets/img/logo/logo.jpeg') }}" alt="FARBO Logo" class="h-10 sm:h-14 lg:absolute lg:left-0 lg:top-1/2 lg:-translate-y-1/2 lg:h-[80px] w-auto object-contain" />
        </a>
      </div>

      <!-- Center: search (desktop) -->
      <form class="farbo-live-search hidden lg:flex flex-1 max-w-xl mx-2" action="{{ url('/categories') }}" method="get">
        <div class="relative flex-1">
          <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
          <input type="search" name="q" placeholder="Search for products..."
            class="w-full h-12 pl-11 pr-4 border border-gray-300 border-r-0 rounded-l-lg text-sm focus:outline-none focus:border-brand-navy focus:ring-2 focus:ring-brand-navy/10 transition" />
        </div>
        <button
          class="h-12 px-6 bg-brand-navy hover:bg-brand-red text-white text-sm font-semibold rounded-r-lg flex items-center gap-2 transition">
          <i class="fa-solid fa-magnifying-glass"></i> Search
        </button>
      </form>

      <!-- Right: icons -->
      <div class="flex items-center gap-1 sm:gap-3 lg:gap-5">
        <!-- Mobile search toggle -->
        <button id="toggleSearch"
          class="lg:hidden w-10 h-10 grid place-items-center rounded-lg text-brand-navy hover: transition"
          aria-label="Search">
          <i id="searchIcon" class="fa-solid fa-magnifying-glass text-lg transition-transform duration-300"></i>
        </button>

        <!-- Wishlist -->
        <a id="farboHeaderWishlist" href="{{ route('home') }}#products"
          class="relative flex flex-col items-center text-brand-navy hover:text-brand-red transition group px-1">
          <span class="relative">
            <i class="fa-regular fa-heart text-xl lg:text-2xl group-hover:scale-110 transition"></i>
            <span
              class="absolute -top-2 -right-2.5 min-w-[18px] h-[18px] px-1 grid place-items-center rounded-full bg-brand-red text-white text-[10px] font-bold">0</span>
          </span>
          <span class="hidden lg:block text-xs font-medium mt-1">Wishlist</span>
        </a>

        <!-- Cart -->
        <button id="openCart"
          class="relative flex flex-col items-center text-brand-navy hover:text-brand-red transition group px-1"
          aria-label="Cart">
          <span class="relative">
            <i class="fa-solid fa-cart-shopping text-xl lg:text-2xl group-hover:scale-110 transition"></i>
            <span id="cartBadge"
              class="absolute -top-2 -right-2.5 min-w-[18px] h-[18px] px-1 grid place-items-center rounded-full bg-brand-red text-white text-[10px] font-bold">0</span>
          </span>
          <span class="hidden lg:block text-xs font-medium mt-1">Cart</span>
        </button>

        <!-- Account -->
        <a id="farboHeaderAccount" href="{{ auth()->check() ? (auth()->user()->is_admin ? url('/admin/profile') : url('/my-account')) : $farboAccountUrl }}" class="flex flex-col items-center text-brand-navy hover:text-brand-red transition group px-1">
          @if(auth()->check())
          @if(auth()->user()->avatar ?? auth()->user()->profile_photo_path)
          <img class="farbo-user-avatar" src="{{ auth()->user()->avatar ?? auth()->user()->profile_photo_path }}" alt="{{ auth()->user()->name }}">
          @else
          <span class="farbo-user-avatar farbo-user-initials">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
          @endif
          <span class="farbo-user-name">{{ auth()->user()->name }}</span>
          @else
          <i class="fa-regular fa-user text-xl lg:text-2xl group-hover:scale-110 transition"></i>
          <span class="hidden lg:block text-xs font-medium mt-1">Account</span>
          @endif
        </a>
      </div>
    </header>

    <!-- Mobile search panel (slides down) -->
    <div id="mobileSearch" class="lg:hidden max-h-0 overflow-hidden transition-all duration-300 bg-white">
      <form class="farbo-live-search px-4 pb-3 flex" action="{{ url('/categories') }}" method="get">
        <input id="mobileSearchInput" type="search" name="q" placeholder="Search for products..."
          class="flex-1 h-11 px-4 border border-gray-300 border-r-0 rounded-l-lg text-sm focus:outline-none focus:border-brand-navy" />
        <button class="h-11 px-4 bg-brand-navy text-white rounded-r-lg"><i
            class="fa-solid fa-magnifying-glass"></i></button>
      </form>
    </div>

    <!-- ===== 3. NAVBAR (desktop) ===== -->
    <nav class="hidden lg:block bg-brand-navy">
      <ul id="desktopNav" class="max-w-7xl mx-auto px-4 flex items-stretch"></ul>
    </nav>
  </div>

  <!-- ===================== OVERLAY ===================== -->
  <div id="overlay"
    class="fixed inset-0 bg-black/50 backdrop-blur-[2px] z-40 opacity-0 invisible transition-all duration-300"></div>

  <!-- ===================== MOBILE LEFT SIDEBAR ===================== -->
  <aside id="mobileMenu"
    class="fixed top-0 left-0 h-full w-[85%] max-w-sm bg-white z-50 -translate-x-full transition-transform duration-300 flex flex-col shadow-2xl">
    <div class="flex items-center justify-between px-5 py-4 border-b">
      <img src="{{ $farboSettings['header_logo'] ?? asset('farbo/assets/img/logo/logo.jpeg') }}" alt="FARBO" class="h-9 w-auto object-contain" />
      <button
        class="close-drawer w-9 h-9 grid place-items-center rounded-full bg-brand-light text-brand-navy hover:bg-brand-red hover:text-white transition"
        aria-label="Close">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <div class="flex-1 overflow-y-auto thin-scroll">
      <p class="px-5 pt-4 pb-2 text-[11px] font-bold uppercase tracking-wider text-gray-400">Categories</p>
      <div id="mobileNav"></div>
      <p class="px-5 pt-5 pb-2 text-[11px] font-bold uppercase tracking-wider text-gray-400">My Account</p>
      <a id="farboSidebarAccount" href="{{ auth()->check() ? (auth()->user()->is_admin ? url('/admin/profile') : url('/my-account')) : route('login') }}" class="flex items-center gap-3 px-5 py-3 text-sm font-medium hover:bg-brand-light">
        @if(auth()->check())
        @if(auth()->user()->avatar ?? auth()->user()->profile_photo_path)
        <img class="farbo-user-avatar" src="{{ auth()->user()->avatar ?? auth()->user()->profile_photo_path }}" alt="{{ auth()->user()->name }}">
        @else
        <span class="farbo-user-avatar farbo-user-initials">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
        @endif
        <span class="farbo-user-name">{{ auth()->user()->name }}</span>
        @else
        <i class="fa-regular fa-user w-5 text-brand-red"></i><span>Login / Register</span>
        @endif
      </a>
      <a id="farboSidebarWishlist" href="{{ route('home') }}#products" class="flex items-center gap-3 px-5 py-3 text-sm font-medium hover:bg-brand-light"><i
          class="fa-regular fa-heart w-5 text-brand-red"></i> Wishlist</a>
      <a href="{{ route('contact.page') }}" class="flex items-center gap-3 px-5 py-3 text-sm font-medium hover:bg-brand-light"><i
          class="fa-solid fa-headset w-5 text-brand-red"></i> Contact Us</a>
    </div>
    <div class="bg-brand-navy text-white px-5 py-4 text-sm space-y-2">
      <a href="tel:{{ $farboSettings['phone_one'] ?? '' }}" class="flex items-center gap-3"><i class="fa-solid fa-phone text-brand-red"></i> {{ $farboSettings['phone_one'] ?? '' }}</a>
      <a href="mailto:{{ $farboSettings['email_one'] ?? '' }}" class="flex items-center gap-3"><i
          class="fa-solid fa-envelope text-brand-red"></i> {{ $farboSettings['email_one'] ?? '' }}</a>
    </div>
  </aside>

  <!-- ===================== CART RIGHT SIDEBAR ===================== -->
  <aside id="cartDrawer"
    class="fixed top-0 right-0 h-full w-full max-w-md bg-white z-50 translate-x-full transition-transform duration-300 flex flex-col shadow-2xl">
    <div class="flex items-center justify-between px-5 py-4 bg-brand-navy text-white">
      <h3 class="font-bold text-lg flex items-center gap-2"><i class="fa-solid fa-cart-shopping"></i> Shopping Cart
        <span id="cartCountTitle" class="text-sm font-medium text-white/70">(0)</span>
      </h3>
      <button
        class="close-drawer w-9 h-9 grid place-items-center rounded-full bg-white/10 hover:bg-brand-red transition"
        aria-label="Close">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <div id="cartItems" class="flex-1 overflow-y-auto thin-scroll p-5 space-y-4"></div>
    <div id="cartFooter" class="border-t p-5 space-y-3 bg-gray-50">
      <div class="flex justify-between text-sm text-gray-600"><span>Subtotal</span><span id="cartSubtotal"
          class="font-semibold text-gray-800">৳0</span></div>
      <div class="flex justify-between text-sm text-gray-600"><span>Shipping</span><span
          class="text-green-600 font-medium">Calculated at checkout</span></div>
      <div class="grid grid-cols-2 gap-3 pt-2">
        <a href="{{ route('checkout.show') }}"
          class="h-11 grid place-items-center rounded-lg border-2 border-brand-navy text-brand-navy font-semibold text-sm hover:bg-brand-navy hover:text-white transition">View
          Cart</a>
        <a href="{{ route('checkout.show') }}"
          class="h-11 grid place-items-center rounded-lg bg-brand-red text-white font-semibold text-sm hover:bg-brand-red2 transition">Checkout</a>
      </div>
    </div>
  </aside>



