<script setup>
import { ref } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { Plus, Pencil, Trash2, Star, MessageSquare, Search } from "@lucide/vue";
import Swal from "sweetalert2";
import AdminLayout from "../Components/AdminLayout.vue";
const props = defineProps({ records: Object, filters: Object });
const search = ref(props.filters?.search || "");
const status = ref(props.filters?.status || "");
function filterReviews() {
    router.get(
        "/admin/customer-reviews",
        {
            search: search.value || undefined,
            status: status.value || undefined,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}
async function remove(review) {
    const result = await Swal.fire({
        title: "Delete this review?",
        text: review.name + "’s review will be removed.",
        icon: "warning",
        showCancelButton: true,
        showCloseButton: true,
        confirmButtonText: "Yes, delete",
        cancelButtonText: "No, keep it",
        allowOutsideClick: false,
        allowEscapeKey: false,
        confirmButtonColor: "#b42318",
    });
    if (result.isConfirmed)
        router.delete("/admin/customer-reviews/" + review.id, {
            preserveScroll: true,
        });
}
</script>
<template>
    <Head title="Customer reviews" /><AdminLayout title="Customer reviews"
        ><div class="page-heading">
            <div>
                <span class="eyebrow">VOICES OF YOUR CUSTOMERS</span>
                <h1>Customer reviews</h1>
                <p>Manage the stories that appear on your storefront.</p>
            </div>
            <Link href="/admin/customer-reviews/create" class="btn primary"
                ><Plus :size="18" />Create review</Link
            >
        </div>
        <section class="panel">
            <form class="admin-filter-bar" @submit.prevent="filterReviews">
                <label class="admin-search">
                    <Search :size="17" />
                    <input
                        v-model="search"
                        type="search"
                        placeholder="Search customer or review"
                    />
                </label>
                <select v-model="status" aria-label="Filter reviews by status">
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
                        filterReviews();
                    "
                >
                    Clear
                </button>
            </form>
            <div class="reviews-table">
                <table>
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Rating</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="review in records.data" :key="review.id">
                            <td>
                                <div class="customer-cell">
                                    <img
                                        v-if="review.image"
                                        :src="review.image"
                                        :alt="review.name"
                                    /><span v-else class="initial">{{
                                        review.name.slice(0, 1)
                                    }}</span
                                    ><strong>{{ review.name }}</strong>
                                </div>
                            </td>
                            <td>
                                <span class="table-rating"
                                    ><Star :size="15" fill="currentColor" />{{
                                        review.star
                                    }}/5</span
                                >
                            </td>
                            <td>
                                <p class="review-excerpt">
                                    {{ review.description }}
                                </p>
                            </td>
                            <td>
                                <span
                                    class="status-pill"
                                    :class="{ active: review.status }"
                                    >{{
                                        review.status ? "Active" : "Inactive"
                                    }}</span
                                >
                            </td>
                            <td>
                                <div class="review-actions">
                                    <Link
                                        class="icon-button"
                                        :href="
                                            '/admin/customer-reviews/' +
                                            review.id +
                                            '/edit'
                                        "
                                        :aria-label="
                                            'Edit review by ' + review.name
                                        "
                                        ><Pencil :size="17" /></Link
                                    ><button
                                        class="icon-button"
                                        :aria-label="
                                            'Delete review by ' + review.name
                                        "
                                        @click="remove(review)"
                                    >
                                        <Trash2 :size="17" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!records.data.length">
                            <td colspan="5">
                                <div class="review-empty">
                                    <MessageSquare :size="36" />
                                    <h3>
                                        {{
                                            records.total
                                                ? "No matching reviews"
                                                : "Make room for customer stories"
                                        }}
                                    </h3>
                                    <p>
                                        {{
                                            records.total
                                                ? "Try changing or clearing your filters."
                                                : "Add your first review and set it active to show it on your storefront."
                                        }}
                                    </p>
                                    <Link
                                        v-if="!records.total"
                                        class="btn primary"
                                        href="/admin/customer-reviews/create"
                                        >Create first review</Link
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
                    {{ records.total }} reviews</span
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
.reviews-table {
    overflow-x: auto;
}
.reviews-table table {
    width: 100%;
    min-width: 750px;
    border-collapse: collapse;
}
.reviews-table th {
    text-align: left;
    background: #f8faf6;
    color: #809080;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    padding: 18px 22px;
}
.reviews-table td {
    padding: 22px;
    border-top: 1px solid #edf0e9;
    font-size: 13px;
}
.customer-cell {
    display: flex;
    gap: 12px;
    align-items: center;
}
.customer-cell img,
.initial {
    width: 46px;
    height: 46px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
}
.initial {
    display: grid;
    place-items: center;
    background: #edf3e7;
    color: #627b54;
    font-size: 20px;
}
.customer-cell strong {
    max-width: 180px;
    overflow-wrap: anywhere;
}
.table-rating {
    display: flex;
    gap: 6px;
    align-items: center;
    color: #ae812f;
    white-space: nowrap;
}
.review-excerpt {
    max-width: 340px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    overflow-wrap: anywhere;
    color: #778479;
    line-height: 1.6;
}
.status-pill {
    display: inline-block;
    padding: 6px 10px;
    border-radius: 20px;
    background: #F3F6FB;
    color: #8a8e82;
    font-size: 11px;
}
.status-pill.active {
    background: #e6f3e9;
    color: #39764b;
}
.review-actions {
    display: flex;
    gap: 6px;
}
.review-empty {
    text-align: center;
    padding: 40px;
}
.review-empty svg {
    margin: auto;
    color: #9baa91;
}
.review-empty h3 {
    margin: 15px 0 9px;
}
.review-empty p {
    margin-bottom: 22px;
    color: #899582;
}
.table-footer {
    flex-wrap: wrap;
    gap: 15px;
}
.pagination {
    flex-wrap: wrap;
}
</style>
