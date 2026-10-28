(() => {
    const key = 'storefront-cart';
    const read = () => {
        try { const cart = JSON.parse(sessionStorage.getItem(key) || '[]'); return Array.isArray(cart) ? cart.filter(i => Number.isInteger(i.id) && Number.isInteger(i.qty) && i.qty > 0 && Number.isFinite(Number(i.price))) : []; }
        catch { return []; }
    };
    const money = value => '৳' + Number(value).toLocaleString('en-BD', {minimumFractionDigits: 2});
    function save(cart) { sessionStorage.setItem(key, JSON.stringify(cart)); window.dispatchEvent(new CustomEvent('farbo-cart-changed')); }
    const escape = value => String(value ?? '').replace(/[&<>"']/g, character => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[character]));
    function render() {
        const cart = read(), count = cart.reduce((sum,item) => sum + item.qty,0);
        document.getElementById('cartBadge').textContent = count;
        document.getElementById('cartCountTitle').textContent = `(${count})`;
        document.getElementById('cartSubtotal').textContent = money(cart.reduce((sum,item) => sum + Number(item.price)*item.qty,0));
        document.getElementById('cartFooter').classList.toggle('hidden',!cart.length);
        document.getElementById('cartItems').innerHTML = cart.length ? cart.map(item => `
            <article class="flex gap-3 p-3 rounded-xl border border-gray-200 bg-white">
                <img src="${escape(item.image || window.farboLogoUrl)}" alt="${escape(item.name)}" class="w-16 h-16 rounded-lg object-contain">
                <div class="flex-1 min-w-0"><a href="/product/${item.id}" class="block text-sm font-semibold text-brand-navy">${escape(item.name)}</a><p class="text-xs text-gray-500 mt-1">${escape(item.variant_label)}</p>
                <div class="flex items-center justify-between mt-3"><div class="flex border border-gray-200 rounded-lg"><button type="button" data-cart-key="${escape(item.key)}" data-change="-1" class="w-8 h-8" aria-label="Decrease quantity">−</button><span class="w-8 grid place-items-center text-sm">${item.qty}</span><button type="button" data-cart-key="${escape(item.key)}" data-change="1" class="w-8 h-8" aria-label="Increase quantity">+</button></div><strong class="text-sm text-brand-navy">${money(Number(item.price)*item.qty)}</strong></div></div>
                <button type="button" data-remove="${escape(item.key)}" class="text-gray-400 hover:text-brand-red self-start" aria-label="Remove ${escape(item.name)}">×</button>
            </article>`).join('') : '<div class="py-16 text-center"><i class="fa-solid fa-cart-shopping text-4xl text-brand-navy"></i><h3 class="mt-5 font-bold">Your cart is empty</h3><p class="mt-2 text-sm text-gray-500">Choose a product to get started.</p></div>';
    }
    document.getElementById('cartItems').addEventListener('click', event => {
        const button = event.target.closest('button'); if (!button) return;
        let cart = read();
        if (button.dataset.remove) cart = cart.filter(item => item.key !== button.dataset.remove);
        else if (button.dataset.cartKey) { const item = cart.find(item => item.key === button.dataset.cartKey); if (!item) return; item.qty = Math.min(Number(item.stock ?? 100),100,Math.max(1,item.qty+Number(button.dataset.change))); }
        save(cart);
    });
    window.farboCart = {
        add(product, quantity = 1) {
            const cart = read(), existing = cart.find(item => item.key === product.key);
            if (!Number.isInteger(quantity) || quantity < 1 || quantity + (existing?.qty || 0) > Math.min(100,Number(product.stock))) return false;
            if (existing) existing.qty += quantity; else cart.push({...product, qty:quantity});
            save(cart); return true;
        }, read, render
    };
    window.addEventListener('farbo-cart-changed',render);
    render();
})();
