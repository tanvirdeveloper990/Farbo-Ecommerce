<script setup>
import { Link } from '@inertiajs/vue3';
import { ref, onMounted, onBeforeUnmount } from "vue";
defineProps({ categories: { type: Array, default: () => [] } });
const categoryUrl = (category) => `/categories/${encodeURIComponent(category.slug || category.id)}`;
const track = ref(null);
let timer;
function move(direction) {
    const element = track.value;
    if (!element) return;
    const maximum = Math.max(0, element.scrollWidth - element.clientWidth);
    const step = element.querySelector('.cat-item')?.offsetWidth || element.clientWidth;
    element.scrollTo({
        left: direction > 0
            ? element.scrollLeft >= maximum - 5 ? 0 : Math.min(maximum, element.scrollLeft + step)
            : element.scrollLeft <= 5 ? maximum : Math.max(0, element.scrollLeft - step),
        behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
    });
}
function stop() { clearInterval(timer); }
function start() {
    stop();
    if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) timer = setInterval(() => move(1), 3500);
}
onMounted(start);
onBeforeUnmount(stop);
</script>

<template>
    <div
        v-if="categories.length"
        class="relative"
        @mouseenter="stop" @mouseleave="start"
        @touchstart.passive="stop" @touchend.passive="start"
        @focusin="stop" @focusout="start"
    >
        <button type="button" aria-label="Previous categories" @click="move(-1)"
            class="absolute left-0 top-[40%] z-10 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full border border-black bg-white text-black shadow-md transition duration-300 hover:bg-black hover:text-white sm:h-11 sm:w-11">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
        </button>
        <div
            ref="track"
            class="category-track flex snap-x snap-mandatory gap-0 overflow-x-auto py-2"
        >
            <Link
                v-for="category in categories"
                :key="category.id"
                :href="categoryUrl(category)"
                prefetch cache-for="15s"
                class="cat-item group shrink-0 basis-1/2 snap-start px-2 text-center sm:basis-1/3 md:basis-1/4 lg:basis-1/6 lg:px-3"
            >
                <div
                    class="mx-auto aspect-square w-full max-w-[180px] rounded-full border border-black/10 p-1.5 transition duration-500 group-hover:border-black"
                >
                    <div
                        class="h-full w-full overflow-hidden rounded-full bg-neutral-100"
                    >
                        <img
                            v-if="category.image"
                            :src="category.image"
                            :alt="category.name"
                            loading="lazy"
                            class="h-full w-full object-cover grayscale-[15%] transition duration-700 group-hover:scale-110 group-hover:grayscale-0"
                        />
                        <span
                            v-else
                            class="grid h-full place-items-center bg-blush px-2 text-sm font-semibold uppercase tracking-wider text-ink"
                        >
                            {{ category.name.slice(0, 2) }}
                        </span>
                    </div>
                </div>
                <h3
                    class="mt-4 text-[11px] font-semibold uppercase tracking-[0.14em] text-black sm:text-xs"
                >
                    {{ category.name }}
                </h3>
                <span
                    class="mx-auto mt-1.5 block h-px w-0 bg-black transition-all duration-300 group-hover:w-8"
                ></span>
            </Link>
        </div>
        <button type="button" aria-label="Next categories" @click="move(1)"
            class="absolute right-0 top-[40%] z-10 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full border border-black bg-white text-black shadow-md transition duration-300 hover:bg-black hover:text-white sm:h-11 sm:w-11">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
        </button>
    </div>
</template>

<style scoped>
.category-track { scrollbar-width: none; -ms-overflow-style: none; }
.category-track::-webkit-scrollbar { display: none; }
</style>
