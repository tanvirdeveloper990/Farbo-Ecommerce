import { computed, ref, onBeforeUnmount } from "vue";
import { router, usePage } from '@inertiajs/vue3';
import Swal from "sweetalert2";
import { notify } from "./notify";
import { trackCartChange } from "./tracking";
import { readWishlist, saveWishlist, wishlistUrl } from "./wishlist";

export function readCart() {
    try {
        const value = JSON.parse(
            window.sessionStorage.getItem("storefront-cart") || "[]",
        );
        return Array.isArray(value)
            ? value.filter(
                  (item) =>
                      Number.isInteger(item.id) &&
                      Number.isInteger(item.qty) &&
                      item.qty > 0 &&
                      Number.isFinite(item.price),
              )
            : [];
    } catch {
        window.sessionStorage.removeItem("storefront-cart");
        return [];
    }
}

export function saveCart(cart) {
    window.sessionStorage.setItem("storefront-cart", JSON.stringify(cart));
    window.dispatchEvent(new CustomEvent('farbo-cart-changed'));
}

export function createStorefrontContext() {
    const page = usePage();
    const cart = ref(readCart());
    const syncCart = () => { cart.value = readCart(); };
    window.addEventListener('farbo-cart-changed', syncCart);
    onBeforeUnmount(() => window.removeEventListener('farbo-cart-changed', syncCart));
    const panel = ref(null);
    const wishlist = ref(readWishlist());
    const query = ref("");
    const searchFocused = ref(false);
    const searchSuggestions = computed(() => {
        const term = query.value.trim().toLowerCase();
        return term ? (page.props.catalogSearchProducts || []).filter(product =>
            `${product.name} ${product.category || ''}`.toLowerCase().includes(term)).slice(0, 6) : [];
    });
    const scrolled = ref(false);
    const cartCount = computed(() =>
        cart.value.reduce((sum, item) => sum + item.qty, 0),
    );
    const subtotal = computed(() =>
        cart.value.reduce((sum, item) => sum + item.qty * item.price, 0),
    );
    const taka = (amount) => "৳" + Number(amount).toLocaleString("en-IN");



    function addToCart(product, variant = null, quantity = 1) {
        const key = product.id + "-" + (variant?.id || "default");
        const existing = cart.value.find((item) => item.key === key);
        const stock = variant?.stock ?? product.stock;
        if (stock < quantity + (existing?.qty || 0)) {
            notify("The selected quantity is not available.");
            return false;
        }

        if (existing) existing.qty += quantity;
        else
            cart.value.push({
                ...product,
                key,
                variant_id: variant?.id || null,
                stock: variant?.stock ?? product.stock,
                variant_label: variant?.label || "",
                price: variant?.price ?? product.price,
                qty: quantity,
            });
        saveCart(cart.value);
        trackCartChange('add_to_cart', { ...product, price: variant?.price ?? product.price, variant_label: variant?.label }, quantity);
        notify(`${quantity} × ${product.name} added to cart.`);
        return true;
    }

    return {
        panel,
        wishlist,
        query,
        searchFocused,
        searchSuggestions,
        closeSearchSuggestions: () => { searchFocused.value = false; },
        scrolled,
        cart,
        cartCount,
        subtotal,
        taka,
        notify,
        addToCart,
        openPanel: (name) => {
            panel.value = name;
        },
        closePanel: () => {
            panel.value = null;
        },
        removeItem: async (item) => {
            const answer = await Swal.fire({
                title: "Remove this item?",
                text: item.name,
                icon: "warning",
                showCancelButton: true,
                showCloseButton: true,
                confirmButtonText: "Yes, remove",
                cancelButtonText: "No, keep it",
                confirmButtonColor: "#7b1e2e",
            });
            if (!answer.isConfirmed) return;
            trackCartChange('remove_from_cart', item, item.qty);
            cart.value = cart.value.filter((entry) => entry.key !== item.key);
            saveCart(cart.value);
        },
        submitSearch: () => {
            searchFocused.value = false;
            router.get('/', { q: query.value.trim() }, { preserveScroll: false });
        },
        showWishlist: () => {
            panel.value = null;
            router.visit(wishlistUrl(wishlist.value));
        },
        toggleWish: (id) => {
            wishlist.value = wishlist.value.includes(id)
                ? wishlist.value.filter((entry) => entry !== id)
                : [...wishlist.value, id];
            saveWishlist(wishlist.value);
        },
        openQuickView: (product) => {
            router.visit("/product/" + product.id);
        },
    };
}
