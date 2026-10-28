<script setup>
import { computed, ref, watch, nextTick, onMounted, onBeforeUnmount } from "vue";
import { sidebarHighlight } from "./sidebarHighlight";
import { Link, usePage } from "@inertiajs/vue3";
import {
    Clock3,
    Menu,
    X,
    ChevronDown,
    LayoutDashboard,
    ShoppingBag,
    Image,
    FileText,
    MessageSquare,
    CreditCard,
    Package,
    Mail,
    CircleHelp,
    PanelsTopLeft,
    Settings,
    Users,
    ShieldCheck,
    LogOut,
    UserRound,
    ArrowUpRight,
    Sprout,
} from "@lucide/vue";
const props = defineProps({ title: String });
const page = usePage();
const navigation = ref(null);
const highlight = ref(null);
let highlightFrame;
const highlightStyle = computed(() => highlight.value ? {
    transform: `translate(${highlight.value.left}px, ${highlight.value.top}px)`,
    width: `${highlight.value.width}px`, height: `${highlight.value.height}px`,
} : {});
function alignHighlight() {
    const nav = navigation.value;
    const active = nav?.querySelector('a.active');
    if (!active) { highlight.value = null; return; }
    const bounds = nav.getBoundingClientRect();
    const target = active.getBoundingClientRect();
    const rect = { left: target.left - bounds.left, top: target.top - bounds.top + nav.scrollTop, width: target.width, height: target.height };
    highlight.value = rect;
    sidebarHighlight.rect = rect;
}
const user = computed(() => page.props.auth.user);
const open = ref(false);
const collapsed = ref(false);
const mobile = ref(window.innerWidth < 1000);
const now = ref(new Date());
const clock = computed(() =>
    new Intl.DateTimeFormat("en-US", {
        hour: "2-digit",
        minute: "2-digit",
        second: "2-digit",
        hour12: true,
    }).format(now.value),
);
const today = computed(() =>
    new Intl.DateTimeFormat("en", {
        weekday: "short",
        month: "short",
        day: "numeric",
    }).format(now.value),
);
let clockTimer;
const updateWidth = () => {
    mobile.value = window.innerWidth < 1000;
    nextTick(alignHighlight);
};
const closeOnEscape = (event) => {
    if (event.key === "Escape") {
        open.value = false;
        dropdown.value = false;
    }
};
onMounted(() => {
    navigation.value.scrollTop = sidebarHighlight.scrollTop;
    highlight.value = sidebarHighlight.rect;
    highlightFrame = requestAnimationFrame(() => { highlightFrame = requestAnimationFrame(alignHighlight); });
    clockTimer = window.setInterval(() => {
        now.value = new Date();
    }, 1000);
    window.addEventListener("resize", updateWidth);
    window.addEventListener("keydown", closeOnEscape);
});
onBeforeUnmount(() => {
    sidebarHighlight.scrollTop = navigation.value?.scrollTop || 0;
    cancelAnimationFrame(highlightFrame);
    window.clearInterval(clockTimer);
    window.removeEventListener("resize", updateWidth);
    window.removeEventListener("keydown", closeOnEscape);
});
const dropdown = ref(false);
const ecommerce = ref(
    page.url.match(
        /categories|brands|colors|sizes|products/,
    ),
);
const orders = ref(page.url.startsWith("/admin/orders"));
const legal = ref(page.url.includes("/policies/"));
const website = ref(
    /\/admin\/(banners|about-us|founder-messages|career|why-choose-us|blogs|faqs|landing-page|pages|contacts)/.test(page.url),
);
const customers = ref(
    page.url.startsWith("/admin/customers") ||
        page.url.startsWith("/admin/customer-reviews"),
);
const websiteLinks = [
    ["Banners", "banners", Image],
    ["About Us", "about-us", FileText],
    ["Founder Message", "founder-messages", FileText],
    ["Career", "career", FileText],
    ["Why Choose Us", "why-choose-us", FileText],
    // ["Blogs", "blogs", FileText],
    // ["FAQs", "faqs", CircleHelp],
    // ["Landing page", "landing-page", PanelsTopLeft],
    // ["Pages", "pages", FileText],
    ["Contacts", "contacts", Mail],
];
const customerLinks = [
    ["All customers", "/admin/customers", Users, "users"],
    ["Customer reviews", "/admin/customer-reviews", MessageSquare, "reviews"],
];
const policyLinks = [
    ["Delivery Policy", "delivery-policy"],
    ["Return Policy", "return-policy"],
    ["Refund Policy", "refund-policy"],
    ["Terms & Conditions", "terms-and-conditions"],
    ["Privacy Policy", "privacy-policy"],
];
const orderLinks = [
    ["All Orders", "/admin/orders", ""],
    ["Pending", "/admin/orders?status=pending", "pending"],
    ["Processing", "/admin/orders?status=processing", "processing"],
    ["On The Way", "/admin/orders?status=on_the_way", "on_the_way"],
    ["On Hold", "/admin/orders?status=on_hold", "on_hold"],
    ["Courier", "/admin/orders?status=courier", "courier"],
    ["Completed", "/admin/orders?status=completed", "completed"],
    ["Cancelled", "/admin/orders?status=cancelled", "cancelled"],
];
const management = ref(page.url.includes("management"));
const commerce = [
    ["Categories", "categories"],
    ["Add product", "products/create"],
    ["All products", "products"],
    ["Brands", "brands"],
    ["Colors", "colors"],
    ["Sizes", "sizes"],
    // ["SMTP setup", "smtp"],
];
const links = [
    ["Coupons", "coupons", FileText],
    ["Gift cards", "gift-cards", FileText],
    ["Media", "media", Image],
    ["Shipping", "shipping", Package],
    ["Courier setup", "courier", Package],
    ["Payments", "payments", CreditCard],
    ["Settings", "settings", Settings],
];
const can = (t) =>
    user.value.is_super_admin ||
    user.value.role?.permissions?.some((p) => p.name === t + ".manage");
