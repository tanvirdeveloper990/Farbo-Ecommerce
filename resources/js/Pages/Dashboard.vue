<script setup>
import { computed } from "vue";
import { Head, Link, usePage } from "@inertiajs/vue3";
import {
    ArrowRight,
    ArrowUpRight,
    BarChart3,
    CircleDollarSign,
    Clock3,
    Package,
    Settings,
    ShoppingBag,
    ShoppingCart,
    ShieldCheck,
    Users,
} from "@lucide/vue";
import AdminLayout from "../Components/AdminLayout.vue";

const props = defineProps({
    stats: Object,
    chart: Object,
    recentOrders: Array,
});
const page = usePage();
const can = (permission) =>
    page.props.auth.user.is_super_admin ||
    page.props.auth.user.role?.permissions?.some(
        (item) => item.name === `${permission}.manage`,
    );
const money = (value) =>
    "৳" +
    Number(value || 0).toLocaleString("en-BD", { maximumFractionDigits: 0 });
const orderPie = computed(() => {const total=Number(props.stats.orders)||0;if(!total)return '#e8eeeb';const pending=Number(props.stats.pending)/total*100;const completed=pending+Number(props.stats.completed)/total*100;return `conic-gradient(#d9a346 0% ${pending}%,#26816a ${pending}% ${completed}%,#7198c4 ${completed}% 100%)`;});
const chartValues = computed(() => props.chart.days.map((day) => day.revenue));
const chartPoints = computed(() => {
    const values = chartValues.value;
    const maximum = Math.max(...values, 1);
    return values
        .map((value, index) => {
            const x = 34 + (index * 692) / Math.max(values.length - 1, 1);
            const y = 220 - (value / maximum) * 184;
            return `${x},${y}`;
        })
        .join(" ");
});
const chartArea = computed(() => `34,220 ${chartPoints.value} 726,220`);
const chartDots = computed(() => {
    const values = chartValues.value;
    const maximum = Math.max(...values, 1);
    return values.map((value, index) => ({
        x: 34 + (index * 692) / Math.max(values.length - 1, 1),
        y: 220 - (value / maximum) * 184,
    }));
});
const cards = computed(() =>
    [
        {
            label: "Total revenue",
            value:
                props.stats.revenue === null
                    ? null
                    : money(props.stats.revenue),
            note: "From completed orders",
            icon: CircleDollarSign,
            tone: "emerald",
        },
        {
            label: "Total orders",
            value: props.stats.orders,
            note: "All order statuses",
            icon: ShoppingCart,
            tone: "blue",
        },
        {
            label: "Pending orders",
            value: props.stats.pending,
            note: "Need your attention",
            icon: Clock3,
            tone: "amber",
        },
        {
            label: "Products",
            value: props.stats.products,
            note: "In your catalog",
            icon: Package,
            tone: "violet",
        },
        {
            label: "Customers",
            value: props.stats.customers,
            note: "Registered accounts",
            icon: Users,
            tone: "rose",
        },
        {
            label: "Team members",
            value: props.stats.team,
            note: "Administrator accounts",
            icon: ShieldCheck,
            tone: "slate",
        },
        {
            label: "Roles",
            value: props.stats.roles,
            note: "Access profiles",
            icon: ShieldCheck,
            tone: "blue",
        },
        {
            label: "Permissions",
            value: props.stats.permissions,
            note: "Available capabilities",
            icon: Settings,
            tone: "violet",
        },
    ].filter((item) => item.value !== null),
);
const statusLabel = (status) =>
    ({
        on_the_way: "On the way",
        on_hold: "On hold",
    })[status] || status.charAt(0).toUpperCase() + status.slice(1);
</script>

