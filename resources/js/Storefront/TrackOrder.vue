<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { PackageSearch, Search, Truck } from '@lucide/vue';
import StorefrontLayout from './StorefrontLayout.vue';
const props = defineProps({ order: Object, orderId: String, searched: Boolean });
const form = useForm({ order_id: props.orderId || '' });
const label = (value) => (value || '').replaceAll('_', ' ').replace(/\b\w/g, (letter) => letter.toUpperCase());
const taka = (value) => '৳' + Number(value || 0).toLocaleString('en-BD', { minimumFractionDigits: 2 });
function submit() {
    form.order_id = form.order_id.trim();
    form.get('/track-order', { preserveScroll: true, preserveState: true });
}
</script>

<template>
    <Head title="Track Your Order" />
    <StorefrontLayout>
        <section class="tracking-page">
            <div class="tracking-heading">
                <PackageSearch :size="32" />
                <p>YOUR ORDER, EVERY STEP OF THE WAY</p>
                <h1>Track Your Order</h1>
                <span>Enter the order number from your confirmation or invoice.</span>
            </div>
            <form @submit.prevent="submit" class="tracking-card tracking-form">
                <label for="order-id">Order ID</label>
                <div class="tracking-input">
                    <input id="order-id" v-model="form.order_id" placeholder="e.g. #10001" maxlength="20" required :aria-invalid="!!form.errors.order_id" aria-describedby="order-id-error" />
                    <button type="submit" :disabled="form.processing"><Search :size="17" />{{ form.processing ? 'Searching…' : 'Track order' }}</button>
                </div>
                <p v-if="form.errors.order_id" id="order-id-error" class="tracking-error">{{ form.errors.order_id }}</p>
            </form>
            <div aria-live="polite">
                <section v-if="order" class="tracking-card tracking-result">
                    <div class="tracking-result-heading">
                        <div><h2>Order #{{ order.number }}</h2><p>Placed {{ new Date(order.created_at).toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' }) }}</p></div>
                        <span class="tracking-status" :class="order.status"><Truck :size="17" />{{ label(order.status) }}</span>
                    </div>
                    <div class="tracking-meta">
                        <div><span>Payment method</span><strong>{{ label(order.payment_method) }}</strong></div>
                        <div><span>Payment status</span><strong>{{ label(order.payment_status) }}</strong></div>
                        <div v-if="order.courier_provider"><span>Courier</span><strong>{{ label(order.courier_provider) }}</strong></div><div v-if="order.courier_tracking"><span>Consignment</span><strong>{{ order.courier_tracking }}</strong></div><div v-if="order.courier_status"><span>Courier status</span><strong>{{ label(order.courier_status) }}</strong></div>
                    </div>
                    <h3>Order details</h3>
                    <div v-for="item in order.items" :key="item.id" class="tracking-item">
                        <div><strong>{{ item.name }}</strong><p v-if="item.variant">{{ item.variant }}</p><p>{{ item.quantity }} × {{ taka(item.unit_price) }}</p></div>
                        <strong>{{ taka(item.total) }}</strong>
                    </div>
                    <dl class="tracking-totals">
                        <div><dt>Subtotal</dt><dd>{{ taka(order.subtotal) }}</dd></div>
                        <div><dt>Delivery fee</dt><dd>{{ taka(order.delivery_fee) }}</dd></div>
                        <div class="tracking-total"><dt>Total</dt><dd>{{ taka(order.total) }}</dd></div>
                    </dl>
                </section>
                <section v-else-if="searched && !form.errors.order_id" class="tracking-card tracking-empty" role="status">
                    <PackageSearch :size="28" /><h2>Order not found</h2><p>Please check the order number on your confirmation and try again.</p>
                </section>
            </div>
        </section>
    </StorefrontLayout>
</template>

<style scoped>
.tracking-page{min-height:65vh;background:#f5f5f6;padding:48px 20px 72px;color:#202b3a}.tracking-heading{max-width:700px;margin:0 auto 30px;text-align:center}.tracking-heading>svg{margin:0 auto 16px;color:#E30613}.tracking-heading>p{font-size:10px;letter-spacing:.2em;color:#E30613;font-weight:700}.tracking-heading h1{font-size:clamp(28px,4vw,40px);font-weight:700;margin:8px 0}.tracking-heading>span{font-size:14px;color:#677386}.tracking-card{max-width:760px;margin:0 auto;border:1px solid #e7e9ed;border-radius:16px;background:white;padding:28px;box-shadow:0 12px 35px #1c29380b}.tracking-form label{display:block;font-size:13px;font-weight:600;margin-bottom:9px}.tracking-input{display:flex;gap:12px}.tracking-input input{flex:1;min-width:0;border:1px solid #d5dae2;border-radius:8px;padding:12px 14px;outline:none;font-size:14px}.tracking-input input:focus{border-color:#E30613;box-shadow:0 0 0 3px #E3061314}.tracking-input button{display:flex;align-items:center;justify-content:center;gap:9px;background:#E30613;color:white;border-radius:8px;padding:12px 20px;font-size:13px;font-weight:600}.tracking-input button:hover{background:#C10510}.tracking-input button:disabled{opacity:.6}.tracking-error{color:#b42318;font-size:12px;margin-top:8px}.tracking-result,.tracking-empty{margin-top:24px}.tracking-result-heading{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap}.tracking-result h2{font-size:21px;font-weight:650}.tracking-result-heading p,.tracking-item p{font-size:12px;color:#677386;margin-top:5px}.tracking-status{display:flex;align-items:center;gap:8px;background:#fef3c7;color:#92400e;padding:9px 13px;border-radius:30px;font-size:12px;font-weight:650}.tracking-status.completed{background:#e7f5ec;color:#246b47}.tracking-status.cancelled{background:#fee2e2;color:#991b1b}.tracking-status.processing,.tracking-status.on_the_way,.tracking-status.courier{background:#e9f1ff;color:#245798}.tracking-meta{display:flex;flex-wrap:wrap;gap:25px;margin:24px 0;padding:20px 0;border-top:1px solid #eee;border-bottom:1px solid #eee}.tracking-meta span{display:block;font-size:11px;color:#677386;margin-bottom:5px}.tracking-meta strong,.tracking-item strong{font-size:13px;font-weight:600}.tracking-result h3{font-size:15px;font-weight:650;margin-bottom:10px}.tracking-item{display:flex;justify-content:space-between;gap:16px;padding:15px 0;border-bottom:1px solid #eee}.tracking-totals{max-width:300px;margin:20px 0 0 auto}.tracking-totals>div{display:flex;justify-content:space-between;gap:20px;padding:7px 0;font-size:13px}.tracking-total{border-top:1px solid #ddd;margin-top:8px;padding-top:14px!important;font-weight:700}.tracking-empty{text-align:center}.tracking-empty svg{margin:auto;color:#E30613}.tracking-empty h2{margin:12px 0 8px;font-weight:650}.tracking-empty p{font-size:13px;color:#677386}@media(max-width:540px){.tracking-page{padding:32px 16px 48px}.tracking-card{padding:22px}.tracking-input{flex-direction:column}}
</style>
