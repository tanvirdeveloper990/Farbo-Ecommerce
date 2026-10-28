export function initHeaderSearch(products) {
    let catalog = products || [];
    const instances = [];
    document.querySelectorAll('.farbo-live-search').forEach((form, index) => {
        const input = form.querySelector('input[name="q"]');
        input.autocomplete = 'off';
        input.setAttribute('role', 'combobox');
        input.setAttribute('aria-autocomplete', 'list');
        input.setAttribute('aria-expanded', 'false');
        const popup = document.createElement('div');
        popup.className = 'farbo-search-results';
        popup.id = `farbo-search-results-${index}`;
        popup.setAttribute('role', 'listbox');
        popup.setAttribute('aria-label', 'Matching products');
        input.setAttribute('aria-controls', popup.id);
        popup.hidden = true;
        const clear = document.createElement('button');
        clear.type = 'button';
        clear.className = 'farbo-search-clear';
        clear.textContent = '×';
        clear.setAttribute('aria-label', 'Clear search');
        clear.hidden = true;
        input.parentElement.insertBefore(clear, input.nextSibling);
        form.appendChild(popup);
        let selected = -1;
        const close = () => { popup.hidden = true; input.setAttribute('aria-expanded', 'false'); input.removeAttribute('aria-activedescendant'); };
        const highlight = () => {
            [...popup.querySelectorAll('a')].forEach((row, i) => {
                row.classList.toggle('is-selected', selected === i);
                row.setAttribute('aria-selected', String(selected === i));
                if (selected === i) {input.setAttribute('aria-activedescendant', row.id); row.scrollIntoView({block:'nearest'});}
            });
        };
        const render = () => {
            const query = input.value.trim().toLocaleLowerCase();
            clear.hidden = !query;
            popup.replaceChildren();
            selected = -1;
            if (!query) {close(); return;}
            const matches = catalog.filter(product => `${product.name} ${product.category || ''}`.toLocaleLowerCase().includes(query)).slice(0, 10);
            matches.forEach((product, i) => {
                const row = document.createElement('a');
                row.href = `/product/${product.id}`;
                row.className = 'farbo-search-result';
                row.id = `${popup.id}-${i}`;
                row.setAttribute('role', 'option');
                const image = document.createElement('img');
                image.src = product.image || '/farbo/assets/img/logo/logo.jpeg';
                image.alt = '';
                image.addEventListener('error', () => { image.src = '/farbo/assets/img/logo/logo.jpeg'; }, {once:true});
                const copy = document.createElement('span'); copy.className = 'farbo-search-copy';
                const name = document.createElement('strong'); name.textContent = product.name;
                const category = document.createElement('small'); category.textContent = product.category || '';
                copy.append(name, category);
                const price = document.createElement('span'); price.className = 'farbo-search-price';
                price.textContent = '৳' + Number(product.price).toLocaleString('en-BD', {maximumFractionDigits:2});
                row.append(image, copy, price); popup.appendChild(row);
            });
            if (!matches.length) { const message = document.createElement('p'); message.className = 'farbo-search-empty'; message.textContent = 'No matching products found.'; popup.appendChild(message); }
            popup.hidden = false;
            input.setAttribute('aria-expanded', 'true');
        };
        input.addEventListener('input', render);
        input.addEventListener('focus', render);
        input.addEventListener('keydown', event => {
            if (event.key === 'Escape') {close(); return;}
            const rows = popup.querySelectorAll('a');
            if ((event.key === 'ArrowDown' || event.key === 'ArrowUp') && rows.length) {
                event.preventDefault(); selected = (selected + (event.key === 'ArrowDown' ? 1 : -1) + rows.length) % rows.length; highlight();
            } else if (event.key === 'Enter' && !popup.hidden && selected >= 0) {event.preventDefault(); rows[selected].click();}
        });
        clear.addEventListener('click', () => {input.value = ''; render(); input.focus();});
        document.addEventListener('click', event => {if (!form.contains(event.target)) close();});
        form.addEventListener('submit', close);
        instances.push({close, render});
    });
    return {update: products => {catalog = products || [];}, close: () => instances.forEach(instance => instance.close())};
}
