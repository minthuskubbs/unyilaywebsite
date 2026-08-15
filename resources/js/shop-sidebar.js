export function initShopSidebar() {
    const sidebar = document.getElementById('shopSidebar');
    const trigger = document.getElementById('openShopFilter');
    const closeBtn = document.getElementById('closeShopFilter');
    const backdrop = document.getElementById('shopFilterBackdrop');
    if (!sidebar || !trigger) return;

    const open = () => {
        sidebar.classList.add('is-open');
        backdrop?.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    };

    const close = () => {
        sidebar.classList.remove('is-open');
        backdrop?.classList.remove('is-open');
        document.body.style.overflow = '';
    };

    trigger.addEventListener('click', open);
    closeBtn?.addEventListener('click', close);
    backdrop?.addEventListener('click', close);
}
