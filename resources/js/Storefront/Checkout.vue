<script setup>
import OrderDiscountRows from "../Components/OrderDiscountRows.vue";
import { computed, provide, ref, watch } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import {
    ArrowLeft,
    Banknote,
    Check,
    CreditCard,
    ShieldCheck,
    Minus, Plus, Trash2, Ticket, Gift, MapPin, ChevronDown,
} from "@lucide/vue";
import axios from "axios";
import StorefrontLayout from "./StorefrontLayout.vue";
import { trackCartChange, trackPaymentInfo } from "./tracking";
import { createStorefrontContext, saveCart } from "./storefrontContext";

const props = defineProps({
    customer: Object,
    shippingCharges: Object,
    freeShippingThreshold: Number,
    policies: Array,
    sslcommerzAvailable: Boolean,
    sslcommerzSandbox: Boolean,
});
const storefront = createStorefrontContext();
provide("storefront", storefront);
const page = usePage();
const cart = storefront.cart;
const form = ref({
    customer_name: props.customer?.name || "",
    phone: props.customer?.phone || "",
    address: props.customer?.address || "",
    area: "inside_dhaka",
    payment_method: "cash_on_delivery",
    customer_note: "",
    coupon_code: "", gift_card_code: "",
    policy_accepted: false,
});
const errors = ref({});
const processing = ref(false);
const subtotal = computed(() =>
    cart.value.reduce((total, item) => total + item.price * item.qty, 0),
);
const deliveryFee = computed(() =>
    subtotal.value >= props.freeShippingThreshold
        ? 0
        : Number(props.shippingCharges[form.value.area] || 0),
);
const discountInputsOpen = ref(false);
const appliedDiscounts = ref(null), discountBusy = ref(false), discountError = ref('');
const cartItems = () => cart.value.map(item => ({product_id:item.id,variant_id:item.variant_id,quantity:item.qty}));
const discountSnapshot = () => JSON.stringify([cartItems(),form.value.coupon_code.trim(),form.value.gift_card_code.trim()]);
const discountAmount = computed(() => Math.min(subtotal.value,Number(appliedDiscounts.value?.discount_amount || 0)));
const total = computed(() => subtotal.value - discountAmount.value + deliveryFee.value);
watch(discountSnapshot, () => {appliedDiscounts.value=null;discountError.value='';});
async function applyDiscounts(){
    const snapshot=discountSnapshot();discountBusy.value=true;discountError.value='';appliedDiscounts.value=null;
    try{const response=await axios.post('/checkout/discounts',{coupon_code:form.value.coupon_code,gift_card_code:form.value.gift_card_code,items:cartItems()});if(snapshot===discountSnapshot())appliedDiscounts.value=response.data;}
    catch(error){if(snapshot===discountSnapshot())discountError.value=Object.values(error.response?.data?.errors||{})[0]?.[0]||'Unable to apply these codes. Please check them and try again.';}
    finally{discountBusy.value=false;}
}
const taka = (value) =>
    "৳" +
    Number(value || 0).toLocaleString("en-BD", { minimumFractionDigits: 2 });
const paymentMethods = computed(() => [
    {
        id: "cash_on_delivery",
        label: "Cash on Delivery",
        note: "Pay when your order arrives",
        icon: Banknote,
    },
    ...(props.sslcommerzAvailable ? [{ id: 'sslcommerz', label: 'Online payment', note: props.sslcommerzSandbox ? 'SSLcommerz · Sandbox test' : 'Cards & mobile banking', icon: CreditCard }] : []),
]);
const policyUrl = (slug) => `/policies/${slug}`;
function changeQuantity(item, change) {
    const next = item.qty + change;
    if (next < 1 || processing.value) return;
    if (next > Math.min(Number(item.stock ?? 100), 100)) {
        storefront.notify('The selected quantity is not available.');
        return;
    }
    trackCartChange(change > 0 ? 'add_to_cart' : 'remove_from_cart', item, Math.abs(change));
    item.qty = next;
    saveCart(cart.value);
}
const policyName = (slug) =>
    props.policies.find((policy) => policy.slug === slug)?.title ||
    (slug === "refund-policy" ? "Refund Policy" : "Privacy Policy");

