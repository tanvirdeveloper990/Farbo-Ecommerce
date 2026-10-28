<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { Save } from '@lucide/vue';
import AdminLayout from '../Components/AdminLayout.vue';
import Field from '../Components/Field.vue';
import MediaPicker from '../Components/MediaPicker.vue';
import SummernoteEditor from '../Components/SummernoteEditor.vue';
const props = defineProps({ settings: Object });
const form = useForm({why_title: props.settings.why_title || '', why_summary: props.settings.why_summary || '', why_image_path: ''});
const save = () => form.post('/admin/why-choose-us', {preserveScroll: true, onSuccess: () => form.reset('why_image_path')});
</script>
<template>
    <Head title="Why Choose Us" />
    <AdminLayout title="Why Choose Us">
        <div class="page-heading"><div><span class="eyebrow">WEBSITE CONTENT</span><h1>Why Choose Us</h1><p>Update the summary and image shown on your homepage.</p></div><a href="/" target="_blank" rel="noopener" class="btn">View storefront ↗</a></div>
        <form class="panel form-panel" @submit.prevent="save">
            <Field id="why_title" label="Heading" :error="form.errors.why_title"><input id="why_title" v-model="form.why_title" maxlength="250" required /></Field>
            <Field id="why_summary" label="Summary" :error="form.errors.why_summary"><SummernoteEditor id="why_summary" v-model="form.why_summary" label="Why Choose Us summary" placeholder="Write your Why Choose Us content…" :disabled="form.processing" /></Field>
            <Field id="why_image" label="Image" :error="form.errors.why_image_path"><img v-if="form.why_image_path || settings.why_image" :src="form.why_image_path || settings.why_image" alt="Why Choose Us image" class="why-image" /><MediaPicker id="why_image" v-model="form.why_image_path" file-input label="Choose image" /></Field>
            <div class="form-actions"><button type="submit" class="btn primary" :disabled="form.processing"><Save :size="18" />{{ form.processing ? 'Saving…' : 'Save changes' }}</button></div>
        </form>
    </AdminLayout>
</template>
<style scoped>
form{display:grid;gap:24px;max-width:960px}.why-image{display:block;width:100%;max-width:440px;max-height:260px;object-fit:cover;border-radius:12px;margin-bottom:16px}.form-actions{display:flex;justify-content:flex-end;padding-top:20px;border-top:1px solid #e7eee9}
</style>
