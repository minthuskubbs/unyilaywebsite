export function initWorkshopAccordion() {
    const items = document.querySelectorAll('#workshopAccordion [data-accordion-item]');
    if (!items.length) return;

    items.forEach((item) => {
        const trigger = item.querySelector('[data-accordion-trigger]');
        trigger?.addEventListener('click', () => {
            if (item.classList.contains('is-active')) return;
            items.forEach((i) => i.classList.remove('is-active'));
            item.classList.add('is-active');
        });
    });
}
