<script setup>
import { Head } from "@inertiajs/vue3";
import AdminLayout from "../Components/AdminLayout.vue";
import IntegrationSetupForm from "../Components/IntegrationSetupForm.vue";
defineProps({ setup: Object });
</script>
<template>
    <Head title="SMTP setup" /><AdminLayout title="SMTP setup"
        ><div class="page-heading">
            <div>
                <span class="eyebrow">EMAIL CONFIGURATION</span>
                <h1>SMTP setup</h1>
                <p>Update your SMTP account details in one place.</p>
            </div>
        </div>
        <div class="setup-width">
            <IntegrationSetupForm
                :setup="setup"
                endpoint="/admin/smtp"
                number="M"
            /></div
    ></AdminLayout>
</template>
<style scoped>
.setup-width {
    max-width: 1080px;
}
</style>
