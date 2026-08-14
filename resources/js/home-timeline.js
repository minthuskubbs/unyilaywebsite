export function initHomeTimeline() {
    const stage = document.getElementById('unylStage');
    const row = document.getElementById('unylEraRow');
    if (!stage || !row) return;

    const items = Array.from(row.querySelectorAll('.unyl-timeline__item'));
    const bgImages = Array.from(stage.querySelectorAll('.unyl-timeline__bgimg'));
    const DURATION = 5000;
    let index = 0;
    let timer;

    function activate(i) {
        index = (i + items.length) % items.length;

        items.forEach((btn, idx) => {
            const bar = btn.querySelector('.unyl-timeline__base span');
            btn.classList.remove('is-active', 'is-done');
            bar.classList.remove('is-filling');
            void bar.offsetWidth;
            if (idx < index) btn.classList.add('is-done');
        });

        const activeBtn = items[index];
        const activeBar = activeBtn.querySelector('.unyl-timeline__base span');
        activeBtn.classList.add('is-active');
        activeBar.style.setProperty('--unyl-duration', DURATION + 'ms');
        requestAnimationFrame(() => activeBar.classList.add('is-filling'));

        bgImages.forEach((img) => {
            img.classList.toggle('is-active', img.getAttribute('data-era') === String(index));
        });

        restart();
    }

    function next() { activate(index + 1); }

    function restart() {
        clearTimeout(timer);
        timer = setTimeout(next, DURATION);
    }

    items.forEach((btn, idx) => {
        btn.addEventListener('click', () => activate(idx));
    });

    stage.addEventListener('mouseenter', () => clearTimeout(timer));
    stage.addEventListener('mouseleave', () => activate(index));

    activate(0);
}