<template>
    <Head title="Dashboard" />
    <AdminLayout title="Dashboard">
        <div class="dashboard-heading dashboard-hero">
            <div>
                <span class="eyebrow">STORE PERFORMANCE</span>
                <h1>
                    Good to see you,
                    {{ page.props.auth.user.name.split(" ")[0] }}
                </h1>
                <p>Your store activity and performance at a glance.</p>
            </div>
            <span class="dashboard-date">
                {{
                    new Intl.DateTimeFormat("en", {
                        weekday: "long",
                        day: "numeric",
                        month: "long",
                        year: "numeric",
                    }).format(new Date())
                }}
            </span>
        </div>

        <section class="dashboard-chart panel">
            <div class="chart-heading">
                <div>
                    <span class="chart-kicker"
                        ><BarChart3 :size="15" /> PERFORMANCE</span
                    >
                    <h2>Revenue overview</h2>
                    <p>Daily completed order revenue over the last 7 days.</p>
                </div>
                <div v-if="chart.canViewOrders" class="chart-total">
                    <span>7-day revenue</span>
                    <strong>{{
                        money(
                            chart.days.reduce(
                                (sum, day) => sum + day.revenue,
                                0,
                            ),
                        )
                    }}</strong>
                </div>
            </div>
            <div v-if="chart.canViewOrders" class="chart-canvas">
                <div class="chart-y-labels" aria-hidden="true">
                    <span>{{ money(Math.max(...chartValues, 0)) }}</span>
                    <span>{{ money(Math.max(...chartValues, 0) * 0.66) }}</span>
                    <span>{{ money(Math.max(...chartValues, 0) * 0.33) }}</span>
                    <span>৳0</span>
                </div>
                <svg
                    class="revenue-chart"
                    viewBox="0 0 760 250"
                    preserveAspectRatio="none"
                    role="img"
                    aria-label="Daily revenue chart for the last seven days"
                >
                    <defs>
                        <linearGradient
                            id="revenueFill"
                            x1="0"
                            x2="0"
                            y1="0"
                            y2="1"
                        >
                            <stop
                                offset="0%"
                                stop-color="#16876f"
                                stop-opacity=".2"
                            />
                            <stop
                                offset="100%"
                                stop-color="#16876f"
                                stop-opacity="0"
                            />
                        </linearGradient>
                    </defs>
                    <path
                        d="M34 36H726 M34 97H726 M34 159H726 M34 220H726"
                        class="chart-grid-line"
                    />
                    <polygon :points="chartArea" fill="url(#revenueFill)" />
                    <polyline :points="chartPoints" class="chart-line" />
                    <circle
                        v-for="(dot, index) in chartDots"
                        :key="index"
                        :cx="dot.x"
                        :cy="dot.y"
                        r="4.5"
                        class="chart-dot"
                    />
                </svg>
                <div class="chart-x-labels">
                    <span v-for="(day, index) in chart.days" :key="index">{{
                        day.date
                    }}</span>
                </div>
            </div>
            <div v-else class="chart-no-access">
                Sales performance is available to administrators with order
                management access.
            </div>
        </section>



