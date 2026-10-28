// Only business event data is sent; customer contact fields are never included.
let config = {};
let lastPage = null;
const purchases = new Set();
const enabled = value => value === true || value === 1 || value === '1';
const safe = fn => { try { return fn(); } catch { return false; } };
const active = () => !window.location.pathname.startsWith('/admin');
function script(id, src) {
    if (document.getElementById(id)) return;
    const element = document.createElement('script');
    element.id = id; element.async = true; element.src = src; document.head.appendChild(element);
}
export function configureTracking(settings = {}) {
    return safe(() => {
        config = {
            pixel: enabled(settings.meta_pixel_enabled) && /^\d{5,25}$/.test(String(settings.meta_pixel_id || '')) ? String(settings.meta_pixel_id) : null,
            gtm: enabled(settings.gtm_enabled) && /^GTM-[A-Z0-9]{4,20}$/.test(settings.gtm_container_id || '') ? settings.gtm_container_id : null,
        };
        if (!active()) { config = {}; return config; }
        if (config.pixel) {
            if (!window.fbq) {
                const fbq = function (...args) { fbq.callMethod ? fbq.callMethod(...args) : fbq.queue.push(args); };
                fbq.push = fbq; fbq.loaded = true; fbq.version = '2.0'; fbq.queue = [];
                window.fbq = fbq; window._fbq = fbq;
            }
            // Inertia changes history without a document reload. The app owns PageView;
            // disable the SDK's automatic history PageView before initializing/loading it.
            window.fbq.disablePushState = true;
            window.__storePixels ||= new Set();
            if (!window.__storePixels.has(config.pixel)) {
                window.fbq('set', 'autoConfig', false, config.pixel);
                window.fbq('init', config.pixel);
                window.__storePixels.add(config.pixel);
            }
            script('store-meta-pixel', 'https://connect.facebook.net/en_US/fbevents.js');
        }
        if (config.gtm) {
            window.dataLayer ||= [];
            if (!document.getElementById('store-gtm-' + config.gtm)) {
                window.dataLayer.push({ 'gtm.start': Date.now(), event: 'gtm.js' });
                script('store-gtm-' + config.gtm, 'https://www.googletagmanager.com/gtm.js?id=' + encodeURIComponent(config.gtm));
            }
        }
        return config;
    });
}
export function trackingItem(product, quantity = 1) {
    return {
        item_id: String(product.product_id ?? product.id),
        item_name: String(product.product_name ?? product.name ?? ''),
        ...(typeof product.category === 'string' ? { item_category: product.category } : {}),
        ...(typeof product.brand === 'string' ? { item_brand: product.brand } : {}),
        ...(product.variant_name || product.variant_label ? { item_variant: product.variant_name || product.variant_label } : {}),
        price: Math.max(0, Number(product.unit_price ?? product.price ?? 0)), quantity: Number(quantity),
    };
}
const validItems = items => items.length && items.every(item => item.item_id !== 'undefined' && Number.isFinite(item.price) && Number.isInteger(item.quantity) && item.quantity > 0);
const valueOf = items => items.reduce((total,item) => total + item.price * item.quantity,0);
function push(event, ecommerce) {
    if (!config.gtm || !active()) return;
    window.dataLayer.push({ ecommerce: null });
    window.dataLayer.push({ event, ecommerce });
}
function pixel(event, items, value, extras = {}, eventId) {
    if (!config.pixel || !active()) return;
    window.fbq('trackSingle', config.pixel, event, {
        currency: 'BDT', value: Number(value), content_type: 'product',
        content_ids: items.map(item => item.item_id),
        contents: items.map(item => ({ id: item.item_id, quantity: item.quantity, item_price: item.price })),
        num_items: items.reduce((sum,item) => sum + item.quantity,0), ...extras,
    }, ...(eventId ? [{ eventID: eventId }] : []));
}
export function trackCartChange(event, product, quantity) {
    return safe(() => {
        if (!['add_to_cart', 'remove_from_cart'].includes(event)) return;
        const items = [trackingItem(product, quantity)]; if (!validItems(items)) return;
        push(event, { currency:'BDT', value:valueOf(items), items });
        if (event === 'add_to_cart') pixel('AddToCart', items, valueOf(items));
    });
}
export function trackPaymentInfo(cart, paymentType) {
    return safe(() => {
        const items = cart.map(item => trackingItem(item,item.qty)); if (!validItems(items)) return;
        push('add_payment_info', {currency:'BDT',value:valueOf(items),payment_type:paymentType,items});
        pixel('AddPaymentInfo',items,valueOf(items));
    });
}
function alreadyPurchased(key) {
    if (purchases.has(key)) return true;
    try { return window.localStorage.getItem(key) === '1'; } catch { return false; }
}
function remember(key) {
    purchases.add(key); try { window.localStorage.setItem(key,'1'); } catch { /* In-memory deduplication remains available. */ }
}
export function trackPurchase(order, fresh) {
    return safe(() => {
        if (!active() || !fresh || !order || order.status === 'cancelled' || (order.payment_method !== 'cash_on_delivery' && order.payment_status !== 'paid')) return;
        const items = (order.items || []).map(item => trackingItem(item,item.quantity));
        if (!validItems(items) || !Number.isFinite(Number(order.total)) || !Number.isInteger(Number(order.id))) return;
        const transaction = String(10000 + Number(order.id));
        for (const target of ['gtm','pixel']) {
            if (!config[target]) continue;
            const key = `store-purchase-v1:${target}:${config[target]}:${transaction}`;
            if (alreadyPurchased(key)) continue;
            if (target === 'gtm') push('purchase', {transaction_id:transaction,currency:'BDT',value:Number(order.subtotal)-Number(order.discount_amount||0),shipping:Number(order.delivery_fee),items});
            else pixel('Purchase', items, Number(order.total), {order_id:transaction}, 'purchase-' + transaction);
            remember(key);
        }
    });
}
export function trackPage(page) {
    return safe(() => {
        if (page.url.startsWith('/admin') || page.props?.admin) { config = {}; return; }
        configureTracking(page.props?.storeSettings);
        if (!config.pixel && !config.gtm) return;
        const pageType = page.component.split('/').pop();
        if (!['Home','Category','NewArrivals','ProductDetails','Checkout','OrderSuccess'].includes(pageType)) return;
        const key = `${page.component}:${page.url}:${config.pixel}:${config.gtm}`;
        if (lastPage === key) return;
        lastPage = key;
        const path = new URL(page.url,window.location.origin).pathname;
        if (config.gtm) window.dataLayer.push({event:'page_view',page_location:window.location.origin+path,page_path:path,page_title:pageType});
        if (config.pixel) window.fbq('trackSingle',config.pixel,'PageView');
        const props = page.props || {};
        if (pageType === 'ProductDetails') {
            const items = [trackingItem(props.product)]; if (!validItems(items)) return;
            push('view_item',{currency:'BDT',value:valueOf(items),items});
            pixel('ViewContent',items,valueOf(items),{content_name:props.product.name});
        } else if (['Home','Category','NewArrivals'].includes(pageType)) {
            const products = Array.isArray(props.products) ? props.products : props.products?.data || [];
            const name = props.category?.name || (pageType === 'Home' ? 'Home products' : 'New arrivals');
            const items = products.slice(0,200).map((item,index) => ({...trackingItem(item),index}));
            if (validItems(items)) push('view_item_list',{item_list_name:name,items});
            if (pageType === 'Category' && config.pixel) window.fbq('trackSingleCustom',config.pixel,'ViewCategory',{content_name:name,content_category:name});
        } else if (pageType === 'Checkout') {
            let cart = []; try { cart = JSON.parse(window.sessionStorage.getItem('storefront-cart') || '[]'); } catch { return; }
            if (!Array.isArray(cart)) return;
            const items = cart.map(item=>trackingItem(item,item.qty)); if (!validItems(items)) return;
            push('begin_checkout',{currency:'BDT',value:valueOf(items),items}); pixel('InitiateCheckout',items,valueOf(items));
        } else if (pageType === 'OrderSuccess') trackPurchase(props.order,props.clearCart);
    });
}
