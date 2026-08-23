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

export function initBreadcrumbDropdown() {
    const trigger = document.getElementById('breadcrumbTrigger');
    const trail = document.getElementById('breadcrumbTrail');
    if (!trigger || !trail) return;

    trigger.addEventListener('click', (e) => {
        e.stopPropagation();
        trail.classList.toggle('is-open');
    });

    document.addEventListener('click', (e) => {
        if (!trail.classList.contains('is-open')) return;
        if (!e.target.closest('.unyl-breadcrumb')) {
            trail.classList.remove('is-open');
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') trail.classList.remove('is-open');
    });
}
