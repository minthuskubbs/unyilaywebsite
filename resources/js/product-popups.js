export function initProductPopups() {
    const modals = document.querySelectorAll('.unyl-popup-modal');
    if (!modals.length) return;

    document.querySelectorAll('[data-modal-trigger]').forEach((btn) => {
        btn.addEventListener('click', () => {
            document.getElementById(btn.dataset.modalTrigger)?.classList.add('is-open');
            document.body.style.overflow = 'hidden';
        });
    });

    document.querySelectorAll('[data-modal-close]').forEach((el) => {
        el.addEventListener('click', () => {
            el.closest('.unyl-popup-modal')?.classList.remove('is-open');
            document.body.style.overflow = '';
        });
    });

    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Escape') return;
        document.querySelectorAll('.unyl-popup-modal.is-open').forEach((m) => m.classList.remove('is-open'));
        document.body.style.overflow = '';
    });

    document.querySelectorAll('[data-tab-trigger]').forEach((tabBtn) => {
        tabBtn.addEventListener('click', () => {
            const group = tabBtn.dataset.tabGroup;
            const target = tabBtn.dataset.tabTrigger;

            document.querySelectorAll(`[data-tab-trigger][data-tab-group="${group}"]`).forEach((t) => t.classList.remove('is-active'));
            document.querySelectorAll(`[data-tab-panel][data-tab-group="${group}"]`).forEach((p) => p.classList.remove('is-active'));

            tabBtn.classList.add('is-active');
            document.querySelector(`[data-tab-panel="${target}"][data-tab-group="${group}"]`)?.classList.add('is-active');
        });
    });
}
