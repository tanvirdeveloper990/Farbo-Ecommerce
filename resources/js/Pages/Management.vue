<script setup>
import { computed, ref, watch, nextTick, onBeforeUnmount } from "vue";
import { Head, Link, useForm, usePage, router } from "@inertiajs/vue3";
import {
    Plus,
    Search,
    Pencil,
    Trash2,
    X,
    Users,
    ShieldCheck,
    KeyRound,
    ArrowRight,
} from "@lucide/vue";
import Swal from "sweetalert2";
import AdminLayout from "../Components/AdminLayout.vue";
import Field from "../Components/Field.vue";
import PasswordInput from "../Components/PasswordInput.vue";
const props = defineProps({
    type: String,
    records: Object,
    roles: Array,
    permissions: Array,
    search: String,
});
const page = usePage();
const owner = computed(() => page.props.auth.user.is_super_admin);
const title = computed(
    () => props.type.charAt(0).toUpperCase() + props.type.slice(1),
);
const singular = computed(
    () =>
        ({ users: "user", roles: "role", permissions: "permission" })[
            props.type
        ],
);
const modal = ref(false);
const editing = ref(null);
const query = ref(props.search || "");
const dialog = ref(null);
let previousFocus;
const form = useForm({
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
    is_admin: false,
    role_id: null,
    description: "",
    permission_ids: [],
});
watch(
    () => props.type,
    () => {
        query.value = props.search || "";
        close();
    },
);
function close() {
    modal.value = false;
    document.body.style.overflow = "";
    previousFocus?.focus();
}
function open(record = null) {
    previousFocus = document.activeElement;
    editing.value = record?.id || null;
    form.reset();
    form.clearErrors();
    if (record) {
        form.name = record.name;
        form.email = record.email || "";
        form.is_admin = !!record.is_admin;
        form.role_id = record.role_id || null;
        form.description = record.description || "";
        form.permission_ids = record.permissions?.map((p) => p.id) || [];
    }
    modal.value = true;
    document.body.style.overflow = "hidden";
    nextTick(() => dialog.value?.querySelector("input")?.focus());
}
function trap(event) {
    if (event.key === "Escape") {
        event.stopPropagation();
        return;
    }
    if (event.key !== "Tab") return;
    const elements = dialog.value.querySelectorAll(
        "button:not(:disabled),input,select,textarea",
    );
    const first = elements[0],
        last = elements[elements.length - 1];
    if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
    }
}
onBeforeUnmount(() => (document.body.style.overflow = ""));
function save() {
    form[editing.value ? "put" : "post"](
        "/admin/management/" +
            props.type +
            (editing.value ? "/" + editing.value : ""),
        {
            preserveScroll: true,
            onSuccess: () => {
                form.reset("password", "password_confirmation");
                if (!editing.value) form.reset();
            },
        },
    );
}
async function remove(record) {
    const result = await Swal.fire({
        title: "Delete " + singular.value + "?",
        text: `“${record.name}” will be permanently removed. This cannot be undone.`,
        icon: "warning",
        showCancelButton: true,
        showCloseButton: true,
        allowOutsideClick: false,
        allowEscapeKey: false,
        confirmButtonText: "Yes, delete",
        cancelButtonText: "No, keep it",
        confirmButtonColor: "#bd4545",
        cancelButtonColor: "#61726a",
        reverseButtons: true,
        focusCancel: true,
    });
    if (result.isConfirmed)
        router.delete("/admin/management/" + props.type + "/" + record.id, {
            preserveScroll: true,
        });
}
const editable = (record) =>
    owner.value || (props.type === "users" && !record.is_admin);
