export function initProductDetail() {
    initGallery();
    initQuantity();
    initVariations();
}

function initGallery() {
    const mainImg = document.getElementById('productMainImageTag');
    const thumbs = document.querySelectorAll('.unyl-product__thumb');
    if (!mainImg || !thumbs.length) return;

    thumbs.forEach((thumb) => {
        thumb.addEventListener('click', () => {
            mainImg.src = thumb.dataset.image;
            thumbs.forEach((t) => t.classList.remove('is-active'));
            thumb.classList.add('is-active');
        });
    });
}

function initQuantity() {
    const input = document.getElementById('productQty');
    if (!input) return;

    document.querySelectorAll('.unyl-qty__btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            const current = parseInt(input.value, 10) || 1;
            const min = parseInt(input.min, 10) || 1;
            const next = btn.dataset.action === 'plus' ? current + 1 : Math.max(min, current - 1);
            input.value = next;
        });
    });
}

function initVariations() {
    const form = document.getElementById('productForm');
    if (!form) return;

    const variations = JSON.parse(form.dataset.variations || '[]');
    if (!variations.length) return;

    const selects = Array.from(form.querySelectorAll('.unyl-product__select'));
    const priceEl = document.getElementById('productPrice');
    const variationIdInput = document.getElementById('variationIdInput');
    const addToCartBtn = document.getElementById('addToCartBtn');
    const basePrice = priceEl ? priceEl.textContent : '';
    const mainImg = document.getElementById('productMainImageTag');
    const defaultImage = mainImg ? mainImg.src : null;

    function currentSelection() {
        const selection = {};
        selects.forEach((select) => {
            selection[select.dataset.attribute] = select.value;
        });
        return selection;
    }

    function matchVariation(selection) {
        return variations.find((variation) => {
            return Object.entries(selection).every(([attr, value]) => {
                const variationValue = variation.attributes[attr] ?? '';
                // Empty variation value means "Any" — matches any selected option.
                return variationValue === '' || variationValue === value;
            });
        });
    }

    function formatPrice(amount) {
        const num = Math.round(parseFloat(amount));
        return isNaN(num) ? '' : num.toLocaleString('en-US') + 'Ks';
    }

    function update() {
        const selection = currentSelection();
        const allSelected = Object.values(selection).every((v) => v !== '');

        if (!allSelected) {
            variationIdInput.value = '0';
            if (addToCartBtn) addToCartBtn.disabled = true;
            if (priceEl) priceEl.textContent = basePrice;
            if (mainImg && defaultImage) mainImg.src = defaultImage;
            return;
        }

        const match = matchVariation(selection);
        if (!match) {
            variationIdInput.value = '0';
            if (addToCartBtn) addToCartBtn.disabled = true;
            if (priceEl) priceEl.textContent = 'This combination is currently unavailable';
            return;
        }

        variationIdInput.value = match.id;
        if (addToCartBtn) addToCartBtn.disabled = match.stock_status !== 'instock';
        if (priceEl) priceEl.textContent = match.stock_status === 'instock' ? formatPrice(match.price) : 'Out of stock';
        if (mainImg) mainImg.src = match.image || defaultImage;
    }

    selects.forEach((select) => select.addEventListener('change', update));
    update();
}