function toggle() {
    if (window.innerWidth < 1000) open.value = !open.value;
    else collapsed.value = !collapsed.value;
}
watch(() => [page.url, ecommerce.value, orders.value, website.value, customers.value, legal.value, management.value, collapsed.value], () => nextTick(alignHighlight));
</script>
<template>
    <div class="admin-shell" :class="{ 'sidebar-collapsed': collapsed }">
        <button
            v-if="open"
            class="sidebar-backdrop"
            aria-label="Close menu"
            @click="open = false"
        ></button>
        <aside
            class="sidebar"
            :class="{ 'mobile-open': open }"
            :inert="mobile ? !open : collapsed"
        >
            <a href="/admin/dashboard" class="admin-brand-logo">
                <img :src="page.props.storeSettings?.header_logo || '/farbo/assets/img/logo/logo.jpeg'" :alt="page.props.storeSettings?.company_name || 'FARBO'" />
            </a>
            <Link href="/admin/profile" class="sidebar-profile">
                <span class="sidebar-profile-avatar">{{ user.name.split(' ').filter(Boolean).map(part => part[0]).slice(0,2).join('').toUpperCase() }}</span>
                <span class="sidebar-profile-copy"><strong>{{ user.name }}</strong><small>{{ user.is_super_admin ? 'Store owner' : user.role?.name || 'Administrator' }}</small></span>
            </Link>
            <button
                class="mobile-close icon-button"
                @click="open = false"
                aria-label="Close sidebar"
            >
                <X />
            </button>
            <p class="nav-label">WORKSPACE</p>
            <nav ref="navigation" class="animated-navigation" @click="open = false">
                <span v-if="highlight" class="navigation-highlight" :style="highlightStyle" aria-hidden="true"></span>
                <Link
                    href="/admin/dashboard"
                    class="nav-item"
                    :class="{ active: page.url === '/admin/dashboard' }"
                    ><LayoutDashboard :size="18" />Dashboard</Link
                >
                <button
                    class="nav-item"
                    @click.stop="ecommerce = !ecommerce"
                    :aria-expanded="!!ecommerce"
                >
                    <ShoppingBag :size="18" />Product Management<ChevronDown
                        class="chevron"
                        :class="{ rotated: ecommerce }"
                        :size="15"
                    />
                </button>
                <div v-if="ecommerce" class="subnav tree-subnav">
                    <template v-for="[label, url] in commerce" :key="url">
                        <Link
                            v-if="
                                ![
                                    'categories',
                                    'brands',
                                    'colors',
                                    'sizes',
                                    'products',
                                    'shipping',
                                ].includes(url.split('/')[0]) ||
                                can(url.split('/')[0])
                            "
                            :href="'/admin/' + url"
                            :class="{
                                active:
                                    page.url === '/admin/' + url ||
                                    (url === 'products' &&
                                        page.url.startsWith(
                                            '/admin/products/',
                                        ) &&
                                        page.url !== '/admin/products/create'),
                            }"
                            >{{ label }}</Link
                        >
                    </template>
                </div>
                <button
                    v-if="can('orders')"
                    class="nav-item"
                    @click.stop="orders = !orders"
                    :aria-expanded="orders"
                >
                    <Package :size="18" />Orders<ChevronDown
                        class="chevron"
                        :class="{ rotated: orders }"
                        :size="15"
                    />
                </button>
                <div v-if="orders && can('orders')" class="subnav tree-subnav">
                    <Link
                        v-for="[label, href, status] in orderLinks"
                        :key="status || 'all-orders'"
                        :href="href"
                        :class="{
                            active: status
                                ? page.url.startsWith(
                                      '/admin/orders?status=' + status,
                                  )
                                : page.url === '/admin/orders',
                        }"
                        >{{ label }}</Link
                    >
                </div>
                <button
                    class="nav-item"
                    @click.stop="website = !website"
                    :aria-expanded="website"
                >
                    <PanelsTopLeft :size="18" />Website<ChevronDown
                        class="chevron"
                        :class="{ rotated: website }"
                        :size="15"
                    />
                </button>
                <div v-if="website" class="subnav tree-subnav">
                    <template
                        v-for="[label, url, icon] in websiteLinks"
                        :key="url"
                    >
                        <Link
                            v-if="url === 'founder-messages' ? can('settings') : (url === 'about-us' ? can('policies') : (['coupons','gift-cards'].includes(url) ? can('discounts') : (!['contacts','career'].includes(url) || can('contacts'))))"
                            :href="'/admin/' + url"
                            :class="{
                                active: page.url.startsWith('/admin/' + url),
                            }"
                        >
                            <component :is="icon" :size="15" />{{ label }}
                        </Link>
                    </template>
                </div>
                <button
                    v-if="can('users') || can('reviews')"
                    class="nav-item"
                    @click.stop="customers = !customers"
                    :aria-expanded="customers"
                >
                    <Users :size="18" />Customers<ChevronDown
                        class="chevron"
                        :class="{ rotated: customers }"
                        :size="15"
                    />
                </button>
                <div v-if="customers" class="subnav tree-subnav">
                    <template
                        v-for="[label, href, icon, permission] in customerLinks"
                        :key="href"
                    >
                        <Link
                            v-if="can(permission)"
                            :href="href"
                            :class="{ active: page.url.startsWith(href) }"
                        >
                            <component :is="icon" :size="15" />{{ label }}
                        </Link>
                    </template>
                </div>
                <template v-for="[label, url, icon] in links" :key="url">
                    <Link
                        v-if="['coupons','gift-cards'].includes(url) ? can('discounts') : (url === 'shipping' ? can('shipping') : (url !== 'media' || can('media')))"
                        :href="'/admin/' + url"
                        class="nav-item"
                        :class="{ active: page.url === '/admin/' + url }"
                        ><component :is="icon" :size="18" />{{ label }}</Link
                    >
                </template>
                <button
                    v-if="can('policies')"
                    class="nav-item"
                    @click.stop="legal = !legal"
                    :aria-expanded="legal"
                >
                    <ShieldCheck :size="18" />Legal &amp; policy<ChevronDown
                        class="chevron"
                        :size="15"
                    />
                </button>
                <div v-if="legal && can('policies')" class="subnav tree-subnav">
                    <Link
                        v-for="[label, slug] in policyLinks"
                        :key="slug"
                        :href="'/admin/policies/' + slug"
                        :class="{
                            active: page.url === '/admin/policies/' + slug,
                        }"
                        >{{ label }}</Link
                    >
                </div>
                <p class="nav-label">ACCESS & CONTROL</p>
                <button
                    v-if="['roles', 'permissions'].some(can)"
                    class="nav-item"
                    @click.stop="management = !management"
                    :aria-expanded="management"
                >
                    <ShieldCheck :size="18" />Access management<ChevronDown
                        class="chevron"
                        :size="15"
                    />
                </button>
                <div v-if="management" class="subnav tree-subnav">
                    <template v-for="t in ['roles', 'permissions']" :key="t"
                        ><Link
                            v-if="can(t)"
                            :href="'/admin/management/' + t"
                            :class="{
                                active: page.url.startsWith(
                                    '/admin/management/' + t,
                                ),
                            }"
                            >{{ t.charAt(0).toUpperCase() + t.slice(1) }}</Link
                        ></template
                    >
                </div>
            </nav>
        </aside>
        <div class="admin-main">
            <header class="topbar">
                <div class="topbar-left">
                    <button
                        class="icon-button"
                        @click="toggle"
                        aria-label="Toggle sidebar"
                    >
                        <Menu :size="22" /></button
                    ><span class="breadcrumb"
                        >Workspace <span>/</span> <b>{{ title }}</b></span
                    >
                </div>
                <div
                    v-if="!mobile"
                    class="header-clock"
                    role="timer"
                    aria-label="Current local time"
                    aria-live="off"
                >
                    <span class="clock-indicator"></span>
                    <Clock3 :size="16" />
                    <time :datetime="now.toISOString()">{{ clock }}</time>
                    <span class="clock-date">{{ today }}</span>
                </div>
                <div class="topbar-right">
                    <Link href="/" class="store-link"
                        >View store <ArrowUpRight :size="15" /></Link
                    ><span class="top-divider"></span>
                    <div
                        class="profile-control"
                        @keydown.esc="dropdown = false"
                    >
                        <button
                            class="profile-trigger"
                            @click="dropdown = !dropdown"
                            :aria-expanded="dropdown"
                        >
                            <span class="avatar">{{
                                user.name.slice(0, 2).toUpperCase()
                            }}</span
                            ><span class="profile-name"
                                ><strong>{{ user.name }}</strong
                                ><small>{{
                                    user.is_super_admin
                                        ? "Store owner"
                                        : user.role?.name || "Administrator"
                                }}</small></span
                            ><ChevronDown :size="15" /></button
                        ><template v-if="dropdown"
                            ><button
                                class="dropdown-backdrop"
                                tabindex="-1"
                                aria-label="Close profile menu"
                                @click="dropdown = false"
                            ></button>
                            <div class="profile-menu">
                                <Link
                                    href="/admin/profile"
                                    @click="dropdown = false"
                                    ><UserRound :size="17" /> My profile</Link
                                ><Link
                                    href="/admin/profile#password"
                                    @click="dropdown = false"
                                    ><ShieldCheck :size="17" /> Change
                                    password</Link
                                ><Link href="/logout" method="post" as="button"
                                    ><LogOut :size="17" /> Log out</Link
                                >
                            </div></template
                        >
                    </div>
                </div>
            </header>
            <main class="admin-content"><slot /></main>
            <footer class="admin-footer">
                © {{ new Date().getFullYear() }} FARBO<span
                    >Made for your next chapter.</span
                >
            </footer>
        </div>
    </div>
