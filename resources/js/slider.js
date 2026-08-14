export function initSliders() {
    document.querySelectorAll('.unyl-slider').forEach(initSlider);
}

function initSlider(root) {
    const viewport = root.querySelector('.unyl-slider__viewport');
    const track = root.querySelector('.unyl-slider__track');
    const stage = root.querySelector('.unyl-slider__stage');
    const dotsWrap = root.querySelector('.unyl-slider-dots');
    const prevBtn = root.querySelector('.unyl-slider-arrow.prev');
    const nextBtn = root.querySelector('.unyl-slider-arrow.next');
    if (!track || !viewport || !stage) return;

    const realSlides = Array.from(track.children);
    const realCount = realSlides.length;
    if (!realCount) return;

    const perViewDesktop = parseInt(root.dataset.perViewDesktop, 10) || 3;
    const perViewTablet = parseInt(root.dataset.perViewTablet, 10) || 2;
    const perViewMobile = parseInt(root.dataset.perViewMobile, 10) || 1;

    let perView = getPerView();
    let index = 0;
    let timer;
    const autoplayMs = 3500;
    const gap = 20;

    // A slider with fewer real slides than perView can't loop meaningfully.
    if (realCount <= perView) {
        return;
    }

    function getPerView() {
        const w = window.innerWidth;
        if (w <= 640) return Math.min(perViewMobile, realCount);
        if (w <= 980) return Math.min(perViewTablet, realCount);
        return Math.min(perViewDesktop, realCount);
    }

    function build() {
        track.innerHTML = '';
        track.style.transition = 'none';

        const headClones = realSlides.slice(0, perView).map((n) => n.cloneNode(true));
        const tailClones = realSlides.slice(realCount - perView).map((n) => n.cloneNode(true));

        tailClones.forEach((n) => track.appendChild(n));
        realSlides.forEach((n) => track.appendChild(n));
        headClones.forEach((n) => track.appendChild(n));

        index = perView;
        layout();

        if (dotsWrap) {
            dotsWrap.innerHTML = '';
            for (let i = 0; i < realCount; i++) {
                const dot = document.createElement('button');
                dot.type = 'button';
                if (i === 0) dot.className = 'active';
                dot.addEventListener('click', () => goTo(i + perView));
                dotsWrap.appendChild(dot);
            }
        }

        requestAnimationFrame(() => {
            track.style.transition = 'transform 0.5s ease';
        });
    }

    function layout() {
        const stageWidth = viewport.clientWidth;
        const slideWidth = (stageWidth - gap * (perView - 1)) / perView;
        Array.from(track.children).forEach((slide) => {
            slide.style.width = slideWidth + 'px';
            slide.style.marginRight = gap + 'px';
        });
        render(false);
    }

    function render(animate) {
        track.style.transition = animate === false ? 'none' : 'transform 0.5s ease';
        const slideWidth = track.children[0].getBoundingClientRect().width + gap;
        track.style.transform = 'translateX(-' + (index * slideWidth) + 'px)';
        updateDots();
    }

    function updateDots() {
        if (!dotsWrap) return;
        const realIndex = ((index - perView) % realCount + realCount) % realCount;
        const dots = dotsWrap.children;
        for (let i = 0; i < dots.length; i++) {
            dots[i].className = i === realIndex ? 'active' : '';
        }
    }

    function goTo(i) {
        index = i;
        render(true);
        restartAutoplay();
    }

    function next() { goTo(index + 1); }
    function prev() { goTo(index - 1); }

    track.addEventListener('transitionend', () => {
        if (index >= realCount + perView) {
            index -= realCount;
            render(false);
        } else if (index < perView) {
            index += realCount;
            render(false);
        }
    });

    function restartAutoplay() {
        clearInterval(timer);
        timer = setInterval(next, autoplayMs);
    }

    nextBtn?.addEventListener('click', next);
    prevBtn?.addEventListener('click', prev);
    stage.addEventListener('mouseenter', () => clearInterval(timer));
    stage.addEventListener('mouseleave', restartAutoplay);

    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            const next = getPerView();
            if (next !== perView) {
                perView = next;
                build();
            } else {
                layout();
            }
        }, 150);
    });

    build();
    restartAutoplay();
}
