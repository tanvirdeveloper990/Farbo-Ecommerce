<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref, watch, nextTick, onMounted, onBeforeUnmount } from 'vue';
const props = defineProps({html: String, title: String});
const content = ref('');
let cleanup = () => {};
let revision = 0;
async function render() {
    const current = ++revision;
    cleanup();
    const fragment = document.createElement('template');
    fragment.innerHTML = props.html;
    const scripts = [...fragment.content.querySelectorAll('script')].map(script => {
        const code = script.textContent;
        script.remove();
        return code;
    });
    content.value = fragment.innerHTML;
    await nextTick();
    if (current !== revision) return;
    const lifecycle = new AbortController();
    window.farboPageSignal = lifecycle.signal;
    const sliderCleanup = window.farboInitSliders?.() || (() => {});
    cleanup = () => { lifecycle.abort(); sliderCleanup(); };
    scripts.forEach(code => { if (code.trim()) new Function(code)(); });
    window.farboCart?.render();
    window.dispatchEvent(new Event('farbo-wishlist-changed'));
}
window.farboNavigate = url => router.visit(url);
onMounted(render);
watch(() => props.html, render);
onBeforeUnmount(() => { ++revision; cleanup(); });
</script>
<template><Head :title="title" /><div v-html="content" /></template>
