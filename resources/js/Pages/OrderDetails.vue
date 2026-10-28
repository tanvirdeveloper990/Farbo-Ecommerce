<script setup>
import OrderDiscountRows from "../Components/OrderDiscountRows.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ArrowLeft, Printer, Save } from "@lucide/vue";
import AdminLayout from "../Components/AdminLayout.vue";
import CourierBooking from "../Components/CourierBooking.vue";

const props = defineProps({ order: Object, statuses: Array, couriers: Object });
const form = useForm({
    status: props.order.status,
    payment_status: props.order.payment_status,
    courier_provider: props.order.courier_provider || "",
});
const taka = (value) =>
    "৳" +
    Number(value || 0).toLocaleString("en-BD", { minimumFractionDigits: 2 });
const orderNumber = 10000 + props.order.id;
const statusLabel = (status) =>
    ({
        on_the_way: "On The Way",
        on_hold: "On Hold",
    })[status] || status.charAt(0).toUpperCase() + status.slice(1);
function save() {
    form.put(`/admin/orders/${props.order.id}`, { preserveScroll: true });
}
function downloadInvoice() {
    window.location.assign(`/admin/orders/${props.order.id}/invoice`);
}
</script>

<template>
    <Head :title="`Order #${orderNumber}`" />
    <AdminLayout :title="`Order #${orderNumber}`">
        <div class="page-heading no-print">
            <div>
                <span class="eyebrow">ORDER MANAGEMENT</span>
                <h1>Order #{{ orderNumber }}</h1>
                <p>Review customer and item details, then update fulfilment.</p>
            </div>
            <div class="heading-actions">
                <Link href="/admin/orders" class="btn secondary"
                    ><ArrowLeft :size="16" />Back</Link
                >
                <button
                    class="btn primary"
                    type="button"
                    @click="downloadInvoice"
                >
                    <Printer :size="16" />Download invoice
                </button>
            </div>
        </div>

        <section class="invoice">
            <header class="invoice-header">
                <div>
                    <span class="invoice-kicker">ORDER OVERVIEW</span>
                    <h2>#{{ orderNumber }}</h2>
                    <p>{{ new Date(order.created_at).toLocaleString() }}</p>
                </div>
                <span class="order-status-pill">{{ statusLabel(order.status) }}</span>
            </header>

            <div class="summary-strip">
                <div>
                    <small>Payment status</small>
                    <strong :class="order.payment_status">{{
                        order.payment_status
                    }}</strong>
                </div>
                <div>
                    <small>Order status</small>
                    <strong>{{ statusLabel(order.status) }}</strong>
                </div>
                <div>
                    <small>Payment method</small>
                    <strong>{{
                        order.payment_method === "cash_on_delivery"
                            ? "Cash on delivery"
                            : order.payment_method
                    }}</strong>
                </div>
                <div>
                    <small>Total items</small>
                    <strong>{{
                        order.items.reduce(
                            (total, item) => total + item.quantity,
                            0,
                        )
                    }}</strong>
                </div>
            </div>

            <div class="invoice-content">
                <div class="info-grid">
                    <section class="info-card">
                        <h3>Customer information</h3>
                        <dl>
                            <dt>Name</dt>
                            <dd>{{ order.customer_name }}</dd>
                            <dt>Phone</dt>
                            <dd>
                                <a :href="`tel:${order.phone}`">{{
                                    order.phone
                                }}</a>
                            </dd>
                            <template v-if="order.email"
                                ><dt>Email</dt>
                                <dd>{{ order.email }}</dd></template
                            >
                            <dt>Address</dt>
                            <dd>{{ order.address }}</dd>
                            <dt>Area</dt>
                            <dd>
                                {{
                                    order.area === "inside_dhaka"
                                        ? "Inside Dhaka"
                                        : "Outside Dhaka"
                                }}
                            </dd>
                        </dl>
                    </section>
                    <section class="info-card">
                        <h3>Order information</h3>
                        <dl>
                            <dt>Invoice</dt>
                            <dd>#{{ orderNumber }}</dd>
                            <dt>Order date</dt>
                            <dd>
                                {{
                                    new Date(order.created_at).toLocaleString()
                                }}
                            </dd>
                            <dt>Subtotal</dt>
                            <dd>{{ taka(order.subtotal) }}</dd>
                            <dt>Delivery</dt>
                            <dd>{{ taka(order.delivery_fee) }}</dd>
                            <dt>Payment</dt>
                            <dd>
                                {{
                                    order.payment_status === "paid"
                                        ? "Paid"
                                        : "Due"
                                }}
                            </dd>
                            <template v-if="order.customer_note"
                                ><dt>Customer note</dt>
                                <dd>{{ order.customer_note }}</dd></template
                            >
                        </dl>
                    </section>
                </div>

                <div class="items-table-wrap">
                    <table class="items-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Unit price</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(item, index) in order.items"
                                :key="item.id"
                            >
                                <td>{{ index + 1 }}</td>
                                <td>
                                    <strong>{{ item.product_name }}</strong
                                    ><small v-if="item.variant_name">{{
                                        item.variant_name
                                    }}</small>
                                </td>
                                <td>
                                    <span class="quantity">{{
                                        item.quantity
                                    }}</span>
                                </td>
                                <td>{{ taka(item.unit_price) }}</td>
                                <td>{{ taka(item.total) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="invoice-bottom">
                    <form class="status-form no-print" @submit.prevent="save">
                        <h3>Update order</h3>
                        <label>
                            <span>Order status</span>
                            <select v-model="form.status">
                                <option
                                    v-for="status in statuses"
                                    :key="status"
                                    :value="status"
                                >
                                    {{ statusLabel(status) }}
                                </option>
                            </select>
                            <small v-if="form.errors.status">{{
                                form.errors.status
                            }}</small>
                        </label>
                        <label>
                            <span>Payment status</span>
                            <select v-model="form.payment_status">
                                <option value="unpaid">Unpaid</option>
                                <option value="paid">Paid</option>
                            </select>
                        </label>
                        <label>
                            <span>Courier</span>
                            <select v-model="form.courier_provider">
                                <option value="">Not assigned</option>
                                <option
                                    v-for="(name, provider) in couriers"
                                    :key="provider"
                                    :value="provider"
                                >
                                    {{ name }}
                                </option>
                            </select>
                        </label>
                        <button
                            class="btn primary"
                            type="submit"
                            :disabled="form.processing"
                        >
                            <Save :size="16" />{{
                                form.processing ? "Saving…" : "Save updates"
                            }}
                        </button>
                    </form>
                    <div class="totals-card">
                        <div>
                            <span>Subtotal</span
                            ><strong>{{ taka(order.subtotal) }}</strong>
                        </div>
                        <div>
                            <span>Delivery</span
                            ><strong>{{ taka(order.delivery_fee) }}</strong>
                        </div>
                        <OrderDiscountRows :order="order" :format="taka"/>
                        <div class="grand-total">
                            <span>Grand total</span
                            ><strong>{{ taka(order.total) }}</strong>
                        </div>
                        <div class="paid-total">
                            <span>Paid</span
                            ><strong>{{
                                taka(
                                    order.payment_status === "paid"
                                        ? order.total
                                        : 0,
                                )
                            }}</strong>
                        </div>
                        <div class="due-total">
                            <span>Due</span
                            ><strong>{{
                                taka(
                                    order.payment_status === "paid"
                                        ? 0
                                        : order.total,
                                )
                            }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <div class="courier-section"><CourierBooking :order="order" /></div>
    </AdminLayout>
</template>

<style scoped>
.heading-actions{display:flex;flex-wrap:wrap;gap:10px}.invoice{width:100%;min-width:0;border:1px solid #e1e8e3;border-radius:18px;background:#fff;overflow:hidden;color:#293c31}.invoice-header{display:flex;align-items:center;justify-content:space-between;gap:20px;padding:26px 28px;background:#f6f9f7;border-bottom:1px solid #e5ebe6}.invoice-kicker{font-size:10px;letter-spacing:.15em;font-weight:700;color:#6c8475}.invoice-header h2{font-size:28px;letter-spacing:-.7px;margin:7px 0}.invoice-header p{font-size:12px;color:#7c8c82}.order-status-pill{padding:8px 14px;border-radius:30px;background:#e4f0e9;color:#2d7252;font-size:12px;font-weight:650;white-space:nowrap}.summary-strip{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));border-bottom:1px solid #e5ebe6;padding:20px 10px}.summary-strip>div{display:grid;gap:8px;padding:0 18px;border-right:1px solid #e5ebe6}.summary-strip>div:last-child{border:0}.summary-strip small{font-size:11px;color:#819085}.summary-strip strong{font-size:13px;text-transform:capitalize;overflow-wrap:anywhere}.summary-strip strong.paid{color:#26774f}.summary-strip strong.unpaid{color:#a75934}.invoice-content{padding:28px}.info-grid{display:grid;grid-template-columns:1fr 1fr;gap:22px}.info-card{padding:22px;border:1px solid #e3eae5;border-radius:14px;background:#fff}.info-card h3,.status-form h3{font-size:15px;font-weight:650;color:#2b4736;margin:0 0 20px}.info-card dl{display:grid;grid-template-columns:90px minmax(0,1fr);gap:14px 16px;font-size:13px;margin:0}.info-card dt{color:#859087}.info-card dd{margin:0;font-weight:500;overflow-wrap:anywhere;line-height:1.6}.info-card a{color:#267754}.items-table-wrap{margin-top:24px;border:1px solid #e3eae5;border-radius:14px;overflow:auto}.items-table{width:100%;min-width:580px;border-collapse:collapse}.items-table th{padding:15px 18px;background:#f6f9f7;color:#7a8c7f;font-size:10px;text-align:left;letter-spacing:.08em;text-transform:uppercase}.items-table td{padding:20px 18px;font-size:13px;border-top:1px solid #e8eee9}.items-table th:nth-last-child(-n+2),.items-table td:nth-last-child(-n+2){text-align:right;white-space:nowrap}.items-table td strong{font-weight:600}.items-table td small{display:block;margin-top:6px;color:#8a948d;font-size:11px}.quantity{display:inline-grid;place-items:center;min-width:30px;height:28px;border-radius:8px;background:#f0f5f1;color:#4b6c56;font-size:12px}.invoice-bottom{display:grid;grid-template-columns:minmax(0,1fr) minmax(300px,.8fr);align-items:start;gap:24px;margin-top:24px}.status-form{display:grid;grid-template-columns:1fr 1fr;gap:16px;padding:22px;background:#f8faf8;border:1px solid #e3eae5;border-radius:14px}.status-form h3{grid-column:1/-1;margin:0 0 2px}.status-form label{display:grid;gap:8px;font-size:12px;color:#718177}.status-form select{min-width:0;width:100%;border:1px solid #dbe5de;border-radius:9px;padding:12px 10px;background:#fff;font:inherit;color:#354b3d}.status-form select:focus{outline:2px solid #9cc7ad;outline-offset:2px}.status-form small{color:#b33b43}.status-form .btn{grid-column:1/-1;justify-self:start;margin-top:4px}.totals-card{padding:12px;border:1px solid #e2e9e4;border-radius:14px;background:#fff}.totals-card>div{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:12px;font-size:13px;color:#75857a}.totals-card strong{white-space:nowrap;color:#354b3d;font-variant-numeric:tabular-nums}.totals-card .grand-total{margin-top:10px;border-radius:10px;background:#edf5f0;color:#305941;font-weight:600;padding:16px 12px}.grand-total strong{font-size:22px}.totals-card .paid-total strong{color:#26774f}.totals-card .due-total strong{color:#a35f37}.courier-section{margin-top:24px}
@media(max-width:1000px){.invoice-bottom{grid-template-columns:1fr 1fr}.invoice-content{padding:22px}.info-card{padding:18px}}
@media(max-width:760px){.info-grid,.invoice-bottom{grid-template-columns:1fr}.invoice-header{padding:22px}.invoice-content{padding:18px}.summary-strip{grid-template-columns:1fr 1fr;gap:20px 0}.summary-strip>div:nth-child(2){border:0}.heading-actions{width:100%}.items-table-wrap{margin-top:18px}.status-form{padding:20px}.courier-section{margin-top:18px}}
@media(max-width:420px){.invoice-header{padding:18px;gap:10px}.invoice-header h2{font-size:24px}.invoice-content{padding:14px}.info-card{padding:16px}.info-card dl{grid-template-columns:70px minmax(0,1fr);font-size:12px;gap:12px}.status-form{grid-template-columns:1fr;padding:16px}.summary-strip>div{padding:0 14px}.summary-strip strong{font-size:12px}.order-status-pill{padding:7px 10px;font-size:11px}.heading-actions .btn{font-size:12px}.grand-total strong{font-size:20px}}
@media print{.no-print,.courier-section{display:none!important}.invoice{border:0}.invoice-header{background:white}}
</style>
