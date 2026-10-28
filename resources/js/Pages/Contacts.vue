<script setup>
import { ref } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import {
    Eye,
    Mail,
    MessageSquare,
    Pencil,
    Phone,
    Search,
    Trash2,
} from "@lucide/vue";
import Swal from "sweetalert2";
import AdminLayout from "../Components/AdminLayout.vue";

const props = defineProps({ records: Object, filters: Object });
const search = ref(props.filters?.search || "");

function filterContacts() {
    router.get(
        "/admin/contacts",
        { search: search.value || undefined },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}

async function remove(contact) {
    const answer = await Swal.fire({
        title: "Delete this message?",
        text: `${contact.name}'s message will be permanently removed.`,
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
        router.delete("/admin/contacts/" + contact.id, {
            preserveScroll: true,
        });
}
</script>

<template>
    <Head title="Contacts" />
    <AdminLayout title="Contacts">
        <div class="page-heading">
            <div>
                <span class="eyebrow">CUSTOMER MESSAGES</span>
                <h1>Contacts</h1>
                <p>Review and manage messages submitted by your customers.</p>
            </div>
        </div>

        <section class="panel contact-panel">
            <div class="contact-toolbar">
                <div class="table-title">
                    <h3>Contact messages</h3>
                    <span class="count-badge">{{ records.total }}</span>
                </div>
                <form class="admin-filter-bar" @submit.prevent="filterContacts">
                    <label class="admin-search">
                        <Search :size="17" />
                        <input
                            v-model="search"
                            type="search"
                            placeholder="Search name, phone or subject"
                        />
                    </label>
                    <button class="btn primary" type="submit">Search</button>
                    <button
                        v-if="search"
                        class="btn secondary"
                        type="button"
                        @click="
                            search = '';
                            filterContacts();
                        "
                    >
                        Clear
                    </button>
                </form>
            </div>
            <div class="contact-table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Phone number</th>
                            <th>Subject</th>
                            <th>Received</th>
                            <th class="actions-heading">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="contact in records.data" :key="contact.id">
                            <td class="contact-name">{{ contact.name }}</td>
                            <td>
                                <a
                                    class="phone-link"
                                    :href="'tel:' + contact.phone_number"
                                >
                                    <Phone :size="14" />{{
                                        contact.phone_number
                                    }}
                                </a>
                            </td>
                            <td class="subject-cell">{{ contact.subject }}</td>
                            <td class="date-cell">
                                {{
                                    new Date(
                                        contact.created_at,
                                    ).toLocaleString()
                                }}
                            </td>
                            <td class="actions-cell">
                                <Link
                                    class="icon-button"
                                    :href="'/admin/contacts/' + contact.id"
                                    :aria-label="
                                        'View message from ' + contact.name
                                    "
                                    ><Eye :size="17"
                                /></Link>
                                <Link
                                    class="icon-button"
                                    :href="
                                        '/admin/contacts/' +
                                        contact.id +
                                        '/edit'
                                    "
                                    :aria-label="
                                        'Edit message from ' + contact.name
                                    "
                                    ><Pencil :size="17"
                                /></Link>
                                <button
                                    class="icon-button danger"
                                    :aria-label="
                                        'Delete message from ' + contact.name
                                    "
                                    @click="remove(contact)"
                                >
                                    <Trash2 :size="17" />
                                </button>
                            </td>
                        </tr>
                        <tr v-if="!records.data.length">
                            <td colspan="5">
                                <div class="contact-empty">
                                    <MessageSquare :size="36" />
                                    <h3>
                                        {{
                                            records.total
                                                ? "No matching messages"
                                                : "No contact messages yet"
                                        }}
                                    </h3>
                                    <p>
                                        {{
                                            records.total
                                                ? "Try changing or clearing your search."
                                                : "Messages submitted from the storefront contact page will appear here."
                                        }}
                                    </p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="table-footer">
                <span>
                    {{ records.from || 0 }}–{{ records.to || 0 }} of
                    {{ records.total }} messages
                </span>
                <div class="pagination">
                    <template v-for="(link, i) in records.links" :key="i">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            :class="{ selected: link.active }"
                            preserve-scroll
                            >{{
                                i === 0
                                    ? "Previous"
                                    : i === records.links.length - 1
                                      ? "Next"
                                      : link.label
                            }}</Link
                        >
                        <span v-else>{{
                            i === 0
                                ? "Previous"
                                : i === records.links.length - 1
                                  ? "Next"
                                  : link.label
                        }}</span>
                    </template>
                </div>
            </div>
        </section>
    </AdminLayout>
</template>

<style scoped>
.contact-panel {
    overflow: hidden;
}
.contact-toolbar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 20px 24px;
}
.contact-toolbar > .admin-filter-bar {
    flex: 1 1 420px;
    padding: 0;
    border: 0;
}
.contact-table-wrap {
    overflow-x: auto;
}
table {
    width: 100%;
    min-width: 780px;
    border-collapse: collapse;
}
th {
    padding: 16px 20px;
    background: #f8faf6;
    color: #809080;
    font-size: 11px;
    letter-spacing: 0.08em;
    text-align: left;
    text-transform: uppercase;
}
td {
    padding: 18px 20px;
    border-top: 1px solid #edf0e9;
    font-size: 13px;
    vertical-align: middle;
}
.contact-name {
    font-weight: 600;
    color: #334c39;
}
.phone-link {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    color: #526858;
    white-space: nowrap;
}
.subject-cell {
    max-width: 300px;
    overflow-wrap: anywhere;
}
.date-cell {
    color: #78857a;
    white-space: nowrap;
}
.actions-heading,
.actions-cell {
    text-align: right;
    white-space: nowrap;
}
.actions-cell .icon-button + .icon-button {
    margin-left: 5px;
}
.contact-empty {
    padding: 48px 20px;
    text-align: center;
    color: #849184;
}
.contact-empty svg {
    margin: 0 auto 14px;
}
.contact-empty h3 {
    margin-bottom: 7px;
    color: #40593b;
    font-size: 16px;
}
.contact-empty p {
    font-size: 13px;
}
@media (max-width: 650px) {
    .contact-toolbar {
        padding: 18px;
    }
}
</style>
