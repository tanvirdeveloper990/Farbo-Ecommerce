<script setup>
import { computed, inject, ref } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
const props = defineProps({ mobile: Boolean });
const { closePanel } = inject("storefront");
const expanded = ref(null);
const menu = computed(() =>
    ((props.mobile ? usePage().props.sidebarCategories : usePage().props.storeCategories) || []).map((category) => ({
        ...category,
        links: category.subcategories || [],
    })),
);
const visibleMenu = computed(() => menu.value.slice(0, 10));
const moreMenu = computed(() => menu.value.slice(10));
const moreOpen = ref(false);
const categoryHref = (category, subcategory = null) =>
    `/categories/${encodeURIComponent(category.slug || category.id)}` +
    (subcategory ? `?subcategory=${subcategory.id}` : "");
</script>
<template>
    <ul v-if="mobile" class="no-scrollbar flex-1 overflow-y-auto px-5">
        <li
            v-for="item in menu"
            :key="item.id"
            class="border-b border-stone/70"
        >
            <button
                v-if="item.links.length"
                @click="expanded = expanded === item.name ? null : item.name"
                :aria-expanded="expanded === item.name"
                class="flex w-full items-center justify-between py-4 text-left text-[13px] font-semibold uppercase tracking-[0.1em]"
            >
                {{ item.name
                }}<span>{{ expanded === item.name ? "−" : "+" }}</span>
            </button>
            <Link prefetch cache-for="15s"
                v-else
                :href="categoryHref(item)"
                @click="closePanel"
                class="flex py-4 text-[13px] font-semibold uppercase tracking-[0.1em]"
                >{{ item.name }}</Link
            >
            <ul
                v-if="item.links && expanded === item.name"
                class="space-y-1 pb-4 pl-3"
            >
                <li v-for="link in item.links" :key="link.id">
                    <Link prefetch cache-for="15s"
                        :href="categoryHref(item, link)"
                        @click="closePanel"
                        class="block py-1.5 text-sm text-muted hover:text-maroon"
                        >{{ link.name }}</Link
                    >
                </li>
            </ul>
        </li>
    </ul>
    <ul
        v-else
        class="relative mx-auto flex max-w-7xl items-center justify-center gap-4 px-4 xl:gap-7"
    >
        <li v-for="item in visibleMenu" :key="item.id" class="group">
            <Link prefetch cache-for="15s"
                :href="categoryHref(item)"
                class="relative flex items-center gap-1 py-4 text-[11px] font-semibold uppercase tracking-[0.12em] transition-colors hover:text-maroon xl:text-[12px]"
                >{{ item.name
                }}<svg
                    v-if="item.links.length"
                    class="h-3 w-3 transition-transform group-hover:rotate-180"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.2"
                    viewBox="0 0 24 24"
                >
                    <path d="M6 9l6 6 6-6" /></svg
                ><span
                    class="absolute bottom-2.5 left-0 h-[2px] w-0 bg-maroon transition-all group-hover:w-full"
                ></span
            ></Link>
            <div
                v-if="item.links.length"
                class="invisible absolute left-1/2 top-full z-50 w-[760px] max-w-[95vw] -translate-x-1/2 translate-y-3 opacity-0 transition-all duration-300 group-hover:visible group-hover:translate-y-0 group-hover:opacity-100 group-focus-within:visible group-focus-within:translate-y-0 group-focus-within:opacity-100"
            >
                <div
                    class="flex gap-8 border-t-2 border-ink bg-white p-6 shadow-2xl shadow-black/10"
                >
                    <Link prefetch cache-for="15s"
                        :href="categoryHref(item)"
                        class="relative block w-52 shrink-0 overflow-hidden"
                        ><img
                            v-if="item.image"
                            :src="item.image"
                            :alt="item.name"
                            loading="lazy"
                            class="h-64 w-full object-cover"
                        /><span
                            v-else
                            class="grid h-64 w-full place-items-center bg-blush px-4 text-center font-serif text-xl text-ink"
                            >{{ item.name }}</span
                        ><span
                            class="absolute bottom-3 left-3 bg-white px-3 py-1.5 text-[11px] font-medium"
                            >Shop {{ item.name }}</span
                        ></Link
                    >
                    <div class="flex flex-1 flex-col">
                        <p class="mb-2 font-serif text-2xl">{{ item.name }}</p>
                        <ul class="grid grid-cols-2 gap-x-8">
                            <li v-for="link in item.links" :key="link.id">
                                <Link prefetch cache-for="15s"
                                    :href="categoryHref(item, link)"
                                    class="flex border-b border-stone py-3 text-sm text-neutral-700 hover:text-maroon"
                                    >{{ link.name }}</Link
                                >
                            </li>
                        </ul>
                        <Link prefetch cache-for="15s"
                            :href="categoryHref(item)"
                            class="mt-auto self-start pt-5 text-sm font-medium underline decoration-maroon underline-offset-4"
                            >View all {{ item.name }}</Link
                        >
                    </div>
                </div>
            </div>
        </li>
        <li v-if="moreMenu.length" class="relative" @keydown.esc="moreOpen = false">
            <button type="button" class="flex items-center gap-1 py-4 text-[11px] font-semibold uppercase tracking-[0.12em] hover:text-maroon xl:text-[12px]"
                :aria-expanded="moreOpen" aria-controls="more-categories" @click="moreOpen = !moreOpen">
                More <span aria-hidden="true">{{ moreOpen ? '−' : '+' }}</span>
            </button>
            <ul v-if="moreOpen" id="more-categories" class="absolute right-0 top-full z-50 max-h-96 w-64 overflow-y-auto border-t-2 border-ink bg-white p-3 shadow-xl">
                <li v-for="item in moreMenu" :key="item.id">
                    <Link prefetch cache-for="15s" :href="categoryHref(item)" class="block px-3 py-2 text-sm hover:bg-blush hover:text-maroon" @click="moreOpen = false">{{ item.name }}</Link>
                    <ul v-if="item.links.length" class="pl-4">
                        <li v-for="link in item.links" :key="link.id">
                            <Link prefetch cache-for="15s" :href="categoryHref(item, link)" class="block px-3 py-1.5 text-xs text-muted hover:text-maroon" @click="moreOpen = false">{{ link.name }}</Link>
                        </li>
                    </ul>
                </li>
            </ul>
        </li>
    </ul>
</template>
