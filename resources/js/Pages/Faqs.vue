<script setup>
import { ref } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import {
    Plus,
    Pencil,
    Trash2,
    ChevronDown,
    CircleHelp,
    Search,
} from "@lucide/vue";
import Swal from "sweetalert2";
import AdminLayout from "../Components/AdminLayout.vue";
const props = defineProps({ records: Object, filters: Object });
const expanded = ref(null);
const search = ref(props.filters?.search || "");
const status = ref(props.filters?.status || "");
function filterFaqs() {
    router.get(
        "/admin/faqs",
        {
            search: search.value || undefined,
            status: status.value || undefined,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}
async function remove(faq) {
    const answer = await Swal.fire({
        title: "Delete this FAQ?",
        text: faq.question,
        icon: "warning",
        showCancelButton: true,
        showCloseButton: true,
        confirmButtonText: "Yes, delete",
        cancelButtonText: "No, keep it",
        allowOutsideClick: false,
        allowEscapeKey: false,
        confirmButtonColor: "#b42318",
    });
    if (answer.isConfirmed)
        router.delete("/admin/faqs/" + faq.id, { preserveScroll: true });
}
</script>
<template>
    <Head title="FAQs" /><AdminLayout title="FAQs"
        ><div class="page-heading">
            <div>
                <span class="eyebrow">YOUR KNOWLEDGE CORNER</span>
                <h1>Frequently asked questions</h1>
                <p>Helpful answers, kept together and easy to update.</p>
            </div>
            <Link href="/admin/faqs/create" class="btn primary"
                ><Plus :size="18" />Create FAQ</Link
            >
        </div>
        <section class="panel">
            <form class="admin-filter-bar" @submit.prevent="filterFaqs">
                <label class="admin-search">
                    <Search :size="17" />
                    <input
                        v-model="search"
                        type="search"
                        placeholder="Search question or answer"
                    />
                </label>
                <select v-model="status" aria-label="Filter FAQs by status">
                    <option value="">All statuses</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
                <button class="btn primary" type="submit">Apply filters</button>
                <button
                    v-if="search || status"
                    class="btn secondary"
                    type="button"
                    @click="
                        search = '';
                        status = '';
                        filterFaqs();
                    "
                >
                    Clear
                </button>
            </form>
            <div v-if="!records.data.length" class="faq-empty">
                <CircleHelp :size="36" />
                <h3>
                    {{
                        records.total
                            ? "No matching FAQs"
                            : "A little clarity goes a long way"
                    }}
                </h3>
                <p>
                    {{
                        records.total
                            ? "Try changing or clearing your filters."
                            : "Add your first question and answer."
                    }}
                </p>
                <Link
                    v-if="!records.total"
                    href="/admin/faqs/create"
                    class="btn primary"
                    >Create first FAQ</Link
                >
            </div>
            <article v-for="faq in records.data" :key="faq.id" class="faq-row">
                <div class="faq-top">
                    <button
                        class="faq-question"
                        :aria-expanded="expanded === faq.id"
                        :aria-controls="'answer-' + faq.id"
                        @click="expanded = expanded === faq.id ? null : faq.id"
                    >
                        <span>{{ faq.question }}</span
                        ><ChevronDown
                            :size="18"
                            :class="{ rotated: expanded === faq.id }"
                        /></button
                    ><span class="faq-status" :class="{ active: faq.status }">{{
                        faq.status ? "Active" : "Inactive"
                    }}</span>
                    <div class="faq-actions">
                        <Link
                            :href="'/admin/faqs/' + faq.id + '/edit'"
                            class="icon-button"
                            :aria-label="'Edit ' + faq.question"
                            ><Pencil :size="17" /></Link
                        ><button
                            class="icon-button"
                            :aria-label="'Delete ' + faq.question"
                            @click="remove(faq)"
                        >
                            <Trash2 :size="17" />
                        </button>
                    </div>
                </div>
                <p
                    :id="'answer-' + faq.id"
                    class="faq-answer"
                    :class="{ clamped: expanded !== faq.id }"
                >
                    {{ faq.answer }}
                </p>
            </article>
            <div class="table-footer">
                <span
                    >{{ records.from || 0 }}–{{ records.to || 0 }} of
                    {{ records.total }} FAQs</span
                >
                <div class="pagination">
                    <template v-for="(link, i) in records.links" :key="i"
                        ><Link
                            v-if="link.url"
                            :href="link.url"
                            :class="{ selected: link.active }"
                            >{{
                                i === 0
                                    ? "Previous"
                                    : i === records.links.length - 1
                                      ? "Next"
                                      : link.label
                            }}</Link
                        ><span v-else>{{
                            i === 0
                                ? "Previous"
                                : i === records.links.length - 1
                                  ? "Next"
                                  : link.label
                        }}</span></template
                    >
                </div>
            </div>
        </section></AdminLayout
    >
</template>
<style scoped>
.faq-row {
    padding: 25px 28px;
    border-bottom: 1px solid #edf1e9;
}
.faq-top {
    display: flex;
    align-items: center;
    gap: 20px;
}
.faq-question {
    display: flex;
    flex: 1;
    min-width: 0;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    border: 0;
    background: none;
    text-align: left;
    font-size: 14px;
    font-weight: 600;
    color: #334c39;
    cursor: pointer;
    padding: 0;
}
.faq-question span {
    overflow-wrap: anywhere;
}
.faq-question svg {
    flex-shrink: 0;
    color: #91a088;
    transition: transform 0.2s;
}
.faq-question .rotated {
    transform: rotate(180deg);
}
.faq-status {
    font-size: 10px;
    border-radius: 20px;
    padding: 6px 10px;
    background: #f0f1ec;
    color: #8a9283;
}
.faq-status.active {
    background: #e5f2e4;
    color: #3f793c;
}
.faq-actions {
    display: flex;
    gap: 6px;
}
.faq-answer {
    white-space: pre-line;
    overflow-wrap: anywhere;
    color: #7c8875;
    font-size: 13px;
    line-height: 1.9;
    margin-top: 15px;
    max-width: 900px;
}
.faq-answer.clamped {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.faq-empty {
    text-align: center;
    padding: 50px 20px;
    color: #86937f;
}
.faq-empty svg {
    margin: auto;
}
.faq-empty h3 {
    color: #40593b;
    margin: 16px 0 8px;
}
.faq-empty .btn {
    margin-top: 23px;
}
.table-footer {
    flex-wrap: wrap;
    gap: 12px;
}
.pagination {
    flex-wrap: wrap;
}
@media (max-width: 650px) {
    .faq-row {
        padding: 20px;
    }
    .faq-top {
        gap: 10px;
        flex-wrap: wrap;
    }
    .faq-question {
        flex-basis: 100%;
        margin-bottom: 5px;
    }
    .faq-actions {
        margin-left: auto;
    }
}
</style>
