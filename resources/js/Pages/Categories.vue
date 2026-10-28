<script setup>
import { ref, watch } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ArrowDown, ArrowUp, GripVertical, ImagePlus, Pencil, Plus, Search, Trash2 } from "@lucide/vue";
import Swal from "sweetalert2";
import AdminLayout from "../Components/AdminLayout.vue";

const props = defineProps({ items: Object, filters: Object });
const search = ref(props.filters?.search || "");
const type = ref(props.filters?.type || "");
const dragged = ref(null);
const dropTarget = ref(null);
const reordering = ref(false);

function startDrag(event, item) {
    if (item.type !== 'category' || reordering.value) { event.preventDefault(); return; }
    dragged.value = item.id;
    event.dataTransfer.effectAllowed = 'move';
    event.dataTransfer.setData('text/plain', String(item.id));
}
function dragOver(event, item) {
    if (!dragged.value || item.type !== 'category' || item.id === dragged.value) return;
    event.preventDefault();
    const bounds = event.currentTarget.getBoundingClientRect();
    dropTarget.value = { id: item.id, position: event.clientY < bounds.top + bounds.height / 2 ? 'before' : 'after' };
}
function reorder(id, targetId, position) {
    if (reordering.value) return;
    reordering.value = true;
    router.post('/admin/categories/reorder', { id, target_id: targetId, position }, {
        preserveScroll: true,
        onFinish: () => { reordering.value = false; dragged.value = null; dropTarget.value = null; },
    });
}
function drop(event, item) {
    event.preventDefault();
    if (dragged.value && dropTarget.value?.id === item.id) reorder(dragged.value, item.id, dropTarget.value.position);
}
function move(item, direction) {
    const categories = props.items.data.filter(row => row.type === 'category');
    const index = categories.findIndex(row => row.id === item.id);
    const target = categories[index + direction];
    if (target) reorder(item.id, target.id, direction < 0 ? 'before' : 'after');
}

watch(
    () => [props.filters?.search, props.filters?.type],
    ([nextSearch, nextType]) => {
        search.value = nextSearch || "";
        type.value = nextType || "";
    },
);

