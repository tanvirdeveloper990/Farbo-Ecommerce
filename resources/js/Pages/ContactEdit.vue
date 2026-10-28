<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ArrowLeft, Save } from "@lucide/vue";
import AdminLayout from "../Components/AdminLayout.vue";
import Field from "../Components/Field.vue";

const props = defineProps({ contact: Object });
const form = useForm({
    name: props.contact.name,
    phone_number: props.contact.phone_number,
    subject: props.contact.subject,
    description: props.contact.description,
});

function save() {
    form.put(`/admin/contacts/${props.contact.id}`);
}
</script>

<template>
    <Head :title="`Edit message from ${contact.name}`" />
    <AdminLayout title="Contacts">
        <Link :href="`/admin/contacts/${contact.id}`" class="back-link">
            <ArrowLeft :size="16" />Back to message
        </Link>
        <div class="page-heading">
            <div>
                <span class="eyebrow">CUSTOMER MESSAGE</span>
                <h1>Edit contact message</h1>
                <p>Update the submitted contact details and message.</p>
            </div>
        </div>
        <form class="panel form-panel contact-edit-form" @submit.prevent="save">
            <div class="contact-fields">
                <Field id="name" label="Name" :error="form.errors.name">
                    <input
                        id="name"
                        v-model="form.name"
                        maxlength="160"
                        required
                    />
                </Field>
                <Field
                    id="phone_number"
                    label="Phone number"
                    :error="form.errors.phone_number"
                >
                    <input
                        id="phone_number"
                        v-model="form.phone_number"
                        type="tel"
                        maxlength="40"
                        required
                    />
                </Field>
                <Field
                    id="subject"
                    label="Subject"
                    :error="form.errors.subject"
                >
                    <input
                        id="subject"
                        v-model="form.subject"
                        maxlength="500"
                        required
                    />
                </Field>
                <Field
                    id="description"
                    label="Description"
                    :error="form.errors.description"
                >
                    <textarea
                        id="description"
                        v-model="form.description"
                        rows="8"
                        maxlength="10000"
                        required
                    ></textarea>
                </Field>
            </div>
            <div class="contact-edit-actions">
                <Link
                    :href="`/admin/contacts/${contact.id}`"
                    class="btn secondary"
                    >Cancel</Link
                >
                <button class="btn primary" :disabled="form.processing">
                    <Save :size="16" />{{
                        form.processing ? "Saving…" : "Save changes"
                    }}
                </button>
            </div>
        </form>
    </AdminLayout>
</template>

<style scoped>
.contact-edit-form {
    max-width: 900px;
}
.contact-fields {
    display: grid;
    gap: 20px;
}
.contact-fields textarea {
    width: 100%;
    resize: vertical;
}
.contact-edit-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 24px;
}
.contact-edit-actions .btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
</style>
