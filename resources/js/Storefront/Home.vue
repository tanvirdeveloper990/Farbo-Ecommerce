<script setup>
import {
    computed,
    ref,
    provide,
    watch,
    onMounted,
    onBeforeUnmount,
    nextTick,
} from "vue";
import { Head, Link, usePage, router } from "@inertiajs/vue3";
import Swal from "sweetalert2";
import { notify } from "./notify";
import { trackCartChange } from "./tracking";
import PolicyContent from "./Components/PolicyContent.vue";
import StorefrontLayout from "./StorefrontLayout.vue";
import ProductRail from "./Components/ProductRail.vue";
import PopularCategories from "./Components/PopularCategories.vue";

import { readWishlist, saveWishlist, wishlistUrl } from "./wishlist";
import { readCart, saveCart } from './storefrontContext';
const props = defineProps({
    searchTerm: { type: String, default: '' },
    newArrivals: { type: Array, default: () => [] },
    products: { type: Array, default: () => [] },
    searchProducts: { type: Array, default: () => [] },
    categorySections: { type: Array, default: () => [] },
    popularCategories: { type: Array, default: () => [] },
    policy: Object,
    banners: { type: Array, default: () => [] },
    settings: { type: Object, default: () => ({}) },
    wishlistMode: { type: Boolean, default: false },

});
const page = usePage();
watch(() => props.searchTerm, value => { query.value = value; search.value = value; wishOnly.value = props.wishlistMode; });
const panel = ref(null),
    selected = ref(null),
    query = ref(props.searchTerm),
    search = ref(props.searchTerm),
    wishlist = ref(readWishlist()),
    wishOnly = ref(props.wishlistMode),
    cart = ref(readCart()),
    selectedVariantId = ref(null),

    qty = ref(1),
    scrolled = ref(false),
    searchFocused = ref(false);
const slide = ref(0);
watch(cart, saveCart, { deep: true });
let slideTimer;
let touchStart = 0;
let previousFocus;
const taka = (n) => "৳" + n.toLocaleString("en-IN");
const cartCount = computed(() =>
    cart.value.reduce((sum, item) => sum + item.qty, 0),
);
const selectedVariant = computed(
    () =>
        selected.value?.variants?.find(
            (option) => option.id === selectedVariantId.value,
        ) || null,
);
const subtotal = computed(() =>
    cart.value.reduce((sum, item) => sum + item.qty * item.price, 0),
);
const visibleProducts = computed(() =>
    props.products.filter(
        (p) =>
            (!wishOnly.value || wishlist.value.includes(p.id)) &&
            (!search.value ||
                (p.name + " " + p.category)
                    .toLowerCase()
                    .includes(search.value.toLowerCase())),
    ),
);
const searchSuggestions = computed(() => {
    const term = query.value.trim().toLowerCase();
    if (!term) return [];

    return props.searchProducts
        .filter((product) =>
            `${product.name} ${product.category}`.toLowerCase().includes(term),
        )
        .slice(0, 7);
});

