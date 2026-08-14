import './bootstrap';
import { initHeaderMenu } from './header-menu';
import { initHomeTimeline } from './home-timeline';
import { initSliders } from './slider';
import { initProductDetail } from './product-detail';
import { initScrollReveal } from './scroll-reveal';

document.addEventListener('DOMContentLoaded', () => {
    initHeaderMenu();
    initHomeTimeline();
    initSliders();
    initProductDetail();
    initScrollReveal();
});
