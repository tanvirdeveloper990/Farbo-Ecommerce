import "../css/storefront.css";
import { createApp, h } from "vue";
import { createInertiaApp, router } from "@inertiajs/vue3";
import Auth from "./Pages/Auth.vue";
import Category from "./Storefront/Category.vue";
import Checkout from "./Storefront/Checkout.vue";
import Contact from "./Storefront/Contact.vue";
import Home from "./Storefront/Home.vue";
import About from './Storefront/About.vue';
import Career from './Storefront/Career.vue';
import FounderMessages from './Storefront/FounderMessages.vue';
import Template from "./Storefront/Template.vue";
import { initHeaderSearch } from './Storefront/headerSearch';
import { readWishlist, saveWishlist, wishlistUrl } from './Storefront/wishlist';
function syncWishlist() {
    const ids = readWishlist();
    document.querySelectorAll('#farboHeaderWishlist, #farboSidebarWishlist').forEach(link => {
        link.href = wishlistUrl(ids);
        const badge = link.querySelector('.absolute');
        if (badge) badge.textContent = ids.length;
    });
    document.querySelectorAll('[data-wish-product]').forEach(button => {
        const selected = ids.includes(Number(button.dataset.wishProduct));
        button.setAttribute('aria-pressed', String(selected));
        button.setAttribute('aria-label', selected ? 'Remove from wishlist' : 'Add to wishlist');
        button.querySelector('i').className = (selected ? 'fa-solid' : 'fa-regular') + ' fa-heart text-xs';
    });
}
window.addEventListener('farbo-wishlist-changed', syncWishlist);
document.addEventListener('click', event => {
    const button = event.target.closest('[data-wish-product]');
    if (!button) return;
    event.preventDefault(); event.stopPropagation();
    const id = Number(button.dataset.wishProduct), ids = readWishlist();
    saveWishlist(ids.includes(id) ? ids.filter(item => item !== id) : [...ids, id]);
}, true);
let headerSearch;
function updateHeaderAccount(user) {
    document.querySelectorAll('#farboHeaderAccount, #farboSidebarAccount').forEach(account => {
    const sidebar = account.id === 'farboSidebarAccount';
    account.replaceChildren();
    account.href = user ? (user.is_admin ? '/admin/profile' : '/my-account') : '/login';
    if (user) {
        const photo = user.avatar || user.profile_photo_path;
        const avatar = document.createElement(photo ? 'img' : 'span');
        avatar.className = 'farbo-user-avatar' + (photo ? '' : ' farbo-user-initials');
        if (photo) {avatar.src = photo; avatar.alt = user.name;} else avatar.textContent = Array.from(user.name || 'U')[0].toLocaleUpperCase();
        const name = document.createElement('span');
        name.className = 'farbo-user-name'; name.textContent = user.name;
        account.append(avatar, name);
        account.title = user.name;
    } else {
        const icon = document.createElement('i'); icon.className = 'fa-regular fa-user text-xl lg:text-2xl';
        const label = document.createElement('span'); label.className = sidebar ? '' : 'hidden lg:block text-xs font-medium mt-1'; label.textContent = sidebar ? 'Login / Register' : 'Account';
        account.append(icon, label); account.removeAttribute('title');
    }
    });
}
import ProductDetails from "./Storefront/ProductDetails.vue";
import OrderSuccess from "./Storefront/OrderSuccess.vue";

import NewArrivals from './Storefront/NewArrivals.vue';
import Reviews from './Storefront/Reviews.vue';
import TrackOrder from './Storefront/TrackOrder.vue';
import Profile from './Pages/Profile.vue';
import CustomerOrders from './Pages/CustomerOrders.vue';
import CustomerOrderDetails from './Pages/CustomerOrderDetails.vue';
import CustomerPassword from './Pages/CustomerPassword.vue';
import HowToBuy from './Storefront/HowToBuy.vue';
const pages = {
    'Storefront/About': About,
    'Storefront/FounderMessages': FounderMessages,
    'Storefront/Career': Career,
    'Storefront/Template': Template,
    'Storefront/HowToBuy': HowToBuy,
    Profile,
    CustomerOrders,
    CustomerOrderDetails,
    CustomerPassword,
    'Storefront/TrackOrder': TrackOrder,
    'Storefront/NewArrivals': NewArrivals,
    'Storefront/Reviews': Reviews,
    "Storefront/Category": Category,
    "Storefront/Checkout": Checkout,
    "Storefront/Contact": Contact,
    "Storefront/Home": Home,
    "Storefront/ProductDetails": ProductDetails,
    "Storefront/OrderSuccess": OrderSuccess,
    Auth,
};

window.farboNavigate = url => router.visit(url);
document.addEventListener('click', event => {
    if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
    const link = event.target.closest('a[href]');
    if (!link || link.hasAttribute('download') || (link.target && link.target !== '_self')) return;
    const url = new URL(link.href, location.href);
    if (url.origin !== location.origin || url.pathname.startsWith('/admin') || !['http:', 'https:'].includes(url.protocol)) return;
    if (url.pathname === location.pathname && url.search === location.search && url.hash) return;
    event.preventDefault();
    router.visit(url.href);
});
document.addEventListener('submit', event => {
    if (event.defaultPrevented) return;
    const form = event.target;
    if (form.method.toLowerCase() !== 'get') return;
    const url = new URL(form.action, location.href);
    if (url.origin !== location.origin || url.pathname.startsWith('/admin')) return;
    event.preventDefault();
    router.get(url.href, Object.fromEntries(new FormData(form)));
});
router.on('before', () => { headerSearch?.close(); window.farboCloseDrawers?.(); });
router.on('success', event => headerSearch?.update(event.detail.page.props.catalogSearchProducts));
router.on('success', event => updateHeaderAccount(event.detail.page.props.auth?.user));
router.on('finish', () => { window.farboCart?.render(); syncWishlist(); });

createInertiaApp({
    progress: false,
    title: (title) => title,
    resolve: (name) => {
        const page = pages[name];
        if (!page) throw new Error(`Unknown storefront page: ${name}`);
        return page;
    },
    setup: ({ el, App, props, plugin }) => {
        headerSearch = initHeaderSearch(props.initialPage.props.catalogSearchProducts);
        updateHeaderAccount(props.initialPage.props.auth?.user);
        syncWishlist();
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
});

