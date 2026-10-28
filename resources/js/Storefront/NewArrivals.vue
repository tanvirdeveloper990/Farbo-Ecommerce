<script setup>
import { provide } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import StorefrontLayout from './StorefrontLayout.vue';
import { createStorefrontContext } from './storefrontContext';
defineProps({ products: Object });
const storefront = createStorefrontContext();
provide('storefront', storefront);
const { taka, toggleWish, wishlist, openQuickView } = storefront;
</script>

<template>
    <Head title="New Arrivals" />
    <StorefrontLayout>
        <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:py-16">
            <div class="mb-10 text-center">
                <p class="mb-3 text-xs uppercase tracking-[0.35em] text-neutral-500">Just landed</p>
                <h1 class="text-3xl font-semibold uppercase tracking-[0.15em] sm:text-4xl">New Arrivals</h1>
                <p class="mt-4 text-sm text-neutral-500">{{ products.total }} products</p>
            </div>
            <div v-if="products.data.length" class="grid grid-cols-2 gap-x-4 gap-y-10 sm:gap-x-6 lg:grid-cols-4">
                <article v-for="product in products.data" :key="product.id" class="group">
                    <div class="relative overflow-hidden bg-neutral-100">
                        <Link :href="`/product/${product.id}`">
                            <img v-if="product.image" :src="product.image" :alt="product.name" loading="lazy" class="aspect-[3/4] w-full object-cover transition duration-500 group-hover:scale-105" />
                        </Link>
                        <span class="absolute left-3 top-3 bg-white px-2 py-1 text-[10px] tracking-widest">NEW</span>
                        <button type="button" :aria-label="wishlist.includes(product.id) ? 'Remove from wishlist' : 'Add to wishlist'" :aria-pressed="wishlist.includes(product.id)" @click="toggleWish(product.id)" class="absolute right-3 top-3 rounded-full bg-white p-2">
                            <svg viewBox="0 0 24 24" class="h-5 w-5" stroke="currentColor" stroke-width="1.5" :fill="wishlist.includes(product.id) ? 'currentColor' : 'none'"><path d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" /></svg>
                        </button>
                        <button type="button" @click="openQuickView(product)" class="absolute bottom-0 left-0 w-full bg-white/95 py-3 text-xs uppercase tracking-widest transition lg:translate-y-full lg:group-hover:translate-y-0 lg:focus:translate-y-0">Quick view</button>
                    </div>
                    <p class="mt-4 text-[10px] uppercase tracking-widest text-neutral-500">{{ product.category }}</p>
                    <Link :href="`/product/${product.id}`" class="mt-1 block text-sm font-medium">{{ product.name }}</Link>
                    <p class="mt-2 text-sm"><strong>{{ taka(product.price) }}</strong><span v-if="product.oldPrice > product.price" class="ml-2 text-neutral-400 line-through">{{ taka(product.oldPrice) }}</span></p>
                </article>
            </div>
            <p v-else class="py-16 text-center text-neutral-500">New arrivals are coming soon.</p>
            <nav v-if="products.last_page > 1" aria-label="New arrivals pages" class="mt-12 flex flex-wrap justify-center gap-2">
                <template v-for="(link, index) in products.links" :key="index">
                    <Link v-if="link.url" :href="link.url" :aria-current="link.active ? 'page' : undefined" class="border px-4 py-2 text-sm" :class="link.active ? 'bg-black text-white' : 'hover:bg-neutral-100'" v-html="link.label" />
                    <span v-else class="border px-4 py-2 text-sm text-neutral-400" v-html="link.label" />
                </template>
            </nav>
        </section>
    </StorefrontLayout>
</template>
