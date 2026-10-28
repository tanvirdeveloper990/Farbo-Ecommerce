import assert from 'node:assert/strict';
import { readWishlist, saveWishlist, wishlistUrl } from '../resources/js/Storefront/wishlist.js';

const storage = new Map();
globalThis.window = { localStorage: {
    getItem: key => storage.get(key) ?? null,
    setItem: (key, value) => storage.set(key, value),
} };
assert.deepEqual(readWishlist(), []);
saveWishlist([5, 13]);
assert.deepEqual(readWishlist(), [5, 13]);
assert.equal(new URL(wishlistUrl(), 'http://localhost').searchParams.get('wish_ids'), '5,13');
saveWishlist([5, 5, '13', -1, null, 13]);
assert.deepEqual(readWishlist(), [5, 13]);
storage.set('storefront-wishlist', '{invalid');
assert.deepEqual(readWishlist(), []);
storage.set('storefront-wishlist', '{}');
assert.deepEqual(readWishlist(), []);
saveWishlist(Array.from({ length: 110 }, (_, i) => i + 1));
assert.equal(readWishlist().length, 100);
window.localStorage.setItem = () => { throw new Error('Storage unavailable'); };
assert.doesNotThrow(() => saveWishlist([5]));
console.log('PASS: wishlist persists valid IDs, builds its URL, and handles invalid or unavailable storage.');
