<script setup>
import { inject, computed } from "vue";
import { Link, usePage, router } from "@inertiajs/vue3";
import { ChevronDown, Trash2, UserRound } from "@lucide/vue";
import StoreNavigation from "./StoreNavigation.vue";
import { wishlistUrl } from "../wishlist";
const {
    panel,
    openPanel,
    closePanel,
    query,
    submitSearch,
    searchSuggestions,
    searchFocused,
    closeSearchSuggestions,
    showWishlist,
    wishlist,
    cart,
    cartCount,
    subtotal,
    taka,
    removeItem,
    notify,
    scrolled,
} = inject("storefront");
function homeClick(event) {
    if (usePage().url === '/') { event.preventDefault(); closePanel(); window.scrollTo({ top: 0, behavior: 'smooth' }); }
}
let suggestionBlurTimer;
function focusSearch() {
    window.clearTimeout(suggestionBlurTimer);
    searchFocused.value = true;
}
function blurSearch() {
    suggestionBlurTimer = window.setTimeout(closeSearchSuggestions, 180);
}
const settings = computed(
    () => usePage().props.storeSettings || usePage().props.settings || {},
);
const user = computed(() => usePage().props.auth.user);
const categories = computed(() => usePage().props.storeCategories || []);
const accountUrl = computed(() =>
    user.value
        ? user.value.is_admin
            ? "/admin/dashboard"
            : "/my-account"
        : "/login",
);
const accountInitials = computed(() =>
    (user.value?.name || "")
        .trim()
        .split(/\s+/)
        .slice(0, 2)
        .map((part) => part.charAt(0))
        .join("")
        .toUpperCase(),
);
function beginCheckout() {
    if (!cart.value.length) return;
    window.sessionStorage.setItem(
        "storefront-cart",
        JSON.stringify(cart.value),
    );
    router.visit("/checkout");
}
</script>
<template>
    <div class="bg-ink text-white">
        <div
            class="mx-auto flex min-h-9 py-2 max-w-7xl items-center justify-between gap-3 px-4 text-[11px] sm:text-xs"
        >
            <div
                class="min-w-0 flex-1 leading-relaxed"
                aria-label="Store announcement"
            >
                <p>
                    <strong>Welcome to&nbsp;</strong>
                    <span class="font-semibold">{{
                        settings.company_name
                    }}</span
                    ><span v-if="settings.marquee" class="text-white/70">
                        | {{ settings.marquee }}</span
                    >
                </p>
            </div>
            <div class="flex shrink-0 items-center gap-4 sm:gap-6">
                <a
                    :href="'tel:' + settings.phone_one"
                    v-if="settings.phone_one"
                    class="flex items-center gap-1.5 hover:text-maroon-light transition-colors"
                    aria-label="Call us"
                >
                    <svg
                        class="h-3.5 w-3.5"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106a1.125 1.125 0 00-1.173.417l-.97 1.293a1.125 1.125 0 01-1.21.38 12.035 12.035 0 01-7.143-7.143 1.125 1.125 0 01.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"
                        />
                    </svg>
                    <span class="hidden sm:inline">{{
                        settings.phone_one
                    }}</span>
                </a>
                <a
                    :href="'mailto:' + settings.email_one"
                    v-if="settings.email_one"
                    class="flex items-center gap-1.5 hover:text-maroon-light transition-colors"
                    aria-label="Email us"
                >
                    <svg
                        class="h-3.5 w-3.5"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"
                        />
                    </svg>
                    <span class="hidden sm:inline">{{
                        settings.email_one
                    }}</span>
                </a>
            </div>
        </div>
    </div>

    <header
        :class="{ 'shadow-md': scrolled }"
        id="siteHeader"
        class="sticky top-0 z-40 bg-white transition-shadow duration-300"
    >
        <div class="mx-auto max-w-7xl px-4">
            <div
                class="relative flex h-20 items-center justify-between gap-4 lg:h-24"
            >
                <div class="flex items-center gap-3">
                    <button
                        id="menuBtn"
                        @click="openPanel('menu')"
                        :aria-expanded="panel === 'menu'"
                        class="-ml-2 rounded-full p-2 hover:bg-blush focus-visible:outline focus-visible:outline-2 focus-visible:outline-maroon"
                        aria-label="Open menu"
                    >
                        <svg
                            class="h-6 w-6"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.6"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5"
                            />
                        </svg>
                    </button>

                    <Link
                        href="/"
                        @click="homeClick"
                        :prefetch="['mount', 'hover']"
                        cache-for="15s"
                        class="absolute left-1/2 -translate-x-1/2 lg:static lg:translate-x-0"
                    >
                        <img
                            :src="settings.header_logo"
                            v-if="settings.header_logo"
                            :alt="settings.company_name"
                            class="h-16 w-auto max-w-[170px] object-contain sm:h-[70px] sm:max-w-[200px] lg:h-20 lg:max-w-[240px]"
                        /><span v-else class="font-serif text-2xl">{{
                            settings.company_name
                        }}</span></Link>
                </div>

                <form
                    class="relative hidden flex-1 lg:flex lg:max-w-xl"
                    @submit.prevent="submitSearch"
                    @keydown.esc.prevent="closeSearchSuggestions"
                >
                    <label
                        class="flex w-full items-center rounded-full border border-stone bg-white py-1 pl-4 pr-1 transition focus-within:border-ink"
                    >
                        <svg
                            class="h-4 w-4 shrink-0 text-muted"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            viewBox="0 0 24 24"
                        >
                            <circle cx="11" cy="11" r="7" />
                            <path stroke-linecap="round" d="M20 20l-3.5-3.5" />
                        </svg>
                        <input
                            v-model="query"
                            aria-label="Search products"
                            type="search"
                            placeholder="Search your products..."
                            class="w-full bg-transparent px-3 py-2 text-sm outline-none placeholder:text-muted"
                            @focus="focusSearch"
                            @blur="blurSearch"
                        />
                        <button
                            class="rounded-full bg-ink px-6 py-2 text-xs font-semibold uppercase tracking-[0.12em] text-white transition hover:bg-maroon"
                        >
                            Search
                        </button>
                    </label>
                    <div
                        v-if="searchFocused && query.trim()"
                        class="absolute left-0 right-0 top-full z-50 mt-2 overflow-hidden rounded-2xl border border-stone bg-white shadow-xl"
                        role="listbox"
                        aria-label="Product search suggestions"
                    >
                        <a
                            v-for="product in searchSuggestions"
                            :key="product.id"
                            :href="'/product/' + product.id"
                            class="flex items-center gap-3 border-b border-stone/70 px-4 py-3 transition last:border-b-0 hover:bg-blush/60 focus-visible:bg-blush/60 focus-visible:outline-none"
                            role="option"
                        >
                            <img
                                v-if="product.image"
                                :src="product.image"
                                :alt="product.name"
                                class="h-12 w-11 shrink-0 rounded-lg bg-neutral-100 object-cover"
                            />
                            <span
                                v-else
                                class="grid h-12 w-11 shrink-0 place-items-center rounded-lg bg-neutral-100 text-[9px] text-muted"
                                >No image</span
                            >
                            <span class="min-w-0 flex-1">
                                <span
                                    class="block truncate text-sm font-semibold text-ink"
                                    >{{ product.name }}</span
                                >
                                <span
                                    class="mt-1 block truncate text-[10px] uppercase tracking-wider text-muted"
                                    >{{ product.category }}</span
                                >
                            </span>
                            <span
                                class="shrink-0 text-sm font-semibold text-ink"
                                >{{ taka(product.price) }}</span
                            >
                        </a>
                        <p
                            v-if="!searchSuggestions.length"
                            class="px-5 py-6 text-center text-sm text-muted"
                            role="status"
                        >
                            No products found. Try another search.
                        </p>
                    </div>
                </form>

                <div class="flex items-center gap-1 sm:gap-2">
                    <a
                        :href="wishlistUrl(wishlist)"
                        @click.prevent="showWishlist"
                        class="relative hidden rounded-full p-2 hover:bg-blush sm:block"
                        aria-label="Wishlist"
                    >
                        <svg
                            class="h-6 w-6"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.6"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"
                            />
                        </svg>
                        <span
                            class="absolute right-0.5 top-0.5 flex h-4 min-w-[16px] items-center justify-center rounded-full bg-maroon px-1 text-[10px] text-white"
                            >{{ wishlist.length }}</span
                        >
                    </a>
                    <button
                        id="cartBtn"
                        @click="openPanel('cart')"
                        :aria-expanded="panel === 'cart'"
                        class="relative rounded-full p-2 hover:bg-blush"
                        aria-label="Open cart"
                    >
                        <svg
                            class="h-6 w-6"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.6"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z"
                            />
                        </svg>
                        <span
                            id="cartCount"
                            class="absolute right-0.5 top-0.5 flex h-4 min-w-[16px] items-center justify-center rounded-full bg-maroon px-1 text-[10px] text-white"
                            >{{ cartCount }}</span
                        >
                    </button>
                    <details v-if="user" class="account-menu">
                        <summary
                            class="account-trigger"
                            :aria-label="`Account menu for ${user.name}`"
                        >
                            <span class="account-initials">{{
                                accountInitials
                            }}</span>
                            <span class="account-name">{{ user.name }}</span>
                            <ChevronDown :size="15" />
                        </summary>
                        <div class="account-dropdown">
                            <div class="account-dropdown-heading">
                                <strong>{{ user.name }}</strong>
                                <span>{{ user.email }}</span>
                            </div>
                            <component :is="user.is_admin ? 'a' : Link"
                                :href="
                                    user.is_admin
                                        ? '/admin/dashboard'
                                        : '/my-account'
                                "
                                >My account</component>
                            <Link v-if="!user.is_admin" href="/my-orders"
                                >My orders</Link>
                            <Link v-if="!user.is_admin" href="/change-password"
                                >Change password</Link>
                            <Link href="/logout" method="post" as="button"
                                >Logout</Link
                            >
                        </div>
                    </details>
                    <Link
                        v-else
                        href="/login"
                        class="rounded-full p-2 hover:bg-blush"
                        aria-label="Sign in"
                    >
                        <UserRound :size="23" stroke-width="1.6" />
                    </Link>
                </div>
            </div>

            <form
                class="relative pb-3 lg:hidden"
                @submit.prevent="submitSearch"
                @keydown.esc.prevent="closeSearchSuggestions"
            >
                <label
                    class="flex items-center rounded-full border border-stone py-1 pl-4 pr-1 focus-within:border-ink"
                >
                    <svg
                        class="h-4 w-4 shrink-0 text-muted"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        viewBox="0 0 24 24"
                    >
                        <circle cx="11" cy="11" r="7" />
                        <path stroke-linecap="round" d="M20 20l-3.5-3.5" />
                    </svg>
                    <input
                        v-model="query"
                        aria-label="Search products"
                        type="search"
                        placeholder="Search your products..."
                        class="w-full bg-transparent px-3 py-1.5 text-sm outline-none placeholder:text-muted"
                        @focus="focusSearch"
                        @blur="blurSearch"
                    />
                    <button
                        class="rounded-full bg-ink px-4 py-1.5 text-[11px] font-semibold uppercase tracking-wider text-white"
                    >
                        Search
                    </button>
                </label>
                <div
                    v-if="searchFocused && query.trim()"
                    class="absolute left-0 right-0 top-[calc(100%-0.5rem)] z-50 overflow-hidden rounded-2xl border border-stone bg-white shadow-xl"
                    role="listbox"
                    aria-label="Product search suggestions"
                >
                    <a
                        v-for="product in searchSuggestions"
                        :key="product.id"
                        :href="'/product/' + product.id"
                        class="flex items-center gap-3 border-b border-stone/70 px-4 py-3 transition last:border-b-0 hover:bg-blush/60 focus-visible:bg-blush/60 focus-visible:outline-none"
                        role="option"
                    >
                        <img
                            v-if="product.image"
                            :src="product.image"
                            :alt="product.name"
                            class="h-12 w-11 shrink-0 rounded-lg bg-neutral-100 object-cover"
                        />
                        <span
                            v-else
                            class="grid h-12 w-11 shrink-0 place-items-center rounded-lg bg-neutral-100 text-[9px] text-muted"
                            >No image</span
                        >
                        <span class="min-w-0 flex-1">
                            <span
                                class="block truncate text-sm font-semibold text-ink"
                                >{{ product.name }}</span
                            >
                            <span
                                class="mt-1 block truncate text-[10px] uppercase tracking-wider text-muted"
                                >{{ product.category }}</span
                            >
                        </span>
                        <span class="shrink-0 text-sm font-semibold text-ink">{{
                            taka(product.price)
                        }}</span>
                    </a>
                    <p
                        v-if="!searchSuggestions.length"
                        class="px-5 py-6 text-center text-sm text-muted"
                        role="status"
                    >
                        No products found. Try another search.
                    </p>
                </div>
            </form>
        </div>

        <nav class="hidden border-t border-stone/70 lg:block">
            <StoreNavigation />
        </nav>
    </header>

    <aside
        id="menuSidebar"
        :inert="panel !== 'menu'"
        :aria-hidden="panel !== 'menu'"
        :class="panel === 'menu' ? 'translate-x-0' : '-translate-x-full'"
        class="fixed inset-y-0 left-0 z-[60] flex w-[85%] max-w-sm flex-col bg-white shadow-2xl transition-transform duration-300 ease-out"
    >
        <div
            class="flex items-center justify-between border-b border-stone px-5 py-4"
        >
            <img
                :src="settings.header_logo"
                v-if="settings.header_logo"
                :alt="settings.company_name"
                class="h-14 w-auto object-contain"
            />
            <button
                @click="closePanel"
                class="close-panel rounded-full p-2 hover:bg-blush"
                aria-label="Close menu"
            >
                <svg
                    class="h-5 w-5"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" d="M6 6l12 12M18 6L6 18" />
                </svg>
            </button>
        </div>

        <StoreNavigation mobile />

        <div class="space-y-3 border-t border-stone bg-blush px-5 py-5 text-sm">
            <component :is="user?.is_admin ? 'a' : Link"
                :href="accountUrl"
                class="flex items-center gap-3 hover:text-maroon"
            >
                <svg
                    class="h-5 w-5"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.6"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 20.118a7.5 7.5 0 0115 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.5-1.632z"
                    />
                </svg>
                {{ user ? "My account" : "Sign in / Create account" }}
            </component>
            <a
                :href="wishlistUrl(wishlist)"
                @click.prevent="showWishlist"
                class="flex items-center gap-3 hover:text-maroon"
            >
                <svg
                    class="h-5 w-5"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.6"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"
                    />
                </svg>
                Wishlist
            </a>
            <a
                :href="'tel:' + settings.phone_one"
                v-if="settings.phone_one"
                class="flex items-center gap-3 hover:text-maroon"
            >
                <svg
                    class="h-5 w-5"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.6"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106a1.125 1.125 0 00-1.173.417l-.97 1.293a1.125 1.125 0 01-1.21.38 12.035 12.035 0 01-7.143-7.143 1.125 1.125 0 01.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"
                    />
                </svg>
                {{ settings.phone_one }}
            </a>
        </div>
    </aside>

    <aside
        id="cartSidebar"
        :inert="panel !== 'cart'"
        :aria-hidden="panel !== 'cart'"
        :class="panel === 'cart' ? 'translate-x-0' : 'translate-x-full'"
        class="fixed inset-y-0 right-0 z-[60] flex w-[90%] max-w-md flex-col bg-white shadow-2xl transition-transform duration-300 ease-out"
    >
        <div
            class="flex items-center justify-between border-b border-stone px-5 py-4"
        >
            <h2 class="font-serif text-2xl font-semibold">
                Your cart
                <span
                    id="cartHeadCount"
                    class="font-sans text-base font-normal text-muted"
                    >({{ cartCount }})</span
                >
            </h2>
            <button
                @click="closePanel"
                class="close-panel rounded-full p-2 hover:bg-blush"
                aria-label="Close cart"
            >
                <svg
                    class="h-5 w-5"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" d="M6 6l12 12M18 6L6 18" />
                </svg>
            </button>
        </div>

        <div
            v-if="cart.length"
            class="border-b border-stone bg-blush px-5 py-3"
        >
            <p class="mb-2 text-xs">
                {{
                    subtotal >= 5000
                        ? "You’ve unlocked free delivery"
                        : "Add " +
                          taka(5000 - subtotal) +
                          " more for free delivery"
                }}
            </p>
            <div class="h-1.5 overflow-hidden rounded-full bg-stone">
                <div
                    class="h-full bg-maroon transition-all"
                    :style="{
                        width: Math.min(100, (subtotal / 5000) * 100) + '%',
                    }"
                ></div>
            </div>
        </div>
        <ul
            v-if="cart.length"
            class="no-scrollbar flex-1 divide-y divide-stone overflow-y-auto px-5"
        >
            <li v-for="item in cart" :key="item.key" class="flex gap-4 py-5">
                <img
                    v-if="item.image"
                    :src="item.image"
                    :alt="item.name"
                    class="h-28 w-20 shrink-0 object-cover"
                />
                <span
                    v-else
                    class="grid h-28 w-20 shrink-0 place-items-center bg-blush text-center text-[10px] text-muted"
                    >No image</span
                >
                <div class="flex flex-1 flex-col">
                    <div class="flex justify-between gap-2">
                        <span class="text-sm font-medium">{{ item.name }}</span
                        ><button
                            @click="removeItem(item)"
                            :aria-label="'Remove ' + item.name"
                            class="p-1"
                        >
                            <Trash2 :size="16" />
                        </button>
                    </div>
                    <p
                        v-if="item.variant_label"
                        class="mt-1 text-xs text-muted"
                    >
                        {{ item.variant_label }}
                    </p>
                    <div
                        class="mt-auto flex items-center justify-between gap-2 pt-3"
                    >
                        <div
                            class="flex items-center rounded-full border border-stone"
                        >
                            <button
                                @click="
                                    item.qty > 1 ? item.qty-- : removeItem(item)
                                "
                                class="h-8 w-8"
                                aria-label="Decrease quantity"
                            >
                                −</button
                            ><span class="w-6 text-center text-sm">{{
                                item.qty
                            }}</span
                            ><button
                                @click="item.qty++"
                                class="h-8 w-8"
                                aria-label="Increase quantity"
                            >
                                +
                            </button>
                        </div>
                        <span class="text-sm font-semibold">{{
                            taka(item.price * item.qty)
                        }}</span>
                    </div>
                </div>
            </li>
        </ul>
        <p
            v-else
            class="flex flex-1 items-center justify-center px-8 text-center text-sm text-muted"
        >
            Your cart is empty.
        </p>
        <div v-if="cart.length" class="border-t border-stone px-5 py-5">
            <div class="mb-1 flex justify-between">
                <span>Subtotal</span><strong>{{ taka(subtotal) }}</strong>
            </div>
            <p class="mb-4 text-xs text-muted">
                Delivery is free on orders over ৳5,000.
            </p>
            <button
                @click="beginCheckout"
                class="w-full rounded-full bg-ink py-3 text-sm font-medium text-white hover:bg-maroon"
            >
                Checkout
            </button>
            <p class="mt-4 text-center text-[11px] text-muted">
                Cash on delivery
            </p>
        </div>
    </aside>

    <button v-if="panel" id="overlay" type="button" aria-label="Close sidebar" @click="closePanel" class="fixed inset-0 z-50 border-0 bg-black/50"></button>
