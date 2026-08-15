import './bootstrap';
import { initHeaderMenu } from './header-menu';
import { initHomeTimeline } from './home-timeline';
import { initSliders } from './slider';
import { initProductDetail } from './product-detail';
import { initScrollReveal } from './scroll-reveal';
import { initSearch } from './search';
import { initWishlist } from './wishlist';
import { initWorkshopAccordion } from './workshop-accordion';
import { initShopSidebar } from './shop-sidebar';
import { initProductPopups } from './product-popups';

document.addEventListener('DOMContentLoaded', () => {
    initHeaderMenu();
    initHomeTimeline();
    initSliders();
    initProductDetail();
    initScrollReveal();
    initSearch();
    initWishlist();
    initWorkshopAccordion();
    initShopSidebar();
    initProductPopups();
});
