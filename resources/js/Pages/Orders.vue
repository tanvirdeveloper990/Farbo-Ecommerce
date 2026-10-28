<script setup>
import { computed, nextTick, ref } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import {
    CheckCircle2,
    Clock3,
    Eye,
    PackageCheck,
    Search,
    ShoppingBag,
    Trash2,
    XCircle, Truck, X,
} from "@lucide/vue";
import Swal from "sweetalert2";
import AdminLayout from "../Components/AdminLayout.vue";
import CourierBooking from "../Components/CourierBooking.vue";

const props = defineProps({
    records: Object,
    filters: Object,
    summary: Object,
});
const courierDialog = ref(null), selectedOrderId = ref(null);
const courierOrder = computed(() => props.records.data.find(order => order.id === selectedOrderId.value));
async function openCourier(order) { selectedOrderId.value = order.id; await nextTick(); courierDialog.value.showModal(); }
function closeCourier() { courierDialog.value.close(); selectedOrderId.value = null; }
const search = ref(props.filters.search || "");
const statusOptions = [
    ["All Orders", ""],
    ["Pending", "pending"],
    ["Processing", "processing"],
    ["On The Way", "on_the_way"],
    ["On Hold", "on_hold"],
    ["Courier", "courier"],
    ["Completed", "completed"],
    ["Cancelled", "cancelled"],
];
const taka = (value) =>
    "৳" +
    Number(value || 0).toLocaleString("en-BD", { minimumFractionDigits: 2 });
const orderNumber = (order) => 10000 + order.id;
const label = (status) =>
    ({
        on_the_way: "On The Way",
        on_hold: "On Hold",
    })[status] || status.charAt(0).toUpperCase() + status.slice(1);