</template>

<style scoped>
.account-menu {
    position: relative;
}
.account-menu summary {
    list-style: none;
}
.account-menu summary::-webkit-details-marker {
    display: none;
}
.account-trigger {
    display: flex;
    min-height: 42px;
    align-items: center;
    gap: 8px;
    border-radius: 999px;
    padding: 3px 9px 3px 3px;
    color: #0B2A5B;
    cursor: pointer;
}
.account-trigger:hover,
.account-menu[open] .account-trigger {
    background: #F3F6FB;
}
.account-initials {
    display: grid;
    width: 35px;
    height: 35px;
    place-items: center;
    border-radius: 50%;
    background: #E30613;
    color: white;
    font-size: 11px;
    font-weight: 700;
}
.account-name {
    max-width: 115px;
    overflow: hidden;
    font-size: 11px;
    font-weight: 600;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.account-dropdown {
    position: absolute;
    z-index: 60;
    top: calc(100% + 10px);
    right: 0;
    display: grid;
    width: 220px;
    overflow: hidden;
    border: 1px solid #F3F6FB;
    border-radius: 12px;
    background: white;
    padding: 7px;
    box-shadow: 0 16px 38px #0B2A5B20;
}
.account-dropdown-heading {
    display: grid;
    gap: 4px;
    border-bottom: 1px solid #dce4ef;
    padding: 10px 10px 12px;
}
.account-dropdown-heading strong {
    overflow: hidden;
    color: #0B2A5B;
    font-size: 12px;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.account-dropdown-heading span {
    overflow: hidden;
    color: #64748b;
    font-size: 10px;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.account-dropdown :deep(a),
.account-dropdown :deep(button) {
    display: flex;
    min-height: 37px;
    align-items: center;
    border: 0;
    border-radius: 7px;
    background: transparent;
    padding: 0 10px;
    color: #0B2A5B;
    font-size: 11px;
    text-align: left;
}
.account-dropdown :deep(a:hover),
.account-dropdown :deep(button:hover) {
    background: #F3F6FB;
    color: #E30613;
}
@media (max-width: 640px) {
    .account-name {
        display: none;
    }
    .account-trigger {
        gap: 4px;
        padding-right: 5px;
    }
}
</style>