<section v-if="chart.canViewOrders" class="dashboard-pie panel"><div><span class="chart-kicker">ORDER OVERVIEW</span><h2>Order distribution</h2><p>Live totals from your store.</p></div><div class="pie-content"><div class="order-donut" :style="{ background: orderPie }" role="img" :aria-label="'Orders: '+stats.pending+' pending, '+stats.orders+' total'"><span>{{ stats.orders }}<small>Total orders</small></span></div><div class="pie-legend"><p><i style="background:#d9a346"></i>Pending <strong>{{ stats.pending }}</strong></p><p><i style="background:#26816a"></i>Completed <strong>{{ stats.completed }}</strong></p><p><i style="background:#7198c4"></i>Other statuses <strong>{{ Math.max(0, stats.orders-stats.pending-stats.completed) }}</strong></p></div></div></section>
        <section class="dashboard-stats" aria-label="Store statistics">
            <article
                v-for="card in cards"
                :key="card.label"
                class="dashboard-stat panel"
                :class="'tone-' + card.tone"
            >
                <div class="stat-topline">
                    <span>{{ card.label }}</span>
                    <span class="stat-symbol" :class="card.tone"
                        ><component :is="card.icon" :size="19"
                    /></span>
                </div>
                <strong>{{ card.value }}</strong>
                <small>{{ card.note }}</small>
            </article>
        </section>

        <section v-if="chart.canViewOrders" class="panel recent-orders">
            <div class="section-heading">
                <div>
                    <span class="section-kicker">LATEST ACTIVITY</span>
                    <h2>Recent orders</h2>
                    <p>The latest orders from your customers.</p>
                </div>
                <Link href="/admin/orders" class="section-link">
                    All orders <ArrowRight :size="16" />
                </Link>
            </div>
            <div class="dashboard-table-scroll">
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th class="amount-heading">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="order in recentOrders" :key="order.id">
                            <td>
                                <Link
                                    :href="`/admin/orders/${order.id}`"
                                    class="dashboard-order-number"
                                    >#{{ 10000 + order.id }}</Link
                                >
                            </td>
                            <td class="dashboard-customer">
                                {{ order.customer_name }}
                            </td>
                            <td class="dashboard-order-date">
                                {{
                                    new Date(
                                        order.created_at,
                                    ).toLocaleDateString()
                                }}
                            </td>
                            <td>
                                <span
                                    class="dashboard-status"
                                    :class="`status-${order.status}`"
                                    >{{ statusLabel(order.status) }}</span
                                >
                            </td>
                            <td class="amount-cell">
                                {{ money(order.total) }}
                            </td>
                        </tr>
                        <tr v-if="!recentOrders.length">
                            <td colspan="5" class="dashboard-empty">
                                No orders yet. New customer orders will appear
                                here.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="dashboard-shortcuts">
            <div class="shortcuts-title">
                <span class="section-kicker">QUICK ACCESS</span>
                <h2>Manage your store</h2>
            </div>
            <div class="shortcut-links">
                <Link
                    v-if="can('products')"
                    href="/admin/products"
                    class="shortcut-card"
                >
                    <span class="shortcut-icon"><Package :size="19" /></span>
                    <span
                        ><strong>Products</strong
                        ><small>Manage your catalog</small></span
                    >
                    <ArrowUpRight :size="17" />
                </Link>
                <Link
                    v-if="can('orders')"
                    href="/admin/orders"
                    class="shortcut-card"
                >
                    <span class="shortcut-icon"
                        ><ShoppingBag :size="19"
                    /></span>
                    <span
                        ><strong>Orders</strong
                        ><small>View and process orders</small></span
                    >
                    <ArrowUpRight :size="17" />
                </Link>
                <Link
                    v-if="can('settings')"
                    href="/admin/settings"
                    class="shortcut-card"
                >
                    <span class="shortcut-icon"><Settings :size="19" /></span>
                    <span
                        ><strong>Store settings</strong
                        ><small>Update store information</small></span
                    >
                    <ArrowUpRight :size="17" />
                </Link>
            </div>
        </section>
    </AdminLayout>
</template>

