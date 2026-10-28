<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Save } from '@lucide/vue';
import AdminLayout from '../Components/AdminLayout.vue';
import Field from '../Components/Field.vue';
import MediaPicker from '../Components/MediaPicker.vue';
import SummernoteEditor from '../Components/SummernoteEditor.vue';
const props=defineProps({record:Object});
const form=useForm({title:props.record?.title || 'Message from the Founder',founder_name:props.record?.founder_name || '',designation:props.record?.designation || '',image_path:'',content:props.record?.content || '',is_active:props.record?.is_active ?? true});
function save(){form.post('/admin/founder-messages'+(props.record ? '/'+props.record.id : ''),{preserveScroll:true});}
</script>
<template>
    <Head :title="record ? 'Edit founder message' : 'Add founder message'" /><AdminLayout title="Founder Message">
        <div class="page-heading"><div><span class="eyebrow">WEBSITE CONTENT</span><h1>{{ record ? 'Edit founder message' : 'Add founder message' }}</h1><p>A personal message from your leadership team.</p></div><Link href="/admin/founder-messages" class="btn">Back to messages</Link></div>
        <form @submit.prevent="save" class="founder-form">
            <section class="panel form-panel"><div class="form-grid"><Field id="founder_title" label="Message heading" :error="form.errors.title"><input id="founder_title" v-model="form.title" maxlength="180" required /></Field><Field id="founder_name" label="Founder name" :error="form.errors.founder_name"><input id="founder_name" v-model="form.founder_name" maxlength="150" required /></Field><Field id="founder_designation" label="Designation / company" :error="form.errors.designation"><input id="founder_designation" v-model="form.designation" maxlength="180" placeholder="Founder & CEO, FARBO" /></Field><label class="active-field"><input v-model="form.is_active" type="checkbox" />Show on website</label></div></section>
            <section class="panel form-panel"><Field id="founder_image" label="Founder photo" :error="form.errors.image_path" hint="Choose a portrait photo from Media or upload a new one."><img v-if="form.image_path || record?.image" class="portrait-preview" :src="form.image_path || record.image" alt="Founder photo preview" /><MediaPicker id="founder_image" v-model="form.image_path" file-input label="Choose founder photo" /></Field></section>
            <section class="panel form-panel"><Field label="Message" :error="form.errors.content"><SummernoteEditor id="founder_content" v-model="form.content" label="Founder message" placeholder="Write the founder's message…" :disabled="form.processing" /></Field></section>
            <div class="form-actions"><Link href="/admin/founder-messages" class="btn">Cancel</Link><button class="btn primary" type="submit" :disabled="form.processing"><Save :size="17" />{{ form.processing ? 'Saving…' : 'Save message' }}</button></div>
        </form>
    </AdminLayout>
</template>
<style scoped>.founder-form{display:grid;gap:24px;max-width:1120px}.portrait-preview{display:block;width:150px;height:190px;object-fit:cover;border-radius:12px;margin-bottom:16px}.active-field{display:flex;align-items:center;gap:10px;font-size:12px}.active-field input{width:auto}.form-actions{display:flex;justify-content:flex-end;gap:12px}</style>
