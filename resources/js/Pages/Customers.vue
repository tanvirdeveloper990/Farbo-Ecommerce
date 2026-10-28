<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Eye, Search, Users } from '@lucide/vue';
import AdminLayout from '../Components/AdminLayout.vue';
const props = defineProps({ records: Object, search: String });
const query = ref(props.search || '');
watch(() => props.search, value => query.value = value || '');
function filter() {
    router.get('/admin/customers', { search: query.value || undefined }, { preserveState: true, preserveScroll: true, replace: true });
}
const date = value => new Date(value).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
</script>

<template>
    <Head title="All customers" />
    <AdminLayout title="Customers">
        <div class="page-heading">
            <div><span class="eyebrow">CUSTOMERS</span><h1>All customers</h1><p>View customer checkout details for each order.</p></div>
            <span class="customer-count"><Users :size="18" />{{ records.total }} customers</span>
        </div>
        <section class="panel">
            <form class="admin-filter-bar" @submit.prevent="filter">
                <label class="admin-search"><Search :size="17" /><input v-model="query" type="search" placeholder="Search name, email or phone" aria-label="Search customers" /></label>
                <button type="submit" class="btn primary">Search</button>
                <button v-if="query" type="button" class="btn secondary" @click="query = ''; filter()">Clear</button>
            </form>
            <div class="table-scroll">
                <table class="customers-table">
                    <thead><tr><th>Customer</th><th>Email</th><th>Phone</th><th>Address</th><th>Order</th><th>Ordered</th><th>View</th></tr></thead>
                    <tbody>
                        <tr v-for="customer in records.data" :key="`${customer.kind}-${customer.id}`">
                            <td><div class="customer-name"><span class="customer-avatar">{{ customer.name.charAt(0).toUpperCase() }}</span><strong>{{ customer.name }}</strong></div></td>
                            <td>{{ customer.email || '—' }}</td><td>{{ customer.phone || '—' }}</td><td>{{ customer.address || '—' }}</td><td>{{ customer.kind === 'order' ? '#' + (10000 + Number(customer.id)) : '—' }}</td><td>{{ customer.last_order_at ? date(customer.last_order_at) : '—' }}</td>
                            <td><Link :href="`/admin/customers/${customer.kind}/${customer.id}`" class="icon-button" :aria-label="`View ${customer.name}`" title="View customer details"><Eye :size="18" /></Link></td>
                        </tr>
                        <tr v-if="!records.data.length"><td colspan="7" class="customers-empty">{{ search ? 'No customers match your search.' : 'No customers have registered or placed an order yet.' }}</td></tr>
                    </tbody>
                </table>
            </div>
            <div v-if="records.total" class="customers-pagination">
                <span>Showing {{ records.from }}–{{ records.to }} of {{ records.total }} customers</span>
                <div><template v-for="(link, index) in records.links" :key="index">
                    <Link v-if="link.url" :href="link.url" :class="{ selected: link.active }">{{ index === 0 ? 'Previous' : index === records.links.length - 1 ? 'Next' : link.label }}</Link>
                    <span v-else class="disabled">{{ index === 0 ? 'Previous' : index === records.links.length - 1 ? 'Next' : link.label }}</span>
                </template></div>
            </div>
        </section>
    </AdminLayout>
</template>

<style scoped>
.customer-count { display: flex; align-items: center; gap: 8px; padding: 10px 14px; border-radius: 10px; background: var(--cream); color: var(--green); font-size: 12px; }
.customers-table { width: 100%; min-width: 760px; border-collapse: collapse; }
.customers-table th, .customers-table td { padding: 16px 20px; border-bottom: 1px solid var(--line); text-align: left; font-size: 12px; }
.customers-table th { background: #f5f7f2; color: #758379; font-size: 10px; text-transform: uppercase; letter-spacing: .06em; }
.customer-name { display: flex; align-items: center; gap: 10px; }
.customer-avatar { display: grid; place-items: center; width: 32px; height: 32px; flex-shrink: 0; border-radius: 50%; background: var(--cream); color: var(--green); }
.customers-table .customers-empty { padding: 50px 20px; text-align: center; color: var(--muted); }
.customers-pagination { display: flex; flex-wrap: wrap; justify-content: space-between; gap: 12px; padding: 18px 20px; font-size: 11px; }
.customers-pagination > div { display: flex; gap: 6px; }
.customers-pagination a, .customers-pagination .disabled { padding: 6px 10px; border: 1px solid var(--line); border-radius: 6px; }
.customers-pagination .selected { background: var(--green); color: white; }
.customers-pagination .disabled { color: var(--muted); }
</style>