function submit() {
    errors.value = {};
    processing.value = true;
    trackPaymentInfo(cart.value, form.value.payment_method);
    router.post(
        "/checkout",
        {
            ...form.value,
            items: cart.value.map((item) => ({
                product_id: item.id,
                variant_id: item.variant_id,
                quantity: item.qty,
            })),
        },
        {
            onError: (response) => {
                errors.value = response;
                storefront.notify(Object.values(response)[0] || 'Please check your checkout details.');
                window.scrollTo({ top: 0, behavior: "smooth" });
            },
            onFinish: () => {
                processing.value = false;
            },
            onSuccess: () => {
                window.sessionStorage.removeItem("storefront-cart");
            },
        },
    );
}
</script>

<template>
    <Head title="Checkout" />
    <StorefrontLayout>
        <main class="checkout-page">
            <div class="checkout-heading">
                <div>
                    <p>SECURE CHECKOUT</p>
                    <h1>Complete your order</h1>
                    <span>Delivery details, payment and a final review.</span>
                </div>
                <Link href="/" class="continue-shopping">
                    <ArrowLeft :size="15" />
                    Continue shopping
                </Link>
            </div>

            <div v-if="page.props.flash?.success" class="checkout-success">
                <Check :size="18" />{{ page.props.flash.success }}
            </div>
            <p v-if="errors.items" class="checkout-error">{{ errors.items }}</p>
            <p
                v-for="[key, message] in Object.entries(errors).filter(
                    ([key]) => key.startsWith('items.'),
                )"
                :key="key"
                class="checkout-error"
            >
                {{ message }}
            </p>

            <div v-if="!cart.length" class="empty-checkout">
                <h2>Your cart is empty</h2>
                <p>Add a product before checking out.</p>
                <Link href="/" class="primary-button">Browse products</Link>
            </div>

            <form v-else class="checkout-grid" @submit.prevent="submit">
                <div class="checkout-fields">
                    <div class="checkout-card checkout-form-card">
                        <div class="card-heading checkout-main-heading">
                            <span class="checkout-section-icon"><MapPin :size="20"/></span><div><h2>Delivery details</h2><p>Where should we send your order?</p></div>
                        </div>
                        <section class="checkout-section">
                            <div class="form-grid">
                                <label class="checkout-field">
                                    <span>Full name *</span>
                                    <input
                                        v-model="form.customer_name"
                                        autocomplete="name"
                                        maxlength="160"
                                        required
                                    />
                                    <small v-if="errors.customer_name">{{
                                        errors.customer_name
                                    }}</small>
                                </label>
                                <label class="checkout-field">
                                    <span>Phone number *</span>
                                    <input
                                        v-model="form.phone"
                                        type="tel"
                                        autocomplete="tel"
                                        maxlength="40"
                                        required
                                    />
                                    <small v-if="errors.phone">{{
                                        errors.phone
                                    }}</small>
                                </label>
                                <label class="checkout-field full-width">
                                    <span>Full delivery address *</span>
                                    <textarea
                                        v-model="form.address"
                                        autocomplete="street-address"
                                        rows="3"
                                        maxlength="2000"
                                        required
                                    ></textarea>
                                    <small v-if="errors.address">{{
                                        errors.address
                                    }}</small>
                                </label>
                                <label class="checkout-field full-width">
                                    <span>Order note (optional)</span>
                                    <textarea
                                        v-model="form.customer_note"
                                        rows="2"
                                        maxlength="2000"
                                        placeholder="Any delivery instructions?"
                                    ></textarea>
                                    <small v-if="errors.customer_note">{{
                                        errors.customer_note
                                    }}</small>
                                </label>
                            </div>
                        </section>

                        <section class="checkout-section">
                            <label class="checkout-field">
                                <span>Delivery area *</span>
                                <select v-model="form.area" required>
                                    <option value="inside_dhaka">
                                        Inside Dhaka —
                                        {{ taka(shippingCharges.inside_dhaka) }}
                                    </option>
                                    <option value="outside_dhaka">
                                        Outside Dhaka —
                                        {{
                                            taka(shippingCharges.outside_dhaka)
                                        }}
                                    </option>
                                </select>
                                <small v-if="errors.area">{{
                                    errors.area
                                }}</small>
                            </label>
                        </section>

                        <section class="checkout-section">
                            <h3 class="checkout-step-heading"><CreditCard :size="18"/>Payment method</h3><div class="payment-grid">
                                <label
                                    v-for="method in paymentMethods"
                                    :key="method.id"
                                    class="payment-option"
                                    :class="{
                                        selected:
                                            form.payment_method === method.id,
                                    }"
                                >
                                    <input
                                        v-model="form.payment_method"
                                        type="radio"
                                        name="payment_method"
                                        :value="method.id"
                                    />
                                    <span class="payment-icon"
                                        ><component
                                            :is="method.icon"
                                            :size="19"
                                    /></span>
                                    <strong>{{ method.label }}</strong>
                                    <small>{{ method.note }}</small>
                                </label>
                            </div>
                            <p
                                v-if="
                                    form.payment_method !== 'cash_on_delivery'
                                "
                                class="payment-notice"
                            >
                                {{ sslcommerzSandbox ? 'Test mode: no real money will be charged.' : 'Pay securely through SSLcommerz.' }}
                                You will continue to the secure payment page after placing your order.
                            </p>
                            <small
                                v-if="errors.payment_method"
                                class="checkout-field-error"
                                >{{ errors.payment_method }}</small
                            >
                        </section>

                        <label class="policy-consent">
                            <input
                                v-model="form.policy_accepted"
                                type="checkbox"
                                required
                            />
                            <span>
                                I agree to the
                                <Link
                                    :href="policyUrl('refund-policy')"
                                    target="_blank"
                                    >{{ policyName("refund-policy") }}</Link
                                >
                                and
                                <Link
                                    :href="policyUrl('privacy-policy')"
                                    target="_blank"
                                    >{{ policyName("privacy-policy") }}</Link
                                >.
                            </span>
                        </label>
                        <small
                            v-if="errors.policy_accepted"
                            class="checkout-field-error"
                            >{{ errors.policy_accepted }}</small
                        >

                        <button
                            class="primary-button submit-order"
                            type="submit"
                            :disabled="processing"
                        >
                            <ShieldCheck :size="17" />{{
                                processing ? "Placing order…" : "Confirm order"
                            }}
                        </button>
                    </div>
                </div>

                <aside class="order-summary">
                    <div class="summary-heading">
                        <h2>Your order</h2>
                        <span
                            >{{
                                cart.reduce(
                                    (count, item) => count + item.qty,
                                    0,
                                )
                            }}
                            items</span
                        >
                    </div>
                    <ul class="summary-items">
                        <li v-for="item in cart" :key="item.key">
                            <img
                                v-if="item.image"
                                :src="item.image"
                                :alt="item.name"
                            />
                            <span v-else class="item-placeholder">Item</span>
                            <div class="item-info">
                                <strong>{{ item.name }}</strong>
                                <small v-if="item.variant_label">{{
                                    item.variant_label
                                }}</small>
                                <div class="item-controls">
                                    <div class="quantity-control" aria-label="Product quantity">
                                        <button type="button" :disabled="item.qty <= 1 || processing" :aria-label="`Decrease ${item.name} quantity`" @click="changeQuantity(item, -1)"><Minus :size="14" /></button>
                                        <span aria-live="polite">{{ item.qty }}</span>
                                        <button type="button" :disabled="processing || item.qty >= Math.min(Number(item.stock ?? 100), 100)" :aria-label="`Increase ${item.name} quantity`" @click="changeQuantity(item, 1)"><Plus :size="14" /></button>
                                    </div>
                                    <button class="remove-product" type="button" :disabled="processing" :aria-label="`Remove ${item.name}`" @click="storefront.removeItem(item)"><Trash2 :size="14" /> Remove</button>
                                </div>
                            </div>
                            <strong class="line-total">{{
                                taka(item.price * item.qty)
                            }}</strong>
                        </li>
                    </ul>
                    <section class="checkout-discounts">
                        <button type="button" class="discount-toggle" :aria-expanded="discountInputsOpen" aria-controls="checkout-discount-fields" @click="discountInputsOpen = !discountInputsOpen"><span class="discount-toggle-icon"><Ticket :size="18"/></span><span class="discount-toggle-copy"><strong>Have a coupon or gift card?</strong><small>{{ discountInputsOpen ? 'Enter your codes below' : 'Click to add your discount code' }}</small></span><ChevronDown :size="18" :class="{ rotated: discountInputsOpen }"/></button>
                        <div v-show="discountInputsOpen" id="checkout-discount-fields" class="discount-expand">
                        <div class="discount-inputs">
                            <label for="checkout-coupon">Coupon code<div class="discount-input-wrap"><Ticket :size="16"/><input id="checkout-coupon" v-model="form.coupon_code" maxlength="60" placeholder="Enter coupon code" :disabled="processing || discountBusy" autocomplete="off" /></div></label>
                            <label for="checkout-gift">Gift card code<div class="discount-input-wrap"><Gift :size="16"/><input id="checkout-gift" v-model="form.gift_card_code" maxlength="60" placeholder="Enter gift card code" :disabled="processing || discountBusy" autocomplete="off" /></div></label>
                        </div>
                        <button class="apply-discounts" type="button" @click="applyDiscounts" :disabled="discountBusy || processing || (!form.coupon_code.trim() && !form.gift_card_code.trim())"><Check :size="16"/>{{ discountBusy?'Verifying codes…':'Apply discount' }}</button>
                        <p v-if="discountError" class="checkout-field-error" role="alert">{{ discountError }}</p>
                        <p v-if="appliedDiscounts" class="discount-applied" role="status"><Check :size="14"/>Your discount has been applied.</p>
                        <small v-if="errors.coupon_code || errors.gift_card_code" class="checkout-field-error">{{ errors.coupon_code || errors.gift_card_code }}</small>
                        </div>
                    </section>
                    <div class="summary-totals">
                        <div>
                            <span>Subtotal</span
                            ><strong>{{ taka(subtotal) }}</strong>
                        </div>
                        <div>
                            <span>Delivery charge</span
                            ><strong>{{
                                deliveryFee ? taka(deliveryFee) : "Free"
                            }}</strong>
                        </div>
                        <p
                            v-if="subtotal < freeShippingThreshold"
                            class="free-shipping-hint"
                        >
                            Add
                            {{ taka(freeShippingThreshold - subtotal) }} more
                            for free delivery.
                        </p>
                        <OrderDiscountRows v-if="appliedDiscounts" :order="appliedDiscounts" :format="taka"/>
                        <div class="total-row">
                            <span>Total payable</span
                            ><strong>{{ taka(total) }}</strong>
                        </div>
                    </div>
                    <p class="summary-secure">
                        <ShieldCheck :size="15" />Your order details are
                        securely submitted.
                    </p>
                </aside>
            </form>
        </main>
    </StorefrontLayout>