</template>

<style scoped>
.animated-navigation { position: relative; isolation: isolate; }
.navigation-highlight { position: absolute; top: 0; left: 0; z-index: -1; pointer-events: none; border-radius: 6px; background: linear-gradient(110deg, #193c34, #142721); box-shadow: inset 0 1px 0 #ffffff0c; transition: transform 340ms cubic-bezier(.22, 1, .36, 1), width 340ms cubic-bezier(.22, 1, .36, 1), height 340ms cubic-bezier(.22, 1, .36, 1); }
.admin-shell .sidebar .animated-navigation :deep(a.active) { background: transparent; box-shadow: none; }
.animated-navigation :deep(a) { transition: color 200ms ease, background-color 200ms ease; }
.animated-navigation :deep(.chevron) { transition: transform 220ms ease; }
@media (prefers-reduced-motion: reduce) {
    .navigation-highlight, .animated-navigation :deep(a), .animated-navigation :deep(.chevron) { transition: none; }
}
.sidebar-profile{display:flex;align-items:center;gap:12px;padding:5px 10px 22px;margin-bottom:6px;border-bottom:1px solid #ffffff12;color:#f3f7f5;text-decoration:none;min-width:0}.sidebar-profile-avatar{display:grid;place-items:center;width:44px;height:44px;flex:0 0 44px;border:1px solid #426556;border-radius:14px;background:#244f40;color:#d8f2e5;font-size:15px;font-weight:700}.sidebar-profile-copy{display:grid;gap:5px;min-width:0}.sidebar-profile-copy strong{font-size:14px;line-height:1.4;overflow-wrap:anywhere}.sidebar-profile-copy small{font-size:11px;color:#94a99e}.sidebar-profile:focus-visible{outline:2px solid #6cc49e;outline-offset:4px}
</style>


