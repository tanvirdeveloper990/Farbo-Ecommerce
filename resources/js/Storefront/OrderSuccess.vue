<script setup>
import OrderDiscountRows from "../Components/OrderDiscountRows.vue";
import { Head, Link, usePage } from "@inertiajs/vue3";
import {
    ArrowLeft,
    Check,
    Download,
    PackageCheck,
    ShieldCheck,
} from "@lucide/vue";
import StorefrontLayout from "./StorefrontLayout.vue";
import { provide } from 'vue';
import { createStorefrontContext } from './storefrontContext';

const props = defineProps({
    order: Object,
    invoiceDownloadUrl: String,
    clearCart: Boolean,
});
if (props.clearCart && !window.sessionStorage.getItem(`storefront-cleared-order-${props.order.id}`)) {
    window.sessionStorage.removeItem('storefront-cart');
    window.sessionStorage.setItem(`storefront-cleared-order-${props.order.id}`, '1');
}
const storefront = createStorefrontContext();
provide('storefront', storefront);
const settings = usePage().props.storeSettings || {};
const orderNumber = 10000 + props.order.id;
const taka = (amount) =>
    "৳" +
    Number(amount || 0).toLocaleString("en-BD", {
        minimumFractionDigits: 2,
    });
const statusLabel = (status) =>
    ({
        cash_on_delivery: "Cash on delivery",
        on_the_way: "On the way",
        on_hold: "On hold",
        inside_dhaka: "Inside Dhaka",
        outside_dhaka: "Outside Dhaka",
    })[status] || status.charAt(0).toUpperCase() + status.slice(1);
</script>

