<script setup>
import { computed, ref } from "vue";
import { Head, router, useForm } from "@inertiajs/vue3";
import { Pencil, Plus, Search, Trash2, X } from "@lucide/vue";
import Swal from "sweetalert2";
import AdminLayout from "../Components/AdminLayout.vue";
import Field from "../Components/Field.vue";

const props = defineProps({
    type: String,
    title: String,
    records: Array,
});
const modal = ref(false);
const editing = ref(null);
const search = ref("");
const status = ref("");
const filteredRecords = computed(() =>
    props.records.filter((record) => {
        const matchesSearch = record.name
            .toLowerCase()
            .includes(search.value.trim().toLowerCase());
        const matchesStatus =
            status.value === "" ||
            record.is_active === (status.value === "active");
        return matchesSearch && matchesStatus;
    }),
);
const form = useForm({ name: "", color: "#7b1e2e", is_active: true });

function open(record = null) {
    editing.value = record;
    form.clearErrors();
    form.name = record?.name || "";
    form.color = record?.color || "#7b1e2e";
    form.is_active = record?.is_active ?? true;
    modal.value = true;
}

function close() {
    if (!form.processing) modal.value = false;
}

function save() {
    const url =
        "/admin/" + props.type + (editing.value ? "/" + editing.value.id : "");
    form[editing.value ? "put" : "post"](url, {
        preserveScroll: true,
        onSuccess: () => {
            modal.value = false;
            form.reset();
        },
    });
}

async function remove(record) {
    const answer = await Swal.fire({
        title: "Delete " + record.name + "?",
        text: "This entry will be removed from your product options.",
        icon: "warning",
        showCancelButton: true,
        showCloseButton: true,
        confirmButtonText: "Yes, delete",
        cancelButtonText: "No, keep it",
        confirmButtonColor: "#b42318",
    });
    if (answer.isConfirmed)
        router.delete("/admin/" + props.type + "/" + record.id, {
            preserveScroll: true,
        });
}
</script>

<template>
    <Head :title="title" />
    <AdminLayout :title="title">
        <div class="page-heading">
            <div>
                <span class="eyebrow">PRODUCT OPTIONS</span>
                <h1>{{ title }}</h1>
                <p>
                    Manage the {{ title.toLowerCase() }} available for products.
                </p>
            </div>
            <button class="btn primary" @click="open()">
                <Plus :size="18" /> Add {{ title.slice(0, -1).toLowerCase() }}
            </button>
        </div>

        <section class="panel attribute-panel">
            <form class="admin-filter-bar" @submit.prevent>
                <label class="admin-search">
                    <Search :size="17" />
                    <input
                        v-model="search"
                        type="search"
                        :placeholder="'Search ' + title.toLowerCase()"
                    />
                </label>
                <select v-model="status" aria-label="Filter by status">
                    <option value="">All statuses</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </form>
            <div v-if="!filteredRecords.length" class="attribute-empty">
                <h3>
                    {{
                        records.length
                            ? "No matching " + title.toLowerCase()
                            : "No " + title.toLowerCase() + " yet"
                    }}
                </h3>
                <p>
                    {{
                        records.length
                            ? "Try changing or clearing your filters."
                            : "Add your first entry to use it in product forms."
                    }}
                </p>
            </div>
            <table v-else class="attribute-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th v-if="type === 'colors'">Color</th>
                        <th>Status</th>
                        <th class="attribute-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="record in filteredRecords" :key="record.id">
                        <td>
                            <span
                                v-if="type === 'colors'"
                                class="color-swatch"
                                :style="{ backgroundColor: record.color }"
                            ></span>
                            {{ record.name }}
                        </td>
                        <td v-if="type === 'colors'">{{ record.color }}</td>
                        <td>
                            <span
                                class="pill"
                                :class="{ 'pill-muted': !record.is_active }"
                                >{{
                                    record.is_active ? "Active" : "Inactive"
                                }}</span
                            >
                        </td>
                        <td class="attribute-actions">
                            <button
                                class="icon-button"
                                :aria-label="'Edit ' + record.name"
                                @click="open(record)"
                            >
                                <Pencil :size="17" />
                            </button>
                            <button
                                class="icon-button"
                                :aria-label="'Delete ' + record.name"
                                @click="remove(record)"
                            >
                                <Trash2 :size="17" />
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </section>

        <div v-if="modal" class="modal-overlay">
            <section
                class="modal attribute-modal"
                role="dialog"
                aria-modal="true"
            >
                <div class="modal-heading">
                    <div>
                        <span class="eyebrow">PRODUCT OPTIONS</span>
                        <h2>
                            {{ editing ? "Edit" : "Add" }}
                            {{ title.slice(0, -1) }}
                        </h2>
                    </div>
                    <button
                        class="icon-button"
                        aria-label="Close"
                        :disabled="form.processing"
                        @click="close"
                    >
                        <X />
                    </button>
                </div>
                <form @submit.prevent="save">
                    <Field
                        id="attribute-name"
                        label="Name"
                        :error="form.errors.name"
                    >
                        <input
                            id="attribute-name"
                            v-model="form.name"
                            required
                            maxlength="150"
                            :placeholder="
                                'Enter ' +
                                title.slice(0, -1).toLowerCase() +
                                ' name'
                            "
                        />
                    </Field>
                    <Field
                        v-if="type === 'colors'"
                        id="attribute-color"
                        label="Color"
                        :error="form.errors.color"
                        hint="Choose a color or enter a six-digit hex value."
                    >
                        <div class="color-input">
                            <input
                                id="attribute-color"
                                v-model="form.color"
                                type="color"
                            /><input
                                v-model="form.color"
                                pattern="^#[0-9A-Fa-f]{6}$"
                                required
                            />
                        </div>
                    </Field>
                    <Field
                        id="attribute-status"
                        label="Status"
                        :error="form.errors.is_active"
                    >
                        <select id="attribute-status" v-model="form.is_active">
                            <option :value="true">Active</option>
                            <option :value="false">Inactive</option>
                        </select>
                    </Field>
                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn secondary"
                            :disabled="form.processing"
                            @click="close"
                        >
                            Cancel
                        </button>
                        <button class="btn primary" :disabled="form.processing">
                            {{
                                form.processing
                                    ? "Saving…"
                                    : "Save " + title.slice(0, -1).toLowerCase()
                            }}
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </AdminLayout>
</template>

<style scoped>
.attribute-panel {
    overflow-x: auto;
}
.attribute-table {
    width: 100%;
    border-collapse: collapse;
}
.attribute-table th,
.attribute-table td {
    padding: 16px 20px;
    border-bottom: 1px solid #e7eee9;
    text-align: left;
}
.attribute-table th {
    color: #6b7b70;
    font-size: 12px;
    letter-spacing: 0.12em;
    text-transform: uppercase;
}
.attribute-actions {
    text-align: right !important;
    white-space: nowrap;
}
.color-swatch {
    display: inline-block;
    width: 20px;
    height: 20px;
    margin-right: 10px;
    border: 1px solid #dce5df;
    border-radius: 50%;
    vertical-align: middle;
}
.attribute-empty {
    padding: 54px 24px;
    text-align: center;
}
.attribute-empty p {
    color: #718078;
}
.pill-muted {
    background: #eef1ef;
    color: #718078;
}
.attribute-modal form {
    padding: 0 24px 24px;
}
.color-input {
    display: flex;
    gap: 10px;
    align-items: center;
}
.color-input input[type="color"] {
    width: 48px;
    height: 42px;
    padding: 3px;
}
.color-input input[type="text"],
.color-input input:not([type]) {
    flex: 1;
}
.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}
</style>
