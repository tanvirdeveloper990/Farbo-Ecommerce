<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import { Save, ChevronLeft } from "@lucide/vue";
import AdminLayout from "../Components/AdminLayout.vue";
import Field from "../Components/Field.vue";
const props = defineProps({ faq: Object });
const form = useForm({
    question: props.faq?.question || "",
    answer: props.faq?.answer || "",
    status: props.faq?.status ?? false,
});
function save() {
    if (props.faq) form.put("/admin/faqs/" + props.faq.id);
    else form.post("/admin/faqs");
}
</script>
<template>
    <Head :title="faq ? 'Edit FAQ' : 'Create FAQ'" /><AdminLayout title="FAQs"
        ><Link href="/admin/faqs" class="back-link"
            ><ChevronLeft :size="16" />All FAQs</Link
        >
        <div class="page-heading">
            <div>
                <span class="eyebrow">HELPFUL ANSWERS</span>
                <h1>{{ faq ? "Edit FAQ" : "Create FAQ" }}</h1>
                <p>Keep every answer clear and easy to understand.</p>
            </div>
        </div>
        <form class="panel form-panel faq-form" @submit.prevent="save">
            <Field
                id="faq-question"
                label="Question *"
                :error="form.errors.question"
                ><input
                    id="faq-question"
                    v-model="form.question"
                    required
                    maxlength="500"
                    placeholder="What would your customer like to know?" /></Field
            ><Field
                id="faq-answer"
                label="Answer *"
                :error="form.errors.answer"
            >
                <textarea
                    id="faq-answer"
                    v-model="form.answer"
                    required
                    rows="9"
                    maxlength="10000"
                    placeholder="Write a helpful answer…"
                ></textarea>
            </Field>
            <div class="answer-count">{{ form.answer.length }} / 10000</div>
            <Field id="faq-status" label="Status" :error="form.errors.status"
                ><select id="faq-status" v-model="form.status">
                    <option :value="false">Inactive</option>
                    <option :value="true">Active</option>
                </select></Field
            >
            <div class="faq-save">
                <button class="btn primary" :disabled="form.processing">
                    <Save :size="17" />{{
                        form.processing ? "Saving…" : "Save FAQ"
                    }}
                </button>
            </div>
        </form></AdminLayout
    >
</template>
<style scoped>
.back-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: #758770;
    margin-bottom: 20px;
}
.faq-form {
    max-width: 950px;
}
.faq-form textarea {
    width: 100%;
    resize: vertical;
    line-height: 1.8;
}
.answer-count {
    text-align: right;
    color: #94a08c;
    font-size: 11px;
    margin: -12px 0 22px;
}
.faq-save {
    display: flex;
    justify-content: flex-end;
    border-top: 1px solid #edf1e9;
    margin-top: 25px;
    padding-top: 25px;
}
</style>
