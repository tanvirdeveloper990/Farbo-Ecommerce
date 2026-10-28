<script setup>
import OrderDiscountRows from "../Components/OrderDiscountRows.vue";
import { Head, Link } from "@inertiajs/vue3";
import { ArrowLeft, Printer } from "@lucide/vue";
import CustomerLayout from "../Components/CustomerLayout.vue";

const props = defineProps({ order: Object, invoiceDownloadUrl: String });
const taka = (amount) =>
    "৳" +
    Number(amount || 0).toLocaleString("en-BD", { minimumFractionDigits: 2 });
const orderNumber = (id) => 10000 + id;
const statusLabel = (status) =>
    ({
        on_the_way: "On the way",
        on_hold: "On hold",
        cash_on_delivery: "Cash on delivery",
    })[status] || status.charAt(0).toUpperCase() + status.slice(1);
function printOrder() {
    window.location.assign(props.invoiceDownloadUrl);
}
</script>

<template>
    <Head :title="`Order #${orderNumber(order.id)}`" />
    <CustomerLayout>
        <div class="customer-order-detail">
            <div class="order-detail-actions print-hide">
                <Link href="/my-orders" class="back-to-orders"
                    ><ArrowLeft :size="16" /> Back to my orders</Link
                >
                <button class="print-button" type="button" @click="printOrder">
                    <Printer :size="16" /> Download invoice
                </button>
            </div>
            <article class="order-invoice">
                <header class="invoice-header">
                    <div>
                        <span>ORDER SUMMARY</span>
                        <h1>Order #{{ orderNumber(order.id) }}</h1>
                        <p>
                            Placed
                            {{ new Date(order.created_at).toLocaleString() }}
                        </p>
                    </div>
                    <div class="invoice-status">
                        {{ statusLabel(order.status) }}
                    </div>
                </header>
                <div class="invoice-information">
                    <section>
                        <h2>Delivery information</h2>
                        <dl>
                            <dt>Name</dt>
                            <dd>{{ order.customer_name }}</dd>
                            <dt>Phone</dt>
                            <dd>{{ order.phone }}</dd>
                            <template v-if="order.email">
                                <dt>Email</dt>
                                <dd>{{ order.email }}</dd>
                            </template>
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
                    <section>
                        <h2>Payment</h2>
                        <dl>
                            <dt>Method</dt>
                            <dd>{{ statusLabel(order.payment_method) }}</dd>
                            <dt>Status</dt>
                            <dd>{{ statusLabel(order.payment_status) }}</dd>
                            <dt>Delivery</dt>
                            <dd>{{ taka(order.delivery_fee) }}</dd>
                            <template v-if="order.customer_note">
                                <dt>Note</dt>
                                <dd>{{ order.customer_note }}</dd>
                            </template>
                        </dl>
                    </section>
                </div>
                <div class="order-items-wrap">
                    <table class="order-items">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Unit price</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in order.items" :key="item.id">
                                <td>
                                    <strong>{{ item.product_name }}</strong>
                                    <small v-if="item.variant_name">{{
                                        item.variant_name
                                    }}</small>
                                </td>
                                <td>{{ item.quantity }}</td>
                                <td>{{ taka(item.unit_price) }}</td>
                                <td>{{ taka(item.total) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="invoice-total">
                    <div>
                        <span>Subtotal</span
                        ><strong>{{ taka(order.subtotal) }}</strong>
                    </div>
                    <div>
                        <span>Delivery fee</span
                        ><strong>{{ taka(order.delivery_fee) }}</strong>
                    </div>
                    <OrderDiscountRows :order="order" :format="taka"/>
                        <div class="grand-total">
                        <span>Total</span
                        ><strong>{{ taka(order.total) }}</strong>
                    </div>
                </div>
            </article>
        </div>
    </CustomerLayout>
</template>

<style scoped>
.customer-order-detail {
    min-width: 0;
}
.order-detail-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 17px;
}
.back-to-orders,
.print-button {
    display: inline-flex;
    min-height: 39px;
    align-items: center;
    justify-content: center;
    gap: 7px;
    border: 1px solid #DCE4EF;
    border-radius: 8px;
    background: white;
    padding: 0 12px;
    color: #0B2A5B;
    font-size: 11px;
}
.print-button {
    border-color: #E30613;
    background: #E30613;
    color: white;
    cursor: pointer;
}
.order-invoice {
    overflow: hidden;
    border: 1px solid #dce4ef;
    border-radius: 13px;
    background: white;
    box-shadow: 0 8px 30px #0B2A5B08;
}
.invoice-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    border-bottom: 1px solid #dce4ef;
    padding: 22px;
}
.invoice-header > div:first-child > span {
    color: #64748b;
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 0.16em;
}
.invoice-header h1 {
    margin-top: 6px;
    color: #0B2A5B;
    font-size: 22px;
}
.invoice-header p {
    margin-top: 3px;
    font-size: 10px;
}
.invoice-status {
    border-radius: 99px;
    background: #fff5df;
    padding: 7px 11px;
    color: #936716;
    font-size: 10px;
    font-weight: 650;
}
.invoice-information {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px;
    padding: 21px 22px;
}
.invoice-information section {
    min-width: 0;
}
.invoice-information h2 {
    margin-bottom: 12px;
    color: #0B2A5B;
    font-size: 13px;
}
.invoice-information dl {
    display: grid;
    grid-template-columns: minmax(62px, 0.4fr) minmax(0, 1fr);
    gap: 8px 11px;
    margin: 0;
    font-size: 10px;
}
.invoice-information dt {
    color: #64748B;
}
.invoice-information dd {
    overflow-wrap: anywhere;
    margin: 0;
    color: #0B2A5B;
    font-weight: 550;
}
.order-items-wrap {
    overflow-x: auto;
}
.order-items {
    width: 100%;
    border-collapse: collapse;
    white-space: nowrap;
}
.order-items th {
    background: #F3F6FB;
    padding: 11px 14px;
    color: #64748B;
    font-size: 9px;
    text-align: left;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}
