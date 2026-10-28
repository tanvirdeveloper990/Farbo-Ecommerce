<script setup>
import { inject, provide, watch, onMounted, nextTick } from "vue";
import { router, usePage } from '@inertiajs/vue3';
import { notify } from './notify';
import { trackPage } from './tracking';
const page = usePage();
let trackingMounted = false;
onMounted(() => { trackingMounted = true; trackPage(page); });
watch(() => page.url, async () => { if (trackingMounted) { await nextTick(); trackPage(page); } });
watch(() => [page.url, page.props.flash?.success, page.props.flash?.error], () => {
    if (page.props.flash?.success) notify(page.props.flash.success, 'success');
    if (page.props.flash?.error) notify(page.props.flash.error, 'error');
}, { immediate: true });
import { createStorefrontContext } from "./storefrontContext";
const storefront = inject("storefront", null);
if (!storefront) provide("storefront", createStorefrontContext());
function followInternalLink(event) {
    if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
    const link = event.target.closest('a[href]');
    if (!link || link.hasAttribute('download') || (link.target && link.target !== '_self')) return;
    const url = new URL(link.href, window.location.href);
    if (url.origin !== window.location.origin || url.pathname.startsWith('/admin')) return;
    if (url.pathname === window.location.pathname && url.search === window.location.search) {
        if (url.hash) return;
        event.preventDefault();
        window.scrollTo({ top: 0, behavior: 'smooth' });
        return;
    }
    event.preventDefault();
    router.visit(url.href);
}
</script>
<template>
    <div class="storefront bg-white font-sans text-ink" @click="followInternalLink">
        <main><slot /></main>
    </div>
</template>
