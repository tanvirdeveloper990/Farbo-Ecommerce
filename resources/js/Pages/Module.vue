<script setup>
import { Head, Link } from "@inertiajs/vue3";
import { Package, ArrowLeft, Check, Layers } from "@lucide/vue";
import AdminLayout from "../Components/AdminLayout.vue";
const props = defineProps({ title: String, module: String });
const descriptions = {
    categories: "Bring structure to your collection.",
    brands: "A home for the brands you love.",
    colors: "Make every shade part of the story.",
    sizes: "The perfect fit starts here.",
    products: "Build a collection worth discovering.",
    courier: "Thoughtful delivery, from your store to their door.",
    smtp: "Keep every conversation connected.",
    media: "Your visual library, beautifully organized.",
    pages: "Tell your story, one page at a time.",
    blogs: "Ideas, inspiration, and stories worth sharing.",
    testimonials: "Let your customers tell their stories.",
    payments: "A simpler way to manage every payment.",
    orders: "Every order is the start of something good.",
    contacts: "Keep your customer conversations close.",
    faqs: "Helpful answers, all in one place.",
    "landing-page": "Make a thoughtful first impression.",
    settings: "The small details that make your store yours.",
};
</script>
<template>
    <Head :title="title" /><AdminLayout :title="title"
        ><div class="page-heading">
            <div>
                <span class="eyebrow">YOUR STORE, TAKING SHAPE</span>
                <h1>{{ title }}</h1>
                <p>{{ descriptions[module] }}</p>
            </div>
            <span class="pill">UI PREVIEW</span>
        </div>
        <section class="panel module-panel">
            <div class="module-art"><Layers :size="60" stroke-width="1" /></div>
            <span class="eyebrow">ROOM FOR WHAT’S NEXT</span>
            <h2>Your {{ title.toLowerCase() }} workspace.</h2>
            <p>
                This space is ready for the next phase of your store.<br />We’ll
                connect its data and management tools when you’re ready.
            </p>
            <div class="module-state">
                <Check :size="17" /> Navigation & layout ready
                <span>·</span> Functionality coming next
            </div>
            <Link href="/admin/dashboard" class="btn secondary"
                ><ArrowLeft :size="17" /> Back to dashboard</Link
            >
        </section></AdminLayout
    >
</template>
