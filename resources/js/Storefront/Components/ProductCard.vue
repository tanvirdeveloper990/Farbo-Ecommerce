<script setup>
import { Link } from '@inertiajs/vue3';
defineProps({product: Object, wished: Boolean});
defineEmits(['wish']);
const money = value => '৳' + Number(value).toLocaleString('en-BD', {minimumFractionDigits: 2, maximumFractionDigits: 2});
</script>
<template>
    <article class="group flex flex-col bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-xl hover:border-brand-navy/30 hover:-translate-y-1 transition-all duration-300 overflow-hidden min-w-0">
        <div class="relative aspect-square bg-brand-light overflow-hidden">
            <Link :href="`/product/${product.id}`" class="absolute inset-0"><img :src="product.image || '/farbo/assets/img/logo/logo.jpeg'" :alt="product.name" loading="lazy" class="w-full h-full object-cover object-center group-hover:scale-110 transition-transform duration-500" /></Link>
            <button type="button" class="absolute top-2.5 right-2.5 w-8 h-8 grid place-items-center rounded-full bg-white text-brand-navy shadow-md opacity-0 group-hover:opacity-100 focus:opacity-100 hover:text-brand-red transition" :aria-pressed="wished" :aria-label="wished ? 'Remove from wishlist' : 'Add to wishlist'" @click="$emit('wish', product.id)"><i :class="wished ? 'fa-solid fa-heart' : 'fa-regular fa-heart'" class="text-xs"></i></button>
        </div>
        <div class="flex-1 flex flex-col p-3 sm:p-4">
            <h3 class="line-clamp-2 h-10 sm:h-11 text-[13px] sm:text-[15px] font-semibold text-gray-800 leading-5 sm:leading-[22px] group-hover:text-brand-navy"><Link :href="`/product/${product.id}`">{{ product.name }}</Link></h3>
            <p v-if="Number(product.price) > 0" class="mt-2 text-base sm:text-lg font-extrabold text-brand-navy">{{ money(product.price) }}</p>
            <Link :href="`/product/${product.id}`" class="mt-3 h-9 sm:h-10 grid place-items-center rounded-lg bg-brand-navy text-white text-xs sm:text-sm font-semibold hover:bg-brand-red transition">View Details</Link>
        </div>
    </article>
</template>
