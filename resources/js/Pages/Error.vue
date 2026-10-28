<script setup>
import { Head, Link, usePage } from "@inertiajs/vue3";
import { Sprout, ArrowLeft } from "@lucide/vue";
const props = defineProps({ status: Number });
const messages = {
    403: [
        "This space is restricted.",
        "Your account does not have permission to access this page.",
    ],
    404: [
        "A little off the path.",
        "We couldn’t find that page. Let’s get you somewhere familiar.",
    ],
    429: [
        "Let’s take a little pause.",
        "Too many attempts. Please wait one minute before trying again.",
    ],
    500: ["Something needs our attention.", "Please try again in a moment."],
    503: [
        "A little work in progress.",
        "We’ll be back shortly. Thank you for your patience.",
    ],
};
const user = usePage().props.auth?.user;
</script>
<template>
    <Head :title="String(status)" />
    <div class="error-page">
        <span class="brand-mark"><Sprout /></span
        ><span class="eyebrow">{{ status }}</span>
        <h1>{{ messages[status]?.[0] }}</h1>
        <p>{{ messages[status]?.[1] }}</p>
        <Link
            :href="
                user ? (user.is_admin ? '/admin/dashboard' : '/dashboard') : '/'
            "
            class="btn primary"
            ><ArrowLeft :size="17" /> Back to
            {{ user ? "dashboard" : "home" }}</Link
        >
    </div>
</template>
