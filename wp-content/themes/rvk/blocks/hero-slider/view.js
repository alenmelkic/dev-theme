/**
 * Hero Slider – View Script (Dual Swiper Sync)
 *
 * Handles:
 *   - Main Hero Swiper initialization
 *   - Thumbnail Cards Swiper initialization
 *   - Syncing both using Swiper Controller module
 *   - Progress bar synchronization
 */

import Swiper from 'swiper';
import { Navigation, Autoplay, Controller } from 'swiper/modules';

class HeroSlider {
    constructor(el) {
        this.el = el;
        this.progressBar = el.querySelector('.hero-slider-progress__bar');

        this.autoplay = el.dataset.autoplay === 'true';
        this.autoplayDelay = parseInt(el.dataset.autoplayDelay) || 5000;

        this.heroSwiper = null;
        this.cardsSwiper = null;

        this.init();
    }

    init() {
        const heroEl = this.el.querySelector('.hero-swiper');
        const cardsEl = this.el.querySelector('.cards-swiper');

        if (!heroEl || !cardsEl) return;

        // 1. Initialize Main Hero Swiper
        this.heroSwiper = new Swiper(heroEl, {
            modules: [Navigation, Autoplay, Controller],
            slidesPerView: 1,
            roundLengths: true,
            loop: true,
            speed: 800,
            grabCursor: true,
            autoplay: this.autoplay ? {
                delay: this.autoplayDelay,
                disableOnInteraction: false,
                pauseOnMouseEnter: true,
            } : false,
            navigation: {
                nextEl: this.el.querySelector('.hero-slider-nav__btn--next'),
                prevEl: this.el.querySelector('.hero-slider-nav__btn--prev'),
            },
            on: {
                autoplayTimeLeft: (_s, _t, progress) => {
                    if (this.progressBar) {
                        this.progressBar.style.width = `${(1 - progress) * 100}%`;
                    }
                },
            },
        });

        // 2. Initialize Thumbnail Cards Swiper
        this.cardsSwiper = new Swiper(cardsEl, {
            modules: [Controller],
            loop: true,
            speed: 800,
            slidesPerView: 1.2,
            spaceBetween: 16,
            grabCursor: true,
            watchSlidesProgress: true,
            breakpoints: {
                768: {
                    slidesPerView: 2.2,
                    spaceBetween: 20,
                }
            }
        });

        // 3. Link them together
        this.heroSwiper.controller.control = this.cardsSwiper;
        this.cardsSwiper.controller.control = this.heroSwiper;
    }
}

function initHeroSliders() {
    const blocks = document.querySelectorAll('.hero-slider-block');
    blocks.forEach((el) => {
        if (!el.heroSliderInstance) {
            el.heroSliderInstance = new HeroSlider(el);
        }
    });
}

// Initial load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initHeroSliders);
} else {
    initHeroSliders();
}

// Swup / AJAX support
document.addEventListener('swup:content:replace', initHeroSliders);
document.addEventListener('swup:page:view', initHeroSliders);
document.addEventListener('swup:enable', initHeroSliders);
