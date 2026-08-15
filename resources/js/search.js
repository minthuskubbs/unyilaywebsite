export function initSearch() {
    const overlay = document.getElementById('unylSearchOverlay');
    const openBtn = document.getElementById('openSearch');
    const closeBtn = document.getElementById('closeSearch');
    const input = document.getElementById('unylSearchInput');
    const results = document.getElementById('unylSearchResults');

    if (!overlay || !openBtn || !input || !results) return;

    let debounceTimer = null;
    let currentRequest = null;

    const open = () => {
        overlay.classList.add('is-open');
        document.body.style.overflow = 'hidden';
        setTimeout(() => input.focus(), 50);
    };

    const close = () => {
        overlay.classList.remove('is-open');
        document.body.style.overflow = '';
    };

    openBtn.addEventListener('click', open);
    closeBtn?.addEventListener('click', close);

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && overlay.classList.contains('is-open')) close();
    });

    const renderResults = (items) => {
        if (!items.length) {
            results.innerHTML = '<p class="unyl-search-results__empty">No products found.</p>';
            return;
        }

        results.innerHTML = items.map((item) => `
            <a class="unyl-search-result" href="${item.url}">
                <span class="unyl-search-result__image">
                    ${item.image ? `<img src="${item.image}" alt="" loading="lazy" />` : ''}
                </span>
                <span class="unyl-search-result__name">${item.name}</span>
                <span class="unyl-search-result__price">${item.price ?? ''}</span>
            </a>
        `).join('');
    };

    input.addEventListener('input', () => {
        const term = input.value.trim();
        clearTimeout(debounceTimer);

        if (term === '') {
            results.innerHTML = '';
            return;
        }

        debounceTimer = setTimeout(() => {
            currentRequest?.abort();
            const controller = new AbortController();
            currentRequest = controller;

            results.innerHTML = '<p class="unyl-search-results__hint">Searching…</p>';

            fetch(`/search?q=${encodeURIComponent(term)}`, { signal: controller.signal })
                .then((res) => res.json())
                .then((data) => renderResults(data.results || []))
                .catch((err) => {
                    if (err.name !== 'AbortError') results.innerHTML = '';
                });
        }, 300);
    });
}
