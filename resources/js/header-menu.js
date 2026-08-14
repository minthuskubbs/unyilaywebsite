export function initHeaderMenu() {
    initDesktopMenu();
    initMobileMenu();
}

function initDesktopMenu() {
    const trigger = document.querySelector('.unyl-menu-trigger');
    const shopMenu = document.getElementById('unylMenuShop');
    const stage1 = document.getElementById('unylMenuStage1');
    const stage2Panels = document.querySelectorAll('.unyl-menu-stage2');
    // Stage 1 (Big Items / Jewelry list) only advances to stage 2 on click —
    // hovering the row must not auto-expand it. Once inside stage 2, the
    // condensed group list there can still switch groups on hover.
    const stage1Rows = document.querySelectorAll('#unylMenuStage1 .unyl-menu-row[data-unyl-group]');
    const stage2Rows = document.querySelectorAll('.unyl-menu-stage2 .unyl-menu-row[data-unyl-group]');
    if (!trigger || !shopMenu) return;

    function showGroup(key) {
        stage1.classList.add('is-hidden');
        stage2Panels.forEach((panel) => panel.classList.toggle('is-active', panel.dataset.unylStage2 === key));
    }

    function resetToStage1() {
        stage1.classList.remove('is-hidden');
        stage2Panels.forEach((panel) => panel.classList.remove('is-active'));
    }

    const openShopMenu = (e) => {
        e.preventDefault();
        shopMenu.classList.add('is-open');
        trigger.classList.add('is-active');
    };
    trigger.addEventListener('mouseenter', openShopMenu);
    trigger.addEventListener('click', openShopMenu);

    stage1Rows.forEach((row) => {
        row.addEventListener('click', (e) => {
            e.preventDefault();
            showGroup(row.dataset.unylGroup);
        });
    });

    stage2Rows.forEach((row) => {
        row.addEventListener('mouseenter', () => showGroup(row.dataset.unylGroup));
        row.addEventListener('click', (e) => {
            e.preventDefault();
            showGroup(row.dataset.unylGroup);
        });
    });

    document.addEventListener('click', (e) => {
        if (!e.target.closest('#unylMenuShop, .unyl-menu-trigger')) {
            shopMenu.classList.remove('is-open');
            trigger.classList.remove('is-active');
            resetToStage1();
        }
    });

    shopMenu.addEventListener('mouseleave', () => {
        shopMenu.classList.remove('is-open');
        trigger.classList.remove('is-active');
        resetToStage1();
    });
}

function initMobileMenu() {
    const mobileMenu = document.getElementById('unylMobileMenu');
    const openBtn = document.getElementById('openMobileMenu');
    const closeBtns = document.querySelectorAll('.unyl-menu-mobile-close');
    const mobileList = document.getElementById('unylMobileList');
    const mobileRows = document.querySelectorAll('.unyl-menu-mobile-row');
    const mobileDetails = document.querySelectorAll('.unyl-menu-mobile-detail');
    const backBtns = document.querySelectorAll('.unyl-menu-mobile-back');

    openBtn?.addEventListener('click', () => mobileMenu?.classList.add('is-active'));

    const closeMobileMenu = () => {
        mobileMenu?.classList.remove('is-active');
        mobileDetails.forEach((d) => d.classList.remove('is-active'));
        mobileList?.classList.add('is-active');
    };
    closeBtns.forEach((btn) => btn.addEventListener('click', closeMobileMenu));

    mobileRows.forEach((row) => {
        row.addEventListener('click', () => {
            const key = row.dataset.unylGroup;
            mobileList?.classList.remove('is-active');
            mobileDetails.forEach((d) => d.classList.toggle('is-active', d.dataset.unylDetail === key));
        });
    });

    backBtns.forEach((btn) => {
        btn.addEventListener('click', () => {
            mobileDetails.forEach((d) => d.classList.remove('is-active'));
            mobileList?.classList.add('is-active');
        });
    });
}
