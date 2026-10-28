<script setup>
import { Head, Link, router, useForm } from "@inertiajs/vue3";
import { Image, Pencil, Plus, Search, Trash2 } from "@lucide/vue";
import Swal from "sweetalert2";
import AdminLayout from "../Components/AdminLayout.vue";

const props = defineProps({
    records: Object,
    categories: Array,
    filters: Object,
});
const filters = useForm({
    search: props.filters.search || "",
    category_id: props.filters.category_id || "",
    start_date: props.filters.start_date || "",
    end_date: props.filters.end_date || "",
});

function applyFilters() {
    router.get("/admin/products", filters.data(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

function resetFilters() {
    filters.search = "";
    filters.category_id = "";
    filters.start_date = "";
    filters.end_date = "";
    applyFilters();
}

async function remove(product) {
    const answer = await Swal.fire({
        title: "Delete this product?",
        text: `${product.name} and its images and variants will be permanently removed.`,
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
        router.delete("/admin/products/" + product.id, {
            preserveScroll: true,
        });
}

const money = (value) =>
    "৳" +
    Number(value || 0).toLocaleString("en-BD", { minimumFractionDigits: 2 });
</script>

<template>
    <Head title="All products" />
    <AdminLayout title="All products">
        <div class="page-heading">
            <div>
                <span class="eyebrow">YOUR COLLECTION, READY TO SELL</span>
                <h1>All products</h1>
                <p>
                    Browse your catalog, check availability, and update
                    products.
                </p>
            </div>
            <Link href="/admin/products/create" class="btn primary">
                <Plus :size="18" />Add product
            </Link>
        </div>

        <section class="panel products-panel">
            <form
                class="admin-filter-bar filter-grid"
                @submit.prevent="applyFilters"
            >
                <label class="admin-search">
                    <Search :size="17" />
                    <input
                        v-model="filters.search"
                        type="search"
                        placeholder="Search product name"
                    />
                </label>
                <label class="filter-select">
                    <span>Category</span>
                    <select v-model="filters.category_id">
                        <option value="">All categories</option>
                        <option
                            v-for="category in categories"
                            :key="category.id"
                            :value="category.id"
                        >
                            {{ category.name }}
                        </option>
                    </select>
                </label>
                <label class="filter-date">
                    <span>Start date</span>
                    <input v-model="filters.start_date" type="date" />
                </label>
                <label class="filter-date">
                    <span>End date</span>
                    <input v-model="filters.end_date" type="date" />
                </label>
                <div class="filter-actions">
                    <button class="btn primary" type="submit">
                        <Search :size="16" />Filter
                    </button>
                    <button
                        class="btn secondary"
                        type="button"
                        @click="resetFilters"
                    >
                        Reset
                    </button>
                </div>
            </form>

            <div class="products-table-wrap">
                <table class="products-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Subcategory</th>
                            <th>Child category</th>
                            <th>Sale price</th>
                            <th>Status</th>
                            <th class="actions-heading">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(product, index) in records.data"
                            :key="product.id"
                        >
                            <td>{{ (records.from || 1) + index }}</td>
                            <td>
                                <div class="product-name-cell">
                                    <img
                                        v-if="
                                            product.featured_image_one ||
                                            product.images[0]
                                        "
                                        :src="
                                            product.featured_image_one ||
                                            product.images[0].path
                                        "
                                        :alt="product.name"
                                    />
                                    <span v-else class="product-placeholder"
                                        ><Image :size="18"
                                    /></span>
                                    <div>
                                        <strong>{{ product.name }}</strong>
                                        <small v-if="product.brand">{{
                                            product.brand.name
                                        }}</small>
                                        <small
                                            v-if="product.new_arrival"
                                            class="new-arrival-label"
                                        >
                                            New Arrival
                                        </small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ product.category?.name || "—" }}</td>
                            <td>{{ product.subcategory?.name || "—" }}</td>
                            <td>{{ product.child_category?.name || "—" }}</td>
                            <td>
                                <span
                                    v-if="product.sale_price !== null"
                                    class="sale-price"
                                    >{{ money(product.sale_price) }}</span
                                >
                                <span v-else-if="product.regular_price !== null">{{
                                    money(product.regular_price)
                                }}</span>
                                <span v-else>—</span>
                            </td>
                            <td>
                                <span
                                    class="status-pill"
                                    :class="{ active: product.status }"
                                    >{{
                                        product.status ? "Active" : "Inactive"
                                    }}</span
                                >
                            </td>
                            <td class="actions-cell">
                                <Link
                                    class="icon-button"
                                    :href="
                                        '/admin/products/' +
                                        product.id +
                                        '/edit'
                                    "
                                    :aria-label="'Edit ' + product.name"
                                    ><Pencil :size="17"
                                /></Link>
                                <button
                                    class="icon-button danger"
                                    :aria-label="'Delete ' + product.name"
                                    @click="remove(product)"
                                >
                                    <Trash2 :size="17" />
                                </button>
                            </td>
                        </tr>
                        <tr v-if="!records.data.length">
                            <td colspan="8">
                                <div class="products-empty">
                                    <h3>
                                        {{
                                            filters.search ||
                                            filters.category_id ||
                                            filters.start_date ||
                                            filters.end_date
                                                ? "No matching products"
                                                : "Your product catalog starts here"
                                        }}
                                    </h3>
                                    <p>
                                        {{
                                            records.total
                                                ? "Try changing the filters."
                                                : "Add your first product to start building your catalog."
                                        }}
                                    </p>
                                    <Link
                                        v-if="!records.total"
                                        href="/admin/products/create"
                                        class="btn primary"
                                        ><Plus :size="17" />Add first
                                        product</Link
                                    >
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="table-footer">
                <span
                    >{{ records.from || 0 }}–{{ records.to || 0 }} of
                    {{ records.total }} products</span
                >
                <div class="pagination">
                    <template v-for="(link, i) in records.links" :key="i">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            :class="{ selected: link.active }"
                            preserve-scroll
                        >
                            {{
                                i === 0
                                    ? "Previous"
                                    : i === records.links.length - 1
                                      ? "Next"
                                      : link.label
                            }}
                        </Link>
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
.products-panel {
    overflow: hidden;
}
.filter-grid {
    display: grid;
    grid-template-columns:
        minmax(210px, 1.4fr) minmax(150px, 1fr) repeat(2, minmax(135px, 0.8fr))
        auto;
    gap: 12px;
    align-items: end;
}
.filter-grid > label:not(.admin-search) {
    display: grid;
    gap: 7px;
    color: #607266;
    font-size: 12px;
}
.filter-grid > .filter-date input,
.filter-grid > .filter-select select {
    width: 100%;
    min-width: 0;
    height: 44px;
    padding: 0 12px;
    border: 1px solid #dce5e9;
    border-radius: 9px;
    background: #fff;
    color: #405665;
    font: inherit;
    font-size: 13px;
}
.filter-actions {
    display: flex;
    gap: 8px;
}
.filter-actions .btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    min-height: 42px;
    padding: 0 13px;
}
.products-table-wrap {
    overflow-x: auto;
}
.products-table {
    width: 100%;
    min-width: 850px;
    border-collapse: collapse;
}
.products-table th {
    padding: 14px 16px;
    background: #f8faf6;
    color: #809080;
    font-size: 11px;
    text-align: left;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    white-space: nowrap;
}
.products-table td {
    padding: 14px 16px;
    border-top: 1px solid #edf0e9;
    color: #4b5d50;
    font-size: 13px;
}
.product-name-cell {
    display: flex;
    align-items: center;
    gap: 11px;
    min-width: 210px;
}
.product-name-cell img,
.product-placeholder {
    display: grid;
    width: 42px;
    height: 42px;
    flex: 0 0 42px;
    place-items: center;
    border-radius: 8px;
    background: #f0f3ef;
    object-fit: cover;
    color: #819084;
}
.product-name-cell strong,
.product-name-cell small {
    display: block;
}
.product-name-cell .new-arrival-label {
    width: fit-content;
    margin-top: 5px;
    padding: 3px 7px;
    border-radius: 999px;
    background: #f1eee2;
    color: #826a2f;
    font-size: 10px;
    font-weight: 600;
}
.product-name-cell strong {
    color: #334c39;
}
.product-name-cell small {
    margin-top: 4px;
    color: #849184;
}
.sale-price {
    color: #7b1e2e;
    font-weight: 600;
}
.status-pill {
    padding: 5px 9px;
    border-radius: 20px;
    background: #f0f1ec;
    color: #858e83;
    font-size: 11px;
}
.status-pill.active {
    background: #e5f2e4;
    color: #3f793c;
}
.actions-heading,
.actions-cell {
    text-align: right !important;
    white-space: nowrap;
}
.actions-cell .icon-button + .icon-button {
    margin-left: 5px;
}
.products-empty {
    padding: 54px 20px;
    text-align: center;
}
.products-empty h3 {
    color: #40593b;
}
.products-empty p {
    margin: 8px 0 18px;
    color: #849184;
}
@media (max-width: 1100px) {
    .filter-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .filter-actions {
        grid-column: 1 / -1;
        justify-content: flex-end;
    }
}
@media (max-width: 600px) {
    .filter-grid {
        grid-template-columns: 1fr;
    }
    .filter-actions {
        grid-column: auto;
    }
}
</style>
