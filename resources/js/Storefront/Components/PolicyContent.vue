<script setup>
import { Link } from '@inertiajs/vue3';
defineProps({ policy: Object });
</script>
<template>
    <section class="mx-auto max-w-4xl px-5 py-12 sm:py-20">
        <Link href="/" prefetch cache-for="15s" class="text-sm text-muted">← Back to shop</Link>
        <h1 class="mt-6 mb-8 font-serif text-4xl sm:text-5xl">
            {{ policy.title }}
        </h1>
        <article
            v-if="
                policy.content && policy.content.replace(/<[^>]*>/g, '').trim()
            "
            class="policy-content"
            v-html="policy.content"
        ></article>
        <p v-else class="text-muted">This policy will be available soon.</p>
    </section>
</template>
<style>
.policy-content {
    line-height: 1.85;
    overflow-wrap: anywhere;
}
.policy-content p,
.policy-content ul,
.policy-content ol,
.policy-content blockquote,
.policy-content table {
    margin-bottom: 1.2em;
}
.policy-content h2 {
    font-size: 1.7em;
    margin: 1.3em 0 0.6em;
    font-weight: 600;
}
.policy-content h3 {
    font-size: 1.35em;
    margin: 1em 0 0.5em;
    font-weight: 600;
}
.policy-content h4 {
    font-size: 1.1em;
    font-weight: 600;
}
.policy-content ul {
    list-style: disc;
    padding-left: 1.5em;
}
.policy-content ol {
    list-style: decimal;
    padding-left: 1.5em;
}
.policy-content a {
    color: #E30613;
    text-decoration: underline;
}
.policy-content blockquote {
    border-left: 3px solid #ddd;
    padding-left: 20px;
}
.policy-content table {
    display: block;
    overflow-x: auto;
    border-collapse: collapse;
    max-width: 100%;
}
.policy-content td,
.policy-content th {
    border: 1px solid #ddd;
    padding: 10px;
}
</style>
