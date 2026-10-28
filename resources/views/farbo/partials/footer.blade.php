  <!-- ===================== 5. FOOTER ===================== -->
  <footer class="relative text-white overflow-hidden bg-[#0A1E45]">

    <!-- Background image (right side) + navy overlay -->
    <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
      <img id="footerBg"
        src="{{ $farboSettings['footer_background'] ?? 'https://images.unsplash.com/photo-1494412574643-ff11b0a5c1c3?auto=format&fit=crop&w=1600&q=70' }}" alt=""
        class="absolute right-0 top-0 h-full w-full lg:w-[55%] object-cover object-center opacity-30 lg:opacity-40"
        onerror="this.style.display='none'" />
      <div class="absolute inset-0 bg-gradient-to-r from-[#0A1E45] via-[#0A1E45]/95 to-[#0C2655]/70"></div>
      <div class="absolute inset-0 bg-gradient-to-t from-[#081838] via-transparent to-transparent"></div>
    </div>

    <!-- Top red accent line -->
    <div class="relative h-1 bg-gradient-to-r from-brand-red via-brand-red to-transparent"></div>

    <!-- Main footer -->
    <div
      class="relative max-w-7xl mx-auto px-4 pt-12 lg:pt-14 pb-10 grid gap-10 sm:grid-cols-2 lg:grid-cols-12 lg:gap-0">

      <!-- About -->
      <div class="sm:col-span-2 lg:col-span-4 lg:pr-10">
        <a href="{{ route('home') }}" class="inline-block bg-white rounded-xl px-4 py-2.5 shadow-lg">
          <img src="{{ $farboSettings['footer_logo'] ?? asset('farbo/assets/img/logo/logo.jpeg') }}" alt="FARBO" class="h-12 sm:h-14 lg:h-16 w-auto object-contain" />
        </a>
        <p class="mt-6 text-sm leading-relaxed text-white/85 max-w-sm">
          {{ $farboSettings['footer_description'] ?? 'Your trusted partner for industrial supply, generator parts, tools, hardware and global sourcing. We connect quality products with businesses worldwide.' }}
        </p>
        @include('farbo.partials.social-links')
      </div>

      <!-- Quick Links -->
      <div class="lg:col-span-2 lg:border-l lg:border-white/15 lg:pl-8">
        <h4 class="text-lg font-bold">Quick Links</h4>
        <span class="block mt-2 mb-5 h-0.5 w-10 bg-brand-red rounded-full"></span>
        <ul class="space-y-3 text-sm text-white/85">
          <li><a href="{{ route('home') }}" class="inline-flex items-center gap-2.5 hover:text-white hover:translate-x-1 transition"><i
                class="fa-solid fa-chevron-right text-[9px] text-white/70"></i> Home</a></li>
          <li><a href="{{ url('/about-us') }}" class="inline-flex items-center gap-2.5 hover:text-white hover:translate-x-1 transition"><i
                class="fa-solid fa-chevron-right text-[9px] text-white/70"></i> About Us</a></li>
          <li><a href="{{ url('/categories') }}" class="inline-flex items-center gap-2.5 hover:text-white hover:translate-x-1 transition"><i
                class="fa-solid fa-chevron-right text-[9px] text-white/70"></i> Products</a></li>
          <li><a href="{{ route('contact.page') }}" class="inline-flex items-center gap-2.5 hover:text-white hover:translate-x-1 transition"><i
                class="fa-solid fa-chevron-right text-[9px] text-white/70"></i> Contact Us</a></li>
          <li><a href="{{ route('founder-message') }}" class="inline-flex items-center gap-2.5 hover:text-white hover:translate-x-1 transition"><i class="fa-solid fa-chevron-right text-[9px] text-white/70"></i> Founder Message</a></li>
          <li><a href="{{ route('career') }}" class="inline-flex items-center gap-2.5 hover:text-white hover:translate-x-1 transition"><i class="fa-solid fa-chevron-right text-[9px] text-white/70"></i> Career</a></li>
        </ul>
      </div>

      <!-- Our Products -->
      <div class="hidden md:block lg:col-span-3 lg:border-l lg:border-white/15 lg:pl-8" id="footerOurProducts">
        <h4 class="text-lg font-bold">Our Products</h4>
        <span class="block mt-2 mb-5 h-0.5 w-10 bg-brand-red rounded-full"></span>
        @include('farbo.partials.footer-categories')
      </div>

      <!-- Contact Information -->
      <div class="sm:col-span-2 lg:col-span-3 lg:border-l lg:border-white/15 lg:pl-8">
        <h4 class="text-lg font-bold">Contact Information</h4>
        <span class="block mt-2 mb-5 h-0.5 w-10 bg-brand-red rounded-full"></span>
        <ul class="space-y-5 text-sm">
          <li class="group flex items-start gap-3.5">
            <span
              class="w-10 h-10 shrink-0 grid place-items-center rounded-lg bg-[#1D4E9E] shadow-md group-hover:bg-brand-red transition"><i
                class="fa-solid fa-phone"></i></span>
            <div>
              <a href="tel:{{ $farboSettings['phone_two'] ?? '' }}" class="font-semibold hover:text-white/80 transition">{{ $farboSettings['phone_two'] ?? '' }}</a>
              <p class="text-white/70 text-xs mt-0.5">Sun - Thu, 9:00 AM - 6:00 PM</p>
            </div>
          </li>
          <li class="group flex items-start gap-3.5">
            <span
              class="w-10 h-10 shrink-0 grid place-items-center rounded-lg bg-[#1D4E9E] shadow-md group-hover:bg-brand-red transition"><i
                class="fa-solid fa-envelope"></i></span>
            <div>
              <a href="mailto:{{ $farboSettings['email_two'] ?? '' }}" class="font-semibold hover:text-white/80 transition">{{ $farboSettings['email_two'] ?? '' }}</a>
              <p class="text-white/70 text-xs mt-0.5">We reply within 24 hours</p>
            </div>
          </li>
          <li class="group flex items-start gap-3.5">
            <span
              class="w-10 h-10 shrink-0 grid place-items-center rounded-lg bg-[#1D4E9E] shadow-md group-hover:bg-brand-red transition"><i
                class="fa-solid fa-location-dot"></i></span>
            <p class="font-semibold leading-relaxed">{{ $farboSettings['address'] ?? '' }}</p>
          </li>
        </ul>
      </div>
    </div>

    <!-- Bottom bar -->
    <div class="relative border-t border-white/15 bg-[#081838]/60">
      <div
        class="max-w-7xl mx-auto px-4 py-5 flex flex-col lg:flex-row items-center justify-between gap-4 text-sm text-white/85">

        <div class="flex flex-col md:flex-row items-center gap-3 md:gap-0">
          <p class="md:pr-6">© {{ date('Y') }} <span class="font-bold text-white">FARBO</span>. All rights reserved.</p>
          <div
            class="flex flex-wrap items-center justify-center gap-x-4 gap-y-2 md:border-l md:border-white/20 md:pl-6">
            <a href="{{ url('/policies/privacy-policy') }}" class="hover:text-white transition">Privacy Policy</a>
            <span class="h-4 w-px bg-white/30"></span>
            <a href="{{ url('/policies/terms-and-conditions') }}" class="hover:text-white transition">Terms &amp; Conditions</a>
            <span class="h-4 w-px bg-white/30"></span>
            <a href="{{ url('/policies/refund-policy') }}" class="hover:text-white transition">Return &amp; Refund Policy</a>
          </div>
        </div>

        <!-- Payment methods -->
        <div class="flex flex-wrap items-center justify-center gap-2">
          <span
            class="h-8 w-14 grid place-items-center bg-white rounded-md shadow text-[#1A1F71] font-extrabold italic text-sm tracking-tight">VISA</span>
          <span class="h-8 w-14 flex items-center justify-center bg-white rounded-md shadow">
            <span class="w-[18px] h-[18px] rounded-full bg-[#EB001B]"></span><span
              class="w-[18px] h-[18px] rounded-full bg-[#F79E1B] -ml-2 mix-blend-multiply"></span>
          </span>
          <span class="h-8 w-14 grid place-items-center bg-white rounded-md shadow"><i
              class="fa-brands fa-paypal text-lg text-[#003087]"></i></span>
          <span
            class="h-8 w-14 grid place-items-center bg-white rounded-md shadow text-[#E2136E] font-extrabold text-[11px]">bKash</span>
          <span
            class="h-8 w-14 grid place-items-center bg-[#8C3494] rounded-md shadow text-white font-bold text-[11px]">Rocket</span>
          <span
            class="h-8 w-14 grid place-items-center bg-white rounded-md shadow text-[#EC1C24] font-extrabold text-[11px]">Nagad</span>
        </div>
      </div>
    </div>
  </footer>

