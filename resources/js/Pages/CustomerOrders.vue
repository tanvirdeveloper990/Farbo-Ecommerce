<script setup>
import { Head, Link } from "@inertiajs/vue3";
import { Eye, PackageCheck, ShoppingBag, Clock3 } from "@lucide/vue";
import CustomerLayout from "../Components/CustomerLayout.vue";

defineProps({ records: Object, summary: Object });
const taka = (amount) =>
    "৳" +
    Number(amount || 0).toLocaleString("en-BD", { minimumFractionDigits: 2 });
const orderNumber = (order) => 10000 + order.id;
const statusLabel = (status) =>
    ({
        on_the_way: "On the way",
        on_hold: "On hold",
    })[status] || status.charAt(0).toUpperCase() + status.slice(1);
</script>

<template>
    <Head title="My orders" />
    <CustomerLayout>
        <div class="customer-orders-page">
            <div class="account-page-heading">
                <span class="account-eyebrow">YOUR PURCHASES</span>
                <h1>My orders</h1>
                <p>Track your orders and view the details of every purchase.</p>
            </div>
            <section class="order-stat-grid">
                <article class="order-stat">
                    <span><ShoppingBag :size="18" /></span>
                    <strong>{{ summary.total }}</strong>
                    <small>Total orders</small>
                </article>
                <article class="order-stat">
                    <span><Clock3 :size="18" /></span>
                    <strong>{{ summary.pending }}</strong>
                    <small>In progress</small>
                </article>
                <article class="order-stat">
                    <span><PackageCheck :size="18" /></span>
                    <strong>{{ summary.completed }}</strong>
                    <small>Completed</small>
                </article>
            </section>
            <section class="orders-table-panel">
                <div class="orders-title-row">
                    <div>
                        <h2>Order history</h2>
                        <p>{{ records.total }} orders in your account</p>
                    </div>
                </div>
                <div v-if="records.data.length" class="orders-table-scroll">
                    <table class="customer-orders-table">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Date</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th><span class="sr-only">View</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="order in records.data" :key="order.id">
                                <td>
                                    <Link
                                        class="order-number-link"
                                        :href="`/my-orders/${order.id}`"
                                        >#{{ orderNumber(order) }}</Link
                                    >
                                </td>
                                <td>
                                    {{
                                        new Date(
                                            order.created_at,
                                        ).toLocaleDateString("en-BD", {
                                            year: "numeric",
                                            month: "short",
                                            day: "numeric",
                                        })
                                    }}
                                </td>
                                <td>
                                    {{
                                        order.items.reduce(
                                            (total, item) =>
                                                total + item.quantity,
                                            0,
                                        )
                                    }}
                                </td>
                                <td class="order-total">
                                    {{ taka(order.total) }}
                                </td>
                                <td>
                                    <span
                                        class="order-status"
                                        :class="`status-${order.status}`"
                                        >{{ statusLabel(order.status) }}</span
                                    >
                                </td>
                                <td>
                                    <span
                                        class="payment-status"
                                        :class="`payment-${order.payment_status}`"
                                        >{{
                                            statusLabel(order.payment_status)
                                        }}</span
                                    >
                                </td>
                                <td class="view-order-cell">
                                    <Link
                                        :href="`/my-orders/${order.id}`"
                                        class="view-order-button"
                                        :aria-label="`View order ${orderNumber(order)}`"
                                        title="View order"
                                    >
                                        <Eye :size="17" />
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="orders-empty">
                    <span><ShoppingBag :size="26" /></span>
                    <h3>No orders yet</h3>
                    <p>Your purchases will appear here after checkout.</p>
                    <Link href="/" class="shop-link">Explore the store</Link>
                </div>
                <nav
                    v-if="records.links.length > 3"
                    class="orders-pagination"
                    aria-label="Order pages"
                >
                    <Link
                        v-for="(link, index) in records.links"
                        :key="index"
                        :href="link.url || '#'"
                        :class="{ active: link.active, disabled: !link.url }"
                        v-html="link.label"
                        preserve-scroll
                    />
                </nav>
            </section>
        </div>
    </CustomerLayout>
</template>