</script>
<template>
    <Head :title="title" /><AdminLayout :title="title"
        ><div class="page-heading">
            <div>
                <span class="eyebrow">PEOPLE & PERMISSIONS</span>
                <h1>{{ title }}</h1>
                <p>
                    {{
                        type === "users"
                            ? "Good people make a great store. Manage your community."
                            : type === "roles"
                              ? "Give every teammate the right space to do their best work."
                              : "Define what your team can access in your workspace."
                    }}
                </p>
            </div>
            <button
                v-if="owner || type === 'users'"
                class="btn primary"
                @click="open()"
            >
                <Plus :size="18" /> Add {{ singular }}
            </button>
        </div>
        <div v-if="!owner && type !== 'users'" class="notice">
            You can view this section. Only the store owner can change access
            rules.
        </div>
        <section class="panel management-panel">
            <div class="table-toolbar">
                <div class="table-title">
                    <h3>All {{ type }}</h3>
                    <span class="count-badge">{{ records.total }}</span>
                </div>
                <form
                    class="search-box admin-search"
                    @submit.prevent="
                        router.get(
                            '/admin/management/' + type,
                            { search: query },
                            { preserveState: true, replace: true },
                        )
                    "
                >
                    <Search :size="17" /><input
                        v-model="query"
                        :placeholder="'Search ' + type + '…'"
                        :aria-label="'Search ' + type"
                    /><button
                        type="submit"
                        class="icon-button"
                        aria-label="Submit search"
                    >
                        <ArrowRight :size="16" />
                    </button>
                </form>
            </div>
            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>
                                {{ type === "users" ? "NAME" : "NAME / KEY" }}
                            </th>
                            <th>
                                {{
                                    type === "users"
                                        ? "ROLE"
                                        : type === "roles"
                                          ? "PERMISSIONS"
                                          : "DESCRIPTION"
                                }}
                            </th>
                            <th>
                                {{
                                    type === "users"
                                        ? "ACCOUNT TYPE"
                                        : type === "roles"
                                          ? "MEMBERS"
                                          : "CREATED"
                                }}
                            </th>
                            <th class="align-right">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="record in records.data" :key="record.id">
                            <td>
                                <div class="record-name">
                                    <span class="avatar small"
                                        ><component
                                            v-if="type !== 'users'"
                                            :is="
                                                type === 'roles'
                                                    ? ShieldCheck
                                                    : KeyRound
                                            "
                                            :size="17"
                                        /><template v-else>{{
                                            record.name
                                                .slice(0, 2)
                                                .toUpperCase()
                                        }}</template></span
                                    >
                                    <div>
                                        <strong>{{ record.name }}</strong
                                        ><small v-if="type === 'users'">{{
                                            record.email
                                        }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span
                                    v-if="type === 'users'"
                                    class="soft-badge"
                                    >{{
                                        record.is_super_admin
                                            ? "Store owner"
                                            : record.role?.name || "—"
                                    }}</span
                                >
                                <div
                                    v-else-if="type === 'roles'"
                                    class="tag-list"
                                >
                                    <span
                                        v-for="p in record.permissions"
                                        :key="p.id"
                                        class="tag"
                                        >{{ p.name }}</span
                                    ><small v-if="!record.permissions.length"
                                        >No permissions</small
                                    >
                                </div>
                                <span v-else>{{
                                    record.description || "—"
                                }}</span>
                            </td>
                            <td>
                                <span
                                    v-if="type === 'users'"
                                    class="status-badge"
                                    :class="{ customer: !record.is_admin }"
                                    ><span class="live-dot"></span
                                    >{{
                                        record.is_admin ? "Admin" : "Customer"
                                    }}</span
                                ><span v-else-if="type === 'roles'"
                                    >{{ record.users_count }} members</span
                                ><span v-else>{{
                                    new Date(
                                        record.created_at,
                                    ).toLocaleDateString()
                                }}</span>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <template v-if="editable(record)"
                                        ><button
                                            class="icon-button"
                                            @click="open(record)"
                                            :aria-label="'Edit ' + record.name"
                                        >
                                            <Pencil :size="16" /></button
                                        ><button
                                            v-if="
                                                !record.is_super_admin &&
                                                record.id !==
                                                    (type === 'users'
                                                        ? page.props.auth.user
                                                              .id
                                                        : null)
                                            "
                                            class="icon-button danger"
                                            @click="remove(record)"
                                            :aria-label="
                                                'Delete ' + record.name
                                            "
                                        >
                                            <Trash2
                                                :size="16"
                                            /></button></template
                                    ><small v-else>Read only</small>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!records.data.length">
                            <td colspan="4">
                                <div class="table-empty">
                                    <Users :size="32" />
                                    <h3>
                                        {{
                                            query
                                                ? "No matches found"
                                                : "A fresh beginning"
                                        }}
                                    </h3>
                                    <p>
                                        {{
                                            query
                                                ? "Try a different name."
                                                : "Add your first " +
                                                  singular +
                                                  " to get started."
                                        }}
                                    </p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="table-footer">
                <span
                    >{{ records.from || 0 }}–{{ records.to || 0 }} of
                    {{ records.total }} {{ type }}</span
                >
                <div class="pagination">
                    <template v-for="(link, i) in records.links" :key="i"
                        ><Link
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
        </section>
        <div v-if="modal" class="modal-overlay">
            <section
                ref="dialog"
                class="modal"
                role="dialog"
                aria-modal="true"
                aria-labelledby="dialog-title"
                @keydown="trap"
            >
                <div class="modal-heading">
                    <div>
                        <span class="eyebrow">YOUR WORKSPACE</span>
                        <h2 id="dialog-title">
                            {{ editing ? "Edit" : "Add" }} {{ singular }}
                        </h2>
                    </div>
                    <button
                        class="icon-button"
                        @click="close"
                        aria-label="Close dialog"
                    >
                        <X />
                    </button>
                </div>
                <form @submit.prevent="save" novalidate>
                    <Field
                        :label="
                            type === 'permissions' ? 'Permission key' : 'Name'
                        "
                        id="entry-name"
                        :error="form.errors.name"
                        :hint="
                            type === 'permissions'
                                ? 'Use a key such as products.manage. Custom keys are available for future modules.'
                                : ''
                        "
                        ><input
                            id="entry-name"
                            v-model="form.name"
                            :aria-invalid="!!form.errors.name" /></Field
                    ><template v-if="type === 'users'"
                        ><Field
                            label="Email address"
                            id="entry-email"
                            :error="form.errors.email"
                            ><input
                                id="entry-email"
                                type="email"
                                v-model="form.email"
                                :aria-invalid="!!form.errors.email"
                        /></Field>
                        <div class="form-grid">
                            <Field
                                :label="
                                    editing
                                        ? 'New password (optional)'
                                        : 'Password'
                                "
                                id="entry-password"
                                :error="form.errors.password"
                                hint="8+ characters, mixed case and a number."
                                ><PasswordInput
                                    id="entry-password"
                                    autocomplete="new-password"
                                    v-model="form.password" /></Field
                            ><Field
                                label="Confirm password"
                                id="entry-confirm"
                                :error="form.errors.password_confirmation"
                                ><PasswordInput
                                    id="entry-confirm"
                                    autocomplete="new-password"
                                    v-model="form.password_confirmation"
                            /></Field>
                        </div>
                        <template v-if="owner"
                            ><Field
                                label="Account type"
                                id="entry-type"
                                :error="form.errors.is_admin"
                                ><select
                                    id="entry-type"
                                    v-model="form.is_admin"
                                >
                                    <option :value="false">Customer</option>
                                    <option :value="true">Administrator</option>
                                </select></Field
                            ><Field
                                v-if="form.is_admin"
                                label="Role"
                                id="entry-role"
                                :error="form.errors.role_id"
                                ><select id="entry-role" v-model="form.role_id">
                                    <option :value="null">
                                        No role assigned
                                    </option>
                                    <option
                                        v-for="role in roles"
                                        :key="role.id"
                                        :value="role.id"
                                    >
                                        {{ role.name }}
                                    </option>
                                </select></Field
                            ></template
                        ></template
                    ><Field
                        v-if="type === 'permissions'"
                        label="Description"
                        id="entry-description"
                        :error="form.errors.description"
                    >
                        <textarea
                            id="entry-description"
                            v-model="form.description"
                            rows="3"
                        />
                    </Field>
                    <div v-if="type === 'roles'" class="field">
                        <label>Permissions</label>
                        <div class="permission-options">
                            <label
                                v-for="permission in permissions"
                                :key="permission.id"
                                class="permission-option"
                                ><input
                                    type="checkbox"
                                    v-model="form.permission_ids"
                                    :value="permission.id"
                                /><span
                                    ><strong>{{ permission.name }}</strong
                                    ><small>{{
                                        permission.description
                                    }}</small></span
                                ></label
                            >
                            <p v-if="!permissions.length">
                                Create a permission first.
                            </p>
                        </div>
                        <small class="field-error">{{
                            form.errors.permission_ids
                        }}</small>
                    </div>
                    <div class="modal-footer">
                        <button class="btn primary" :disabled="form.processing">
                            {{
                                form.processing ? "Saving…" : "Save " + singular
                            }}
                        </button>
                    </div>
                </form>
            </section>
        </div></AdminLayout
    >
</template>