<template>
    <Head :title="`Order #${orderNumber} confirmed`" />
    <StorefrontLayout>
        <main class="success-page">
            <section class="success-card">
                <div class="success-mark">
                    <Check :size="30" :stroke-width="2.5" />
                </div>
                <p class="success-eyebrow">ORDER RECEIVED</p>
                <p v-if="order.payment_method !== 'cash_on_delivery'" class="success-message"><strong>{{ order.payment_status === 'paid' ? 'Payment verified' : 'Payment not confirmed' }}</strong> · {{ order.status === 'on_hold' ? 'Your payment needs a manual review.' : (order.payment_status === 'paid' ? 'Your payment was received successfully.' : 'Keep your order number and contact us if you need help.') }}</p>
                <h1>Thank you, {{ order.customer_name }}!</h1>
                <p class="success-message">
                    Your order has been placed successfully. We’ll contact you
                    at <strong>{{ order.phone }}</strong> to confirm delivery.
                </p>

                <Link :href="`/track-order?order_id=${orderNumber}`" class="track-confirmed-order">Track your order →</Link>
                <div class="order-reference">
                    <span>Order number</span>
                    <strong>#{{ orderNumber }}</strong>
                    <small>{{
                        new Date(order.created_at).toLocaleString()
                    }}</small>
                </div>

                <div class="success-actions">
                    <a
                        :href="invoiceDownloadUrl"
                        class="download-invoice"
                        download
                    >
                        <Download :size="17" /> Download invoice
                    </a>
                    <Link href="/" class="continue-shopping">
                        <ArrowLeft :size="16" /> Continue shopping
                    </Link>
                </div>
            </section>

            <div class="receipt-grid">
                <section class="receipt-card items-card">
                    <div class="receipt-heading">
                        <PackageCheck :size="19" />
                        <div>
                            <h2>Order details</h2>
                            <p>
                                {{ order.items.length }}
                                {{
                                    order.items.length === 1
                                        ? "product"
                                        : "products"
                                }}
                            </p>
                        </div>
                    </div>
                    <div class="receipt-items">
                        <article
                            v-for="item in order.items"
                            :key="item.id"
                            class="receipt-item"
                        >
                            <div class="receipt-item-copy">
                                <strong>{{ item.product_name }}</strong>
                                <span v-if="item.variant_name">{{
                                    item.variant_name
                                }}</span>
                                <small>
                                    {{ item.quantity }} ×
                                    {{ taka(item.unit_price) }}
                                </small>
                            </div>
                            <strong class="receipt-line-total">{{
                                taka(item.total)
                            }}</strong>
                        </article>
                    </div>
                    <div class="receipt-totals">
                        <div>
                            <span>Subtotal</span>
                            <strong>{{ taka(order.subtotal) }}</strong>
                        </div>
                        <div>
                            <span>Delivery charge</span>
                            <strong>{{
                                Number(order.delivery_fee) > 0
                                    ? taka(order.delivery_fee)
                                    : "Free"
                            }}</strong>
                        </div>
                        <OrderDiscountRows :order="order" :format="taka"/>
                        <div class="receipt-grand-total">
                            <span>Total payable</span>
                            <strong>{{ taka(order.total) }}</strong>
                        </div>
                    </div>
                </section>

                <aside class="receipt-card delivery-card">
                    <div class="receipt-heading">
                        <ShieldCheck :size="19" />
                        <div>
                            <h2>Delivery &amp; payment</h2>
                            <p>Information saved with your order</p>
                        </div>
                    </div>
                    <dl class="delivery-details">
                        <dt>Recipient</dt>
                        <dd>{{ order.customer_name }}</dd>
                        <dt>Phone</dt>
                        <dd>{{ order.phone }}</dd>
                        <template v-if="order.email">
                            <dt>Email</dt>
                            <dd>{{ order.email }}</dd>
                        </template>
                        <dt>Delivery area</dt>
                        <dd>{{ statusLabel(order.area) }}</dd>
                        <dt>Address</dt>
                        <dd>{{ order.address }}</dd>
                        <dt>Payment method</dt>
                        <dd>{{ statusLabel(order.payment_method) }}</dd>
                        <dt>Payment status</dt>
                        <dd>{{ statusLabel(order.payment_status) }}</dd>
                        <template v-if="order.customer_note">
                            <dt>Order note</dt>
                            <dd>{{ order.customer_note }}</dd>
                        </template>
                    </dl>
                    <p class="delivery-status-note">
                        Your order is currently
                        <strong>{{ statusLabel(order.status) }}</strong
                        >.
                    </p>
                </aside>
            </div>
            <p class="success-footer-note">
                {{ settings.company_name || "Thank you for shopping with us" }}
                <span> · </span>
                We appreciate your order.
            </p>
        </main>
    </StorefrontLayout>
</template>

