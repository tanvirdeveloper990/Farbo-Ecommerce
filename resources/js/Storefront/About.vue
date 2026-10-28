<script setup>
import { Head } from '@inertiajs/vue3';
import StorefrontLayout from './StorefrontLayout.vue';
import PolicyContent from './Components/PolicyContent.vue';
defineProps({policy: Object});
</script>
<template><Head title="About Us | FARBO" /><StorefrontLayout><div class="about-page"><PolicyContent :policy="policy" /></div></StorefrontLayout></template>
<style scoped>.about-page{min-height:60vh;background:linear-gradient(180deg,#f5f9fc 0,#fff 240px);color:#283747}</style>