<style scoped>
.dashboard-heading {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 24px;
}
.dashboard-heading .eyebrow,
.section-kicker {
    display: block;
    margin-bottom: 7px;
    color: #738497;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.13em;
}
.dashboard-heading h1 {
    color: #192d3f;
    font-size: clamp(23px, 2.3vw, 31px);
    letter-spacing: -0.04em;
}
.dashboard-heading p,
.section-heading p {
    margin-top: 6px;
    color: #718195;
    font-size: 13px;
}
.dashboard-date {
    border: 1px solid #dce4ea;
    border-radius: 9px;
    background: #fff;
    padding: 10px 14px;
    color: #506276;
    font-size: 12px;
    font-weight: 600;
    white-space: nowrap;
}
.panel {
    border-color: #e1e8ee;
    border-radius: 14px;
    box-shadow: 0 4px 16px #243b5308;
}
.dashboard-chart {
    margin-bottom: 20px;
    padding: 24px 26px 18px;
}
.chart-heading {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 18px;
}
.chart-kicker {
    display: flex;
    align-items: center;
    gap: 7px;
    color: #16876f;
    font-size: 10px;
    font-weight: 750;
    letter-spacing: 0.14em;
}
.chart-heading h2,
.section-heading h2,
.shortcuts-title h2 {
    margin-top: 9px;
    color: #1c3042;
    font-size: 19px;
    font-weight: 750;
    letter-spacing: -0.03em;
}
.chart-heading p {
    margin-top: 4px;
    color: #7c8b9b;
    font-size: 12px;
}
.chart-total {
    display: grid;
    gap: 5px;
    text-align: right;
}
.chart-total span {
    color: #7e8c9a;
    font-size: 11px;
}
.chart-total strong {
    color: #182f42;
    font-size: 21px;
    font-variant-numeric: tabular-nums;
}
.chart-canvas {
    position: relative;
    height: 245px;
    margin-top: 20px;
    padding-left: 76px;
}
.revenue-chart {
    width: 100%;
    height: 215px;
    overflow: visible;
}
.chart-grid-line {
    fill: none;
    stroke: #e9eef2;
    stroke-dasharray: 4 5;
    stroke-width: 1;
}
.chart-line {
    fill: none;
    stroke: #16876f;
    stroke-linecap: round;
    stroke-linejoin: round;
    stroke-width: 3.5;
    vector-effect: non-scaling-stroke;
}
.chart-dot {
    fill: #fff;
    stroke: #16876f;
    stroke-width: 3;
    vector-effect: non-scaling-stroke;
}
.chart-y-labels {
    position: absolute;
    top: 7px;
    bottom: 27px;
    left: 0;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    color: #98a5b1;
    font-size: 10px;
    font-variant-numeric: tabular-nums;
}
.chart-x-labels {
    display: flex;
    justify-content: space-between;
    color: #8c9aa7;
    font-size: 10px;
}
.chart-no-access {
    margin-top: 18px;
    border-radius: 9px;
    background: #f3f6f8;
    padding: 23px;
    color: #647789;
    font-size: 13px;
}
.dashboard-stats {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 14px;
    margin-bottom: 20px;
}
.dashboard-stat {
    position: relative;
    overflow: hidden;
    min-width: 0;
    border: 1px solid #ffffffb3;
    background: linear-gradient(145deg, #ffffff, #f3f7f8);
    padding: 18px 20px;
    box-shadow:
        0 10px 22px #263f5612,
        0 3px 6px #263f560a,
        inset 0 1px 0 #ffffff;
    transition:
        transform 0.2s ease,
        box-shadow 0.2s ease;
}
.dashboard-stat::after {
    position: absolute;
    top: -42px;
    right: -34px;
    width: 132px;
    height: 132px;
    border-radius: 50%;
    background: linear-gradient(145deg, #ffffff80, #ffffff00);
    content: "";
    pointer-events: none;
}
.dashboard-stat:hover {
    transform: perspective(800px) translateY(-4px) rotateX(1deg);
    box-shadow:
        0 16px 28px #263f5620,
        0 5px 10px #263f5610,
        inset 0 1px 0 #ffffff;
}
.dashboard-stat.tone-emerald {
    background: linear-gradient(145deg, #f1fff9 0%, #d7f2e7 55%, #c6e8de 100%);
}
.dashboard-stat.tone-blue {
    background: linear-gradient(145deg, #f2f8ff 0%, #dceafa 55%, #ccdef3 100%);
}
.dashboard-stat.tone-amber {
    background: linear-gradient(145deg, #fffaf0 0%, #fff0d1 55%, #f8e2b5 100%);
}
.dashboard-stat.tone-violet {
    background: linear-gradient(145deg, #faf7ff 0%, #eae1fb 55%, #ded0f4 100%);
}
.dashboard-stat.tone-rose {
    background: linear-gradient(145deg, #fff6f8 0%, #f9e1e7 55%, #f0d0dc 100%);
}
.dashboard-stat.tone-slate {
    background: linear-gradient(145deg, #f8fafc 0%, #e4ebf1 55%, #d6e0e9 100%);
}
.stat-topline {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    color: #708194;
    font-size: 12px;
    font-weight: 600;
}
.stat-symbol {
    display: grid;
    width: 37px;
    height: 37px;
    place-items: center;
    border-radius: 11px;
}
.stat-symbol.emerald {
    background: #e6f5f0;
    color: #16876f;
}
.stat-symbol.blue {
    background: #e9f1fb;
    color: #477dbb;
}
.stat-symbol.amber {
    background: #fff4df;
    color: #bd8423;
}
.stat-symbol.violet {
    background: #f1edfb;
    color: #8066bb;
}
.stat-symbol.rose {
    background: #fbecef;
    color: #bd6275;
}
.stat-symbol.slate {
    background: #edf1f5;
    color: #607489;
}
.dashboard-stat > strong {
    position: relative;
    z-index: 1;
    display: block;
    margin-top: 12px;
    overflow-wrap: anywhere;
    color: #1b3042;
    font-size: 25px;
    font-variant-numeric: tabular-nums;
    letter-spacing: -0.04em;
}
.dashboard-stat > small {
    position: relative;
    z-index: 1;
    display: block;
    margin-top: 3px;
    color: #8b99a6;
    font-size: 11px;
}
.recent-orders {
    margin-bottom: 24px;
}
.section-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 18px;
    padding: 21px 24px;
}
.section-heading h2 {
    margin-top: 0;
}
.section-heading p {
    font-size: 12px;
}
.section-link {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    color: #177c69;
    font-size: 12px;
    font-weight: 700;
}
.section-link:hover {
    color: #105c50;
}
.dashboard-table-scroll {
    overflow-x: auto;
}
.dashboard-table {
    width: 100%;
    min-width: 650px;
    border-collapse: collapse;
    text-align: left;
}
.dashboard-table th {
    padding: 13px 22px;
    border-top: 1px solid #e9eef2;
    border-bottom: 1px solid #e1e8ee;
    background: #f3f6f8;
    color: #607488;
    font-size: 10px;
    font-weight: 750;
    letter-spacing: 0.09em;
    text-transform: uppercase;
    white-space: nowrap;
}
.dashboard-table td {
    padding: 14px 22px;
    border-bottom: 1px solid #edf1f4;
    color: #627487;
    font-size: 12px;
}
.dashboard-table tbody tr:hover {
    background: #fafcfd;
}
.dashboard-table tbody tr:last-child td {
    border-bottom: 0;
}
.dashboard-order-number {
    color: #177c69;
    font-weight: 750;
}
.dashboard-order-number:hover {
    text-decoration: underline;
}
.dashboard-customer {
    color: #283e52 !important;
    font-weight: 650;
}
.dashboard-order-date {
    color: #8391a0 !important;
    white-space: nowrap;
}
.dashboard-table .amount-heading,
.dashboard-table .amount-cell {
    text-align: right;
    font-weight: 700;
    color: #263b4e;
    white-space: nowrap;
}
.dashboard-status {
    display: inline-flex;
    border-radius: 999px;
    padding: 5px 9px;
    background: #edf1f4;
    color: #566a7e;
    font-size: 10px;
    font-weight: 700;
    white-space: nowrap;
}
.dashboard-status.status-pending {
    background: #fff4dd;
    color: #936516;
}
.dashboard-status.status-processing,
.dashboard-status.status-on_the_way,
.dashboard-status.status-courier {
    background: #eaf2fc;
    color: #356fae;
}
.dashboard-status.status-completed {
    background: #e6f5ee;
    color: #25805b;
}
.dashboard-status.status-cancelled,
.dashboard-status.status-on_hold {
    background: #faeaea;
    color: #ae4d4d;
}
.dashboard-empty {
    padding: 28px !important;
    color: #8391a0 !important;
    text-align: center;
}
.dashboard-shortcuts {
    padding-bottom: 10px;
}
.shortcuts-title {
    margin-bottom: 12px;
}
.shortcuts-title .section-kicker {
    margin-bottom: 0;
}
.shortcuts-title h2 {
    margin-top: 4px;
}
.shortcut-links {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
}
.shortcut-card {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
    border: 1px solid #e1e8ee;
    border-radius: 12px;
    background: #fff;
    padding: 15px;
    transition:
        border-color 0.2s,
        transform 0.2s,
        box-shadow 0.2s;
}
.shortcut-card:hover {
    transform: translateY(-2px);
    border-color: #b9d7ce;
    box-shadow: 0 8px 18px #243b530c;
}
.shortcut-icon {
    display: grid;
    width: 38px;
    height: 38px;
    place-items: center;
    border-radius: 11px;
    background: #eaf5f1;
    color: #177c69;
}
.shortcut-card > span:nth-child(2) {
    display: grid;
    flex: 1;
    gap: 4px;
    min-width: 0;
}
.shortcut-card strong {
    color: #294054;
    font-size: 12px;
}
.shortcut-card small {
    color: #8291a0;
    font-size: 10px;
}
.shortcut-card > svg {
    color: #8797a5;
}
@media (max-width: 900px) {
    .dashboard-stats {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .shortcut-links {
        grid-template-columns: 1fr;
    }
}
@media (max-width: 600px) {
    .dashboard-heading {
        align-items: flex-start;
        flex-direction: column;
    }
    .dashboard-date {
        align-self: flex-start;
    }
    .dashboard-chart {
        padding: 19px 15px 15px;
    }
    .chart-heading {
        flex-direction: column;
    }
    .chart-total {
        text-align: left;
    }
    .chart-canvas {
        height: 210px;
        padding-left: 58px;
    }
    .revenue-chart {
        height: 180px;
    }
    .dashboard-stats {
        gap: 9px;
    }
    .dashboard-stat {
        padding: 14px;
    }
    .dashboard-stat > strong {
        font-size: 21px;
    }
    .section-heading {
        align-items: flex-start;
        padding: 18px 15px;
    }
    .dashboard-table th,
    .dashboard-table td {
        padding: 12px 14px;
    }
}
.dashboard-hero{position:relative;overflow:hidden;padding:32px 34px;border-radius:20px;background:linear-gradient(120deg,#163c30,#23694f 65%,#368464);box-shadow:0 14px 30px #183d3014;align-items:center!important}.dashboard-hero:after{content:'';position:absolute;width:240px;height:240px;border:1px solid #ffffff0d;border-radius:50%;right:-65px;top:-110px;box-shadow:0 0 0 40px #ffffff03;pointer-events:none}.dashboard-hero h1{color:#f6faf5!important;font-size:30px!important;letter-spacing:-1px}.dashboard-hero p{color:#bed6c8!important;font-size:13px}.dashboard-hero .eyebrow{color:#94d0b3!important;letter-spacing:.18em}.dashboard-hero .dashboard-date{position:relative;z-index:1;background:#ffffff0c;border:1px solid #ffffff14;padding:12px 16px;border-radius:10px;color:#d7eadf}.dashboard-stat.panel{border-radius:17px;border:1px solid #e6ece8;box-shadow:0 5px 20px #1c382805;transition:transform .2s,box-shadow .2s}.dashboard-stat.panel:hover{transform:translateY(-4px);box-shadow:0 12px 28px #1c382812}.dashboard-chart.panel,.recent-orders.panel{border-radius:19px;border:1px solid #e6ece8;box-shadow:0 6px 25px #1c382805}.dashboard-chart.panel{background:linear-gradient(160deg,#fff,#f7fbf8)}.shortcut-card{border-radius:15px!important}.dashboard-heading,.dashboard-stats,.dashboard-chart,.recent-orders{animation:dashboard-entrance .5s both}.dashboard-stats{animation-delay:.08s}.dashboard-chart{animation-delay:.12s}.recent-orders{animation-delay:.16s}@keyframes dashboard-entrance{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}@media(max-width:700px){.dashboard-hero{padding:24px;align-items:flex-start!important}.dashboard-hero h1{font-size:25px!important}.dashboard-hero .dashboard-date{font-size:10px;padding:9px 12px}}@media(prefers-reduced-motion:reduce){.dashboard-heading,.dashboard-stats,.dashboard-chart,.recent-orders{animation:none}.dashboard-stat.panel{transition:none}}
.dashboard-pie{padding:24px;margin-bottom:20px;border-radius:19px}.dashboard-pie h2{font-size:20px;margin:8px 0}.dashboard-pie p{color:#77827f;font-size:13px}.pie-content{display:flex;align-items:center;justify-content:center;gap:40px;margin-top:20px}.order-donut{width:180px;height:180px;border-radius:50%;display:grid;place-items:center}.order-donut>span{background:#fff;border-radius:50%;width:130px;height:130px;display:flex;flex-direction:column;align-items:center;justify-content:center;font-size:28px;font-weight:700}.order-donut small{font-size:11px;color:#7a8781;font-weight:400;margin-top:5px}.pie-legend p{display:flex;align-items:center;gap:10px;margin:14px 0}.pie-legend i{width:10px;height:10px;border-radius:50%}.pie-legend strong{margin-left:18px;color:#263c32}.dashboard-stat::after{display:none}.dashboard-stat.panel,.dashboard-stat.panel:hover{box-shadow:none;transform:none}.dashboard-stat.tone-emerald{background:#e2f2e9}.dashboard-stat.tone-blue{background:#e5eef9}.dashboard-stat.tone-amber{background:#fff0d4}.dashboard-stat.tone-violet{background:#eee6fa}.dashboard-stat.tone-rose{background:#f8e5ec}.dashboard-stat.tone-slate{background:#e8edf1}@media(max-width:500px){.pie-content{gap:20px;flex-direction:column}}
</style>
