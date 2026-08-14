/**
 * Fades + slides each top-level homepage section up into view as the user
 * scrolls to it. Progressive enhancement: the hidden state is only applied
 * once JS actually runs (via the "unyl-reveal" class added here), so a page
 * with JS disabled or slow just shows everything normally, not permanently
 * invisible content.
 */
export function initScrollReveal() {
    // Descendant selector (not just direct children) — homepage sections
    // sit directly under <main>, but About/Contact wrap theirs in a page
    // container div first. No other page uses <section> anywhere, so this
    // stays scoped to the sections actually meant to reveal.
    const sections = document.querySelectorAll('main section');
    if (!sections.length) return;

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.15, rootMargin: '0px 0px -60px 0px' }
    );

    sections.forEach((section) => {
        section.classList.add('unyl-reveal');
        observer.observe(section);
    });
}
