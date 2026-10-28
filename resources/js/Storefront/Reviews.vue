<script setup>
import { Head } from '@inertiajs/vue3';
import StorefrontLayout from './StorefrontLayout.vue';
import CustomerReviewsSection from './Components/CustomerReviewsSection.vue';
defineProps({ reviews: { type: Array, default: () => [] } });
</script>

<template>
    <Head title="Customer Reviews" />
    <StorefrontLayout>
        <CustomerReviewsSection :reviews="reviews" />
    </StorefrontLayout>
</template>
