<script setup>
import { Head } from "@inertiajs/vue3";
import AdminLayout from "../Components/AdminLayout.vue";
import IntegrationSetupForm from "../Components/IntegrationSetupForm.vue";
defineProps({ setup: Object });
</script>
<template>
    <Head title="Payments" /><AdminLayout title="Payments"
        ><div class="page-heading">
            <div>
                <span class="eyebrow">PAYMENT CONFIGURATION</span>
                <h1>Payments</h1>
                <p>Test mode opens the SSLcommerz sandbox. Live mode requires your approved merchant credentials.</p>
            </div>
        </div>
        <div class="setup-width">
            <IntegrationSetupForm
                :setup="setup"
                endpoint="/admin/payments/sslcommerz"
                number="S"
            /></div
    ></AdminLayout>
</template>
<style scoped>
.setup-width {
    max-width: 1080px;
}
</style>