<style scoped>
.account-page-heading {
    margin: 2px 0 22px;
}
.account-eyebrow {
    color: #64748b;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.17em;
}
.account-page-heading h1 {
    margin-top: 7px;
    color: #0B2A5B;
    font-size: clamp(24px, 3vw, 32px);
}
.account-page-heading p {
    margin-top: 5px;
}
.order-stat-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 13px;
    margin-bottom: 20px;
}
.order-stat {
    display: grid;
    min-height: 112px;
    grid-template-columns: 38px 1fr;
    align-content: center;
    align-items: center;
    gap: 3px 11px;
    border: 1px solid #dce4ef;
    border-radius: 12px;
    background: white;
    padding: 16px;
}
.order-stat > span {
    display: grid;
    width: 38px;
    height: 38px;
    grid-row: span 2;
    place-items: center;
    border-radius: 10px;
    background: #F3F6FB;
    color: #E30613;
}
.order-stat strong {
    color: #0B2A5B;
    font-size: 21px;
}
.order-stat small {
    color: #64748b;
    font-size: 10px;
}
.orders-table-panel {
    overflow: hidden;
    border: 1px solid #dce4ef;
    border-radius: 13px;
    background: #fff;
    box-shadow: 0 8px 30px #0B2A5B08;
}
.orders-title-row {
    padding: 20px 20px 17px;
}
.orders-title-row h2 {
    color: #0B2A5B;
    font-size: 15px;
}
.orders-title-row p {
    margin-top: 4px;
    font-size: 11px;
}
.orders-table-scroll {
    overflow-x: auto;
}
.customer-orders-table {
    width: 100%;
    border-collapse: collapse;
    white-space: nowrap;
}
.customer-orders-table th {
    background: #F3F6FB;
    padding: 12px 15px;
    color: #64748B;
    font-size: 10px;
    font-weight: 700;
    text-align: left;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}
.customer-orders-table td {
    border-top: 1px solid #F3F6FB;
    padding: 13px 15px;
    color: #0B2A5B;
    font-size: 11px;
}
.order-number-link {
    color: #E30613;
    font-weight: 700;
}
.order-number-link:hover {
    text-decoration: underline;
}
.order-total {
    color: #0B2A5B !important;
    font-weight: 700;
}
.order-status,
.payment-status {
    display: inline-flex;
    border-radius: 99px;
    background: #F3F6FB;
    padding: 5px 9px;
    color: #0B2A5B;
    font-size: 9px;
    font-weight: 650;
}
.status-completed,
.payment-paid {
    background: #eaf6ef;
    color: #18724e;
}
.status-cancelled {
    background: #fff0ef;
    color: #a12626;
}
.status-pending {
    background: #fff5df;
    color: #936716;
}
.view-order-cell {
    text-align: right;
}
.view-order-button {
    display: inline-grid;
    width: 33px;
    height: 33px;
    place-items: center;
    border: 1px solid #dce4ef;
    border-radius: 8px;
    color: #64748B;
}
.view-order-button:hover {
    border-color: #E30613;
    color: #E30613;
}
.orders-empty {
    display: grid;
    justify-items: center;
    padding: 48px 20px;
    text-align: center;
}
.orders-empty > span {
    display: grid;
    width: 54px;
    height: 54px;
    place-items: center;
    border-radius: 50%;
    background: #F3F6FB;
    color: #E30613;
}
.orders-empty h3 {
    margin-top: 14px;
    color: #0B2A5B;
}
.orders-empty p {
    margin-top: 5px;
}
.shop-link {
    margin-top: 17px;
    border-radius: 8px;
    background: #E30613;
    padding: 10px 15px;
    color: white;
    font-size: 11px;
}
.orders-pagination {
    display: flex;
    justify-content: flex-end;
    gap: 5px;
    border-top: 1px solid #dce4ef;
    padding: 13px 16px;
}
.orders-pagination :deep(a) {
    display: grid;
    min-width: 30px;
    height: 30px;
    place-items: center;
    border: 1px solid #dce4ef;
    border-radius: 6px;
    color: #64748B;
    font-size: 11px;
}
.orders-pagination :deep(a.active) {
    border-color: #E30613;
    background: #E30613;
    color: white;
}
.orders-pagination :deep(a.disabled) {
    opacity: 0.45;
    pointer-events: none;
}
@media (max-width: 640px) {
    .order-stat-grid {
        gap: 8px;
    }
    .order-stat {
        min-height: 95px;
        grid-template-columns: 1fr;
        justify-items: start;
        padding: 11px;
    }
    .order-stat > span {
        grid-row: auto;
        width: 32px;
        height: 32px;
    }
    .order-stat strong {
        font-size: 18px;
    }
    .order-stat small {
        font-size: 9px;
    }
    .orders-title-row {
        padding-inline: 14px;
    }
}
</style>
