<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import AdminLayout from "../Components/AdminLayout.vue";
import IntegrationSetupForm from "../Components/IntegrationSetupForm.vue";
defineProps({ steadfast: Object, pathao: Object, redx: Object });
const connection = useForm({});
function test(provider) { connection.post('/admin/courier/'+provider+'/test', {preserveScroll:true}); }
</script>
<template>
    <Head title="Courier setup" /><AdminLayout title="Courier setup"
        ><div class="page-heading">
            <div>
                <span class="eyebrow">DELIVERY CONNECTIONS</span>
                <h1>Courier setup</h1>
                <p>
                    Each courier has its own settings. Update only the
                    connection you need.
                </p>
            </div>
        </div>
        <section class="panel" style="padding:20px;margin-bottom:24px"><h3 style="font-weight:700">Verify saved connections</h3><p style="font-size:12px;color:#748479;margin-top:8px">Save credentials first, then test authentication without creating a parcel. REDX needs the pickup store ID, which differs from your shop ID.</p><div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:14px"><button v-for="provider in ['steadfast','pathao','redx']" :key="provider" class="btn secondary" type="button" :disabled="connection.processing" @click="test(provider)">Test {{ provider }}</button></div><p v-for="(error,key) in connection.errors" :key="key" style="color:#ab3547;font-size:12px;margin-top:12px">{{ error }}</p></section>
        <div class="courier-jumps">
            <a href="#steadfast">01 · Steadfast</a
            ><a href="#pathao">02 · Pathao</a><a href="#redx">03 · REDX</a>
        </div>
        <div class="setup-width">
            <div id="steadfast">
                <IntegrationSetupForm
                    :setup="steadfast"
                    endpoint="/admin/courier/steadfast"
                    number="01"
                />
            </div>
            <div id="pathao">
                <IntegrationSetupForm
                    :setup="pathao"
                    endpoint="/admin/courier/pathao"
                    number="02"
                />
            </div>
            <div id="redx">
                <IntegrationSetupForm
                    :setup="redx"
                    endpoint="/admin/courier/redx"
                    number="03"
                />
            </div></div
    ></AdminLayout>
</template>
<style scoped>
.setup-width {
    max-width: 1080px;
}
.setup-width > div {
    scroll-margin-top: 110px;
}
.courier-jumps {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 25px;
}
.courier-jumps a {
    font-size: 12px;
    color: #5c7352;
    background: #eaf0e5;
    border: 1px solid #dce5d4;
    border-radius: 8px;
    padding: 11px 17px;
}
.courier-jumps a:hover {
    background: #dfead7;
}
</style>