.order-items td {
    border-top: 1px solid #F3F6FB;
    padding: 13px 14px;
    color: #0B2A5B;
    font-size: 10px;
}
.order-items td:first-child {
    white-space: normal;
}
.order-items td strong,
.order-items td small {
    display: block;
}
.order-items td strong {
    color: #0B2A5B;
    font-size: 11px;
}
.order-items td small {
    margin-top: 3px;
}
.invoice-total {
    display: grid;
    width: min(330px, 100%);
    gap: 9px;
    margin-left: auto;
    padding: 18px 22px 22px;
}
.invoice-total div {
    display: flex;
    justify-content: space-between;
    gap: 15px;
    color: #64748B;
    font-size: 11px;
}
.invoice-total strong {
    color: #0B2A5B;
    font-weight: 650;
}
.invoice-total .grand-total {
    border-top: 1px solid #dce4ef;
    padding-top: 11px;
    color: #0B2A5B;
    font-size: 13px;
    font-weight: 700;
}
.invoice-total .grand-total strong {
    color: #E30613;
    font-size: 17px;
}
@media (max-width: 560px) {
    .invoice-information {
        grid-template-columns: 1fr;
        padding: 17px;
    }
    .invoice-header {
        align-items: flex-start;
        padding: 17px;
    }
    .invoice-header h1 {
        font-size: 18px;
    }
    .order-items th,
    .order-items td {
        padding-inline: 10px;
    }
}
@media print {
    :global(body) {
        background: white !important;
    }
    :global(#siteHeader),
    :global(#contact),
    :global(.customer-sidebar),
    :global(.print-hide) {
        display: none !important;
    }
    :global(.customer-account-shell) {
        display: block !important;
        width: 100% !important;
        min-height: 0 !important;
        padding: 0 !important;
    }
    :global(.customer-account-content) {
        width: 100% !important;
    }
    .order-invoice {
        border: 0;
        box-shadow: none;
    }
}
</style>
