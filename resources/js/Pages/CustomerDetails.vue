<script setup>
import OrderDiscountRows from "../Components/OrderDiscountRows.vue";
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, MapPin, ShoppingBag, UserRound } from '@lucide/vue';
import AdminLayout from '../Components/AdminLayout.vue';
defineProps({ customer: Object, orders: Object });
const money = value => '৳' + Number(value).toLocaleString('en-BD', { minimumFractionDigits: 2 });
const label = value => (value || '—').replaceAll('_', ' ');
const date = value => new Date(value).toLocaleString('en-GB');
</script>

<template>
    <Head :title="`${customer.name} | Customer details`" />
    <AdminLayout title="Customer details">
        <Link href="/admin/customers" class="back-link"><ArrowLeft :size="16" />All customers</Link>
        <div class="page-heading"><div><span class="eyebrow">CUSTOMER DETAILS</span><h1>{{ customer.name }}</h1><p>{{ customer.order_id ? 'Customer information for order #' + (10000 + Number(customer.order_id)) : 'Registered customer' }}</p></div></div>
        <section class="panel customer-summary">
            <h2><UserRound :size="18" />Customer information</h2>
            <dl class="detail-grid">
                <div><dt>Name</dt><dd>{{ customer.name }}</dd></div><div><dt>Phone</dt><dd>{{ customer.phone || '—' }}</dd></div>
                <div><dt>Email</dt><dd>{{ customer.email || '—' }}</dd></div><div><dt>Address</dt><dd>{{ customer.address || '—' }}</dd></div>
                <div><dt>Orders in this view</dt><dd>{{ customer.orders_count }}</dd></div><div><dt>Order value</dt><dd>{{ money(customer.total_order_value) }}</dd></div>
            </dl>
            <details v-if="customer.account"><summary>Registered account information</summary><dl class="detail-grid">
                <div v-for="key in ['name', 'email', 'phone', 'address']" :key="key"><dt>{{ label(key) }}</dt><dd>{{ customer.account[key] || '—' }}</dd></div>
                <div><dt>Registered</dt><dd>{{ date(customer.account.created_at) }}</dd></div>
            </dl></details>
        </section>
        <section v-for="order in orders.data" :key="order.id" class="panel order-panel">
            <div class="order-heading"><h2><ShoppingBag :size="18" />Order #{{ 10000 + Number(order.id) }}</h2><span>{{ date(order.created_at) }} · {{ label(order.status) }}</span></div>
            <dl class="detail-grid">
                <div><dt>Customer name</dt><dd>{{ order.customer_name }}</dd></div><div><dt>Phone</dt><dd>{{ order.phone }}</dd></div>
                <div><dt>Email</dt><dd>{{ order.email || '—' }}</dd></div><div><dt>Delivery area</dt><dd>{{ label(order.area) }}</dd></div>
                <div class="wide"><dt><MapPin :size="13" />Delivery address</dt><dd>{{ order.address }}</dd></div>
                <div><dt>Payment method</dt><dd>{{ label(order.payment_method) }}</dd></div><div><dt>Payment status</dt><dd>{{ label(order.payment_status) }}</dd></div>
                <div><dt>Courier</dt><dd>{{ label(order.courier_provider) }}</dd></div><div><dt>Order status</dt><dd>{{ label(order.status) }}</dd></div>
                <div class="wide"><dt>Customer note</dt><dd>{{ order.customer_note || 'No note provided.' }}</dd></div>
            </dl>
            <div class="table-scroll"><table class="items-table"><thead><tr><th>Product</th><th>Variant</th><th>Quantity</th><th>Unit price</th><th>Total</th></tr></thead><tbody>
                <tr v-for="item in order.items" :key="item.id"><td>{{ item.product_name }}</td><td>{{ item.variant_name || '—' }}</td><td>{{ item.quantity }}</td><td>{{ money(item.unit_price) }}</td><td>{{ money(item.total) }}</td></tr>
            </tbody></table></div>
            <div class="order-totals"><span>Subtotal <strong>{{ money(order.subtotal) }}</strong></span><OrderDiscountRows :order="order" :format="money"/><span>Delivery fee <strong>{{ money(order.delivery_fee) }}</strong></span><span>Total <strong>{{ money(order.total) }}</strong></span></div>
        </section>
        <section v-if="!orders.total" class="panel empty-orders">This customer has not placed an order yet.</section>
        <div v-if="orders.last_page > 1" class="order-pagination"><template v-for="(link, index) in orders.links" :key="index"><Link v-if="link.url" :href="link.url" class="btn" :class="link.active ? 'primary' : 'secondary'">{{ index === 0 ? 'Previous' : index === orders.links.length - 1 ? 'Next' : link.label }}</Link></template></div>
    </AdminLayout>
</template>

<style scoped>
.customer-summary, .order-panel { padding: 24px; margin-bottom: 20px; }
h2 { display: flex; align-items: center; gap: 9px; margin: 0; font-size: 15px; color: var(--dark); }
.detail-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px 28px; margin: 24px 0; }
dt { display: flex; align-items: center; gap: 5px; margin-bottom: 5px; font-size: 11px; color: var(--muted); text-transform: capitalize; }
dd { margin: 0; font-size: 13px; line-height: 1.6; white-space: pre-wrap; overflow-wrap: anywhere; }
.wide { grid-column: 1 / -1; }
summary { cursor: pointer; color: var(--green); font-size: 12px; }
.order-heading { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; padding-bottom: 16px; border-bottom: 1px solid var(--line); }
.order-heading > span { font-size: 11px; color: var(--muted); text-transform: capitalize; }
.items-table { width: 100%; min-width: 550px; border-collapse: collapse; font-size: 12px; }
.items-table th, .items-table td { text-align: left; padding: 12px; border-bottom: 1px solid var(--line); }
.items-table th { background: #f5f7f2; font-size: 10px; color: var(--muted); }
.order-totals { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 24px; padding-top: 20px; font-size: 12px; }
.order-totals strong { margin-left: 8px; }
.order-pagination { display: flex; flex-wrap: wrap; gap: 8px; }
.empty-orders { padding: 40px; text-align: center; color: var(--muted); }
@media (max-width: 600px) { .detail-grid { grid-template-columns: 1fr; } .customer-summary, .order-panel { padding: 18px; } }
.order-panel{border-radius:18px;padding:26px;border:1px solid #e1e9e4}.order-totals{display:grid;grid-template-columns:minmax(0,1fr);margin-left:auto;max-width:430px;gap:14px;padding:22px 0 0}.order-totals>span{display:flex;justify-content:space-between;align-items:center;gap:16px;font-size:13px;color:#65756d}.order-totals>span:last-child{border-top:1px solid #e4ebe6;padding-top:16px;color:#244c3a;font-weight:600}.order-totals>span:last-child strong{font-size:21px}.order-totals strong{white-space:nowrap}.order-heading{flex-wrap:wrap;gap:12px}.order-totals :deep(.order-savings){margin:0}.table-scroll{overflow:auto}@media(max-width:600px){.order-panel{padding:18px}.order-totals{max-width:none}.detail-grid{grid-template-columns:1fr}.order-heading h2{font-size:16px}}
</style>
