const key = 'storefront-wishlist';

export function readWishlist() {
    try {
        const entries = JSON.parse(window.localStorage.getItem(key) || '[]');
        return Array.isArray(entries)
            ? [...new Set(entries.filter(id => Number.isSafeInteger(id) && id > 0))].slice(0, 100)
            : [];
    } catch {
        return [];
    }
}

export function saveWishlist(entries) {
    try { window.localStorage.setItem(key, JSON.stringify(entries)); } catch {}
    window.dispatchEvent?.(new Event('farbo-wishlist-changed'));
}

export function wishlistUrl(entries = readWishlist()) {
    const query = new URLSearchParams({ wishlist: '1', wish_ids: entries.join(',') });
    return '/categories?' + query.toString();
}
