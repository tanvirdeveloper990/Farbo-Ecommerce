<script setup>
import { ref, inject } from "vue";
import { Link } from "@inertiajs/vue3";
defineProps({
    title: String,
    products: Array,
    href: { type: String, default: "/#collections" },
});
const track = ref(null);
const { taka, toggleWish, wishlist, openQuickView } = inject("storefront");
function move(direction) {
    const el = track.value;
    const max = el.scrollWidth - el.clientWidth;
    const step = el.querySelector(".prod-item")?.clientWidth || el.clientWidth;
    el.scrollTo({
        left:
            direction > 0
                ? el.scrollLeft >= max - 5
                    ? 0
                    : el.scrollLeft + step
                : el.scrollLeft <= 5
                  ? max
                  : el.scrollLeft - step,
        behavior: "smooth",
    });
}
</script>
<template>
    <section class="bg-white py-12 sm:py-16">
        <div class="mx-auto max-w-7xl px-4">
            <div
                class="mb-8 flex items-end justify-between gap-4 border-b border-black/10 pb-5 sm:mb-10"
            >
                <div>
                    <h2
                        class="text-xl font-bold uppercase tracking-[0.12em] text-black sm:text-3xl"
                    >
                        {{ title }} <span class="prod-outline"></span>
                    </h2>
                </div>
                <Link
                    :href="href"
                    class="group inline-flex shrink-0 items-center gap-2 border-b border-black pb-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-black transition hover:text-neutral-600 sm:text-xs"
                >
                    See all
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
                </Link>
            </div>

            <div class="relative">
                <button
                    @click="move(-1)"
                    aria-label="Previous products"
                    class="absolute left-1 top-[38%] z-20 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full bg-white/90 text-black shadow-md backdrop-blur transition hover:bg-black hover:text-white sm:left-3 sm:h-11 sm:w-11"
                >
                    <svg
                        class="h-4 w-4 sm:h-5 sm:w-5"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M15 19l-7-7 7-7"
                        />
                    </svg>
                </button>

                <div
                    ref="track"
                    class="product-track -mx-2 flex snap-x snap-mandatory overflow-x-auto"
                >
                    <div
                        v-for="product in products"
                        :key="product.id"
                        class="prod-item min-w-0 shrink-0 grow-0 basis-1/2 snap-start px-2 md:basis-1/3 lg:basis-1/4"
                    >
                        <div
                            class="product-card-media group relative aspect-[3/4] overflow-hidden bg-neutral-100"
                        >
                            <Link :href="`/product/${product.id}`" class="product-card-image">
                                <img
                                    v-if="product.image"
                                    :src="product.image"
                                    :alt="product.name"
                                    loading="lazy"
                                    class="p-img h-full w-full object-cover object-top transition duration-700 ease-out group-hover:scale-105"
                                />
                                <span
                                    v-else
                                    class="grid aspect-[3/4] place-items-center text-xs text-neutral-500"
                                >
                                    Product image coming soon
                                </span>
                            </Link>
                            <button
                                @click="toggleWish(product.id)"
                                :aria-pressed="wishlist.includes(product.id)"
                                class="wish-btn absolute left-2 top-2 flex h-9 w-9 items-center justify-center rounded-full text-black transition hover:bg-white/80 sm:left-3 sm:top-3"
                                :aria-label="wishlist.includes(product.id) ? 'Remove from wishlist' : 'Add to wishlist'"
                            >
                                <svg
                                    class="h-5 w-5"
                                    :fill="
                                        wishlist.includes(product.id)
                                            ? 'currentColor'
                                            : 'none'
                                    "
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
                            </button>
                            <span
                                v-if="product.newArrival"
                                class="absolute right-2 top-2 px-2 py-1 text-[9px] font-bold tracking-[0.15em] sm:right-3 sm:top-3 sm:px-2.5 sm:text-[10px] bg-white text-black"
                                >NEW</span
                            >

                            <button
                                @click="openQuickView(product)"
                                class="qv-btn absolute inset-x-3 bottom-3 hidden translate-y-4 items-center justify-center gap-2 bg-black py-3 text-xs font-semibold uppercase tracking-[0.18em] text-white opacity-0 transition duration-300 hover:bg-neutral-800 group-hover:translate-y-0 group-hover:opacity-100 lg:flex"
                            >
                                <svg
                                    class="h-4 w-4"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                    />
                                </svg>
                                Quick view
                            </button>

                            <button
                                @click="openQuickView(product)"
                                class="qv-btn absolute bottom-2 right-2 flex h-9 w-9 items-center justify-center rounded-full bg-white text-black shadow lg:hidden"
                                aria-label="Quick view"
                            >
                                <svg
                                    class="h-4 w-4"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                    />
                                </svg>
                            </button>
                        </div>
                        <div class="pt-3 sm:pt-4">
                            <p
                                class="p-cat text-[10px] font-medium tracking-[0.2em] text-neutral-500"
                            >
                                {{ product.category }}
                            </p>
                            <Link
                                :href="`/product/${product.id}`"
                                class="p-name mt-1 block truncate text-xs font-semibold uppercase tracking-[0.06em] text-black hover:underline sm:text-sm"
                                >{{ product.name }}</Link
                            >
                            <div class="mt-1.5 flex items-center gap-2">
                                <span
                                    class="p-price text-sm font-bold text-black"
                                    >{{ taka(product.price) }}</span
                                >
                                <span
                                    v-if="product.oldPrice"
                                    class="p-old text-xs text-neutral-400 line-through"
                                    >{{ taka(product.oldPrice) }}</span
                                >
                            </div>
                        </div>
                    </div>
                </div>
                <button
                    @click="move(1)"
                    aria-label="Next products"
                    class="absolute right-1 top-[38%] z-20 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full bg-white/90 text-black shadow-md backdrop-blur transition hover:bg-black hover:text-white sm:right-3 sm:h-11 sm:w-11"
                >
                    <svg
                        class="h-4 w-4 sm:h-5 sm:w-5"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M9 5l7 7-7 7"
                        />
                    </svg>
                </button>
            </div>
        </div>
    </section>
</template>