</template>

<style scoped>
.checkout-page {
    width: 100%;
    min-height: 65vh;
    margin: 0 auto;
    padding: 36px 16px 75px;
    color: #0B2A5B;
}
.checkout-heading {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    gap: 20px;
    margin-bottom: 26px;
}
.checkout-heading p {
    color: #64748B;
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 0.22em;
}
.checkout-heading h1 {
    margin-top: 5px;
    font-family: Poppins, sans-serif;
    font-size: clamp(26px, 4vw, 38px);
}
.checkout-heading span {
    display: block;
    margin-top: 5px;
    color: #64748B;
    font-size: 12px;
}
.continue-shopping {
    color: #64748B;
    font-size: 11px;
    text-decoration: underline;
    text-underline-offset: 4px;
}
.checkout-success {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 16px;
    border: 1px solid #bfe8d1;
    border-radius: 9px;
    background: #effbf4;
    padding: 12px 14px;
    color: #16734d;
    font-size: 12px;
}
.checkout-error {
    margin: 7px 0;
    color: #ae2525;
    font-size: 12px;
}
.checkout-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.45fr) minmax(290px, 0.8fr);
    gap: 22px;
    align-items: start;
}
.checkout-fields {
    display: grid;
    gap: 0;
}
.checkout-card,
.order-summary {
    border: 1px solid #dce4ef;
    border-radius: 13px;
    background: white;
    box-shadow: 0 4px 16px #0B2A5B08;
}
.checkout-card {
    padding: 19px;
}
.checkout-form-card {
    padding: 19px;
}
.checkout-section {
    padding: 0;
}
.checkout-main-heading {
    margin-bottom: 16px;
    border-bottom: 1px solid #dce4ef;
    padding-bottom: 13px;
}
.checkout-main-heading h2 {
    color: #0B2A5B;
    font-size: 17px;
    font-weight: 700;
}
.checkout-form-card .policy-consent {
    margin-top: 16px;
}
.checkout-form-card .submit-order {
    margin-top: 14px;
}
.checkout-section + .checkout-section {
    margin-top: 17px;
}
.card-heading {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 18px;
}
.step-number {
    display: grid;
    width: 30px;
    height: 30px;
    flex: 0 0 auto;
    place-items: center;
    border-radius: 50%;
    background: #F3F6FB;
    color: #E30613;
    font-size: 10px;
    font-weight: 700;
}
.card-heading h2 {
    font-size: 15px;
    font-weight: 700;
}
.card-heading p {
    margin-top: 3px;
    color: #64748B;
    font-size: 10px;
}
.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 13px;
}
.checkout-field {
    display: grid;
    gap: 6px;
    color: #0B2A5B;
    font-size: 10px;
    font-weight: 650;
}
.checkout-field input,
.checkout-field textarea,
.checkout-field select {
    width: 100%;
    border: 1px solid #DCE4EF;
    border-radius: 7px;
    background: #fff;
    padding: 10px 11px;
    color: #0B2A5B;
    font-size: 12px;
    font-weight: 400;
    outline: none;
}
.checkout-field input:focus,
.checkout-field textarea:focus,
.checkout-field select:focus {
    border-color: #64748B;
    box-shadow: 0 0 0 3px #E3061312;
}
.checkout-field small,
.checkout-field-error {
    color: #b42318;
    font-size: 10px;
    font-weight: 400;
}
.full-width {
    grid-column: 1 / -1;
}
.payment-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 9px;
}
.payment-option {
    position: relative;
    display: grid;
    justify-items: center;
    gap: 7px;
    border: 1px solid #DCE4EF;
    border-radius: 10px;
    padding: 15px 8px 13px;
    cursor: pointer;
    text-align: center;
}
.payment-option.selected {
    border-color: #E30613;
    background: #F3F6FB;
    box-shadow: 0 4px 10px #E306130d;
}
.payment-option input {
    position: absolute;
    top: 9px;
    right: 9px;
    accent-color: #E30613;
}
.payment-icon {
    display: grid;
    width: 34px;
    height: 34px;
    place-items: center;
    border-radius: 50%;
    background: #0B2A5B;
    color: white;
}
.payment-option:nth-child(2) .payment-icon {
    background: #ed6c24;
}
.payment-option:nth-child(3) .payment-icon {
    background: #c8172b;
}
.payment-option strong {
    font-size: 10px;
}
.payment-option small {
    color: #64748B;
    font-size: 9px;
}
.payment-notice {
    margin-top: 11px;
    border-radius: 7px;
    background: #fff7e8;
    padding: 10px;
    color: #795519;
    font-size: 10px;
    line-height: 1.6;
}
.policy-consent {
    display: flex;
    align-items: flex-start;
    gap: 9px;
    border: 1px solid #dce4ef;
    border-radius: 9px;
    padding: 13px;
    color: #0B2A5B;
    font-size: 10px;
    line-height: 1.6;
}
.policy-consent input {
    margin-top: 2px;
    accent-color: #E30613;
}
.policy-consent a {
    color: #E30613;
    text-decoration: underline;
}
.primary-button {
    display: inline-flex;
    min-height: 45px;
    align-items: center;
    justify-content: center;
    gap: 8px;
    border: 0;
    border-radius: 8px;
    background: #E30613;
    padding: 12px 17px;
    color: white;
    font-size: 11px;
    font-weight: 700;
    transition: background 0.2s;
}
.primary-button:hover {
    background: #C10510;
}
.primary-button:disabled {
    cursor: wait;
    opacity: 0.6;
}
.submit-order {
    width: 100%;
}
.order-summary {
    position: sticky;
    top: 18px;
    overflow: hidden;
}
.summary-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #dce4ef;
    padding: 17px;
}
.summary-heading h2 {
    font-size: 15px;
    font-weight: 700;
}
.summary-heading span {
    color: #64748b;
    font-size: 10px;
}
.summary-items {
    max-height: 330px;
    overflow: auto;
    padding: 0 16px;
}
.summary-items li {
    display: flex;
    align-items: center;
    gap: 10px;
    border-bottom: 1px solid #F3F6FB;
    padding: 13px 0;
}
.summary-items img,
.item-placeholder {
    width: 53px;
    height: 63px;
    flex: 0 0 auto;
    border-radius: 6px;
    background: #F3F6FB;
    object-fit: cover;
}
.item-placeholder {
    display: grid;
    place-items: center;
    color: #64748B;
    font-size: 9px;
}
.item-info {
    display: grid;
    flex: 1;
    gap: 4px;
    min-width: 0;
}
.item-info strong {
    overflow: hidden;
    font-size: 10px;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.item-info small {
    color: #64748B;
    font-size: 9px;
}
.line-total {
    flex: 0 0 auto;
    font-size: 10px;
}
.summary-totals {
    padding: 14px 16px 8px;
}
.summary-totals > div {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    padding: 6px 0;
    color: #64748B;
    font-size: 11px;
}
.summary-totals > div strong {
    color: #0B2A5B;
}
.free-shipping-hint {
    margin: 4px 0 8px;
    border-radius: 6px;
    background: #fbf5f0;
    padding: 8px;
    color: #64748B;
    font-size: 9px;
}
.summary-totals .total-row {
    margin-top: 6px;
    border-top: 1px dashed #DCE4EF;
    padding: 13px 0 8px;
    color: #0B2A5B;
    font-size: 13px;
    font-weight: 700;
}
.summary-totals .total-row strong {
    color: #E30613;
    font-size: 16px;
}
.summary-secure {
    display: flex;
    align-items: center;
    gap: 7px;
    border-top: 1px solid #F3F6FB;
    padding: 13px 16px;
    color: #64748B;
    font-size: 9px;
}
.summary-secure svg {
    color: #E30613;
}
.empty-checkout {
    padding: 80px 20px;
    border: 1px solid #dce4ef;
    border-radius: 14px;
    text-align: center;
}
.empty-checkout p {
    margin: 7px 0 18px;
    color: #64748B;
    font-size: 12px;
}
@media (max-width: 780px) {
    .checkout-grid {
        grid-template-columns: 1fr;
    }
    .order-summary {
        position: static;
        grid-row: 1;
    }
    .checkout-page {
        padding: 25px 10px 55px;
    }
}
@media (max-width: 520px) {
    .checkout-heading {
        align-items: flex-start;
        flex-direction: column;
    }
    .form-grid {
        grid-template-columns: 1fr;
    }
    .full-width {
        grid-column: auto;
    }
    .payment-grid {
        gap: 6px;
    }
    .payment-option {
        padding-inline: 4px;
    }
}

.checkout-page{font-family: Poppins, sans-serif;background:#F3F6FB;box-sizing:border-box}
.checkout-heading h1{font-family:inherit;font-weight:750;letter-spacing:-1.2px}
.checkout-heading{padding:12px 4px}.checkout-heading p{font-size:11px}.checkout-heading span{font-size:14px}
.checkout-card,.order-summary{border:1px solid #F3F6FB;border-radius:20px;background:white;box-shadow:0 8px 28px #0B2A5B06}
.checkout-form-card{padding:clamp(20px,3vw,34px)}.checkout-main-heading h2,.summary-heading h2{font-size:20px;font-weight:700;letter-spacing:-.5px}
.checkout-field{font-size:13px}.checkout-field input,.checkout-field textarea,.checkout-field select{font-family:inherit;font-size:14px;border-radius:10px;padding:13px 14px;background:#F3F6FB}
.checkout-section + .checkout-section{border-top:1px solid #F3F6FB;padding-top:22px;margin-top:24px}
.payment-option{border-radius:12px}.submit-order{min-height:52px;border-radius:12px;font-size:14px;box-shadow:none}
.summary-items li{align-items:start}.item-info{min-width:0}.item-info strong{font-size:13px;line-height:1.5}.line-total{font-size:13px;white-space:nowrap}
.item-controls{display:flex;flex-wrap:wrap;gap:8px;margin-top:10px;align-items:center}
.quantity-control{display:flex;align-items:center;border:1px solid #F3F6FB;border-radius:8px;overflow:hidden}.quantity-control button{display:grid;place-items:center;width:28px;height:30px;background:#F3F6FB;border:0;color:#0B2A5B;cursor:pointer}.quantity-control span{min-width:28px;text-align:center;font-size:12px}.quantity-control button:disabled{opacity:.35;cursor:default}
.remove-product{display:flex;align-items:center;gap:4px;font-size:11px;color:#64748B;border:0;background:transparent;cursor:pointer;padding:5px 0}.remove-product:disabled{opacity:.4}.quantity-control button:focus-visible,.remove-product:focus-visible{outline:2px solid #E30613;outline-offset:-2px}
@media(max-width:780px){.checkout-grid{gap:18px}.order-summary{grid-row:auto}.checkout-page{padding-top:18px}.checkout-heading h1{font-size:28px}}
@media(max-width:420px){.summary-items{padding-inline:12px}.summary-items li{gap:8px}.summary-items img,.item-placeholder{width:44px;height:58px}.line-total{font-size:11px}.checkout-form-card{padding:18px}}
.checkout-discounts{padding:18px 16px;border-block:1px solid #F3F6FB}.checkout-discounts h3{font-size:14px;font-weight:700;margin-bottom:12px}.checkout-discounts label{display:flex;flex-direction:column;gap:6px;font-size:12px;margin-bottom:12px}.checkout-discounts input{width:100%;padding:11px;border:1px solid #DCE4EF;border-radius:8px;font:inherit}.checkout-discounts button{padding:10px 15px;border-radius:8px;background:#E30613;color:white;font-size:12px;font-weight:600}.checkout-discounts button:disabled{opacity:.45}.discount-applied{font-size:12px;color:#277653;margin-top:10px}

.checkout-page{max-width:1360px;padding:32px clamp(12px,3vw,36px) 64px;background:#F3F6FB;font-family: Poppins, sans-serif}.checkout-grid{grid-template-columns:minmax(0,1.3fr) minmax(350px,.9fr);gap:28px}.checkout-heading{margin-bottom:20px}.checkout-heading h1{font-size:clamp(28px,3vw,38px)}.checkout-form-card{padding:28px}.checkout-main-heading{display:flex;align-items:center;gap:12px;padding-bottom:20px;margin-bottom:24px}.checkout-main-heading p{font-size:13px;color:#64748B;margin-top:5px}.checkout-section-icon{display:grid;place-items:center;width:44px;height:44px;border-radius:13px;background:#F3F6FB;color:#E30613}.checkout-step-heading{display:flex;align-items:center;gap:9px;font-size:15px;font-weight:650;margin-bottom:16px;color:#0B2A5B}.checkout-field input:focus,.checkout-field textarea:focus,.checkout-field select:focus{outline:0;border-color:#64748B;box-shadow:0 0 0 3px #E306130c}.payment-option{padding:18px;transition:border-color .2s,background .2s}.payment-option.selected{background:#F3F6FB;border-color:#64748B}.submit-order{transition:background .2s,transform .2s}.submit-order:hover:not(:disabled){transform:translateY(-1px);background:#C10510}.order-summary{position:sticky;top:24px;overflow:hidden}.summary-heading{padding:24px;background:#F3F6FB}.summary-heading span{background:#F3F6FB;border-radius:20px;padding:5px 10px;font-size:11px;color:#64748B}.summary-items{padding-inline:22px}.summary-items li{padding-block:20px}.summary-items img,.item-placeholder{width:58px;height:76px;border-radius:10px;object-fit:cover}.summary-totals{padding:22px}.summary-totals>div{font-size:13px}.summary-totals .total-row{padding-top:18px;margin-top:10px;font-size:15px}.summary-totals .total-row strong{font-size:24px;letter-spacing:-.6px}.summary-secure{background:#F3F6FB;margin:0;padding:16px;justify-content:center;font-size:11px}
.checkout-discounts{padding:22px;background:#fff;border-block:1px solid #F3F6FB}.discount-title{display:flex;align-items:center;gap:10px;margin-bottom:18px}.discount-title>span{display:grid;place-items:center;width:38px;height:38px;border-radius:11px;color:#E30613;background:#F3F6FB}.discount-title h3{margin:0;font-size:14px}.discount-title p{font-size:11px;color:#64748B;margin-top:4px}.discount-inputs{display:grid;grid-template-columns:1fr 1fr;gap:12px}.checkout-discounts label{font-size:11px;font-weight:600;gap:8px;margin-bottom:14px;min-width:0}.discount-input-wrap{display:flex;align-items:center;gap:8px;border:1px solid #DCE4EF;border-radius:10px;padding-left:11px;color:#64748B;background:#F3F6FB}.discount-input-wrap:focus-within{border-color:#64748B;box-shadow:0 0 0 3px #E306130c}.discount-input-wrap svg{flex-shrink:0}.checkout-discounts input{border:0;min-width:0;padding:12px 8px;background:transparent;border-radius:10px;font-size:12px;outline:none}.checkout-discounts .apply-discounts{display:flex;align-items:center;justify-content:center;gap:8px;width:100%;min-height:42px;background:#fff;color:#E30613;border:1px solid #64748B;border-radius:10px;transition:background .2s}.apply-discounts:hover:not(:disabled){background:#fbf1f3}.discount-applied{display:flex;gap:6px;align-items:center;font-size:11px;line-height:1.6}
@media(max-width:960px){.checkout-grid{grid-template-columns:minmax(0,1fr) minmax(320px,.9fr);gap:18px}.checkout-form-card{padding:22px}.discount-inputs{grid-template-columns:1fr}.order-summary{position:static}}
@media(max-width:760px){.checkout-grid{grid-template-columns:1fr}.checkout-page{padding:20px 14px 40px}.checkout-heading{align-items:flex-start;gap:12px}.checkout-heading span{font-size:12px}.continue-shopping{white-space:nowrap}.discount-inputs{grid-template-columns:1fr 1fr}.checkout-form-card{padding:22px}.order-summary{grid-row:auto}.summary-heading,.summary-totals,.checkout-discounts{padding:20px}}
@media(max-width:420px){.discount-inputs,.form-grid{grid-template-columns:1fr}.checkout-heading{flex-wrap:wrap}.summary-items li{display:grid;grid-template-columns:48px minmax(0,1fr)}.summary-items img,.item-placeholder{width:48px;height:64px}.line-total{grid-column:2;justify-self:start}.checkout-form-card{padding:18px}.checkout-field input,.checkout-field textarea,.checkout-field select{font-size:16px}.summary-totals .total-row strong{font-size:22px}}
@media(prefers-reduced-motion:reduce){.submit-order,.payment-option,.apply-discounts{transition:none}}
.checkout-discounts .discount-toggle{display:flex;align-items:center;gap:11px;width:100%;padding:0;border:0;background:transparent;color:#0B2A5B;text-align:left;cursor:pointer}.discount-toggle-icon{display:grid;place-items:center;width:38px;height:38px;flex:0 0 38px;background:#F3F6FB;color:#E30613;border-radius:11px}.discount-toggle-copy{display:grid;gap:5px;flex:1;min-width:0}.discount-toggle-copy strong{font-size:13px;font-weight:600}.discount-toggle-copy small{font-size:11px;color:#64748B;font-weight:400}.discount-toggle>svg{flex-shrink:0;transition:transform .2s}.discount-toggle>svg.rotated{transform:rotate(180deg)}.discount-expand{padding-top:18px}.discount-toggle:focus-visible{outline:2px solid #64748B;outline-offset:6px;border-radius:6px}@media(prefers-reduced-motion:reduce){.discount-toggle>svg{transition:none}}
</style>
