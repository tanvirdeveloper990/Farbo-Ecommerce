<script setup>
import { watch } from "vue";
import { Head, useForm } from "@inertiajs/vue3";
import AdminLayout from "../Components/AdminLayout.vue";
import SummernoteEditor from "../Components/SummernoteEditor.vue";
const props = defineProps({ policy: Object });
const form = useForm({ content: props.policy.content || "" });
watch(
    () => props.policy,
    (policy) => {
        form.content = policy.content || "";
        form.clearErrors();
    },
);
function save() {
    form.put("/admin/policies/" + props.policy.slug, { preserveScroll: true });
}
</script>
<template>
    <Head :title="policy.title" /><AdminLayout :title="policy.title"
        ><div class="page-heading">
            <div>
                <span class="eyebrow">{{ policy.slug === 'about-us' ? 'WEBSITE CONTENT' : 'LEGAL & POLICY' }}</span>
                <h1>{{ policy.title }}</h1>
            </div>
        </div>
        <form class="panel form-panel" @submit.prevent="save">
            <SummernoteEditor
                :key="policy.slug"
                v-model="form.content"
                :disabled="form.processing"
            />
            <p v-if="form.errors.content" class="field-error" role="alert">
                {{ form.errors.content }}
            </p>
            <div class="policy-save">
                <button class="btn primary" :disabled="form.processing">
                    {{ form.processing ? "Updating…" : (policy.slug === 'about-us' ? 'Save About Us' : 'Update policy') }}
                </button>
            </div>
        </form></AdminLayout
    >
</template>
<style scoped>
.policy-save {
    display: flex;
    justify-content: flex-end;
    margin-top: 24px;
}
</style>