function openPanel(name) {
    previousFocus = document.activeElement;
    panel.value = name;
    nextTick(() =>
        document.querySelector("#" + name + "Sidebar button")?.focus(),
    );
}
function closePanel() {
    panel.value = null;
    previousFocus?.focus();
}
function submitSearch() {
    if (props.policy) {
        router.get('/', { q: query.value.trim() }, { preserveScroll: false });
        return;
    }
    search.value = query.value.trim();
    searchFocused.value = Boolean(search.value);
    wishOnly.value = false;
    document
        .getElementById("collections")
        ?.scrollIntoView({ behavior: "smooth" });
}
function closeSearchSuggestions() {
    searchFocused.value = false;
}
function showWishlist() {
    closePanel();
    router.visit(wishlistUrl(wishlist.value));
}
function toggleWish(id) {
    wishlist.value = wishlist.value.includes(id)
        ? wishlist.value.filter((v) => v !== id)
        : [...wishlist.value, id];
    saveWishlist(wishlist.value);
}
function showAllProducts() {
    if (wishOnly.value) {
        router.visit('/#collections');
        return;
    }
    search.value = '';
    query.value = '';
}
function openQuickView(product) {
    previousFocus = document.activeElement;
    selected.value = product;
    selectedVariantId.value = null;
    qty.value = 1;
    nextTick(() => document.getElementById("qvClose")?.focus());
}
function closeQuickView() {
    selected.value = null;
    previousFocus?.focus();
}
function addToCart() {
    const variant = selected.value.variants?.find(
        (option) => option.id === selectedVariantId.value,
    );
    if (selected.value.variants?.length && !variant) {
        notify("Choose a product option before adding it to your cart.");
        return;
    }
    const key = selected.value.id + "-" + (variant?.id || "default");
    const item = cart.value.find((p) => p.key === key);
    if (
        (variant?.stock ?? selected.value.stock) <
        qty.value + (item?.qty || 0)
    ) {
        notify("The selected quantity is not available.");
        return;
    }
    if (item) item.qty += qty.value;
    else
        cart.value.push({
            ...selected.value,
            key,
            variant_id: variant?.id || null,
            stock: variant?.stock ?? selected.value.stock,
            variant_label: variant?.label || "",
            price: variant?.price ?? selected.value.price,
            qty: qty.value,
        });
    trackCartChange('add_to_cart', { ...selected.value, price: variant?.price ?? selected.value.price, variant_label: variant?.label }, qty.value);
    notify(`${qty.value} × ${selected.value.name} added to cart.`);
}
async function removeItem(item) {
    const result = await Swal.fire({
        title: "Remove this item?",
        text: item.name,
        icon: "warning",
        showCancelButton: true,
        showCloseButton: true,
        allowOutsideClick: false,
        allowEscapeKey: false,
        confirmButtonText: "Yes, remove",
        cancelButtonText: "No, keep it",
        confirmButtonColor: "#E30613",
    });
    if (result.isConfirmed) {
        trackCartChange('remove_from_cart', item, item.qty);
        cart.value = cart.value.filter((p) => p.key !== item.key);
    }
}
function trap(event, root) {
    if (event.key !== "Tab") return;
    const controls = [
        ...root.querySelectorAll("button,a[href],input,select"),
    ].filter((el) => el.getClientRects().length);
    const first = controls[0],
        last = controls.at(-1);
    if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last?.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first?.focus();
    }
}
function trapQuickView(event) {
    trap(event, event.currentTarget);
}
function trapPanel(event) {
    if (panel.value)
        trap(event, document.getElementById(panel.value + "Sidebar"));
}
watch([panel, selected], () => {
    document.body.classList.toggle(
        "overflow-hidden",
        !!panel.value || !!selected.value,
    );
});
function changeSlide(direction) {
    const count = props.banners.length;
    slide.value = count ? (slide.value + direction + count) % count : 0;
}
function pauseSlides() {
    clearInterval(slideTimer);
}
function startSlides() {
    pauseSlides();
    if (
        props.banners.length > 1 &&
        !window.matchMedia("(prefers-reduced-motion: reduce)").matches
    )
        slideTimer = setInterval(() => changeSlide(1), 5000);
}
function swipe(event) {
    const diff = event.changedTouches[0].clientX - touchStart;
    if (Math.abs(diff) > 50) changeSlide(diff < 0 ? 1 : -1);
    startSlides();
}
function updateScroll() {
    scrolled.value = window.scrollY > 40;
}
onMounted(() => {
    startSlides();
    updateScroll();
    window.addEventListener("scroll", updateScroll, { passive: true });
    document.addEventListener("keydown", trapPanel);
    if (wishOnly.value || search.value) nextTick(() => document.getElementById("collections")?.scrollIntoView());
});
onBeforeUnmount(() => {
    pauseSlides();
    window.removeEventListener("scroll", updateScroll);
    document.removeEventListener("keydown", trapPanel);
    document.body.classList.remove("overflow-hidden");
});
provide("storefront", {
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
    toggleWish,
    openQuickView,
    deliveryCharges: {
        insideDhaka: Number(props.settings.shipping_inside_dhaka ?? 80),
        outsideDhaka: Number(props.settings.shipping_outside_dhaka ?? 150),
    },
});
</script>
<template>
    <Head
        :title="
            policy
                ? policy.title + ' | ' + settings.company_name
                : settings.company_name
        "
        /><StorefrontLayout>
        <PolicyContent v-if="policy" :policy="policy" />
        <template v-else>
            <section
                class="relative overflow-hidden bg-blush"
                id="bannerSlider"
                v-if="banners.length"
                @mouseenter="pauseSlides"
                @mouseleave="startSlides"
                @touchstart.passive="
                    touchStart = $event.touches[0].clientX;
                    pauseSlides();
                "
                @touchend="swipe($event)"
                aria-roledescription="carousel"
            >
                <div
                    id="sliderTrack"
                    :style="{ transform: `translateX(-${slide * 100}%)` }"
                    class="flex items-start transition-transform duration-700 ease-[cubic-bezier(.22,.61,.36,1)]"
                >
                    <a
                        v-for="(banner, index) in banners"
                        :key="banner.id"
                        href="#collections"
                        class="block w-full shrink-0"
                    >
                        <img
                            :src="banner.image"
                            :alt="
                                settings.company_name + ' banner ' + (index + 1)
                            "
                            class="block w-full aspect-[2.4/1] object-cover"
                            :loading="index ? 'lazy' : 'eager'"
                        />
                    </a>
                </div>

                <div
                    id="sliderDots"
                    v-if="banners.length > 1"
                    class="absolute bottom-3 left-1/2 flex -translate-x-1/2 items-center gap-2 sm:bottom-5"
                >
                    <button
                        v-for="n in banners.length"
                        :key="n"
                        @click="slide = n - 1"
                        :aria-label="'Go to slide ' + n"
                        :aria-current="slide === n - 1 ? 'true' : undefined"
                        class="h-2.5 rounded-full bg-white transition-all"
                        :class="slide === n - 1 ? 'w-7' : 'w-2.5 opacity-50'"
                    ></button>
                </div>
            </section>
            <section class="bg-white py-12 sm:py-16 lg:py-20">
                <div class="mx-auto max-w-7xl px-4">
                    <div class="mb-10 text-center sm:mb-14">
                        <h2
                            class="text-2xl font-bold uppercase tracking-[0.14em] text-black sm:text-3xl lg:text-4xl"
                        >
                            Popular <span class="cat-outline">Category</span>
                        </h2>
                        <div
                            class="mx-auto mt-4 flex items-center justify-center gap-2"
                        >
                            <span class="h-px w-10 bg-black/30"></span>
                            <span class="h-1.5 w-1.5 rotate-45 bg-black"></span>
                            <span class="h-px w-10 bg-black/30"></span>
                        </div>
                    </div>

                    <PopularCategories :categories="popularCategories" />
                </div>
            </section>
            <section
                v-if="!policy && !search && !wishOnly && newArrivals.length"
                class="bg-white py-12 sm:py-16 lg:py-20"
            >
                <div class="mx-auto max-w-7xl px-4">
                    <div class="mb-10 text-center sm:mb-14">
                        <p
                            class="mb-3 text-[11px] font-medium tracking-[0.3em] text-neutral-500 sm:text-xs"
                        >
                            JUST LANDED
                        </p>
                        <h2
                            class="text-2xl font-bold uppercase tracking-[0.14em] text-black sm:text-3xl lg:text-4xl"
                        >
                            New <span class="na-outline">Arrival</span>
                        </h2>
                        <div
                            class="mx-auto mt-4 flex items-center justify-center gap-2"
                        >
                            <span class="h-px w-10 bg-black/30"></span>
                            <span class="h-1.5 w-1.5 rotate-45 bg-black"></span>
                            <span class="h-px w-10 bg-black/30"></span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-5">
                        <Link
                            v-for="product in newArrivals.slice(0, 2)"
                            :key="product.id"
                            :href="`/product/${product.id}`"
                            class="group relative block h-[440px] overflow-hidden bg-neutral-100 sm:h-[540px] lg:h-[620px]"
                        >
                            <img
                                v-if="product.image"
                                :src="product.image"
                                :alt="product.name"
                                loading="lazy"
                                class="h-full w-full object-cover object-top transition duration-[1200ms] ease-out group-hover:scale-105"
                            />
                            <div
                                v-else
                                class="grid h-full place-items-center bg-neutral-100 text-sm text-neutral-500"
                            >
                                Product image coming soon
                            </div>
                            <div
                                class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/75 via-black/5 to-transparent"
                            ></div>
                            <span
                                class="absolute left-4 top-4 bg-white px-3 py-1.5 text-[10px] font-semibold tracking-[0.2em] text-black sm:left-6 sm:top-6"
                                >NEW</span
                            >
                            <div
                                class="absolute inset-x-0 bottom-0 p-6 text-white sm:p-8"
                            >
                                <p
                                    class="text-[11px] font-medium tracking-[0.25em] text-white/80"
                                >
                                    {{ product.category }}
                                </p>
                                <h3
                                    class="mt-2 text-2xl font-semibold uppercase tracking-[0.08em] sm:text-3xl"
                                >
                                    {{ product.name }}
                                </h3>
                                <p class="mt-2 text-sm font-semibold">
                                    {{ taka(product.price) }}
                                    <del
                                        v-if="product.oldPrice"
                                        class="ml-2 font-normal text-white/70"
                                        >{{ taka(product.oldPrice) }}</del
                                    >
                                </p>
                                <span
                                    class="mt-5 inline-flex items-center gap-3 border border-white px-6 py-3 text-xs font-semibold uppercase tracking-[0.18em] transition duration-300 group-hover:bg-white group-hover:text-black"
                                >
                                    Shop now
                                    <svg
                                        class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M17 8l4 4m0 0l-4 4m4-4H3"
                                        />
                                    </svg>
                                </span>
                            </div>
                        </Link>
                    </div>

                    <div
                        v-if="newArrivals.length > 2"
                        class="mt-10 text-center sm:mt-12"
                    >
                        <Link
                            href="/new-arrivals"

                            class="inline-flex items-center gap-3 bg-black px-8 py-3.5 text-xs font-semibold uppercase tracking-[0.2em] text-white transition duration-300 hover:bg-neutral-800"
                        >
                            View all new arrivals
                            <svg
                                class="h-4 w-4 transition-transform duration-300"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M17 8l4 4m0 0l-4 4m4-4H3"
                                />
                            </svg>
                        </Link>
                    </div>
                </div>
            </section>
            <section
                v-if="settings.shop_image"
                class="bg-white py-10 sm:py-14 lg:py-16"
            >
                <div class="mx-auto max-w-7xl px-4">
                    <a
                        href="/categories"
                        class="group relative block h-[420px] overflow-hidden bg-neutral-100 sm:h-[480px] lg:h-[540px]"
                    >
                        <img
                            :src="settings.shop_image"
                            alt="Shop collection"
                            loading="lazy"
                            class="absolute inset-0 h-full w-full object-cover object-center transition duration-[1400ms] ease-out group-hover:scale-105"
                        />

                        <div
                            class="absolute bg-white/75 md:bg-transparent md:bg-gradient-to-r md:from-white md:via-white/85 md:to-transparent"
                        ></div>

                        <div
                            class="absolute right-4 top-4 flex h-20 w-20 flex-col items-center justify-center rounded-full bg-black text-white shadow-xl transition duration-500 group-hover:rotate-12 sm:right-8 sm:top-8 sm:h-28 sm:w-28"
                        >
                            <span
                                class="text-[10px] font-medium tracking-[0.15em] sm:text-xs"
                                >UP TO</span
                            >
                            <span
                                class="text-xl font-bold leading-none sm:text-3xl"
                                >40%</span
                            >
                            <span
                                class="text-[10px] font-semibold tracking-[0.15em] sm:text-xs"
                                >OFF</span
                            >
                        </div>
                    </a>
                </div>
            </section>
            <div id="collections" class="scroll-mt-52">
                <div
                    v-if="search || wishOnly"
                    class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 pt-8"
                >
                    <p class="text-sm">
                        {{
                            wishOnly
                                ? "Your wishlist"
                                : "Results for “" + search + "”"
                        }}
                        · {{ visibleProducts.length }} {{ visibleProducts.length === 1 ? 'item' : 'items' }}
                    </p>
                    <button
                        @click="showAllProducts"
                        class="text-sm underline"
                    >
                        Show all products
                    </button>
                </div>
                <div
                    v-if="(search || wishOnly) && !visibleProducts.length"
                    class="px-4 py-16 text-center text-muted"
                >
                    {{
                        wishOnly
                            ? "Save your favorite pieces using the heart icon."
                            : "No matching products. Try another search."
                    }}
                </div>
                <ProductRail
                    v-if="(search || wishOnly) && visibleProducts.length"
                    :title="wishOnly ? 'Wishlist' : 'Search results'"
                    :products="visibleProducts"
                />
                <template v-if="!search && !wishOnly">
                <template
                    v-for="category in categorySections"
                    :key="category.id"
                >
                    <ProductRail
                        v-if="category.products.length"
                        :title="category.name"
                        :products="category.products"
                        :href="`/categories/${encodeURIComponent(category.slug || category.id)}`"
                    />
                    <section v-else class="bg-white py-12 sm:py-16">
                        <div
                            class="mx-auto flex max-w-7xl flex-col items-center px-4 text-center"
                        >
                            <h2
                                class="text-xl font-bold uppercase tracking-[0.12em] text-black sm:text-3xl"
                            >
                                {{ category.name }}
                            </h2>
                            <p class="mt-3 text-sm text-neutral-500">
                                Products are coming soon.
                            </p>
                            <Link
                                :href="`/categories/${encodeURIComponent(category.slug || category.id)}`"
                                class="mt-5 border-b border-black pb-1 text-xs font-semibold uppercase tracking-[0.16em] text-black hover:text-neutral-600"
                            >
                                Explore {{ category.name }}
                            </Link>
                        </div>
                    </section>
                </template>
                </template>
            </div>

            <div
                v-if="selected"
                id="qvModal"
                class="fixed inset-0 z-[70] flex items-end justify-center transition-opacity duration-300 sm:items-center sm:p-4"
                role="dialog"
                aria-modal="true"
                aria-labelledby="qvName"
                @keydown="trapQuickView"
            >
                <div id="qvBackdrop" class="absolute inset-0 bg-black/60"></div>

                <div
                    id="qvBox"
                    class="relative grid max-h-[92vh] w-full max-w-4xl overflow-y-auto bg-white transition-transform duration-300 md:grid-cols-2"
                >
                    <button
                        id="qvClose"
                        @click="closeQuickView"
                        aria-label="Close quick view"
                        class="absolute right-3 top-3 z-10 flex h-9 w-9 items-center justify-center rounded-full bg-white text-black shadow hover:bg-black hover:text-white"
                    >
                        <svg
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                d="M6 6l12 12M18 6L6 18"
                            />
                        </svg>
                    </button>

                    <div
                        class="aspect-[3/4] bg-neutral-100 md:aspect-auto md:h-full"
                    >
                        <img
                            v-if="selected.image"
                            id="qvImg"
                            :src="selected.image"
                            :alt="selected.name"
                            class="h-full w-full object-cover object-top"
                        />
                        <div
                            v-else
                            class="grid h-full min-h-64 place-items-center text-sm text-neutral-500"
                        >
                            Product image coming soon
                        </div>
                    </div>

                    <div class="flex flex-col p-6 sm:p-8">
                        <p
                            id="qvCat"
                            class="text-[11px] font-medium tracking-[0.25em] text-neutral-500"
                        >
                            {{ selected.category }}
                        </p>
                        <h3
                            id="qvName"
                            class="mt-2 text-xl font-semibold uppercase tracking-[0.06em] text-black sm:text-2xl"
                        >
                            {{ selected.name }}
                        </h3>
                        <div class="mt-3 flex items-center gap-3">
                            <span
                                id="qvPrice"
                                class="text-xl font-bold text-black"
                                >{{
                                    taka(
                                        selectedVariant?.price ??
                                            selected.price,
                                    )
                                }}</span
                            >
                            <span
                                id="qvOld"
                                class="text-sm text-neutral-400 line-through"
                                >{{
                                    selected.oldPrice && !selectedVariant
                                        ? taka(selected.oldPrice)
                                        : ""
                                }}</span
                            >
                        </div>
                        <p
                            v-if="selected.description"
                            class="mt-5 text-sm leading-relaxed text-neutral-600"
                        >
                            {{ selected.description }}
                        </p>

                        <p
                            v-if="selected.variants?.length"
                            class="mt-6 text-[11px] font-semibold tracking-[0.2em] text-black"
                        >
                            SELECT OPTION
                        </p>
                        <div id="qvSizes" class="mt-3 flex flex-wrap gap-2">
                            <button
                                v-for="option in selected.variants"
                                :key="option.id"
                                @click="selectedVariantId = option.id"
                                :aria-pressed="selectedVariantId === option.id"
                                class="h-10 min-w-[44px] border px-3 text-xs font-semibold"
                                :class="
                                    selectedVariantId === option.id
                                        ? 'border-black bg-black text-white'
                                        : 'border-black/20 text-black'
                                "
                                :disabled="option.stock < 1"
                            >
                                {{ option.label || "Option " + option.id }}
                            </button>
                            <span
                                v-if="!selected.variants?.length"
                                class="text-sm text-neutral-600"
                                >Standard</span
                            >
                        </div>

                        <div class="mt-6 flex items-center gap-3">
                            <div
                                class="flex items-center border border-black/20"
                            >
                                <button
                                    id="qvMinus"
                                    @click="qty = Math.max(1, qty - 1)"
                                    class="h-12 w-10 text-lg hover:bg-neutral-100"
                                    aria-label="Decrease"
                                >
                                    −
                                </button>
                                <span
                                    id="qvQty"
                                    class="w-8 text-center text-sm font-medium"
                                    >{{ qty }}</span
                                >
                                <button
                                    id="qvPlus"
                                    @click="qty++"
                                    class="h-12 w-10 text-lg hover:bg-neutral-100"
                                    aria-label="Increase"
                                >
                                    +
                                </button>
                            </div>
                            <button
                                id="qvAdd"
                                @click="addToCart"
                                class="h-12 flex-1 bg-black text-xs font-semibold uppercase tracking-[0.2em] text-white transition hover:bg-neutral-800"
                            >
                                Add to cart
                            </button>
                        </div>
                        <p
                            id="qvMsg"
                            class="mt-3 h-4 text-xs text-neutral-600"
                        ></p>
                    </div>
                </div>
            </div>
        </template>
    </StorefrontLayout>
</template>