<style scoped>
.success-page {
    width: min(1080px, 100%);
    min-height: 62vh;
    margin: 0 auto;
    padding: 38px 22px 68px;
    color: #0B2A5B;
}
.success-card {
    display: grid;
    justify-items: center;
    border: 1px solid #dce4ef;
    border-radius: 16px;
    background:
        radial-gradient(ellipse at 50% 0, #F3F6FB 0, transparent 65%), #fff;
    padding: clamp(25px, 5vw, 42px) 20px 30px;
    text-align: center;
    box-shadow: 0 12px 40px #0B2A5B0a;
}
.success-mark {
    display: grid;
    width: 58px;
    height: 58px;
    place-items: center;
    border: 1px solid #c9e8d4;
    border-radius: 50%;
    background: #eaf7ee;
    color: #218153;
}
.success-eyebrow {
    margin-top: 17px;
    color: #64748B;
    font-size: 9px;
    font-weight: 750;
    letter-spacing: 0.2em;
}
.success-card h1 {
    margin-top: 7px;
    font-family: Poppins, sans-serif;
    font-size: clamp(25px, 4vw, 36px);
    font-weight: 500;
    letter-spacing: -0.5px;
}
.success-message {
    max-width: 550px;
    margin-top: 8px;
    color: #64748B;
    font-size: 12px;
    line-height: 1.8;
}
.success-message strong {
    color: #0B2A5B;
}
.order-reference {
    display: grid;
    justify-items: center;
    gap: 4px;
    margin-top: 19px;
    border-block: 1px solid #F3F6FB;
    padding: 12px 28px;
}
.order-reference span {
    color: #64748b;
    font-size: 9px;
    font-weight: 650;
    letter-spacing: 0.1em;
    text-transform: uppercase;
}
.order-reference strong {
    color: #E30613;
    font-size: 19px;
    letter-spacing: 0.03em;
}
.order-reference small {
    color: #64748B;
    font-size: 9px;
}
.success-actions {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 9px;
    margin-top: 20px;
}
.download-invoice,
.continue-shopping {
    display: inline-flex;
    min-height: 42px;
    align-items: center;
    justify-content: center;
    gap: 8px;
    border: 1px solid #E30613;
    border-radius: 8px;
    padding: 0 15px;
    font-size: 11px;
    font-weight: 650;
    transition:
        background-color 150ms ease,
        color 150ms ease;
}
.download-invoice {
    background: #E30613;
    color: white;
}
.download-invoice:hover {
    background: #C10510;
}
.continue-shopping {
    background: white;
    color: #E30613;
}
.continue-shopping:hover {
    background: #F3F6FB;
}
.receipt-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.15fr) minmax(280px, 0.85fr);
    align-items: start;
    gap: 17px;
    margin-top: 18px;
}
.receipt-card {
    min-width: 0;
    border: 1px solid #dce4ef;
    border-radius: 13px;
    background: #fff;
    padding: 20px;
    box-shadow: 0 8px 28px #0B2A5B08;
}
.receipt-heading {
    display: flex;
    align-items: center;
    gap: 11px;
    border-bottom: 1px solid #dce4ef;
    padding-bottom: 14px;
    color: #E30613;
}
.receipt-heading h2 {
    color: #0B2A5B;
    font-size: 14px;
    font-weight: 700;
}
.receipt-heading p {
    margin-top: 3px;
    color: #64748B;
    font-size: 10px;
}
.receipt-items {
    display: grid;
}
.receipt-item {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 15px;
    border-bottom: 1px solid #F3F6FB;
    padding: 14px 0;
}
.receipt-item-copy {
    display: grid;
    min-width: 0;
    gap: 4px;
}
.receipt-item-copy strong {
    color: #0B2A5B;
    font-size: 11px;
}
.receipt-item-copy span,
.receipt-item-copy small {
    color: #64748b;
    font-size: 10px;
}
.receipt-line-total {
    flex: 0 0 auto;
    color: #0B2A5B;
    font-size: 11px;
}
.receipt-totals {
    display: grid;
    gap: 10px;
    padding-top: 14px;
}
.receipt-totals div {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    color: #64748B;
    font-size: 11px;
}
.receipt-totals strong {
    color: #0B2A5B;
    font-weight: 650;
}
.receipt-totals .receipt-grand-total {
    margin-top: 2px;
    border-top: 1px solid #dce4ef;
    padding-top: 12px;
    color: #0B2A5B;
    font-size: 13px;
    font-weight: 700;
}
.receipt-grand-total strong {
    color: #E30613;
    font-size: 16px;
}
.delivery-details {
    display: grid;
    grid-template-columns: minmax(90px, 0.45fr) minmax(0, 1fr);
    gap: 11px 12px;
    margin: 0;
    padding-top: 16px;
    font-size: 10px;
}
.delivery-details dt {
    color: #64748B;
}
.delivery-details dd {
    overflow-wrap: anywhere;
    margin: 0;
    color: #0B2A5B;
    font-weight: 550;
}
.delivery-status-note {
    margin-top: 17px;
    border-radius: 8px;
    background: #fbf5e9;
    padding: 10px 12px;
    color: #0B2A5B;
    font-size: 10px;
}
.delivery-status-note strong {
    color: #785719;
}
.success-footer-note {
    margin-top: 19px;
    color: #64748B;
    font-size: 10px;
    text-align: center;
}
.success-footer-note span {
    padding-inline: 4px;
}
@media (max-width: 760px) {
    .success-page {
        padding: 25px 15px 48px;
    }
    .receipt-grid {
        grid-template-columns: 1fr;
    }
}
@media (max-width: 440px) {
    .success-page {
        padding-inline: 12px;
    }
    .success-card {
        border-radius: 12px;
        padding-inline: 15px;
    }
    .success-actions {
        width: 100%;
    }
    .success-actions a {
        width: 100%;
    }
    .receipt-card {
        padding: 16px;
    }
    .delivery-details {
        grid-template-columns: 82px minmax(0, 1fr);
    }
}

