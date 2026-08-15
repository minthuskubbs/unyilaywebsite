export function initWishlist() {
    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    const countBadge = document.getElementById('wishlistCount');
    if (!token) return;

    document.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-wishlist-toggle]');
        if (!btn) return;

        e.preventDefault();
        e.stopPropagation();

        const productId = btn.dataset.productId;

        fetch('/wishlist/toggle', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ product_id: productId }),
        })
            .then((res) => res.json())
            .then((data) => {
                document.querySelectorAll(`[data-wishlist-toggle][data-product-id="${productId}"]`).forEach((el) => {
                    el.classList.toggle('is-active', data.in_wishlist);
                });

                if (countBadge) {
                    countBadge.textContent = data.count;
                    countBadge.style.display = data.count > 0 ? '' : 'none';
                }
            })
            .catch(() => {});
    });
}