function filter() {
    router.get(
        "/admin/categories",
        { search: search.value || undefined, type: type.value || undefined },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

async function remove(item) {
    const answer = await Swal.fire({
        title: `Delete ${item.name}?`,
        text:
            item.type === "category"
                ? "Its subcategories and child categories will also be deleted."
                : item.type === "subcategory"
                  ? "Its child categories will also be deleted."
                  : "This category will be removed.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Delete",
        cancelButtonText: "Cancel",
        confirmButtonColor: "#b42318",
    });
    if (answer.isConfirmed) {
        router.delete(`/admin/categories/records/${item.type}/${item.id}`, {
            preserveScroll: true,
        });
    }
}

const editUrl = (item) =>
    `/admin/categories/records/${item.type}/${item.id}/edit`;
</script>

<template>
    <Head title="Categories" />
    <AdminLayout title="Categories">
        <div class="page-heading">
            <div>
                <span class="eyebrow">PRODUCT CATALOG</span>
                <h1>Categories</h1>
                <p>Drag category handles to change their order. The footer shows the first five active categories.</p>
            </div>
            <Link href="/admin/categories/create" class="btn primary">
                <Plus :size="18" />Add category
            </Link>
        </div>

        <section class="panel category-panel">
            <form class="admin-filter-bar" @submit.prevent="filter">
                <label class="admin-search">
                    <Search :size="17" />
                    <input
                        v-model="search"
                        type="search"
                        placeholder="Search name, slug or parent"
                    />
                </label>
                <select v-model="type" aria-label="Filter category level">
                    <option value="">All levels</option>
                    <option value="category">Categories</option>
                    <option value="subcategory">Subcategories</option>
                    <option value="child">Child categories</option>
                </select>
                <button class="btn primary" type="submit">
                    <Search :size="16" />Filter
                </button>
            </form>

            <div class="category-table-wrap">
                <table class="category-table">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Image</th>
                            <th>Name / slug</th>
                            <th>Level</th>
                            <th>Parent</th>
                            <th>Status</th>
                            <th class="actions-heading">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="item in items.data"
                            :key="`${item.type}-${item.id}`"
                            :class="{ 'drop-before': dropTarget?.id === item.id && item.type === 'category' && dropTarget.position === 'before', 'drop-after': dropTarget?.id === item.id && item.type === 'category' && dropTarget.position === 'after' }"
                            @dragover="dragOver($event, item)"
                            @drop="drop($event, item)"
                        >
                            <td>
                                <div v-if="item.type === 'category'" class="order-controls">
                                    <button type="button" class="icon-button drag-handle" :draggable="!reordering" :disabled="reordering"
                                        :aria-label="`Drag ${item.name} to reorder`" @dragstart="startDrag($event, item)" @dragend="dragged = null; dropTarget = null">
                                        <GripVertical :size="16" />
                                    </button>
                                    <button type="button" class="icon-button" :disabled="reordering || !items.data.slice(0, items.data.indexOf(item)).some(row => row.type === 'category')" :aria-label="`Move ${item.name} up`" @click="move(item, -1)"><ArrowUp :size="14" /></button>
                                    <button type="button" class="icon-button" :disabled="reordering || !items.data.slice(items.data.indexOf(item) + 1).some(row => row.type === 'category')" :aria-label="`Move ${item.name} down`" @click="move(item, 1)"><ArrowDown :size="14" /></button>
                                </div>
                            </td>
                            <td>
                                <img
                                    v-if="item.image"
                                    class="category-thumb"
                                    :src="item.image"
                                    :alt="item.name"
                                />
                                <span
                                    v-else
                                    class="category-thumb category-thumb-empty"
                                >
                                    <ImagePlus :size="18" />
                                </span>
                            </td>
                            <td>
                                <strong class="category-name">{{
                                    item.name
                                }}</strong>
                                <small class="category-slug">{{
                                    item.slug
                                }}</small>
                            </td>
                            <td>
                                <span class="category-level" :class="item.type">
                                    {{
                                        item.type === "child"
                                            ? "Child"
                                            : item.type === "subcategory"
                                              ? "Subcategory"
                                              : "Category"
                                    }}
                                </span>
                            </td>
                            <td>{{ item.parent || "—" }}</td>
                            <td>
                                <span
                                    class="category-status"
                                    :class="{ inactive: !item.is_active }"
                                >
                                    {{ item.is_active ? "Active" : "Inactive" }}
                                </span>
                            </td>
                            <td class="category-actions">
                                <Link
                                    :href="editUrl(item)"
                                    class="icon-button"
                                    :aria-label="`Edit ${item.name}`"
                                >
                                    <Pencil :size="16" />
                                </Link>
                                <button
                                    class="icon-button danger"
                                    :aria-label="`Delete ${item.name}`"
                                    @click="remove(item)"
                                >
                                    <Trash2 :size="16" />
                                </button>
                            </td>
                        </tr>
                        <tr v-if="!items.data.length">
                            <td colspan="7" class="category-empty">
                                No categories found. Try changing the filters or
                                add a category.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-if="items.total" class="category-table-footer">
                <span
                    >Showing {{ items.from }}–{{ items.to }} of
                    {{ items.total }}</span
                >
                <div class="category-pagination">
                    <template v-for="(link, index) in items.links" :key="index">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            :class="{ selected: link.active }"
                        >
                            {{
                                index === 0
                                    ? "Previous"
                                    : index === items.links.length - 1
                                      ? "Next"
                                      : link.label
                            }}
                        </Link>
                        <span v-else>
                            {{
                                index === 0
                                    ? "Previous"
                                    : index === items.links.length - 1
                                      ? "Next"
                                      : link.label
                            }}
                        </span>
                    </template>
                </div>
            </div>
        </section>
    </AdminLayout>
</template>

<style scoped>
.order-controls { display: flex; align-items: center; gap: 2px; }
.drag-handle { cursor: grab; }
.drag-handle:active { cursor: grabbing; }
.drop-before td { box-shadow: inset 0 3px #13795b; }
.drop-after td { box-shadow: inset 0 -3px #13795b; }
.category-panel {
    overflow: hidden;
}
.category-table-wrap {
    overflow-x: auto;
}
.category-table {
    width: 100%;
    min-width: 760px;
    border-collapse: collapse;
}
.category-table th,
.category-table td {
    padding: 14px 18px;
    border-bottom: 1px solid #edf1f3;
    text-align: left;
}
.category-table th {
    background: #f2f5f7;
    color: #607386;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.07em;
    text-transform: uppercase;
}
.category-table td {
    color: #44596a;
    font-size: 12px;
}
.category-thumb {
    display: grid;
    width: 42px;
    height: 42px;
    place-items: center;
    border: 1px solid #e5ebef;
    border-radius: 9px;
    object-fit: cover;
}
.category-thumb-empty {
    background: #f4f7f8;
    color: #9aa8b1;
}
.category-name,
.category-slug {
    display: block;
}
.category-name {
    color: #263b4d;
}
.category-slug {
    margin-top: 4px;
    color: #8a9aa7;
    font-size: 10px;
}
.category-level,
.category-status {
    display: inline-flex;
    align-items: center;
    border-radius: 99px;
    padding: 5px 9px;
    background: #edf3f7;
    color: #526b7f;
    font-size: 10px;
    font-weight: 650;
    white-space: nowrap;
}
.category-level.subcategory {
    background: #eef6f2;
    color: #39715b;
}
.category-level.child {
    background: #f5f0fa;
    color: #725397;
}
.category-status {
    background: #e9f6ef;
    color: #28764e;
}
.category-status.inactive {
    background: #f4f0ef;
    color: #94746d;
}
.category-actions {
    text-align: right !important;
    white-space: nowrap;
}
.category-actions .icon-button + .icon-button {
    margin-left: 5px;
}
.category-empty {
    padding: 45px 20px !important;
    color: #8796a1 !important;
    text-align: center !important;
}
.category-table-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    padding: 16px 20px;
    color: #718392;
    font-size: 11px;
}
.category-pagination {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
}
.category-pagination a,
.category-pagination span {
    padding: 7px 9px;
    border: 1px solid #e3e9ed;
    border-radius: 7px;
    color: #607386;
    text-decoration: none;
}
.category-pagination .selected {
    border-color: #176b57;
    background: #176b57;
    color: #fff;
}
@media (max-width: 600px) {
    .category-table-footer {
        align-items: flex-start;
        flex-direction: column;
    }
}
</style>