.success-page{font-family: Poppins, sans-serif;width:min(1180px,100%);padding-top:40px}
.success-card{position:relative;overflow:hidden;border-radius:24px;background:#F3F6FB;border:1px solid #F3F6FB;box-shadow:0 15px 50px #0B2A5B08;animation:receipt-arrive .6s ease both}
.success-card:before,.success-card:after{content:'';position:absolute;border:1px solid #DCE4EF;border-radius:50%;width:230px;height:230px;pointer-events:none;opacity:.55}.success-card:before{left:-140px;top:-100px}.success-card:after{right:-140px;bottom:-120px}
.success-card h1{font-family:inherit;font-size:clamp(25px,4vw,40px);letter-spacing:-1.2px}.success-mark{width:76px;height:76px;background:#23775c;color:white;border:6px solid #e2f1e9;box-shadow:0 0 0 8px #f0f7f3;animation:confirmation-pop .65s ease both}
.success-message{max-width:520px;line-height:1.8}.track-confirmed-order{color:#E30613;font-size:13px;font-weight:600;margin-top:15px}.order-reference{border-radius:14px;background:white;padding:16px 30px;margin-top:20px}.receipt-card{border-radius:20px;box-shadow:0 8px 32px #0B2A5B05;animation:receipt-arrive .6s .12s ease both}.download-invoice,.continue-shopping{border-radius:10px;min-height:46px}.success-footer-note{font-size:12px;line-height:1.7}
@keyframes receipt-arrive{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:none}}@keyframes confirmation-pop{from{opacity:0;transform:scale(.7)}to{opacity:1;transform:scale(1)}}@media(prefers-reduced-motion:reduce){.success-card,.success-mark,.receipt-card{animation:none}}@media(max-width:600px){.success-page{padding:22px 12px 45px}.success-actions{width:100%;flex-direction:column}.success-actions a{justify-content:center}.success-card{padding-inline:18px}}
.receipt-grid{gap:24px;margin-top:24px}.receipt-card{padding:26px}.receipt-heading h2{font-size:17px}.receipt-heading p{font-size:12px}.receipt-item{padding:18px 0}.receipt-item-copy strong,.receipt-line-total{font-size:14px}.receipt-item-copy span,.receipt-item-copy small{font-size:12px}.receipt-totals{gap:13px;padding-top:20px}.receipt-totals>div{font-size:13px}.receipt-totals .receipt-grand-total{padding-top:18px;margin-top:5px;font-size:15px}.receipt-grand-total strong{font-size:23px}.delivery-details{font-size:13px;gap:16px 14px}.delivery-status-note{font-size:12px;padding:14px}.success-message{font-size:14px}.success-actions a{font-size:13px;padding-inline:20px}.order-reference small{font-size:11px}.order-reference span{font-size:10px}.order-reference strong{font-size:22px}@media(max-width:760px){.receipt-card{padding:20px}.delivery-details{font-size:12px}.receipt-grid{gap:18px}}@media(max-width:380px){.receipt-card{padding:16px}.receipt-item-copy strong,.receipt-line-total{font-size:12px}}
</style>