function filter(status = props.filters.status || "") {
    router.get(
        "/admin/orders",
        { status: status || undefined, search: search.value || undefined },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}
async function remove(order) {
    const answer = await Swal.fire({
        title: "Delete this order?",
        text: `Order #${orderNumber(order)} will be permanently removed.`,
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
        router.delete("/admin/orders/" + order.id, { preserveScroll: true });
}
</script>

<template>
    <Head title="Orders" />
    <AdminLayout title="Orders">
        <div class="page-heading">
            <div>
                <span class="eyebrow">SALES MANAGEMENT</span>
                <h1>Orders</h1>
                <p>
                    Review customer orders and follow each order through
                    delivery.
                </p>
            </div>
        </div>

        <section class="order-summary">
            <article class="panel summary-card">
                <span class="summary-icon purple"
                    ><ShoppingBag :size="19"
                /></span>
                <strong>{{ summary.total }}</strong>
                <small>Total orders</small>
            </article>
            <article class="panel summary-card">
                <span class="summary-icon amber"><Clock3 :size="19" /></span>
                <strong>{{ summary.pending }}</strong>
                <small>Pending</small>
            </article>
            <article class="panel summary-card">
                <span class="summary-icon green"
                    ><CheckCircle2 :size="19"
                /></span>
                <strong>{{ summary.completed }}</strong>
                <small>Completed</small>
            </article>
            <article class="panel summary-card">
                <span class="summary-icon red"><XCircle :size="19" /></span>
                <strong>{{ summary.cancelled }}</strong>
                <small>Cancelled</small>
            </article>
            <article class="panel summary-card">
                <span class="summary-icon purple"
                    ><PackageCheck :size="19"
                /></span>
                <strong>{{ taka(summary.revenue) }}</strong>
                <small>Completed revenue</small>
            </article>
        </section>

        <section class="panel orders-panel">
            <div class="orders-toolbar">
                <div class="status-tabs">
                    <button
                        v-for="[name, status] in statusOptions"
                        :key="status || 'all'"
                        type="button"
                        :class="{ selected: (filters.status || '') === status }"
                        @click="filter(status)"
                    >
                        {{ name }}
                    </button>
                </div>
                <form
                    class="orders-search admin-search"
                    @submit.prevent="filter()"
                >
                    <Search :size="17" />
                    <input
                        v-model="search"
                        type="search"
                        aria-label="Search orders by order number, customer or phone"
                        placeholder="Order ID, customer name or phone"
                    />
                    <button class="btn primary" type="submit">Search</button>
                </form>
            </div>
            <div class="orders-table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Payment &amp; status</th>
                            <th>Courier</th>
                            <th class="actions-heading">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(order, index) in records.data"
                            :key="order.id"
                        >
                            <td>{{ (records.from || 1) + index }}</td>
                            <td>
                                <Link
                                    class="order-number"
                                    :href="`/admin/orders/${order.id}`"
                                    >#{{ orderNumber(order) }}</Link
                                >
                                <small class="order-date">{{
                                    new Date(order.created_at).toLocaleString()
                                }}</small>
                            </td>
                            <td>
                                <strong>{{ order.customer_name }}</strong>
                                <a
                                    class="customer-phone"
                                    :href="`tel:${order.phone}`"
                                    >{{ order.phone }}</a
                                >
                                <small class="customer-address"
                                    >{{ order.address }},
                                    {{
                                        order.area === "inside_dhaka"
                                            ? "Inside Dhaka"
                                            : "Outside Dhaka"
                                    }}</small
                                >
                            </td>
                            <td class="amount-cell">
                                <small>Total {{ taka(order.total) }}</small>
                                <small
                                    >Paid
                                    {{
                                        taka(
                                            order.payment_status === "paid"
                                                ? order.total
                                                : 0,
                                        )
                                    }}</small
                                >
                                <small
                                    >Due
                                    {{
                                        taka(
                                            order.payment_status === "paid"
                                                ? 0
                                                : order.total,
                                        )
                                    }}</small
                                >
                                <span class="payment-method">{{
                                    order.payment_method === "cash_on_delivery"
                                        ? "Cash on delivery"
                                        : order.payment_method
                                }}</span>
                            </td>
                            <td>
                                <span
                                    class="status-pill"
                                    :class="order.status"
                                    >{{ label(order.status) }}</span
                                >
                                <small
                                    class="payment-status"
                                    :class="order.payment_status"
                                    >{{ order.payment_status }}</small
                                >
                            </td>
                            <td>
                                <span
                                    v-if="order.courier_provider"
                                    class="courier-badge"
                                    >{{ order.courier_provider }}</span
                                >
                                <span v-else class="muted">—</span>
                            </td>
                            <td class="actions-cell">
                                <button class="courier-row-button" type="button" :aria-label="'Courier for order '+orderNumber(order)" @click="openCourier(order)"><Truck :size="15"/>{{ order.shipment ? 'Courier details' : 'Send to courier' }}</button>
                                <Link
                                    class="icon-button"
                                    :href="`/admin/orders/${order.id}`"
                                    :aria-label="`View order ${orderNumber(order)}`"
                                    ><Eye :size="17"
                                /></Link>
                                <button
                                    class="icon-button danger"
                                    :aria-label="`Delete order ${orderNumber(order)}`"
                                    @click="remove(order)"
                                >
                                    <Trash2 :size="17" />
                                </button>
                            </td>
                        </tr>
                        <tr v-if="!records.data.length">
                            <td colspan="7">
                                <div class="orders-empty">
                                    <ShoppingBag :size="36" />
                                    <h3>No orders found</h3>
                                    <p>
                                        Customer orders will appear here after
                                        checkout.
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
                    {{ records.total }} orders</span
                >
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
        <dialog ref="courierDialog" class="courier-dialog" @cancel.prevent="closeCourier" @click="event => { if (event.target === courierDialog) closeCourier(); }">
            <header class="courier-dialog-heading"><div><span>ORDER FULFILMENT</span><h2>Order #{{ courierOrder ? orderNumber(courierOrder) : '' }}</h2><p>{{ courierOrder?.customer_name }} · {{ courierOrder?.phone }}</p></div><button class="icon-button" type="button" aria-label="Close courier dialog" @click="closeCourier"><X :size="20"/></button></header>
            <CourierBooking v-if="courierOrder" :key="courierOrder.id" :order="courierOrder"/>
        </dialog>
    </AdminLayout>
</template>

<style scoped>
.order-summary {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 14px;
    margin-bottom: 22px;
}
.summary-card {
    min-width: 0;
    padding: 17px;
    display: grid;
    grid-template-columns: auto 1fr;
    align-items: center;
    column-gap: 11px;
}
.summary-icon {
    grid-row: span 2;
    width: 38px;
    height: 38px;
    display: grid;
    place-items: center;
    border-radius: 11px;
}
.summary-icon.purple {
    color: #6b35c5;
    background: #f0e9ff;
}
.summary-icon.amber {
    color: #b76a00;
    background: #fff2ce;
}
.summary-icon.green {
    color: #087f5b;
    background: #dff7ed;
}
.summary-icon.red {
    color: #c92a2a;
    background: #ffeded;
}
.summary-card strong {
    min-width: 0;
    overflow-wrap: anywhere;
    color: #302252;
    font-size: 20px;
}
.summary-card small {
    color: #7d778e;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
}
.orders-panel {
    overflow: hidden;
}
.orders-toolbar {
    padding: 18px 20px;
    display: flex;
    flex-direction: column;
    gap: 15px;
}
.status-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 7px;
}
.status-tabs button {
    border: 1px solid #ebe8f4;
    border-radius: 8px;
    background: #fff;
    padding: 8px 11px;
    color: #635d72;
    font-size: 12px;
}
.status-tabs button.selected {
    border-color: #5425a7;
    color: #fff;
    background: #5425a7;
}
.orders-search {
    display: flex;
    gap: 8px;
    max-width: 550px;
}
.orders-search input {
    flex: 1;
    min-width: 0;
}
.orders-search > .btn {
    min-height: 44px;
}
.orders-table-wrap {
    overflow-x: auto;
}
table {
    width: 100%;
    min-width: 880px;
    border-collapse: collapse;
}
th {
    padding: 13px 15px;
    background: #f8f6fc;
    color: #62598a;
    font-size: 10px;
    letter-spacing: 0.07em;
    text-align: left;
    text-transform: uppercase;
}
td {
    padding: 15px;
    border-top: 1px solid #eeeaf4;
    font-size: 12px;
    vertical-align: middle;
}
.order-number {
    display: block;
    color: #5a27aa;
    font-weight: 700;
}
.order-date,
.customer-address,
.customer-phone,
.amount-cell small {
    display: block;
    margin-top: 4px;
    color: #817b90;
    font-size: 11px;
}
.customer-phone {
    color: #554b6f;
}
.customer-address {
    max-width: 210px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.amount-cell small {
    color: #4e4764;
}
.payment-method {
    display: inline-block;
    margin-top: 6px;
    border-radius: 5px;
    background: #f0e9ff;
    padding: 3px 6px;
    color: #5b2da7;
    font-size: 10px;
}
.status-pill,
.payment-status,
.courier-badge {
    display: inline-block;
    border-radius: 999px;
    padding: 5px 9px;
    background: #eceff3;
    color: #505968;
    font-size: 10px;
    font-weight: 700;
    white-space: nowrap;
}
.status-pill.pending {
    background: #fff3c4;
    color: #8c5a00;
}
.status-pill.processing,
.status-pill.on_the_way {
    background: #e2eeff;
    color: #2257a7;
}
.status-pill.on_hold {
    background: #fff0da;
    color: #9a5600;
}
.status-pill.courier {
    background: #e8e0ff;
    color: #6033a4;
}
.status-pill.completed {
    background: #dff7eb;
    color: #087f5b;
}
.status-pill.cancelled {
    background: #ffeded;
    color: #bd2929;
}
.payment-status {
    display: block;
    width: fit-content;
    margin-top: 6px;
    background: #ffeded;
    color: #bd2929;
    text-transform: capitalize;
}
.payment-status.paid {
    background: #dff7eb;
    color: #087f5b;
}
.courier-badge {
    border-radius: 5px;
    background: #eaf3ff;
    color: #35638f;
    text-transform: capitalize;
}
.muted {
    color: #aaa;
}
.actions-heading,
.actions-cell {
    text-align: right;
    white-space: nowrap;
}
.actions-cell .icon-button + .icon-button {
    margin-left: 5px;
}
.orders-empty {
    padding: 46px 18px;
    text-align: center;
    color: #91899e;
}
.orders-empty svg {
    margin: 0 auto 12px;
}
.orders-empty h3 {
    margin-bottom: 6px;
    color: #4f4667;
    font-size: 16px;
}
.orders-empty p {
    font-size: 12px;
}
.table-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    padding: 15px 20px;
    color: #80798e;
    font-size: 12px;
}
.pagination {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
}
.pagination a,
.pagination span {
    border-radius: 5px;
    padding: 5px 8px;
}
.pagination a {
    color: #62598a;
}
.pagination a.selected {
    color: #fff;
    background: #5425a7;
}
@media (max-width: 900px) {
    .order-summary {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}
@media (max-width: 560px) {
    .order-summary {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .table-footer {
        align-items: flex-start;
        flex-direction: column;
    }
}
.orders-toolbar{padding:22px;display:grid;gap:18px}.orders-search.admin-search{display:flex;align-items:center;gap:12px;max-width:620px;width:100%;padding:5px 5px 5px 15px;border:1px solid #dfe7e2;border-radius:12px;background:#f9fbfa;box-sizing:border-box}.orders-search.admin-search:focus-within{border-color:#4c8c70;box-shadow:0 0 0 3px #276b4f0b}.orders-search>svg{flex:0 0 18px;color:#82948b}.orders-search.admin-search input{width:100%;min-width:0;border:0;background:transparent;box-shadow:none;outline:none;padding:10px 0;font-size:13px}.orders-search>.btn{min-height:40px;padding:0 20px;border-radius:8px;box-shadow:none;flex-shrink:0}.status-tabs{gap:7px;flex-wrap:wrap}.status-tabs button{border-color:#e3e9e5;color:#66776d;border-radius:8px}.status-tabs button.selected{background:#24674f;border-color:#24674f}.orders-panel{border-radius:16px;overflow:hidden}@media(max-width:500px){.orders-toolbar{padding:15px}.orders-search.admin-search{gap:8px;padding-left:10px}.orders-search>.btn{padding:0 12px}.orders-search.admin-search input{font-size:12px}.status-tabs button{font-size:11px;padding:8px 10px}}
.courier-row-button{display:inline-flex;align-items:center;gap:6px;white-space:nowrap;border:1px solid #d9e8df;border-radius:8px;background:#f0f7f3;color:#2c7052;padding:8px 10px;font-size:11px;font-weight:600;margin-right:8px;cursor:pointer}.courier-row-button:hover{background:#e2efe8}.courier-row-button:focus-visible{outline:2px solid #4a9874;outline-offset:2px}.courier-dialog{width:min(640px,calc(100% - 28px));max-height:calc(100dvh - 40px);overflow:auto;margin:auto;padding:0;border:1px solid #dfe8e2;border-radius:20px;background:#fff;box-shadow:0 24px 80px #14261c35;color:#263f30}.courier-dialog::backdrop{background:#101c1680;backdrop-filter:blur(3px)}.courier-dialog-heading{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;padding:24px;border-bottom:1px solid #e5ece7;background:#f7faf8}.courier-dialog-heading span{font-size:9px;letter-spacing:.14em;color:#77917f;font-weight:650}.courier-dialog-heading h2{font-size:23px;margin:7px 0}.courier-dialog-heading p{font-size:12px;color:#728578;overflow-wrap:anywhere}.courier-dialog :deep(.courier-booking){margin:0;border:0;border-radius:0;box-shadow:none}.courier-dialog-heading .icon-button{flex-shrink:0}@media(max-width:500px){.courier-dialog-heading{padding:20px}.courier-dialog{border-radius:16px}.courier-row-button{font-size:10px;padding:7px}}
</style>
