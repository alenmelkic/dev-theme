// Mobile menu

const focusableSelectors = 'a, button, input, textarea, select, [tabindex]:not([tabindex="-1"])';

let focusableElements, firstFocusableElement, lastFocusableElement;
let navMenu, hamburger;

const updateFocusableElements = () => {
    focusableElements = navMenu?.querySelectorAll(focusableSelectors);
    firstFocusableElement = focusableElements?.[0];
    lastFocusableElement = focusableElements?.[focusableElements.length - 1];
};

const handleTabKey = (event) => {
    if (event.key !== "Tab") return;

    updateFocusableElements();
    const activeElement = document.activeElement;
    const activeSecondLevel = document.querySelector('.second-level.active');

    if (activeSecondLevel) {
        const secondLevelElements = activeSecondLevel.querySelectorAll(focusableSelectors);
        const lastSecondLevelItem = secondLevelElements[secondLevelElements.length - 1];
        const backButton = activeSecondLevel.querySelector('.back__menu');

        if (!event.shiftKey && activeElement === lastSecondLevelItem && backButton) {
            event.preventDefault();
            backButton.focus();
            return;
        }
    }

    requestAnimationFrame(() => {
        if (!event.shiftKey && activeElement === lastFocusableElement) {
            event.preventDefault();
            hamburger?.focus();
        } else if (event.shiftKey && activeElement === hamburger) {
            event.preventDefault();
            lastFocusableElement?.focus();
        }
    });
};

const closeOnEscape = (event) => {
    if (event.key === "Escape") {
        navMenu?.classList.remove("active");
        hamburger?.classList.remove("active");
        hamburger?.setAttribute("aria-expanded", "false");
        navMenu?.setAttribute('aria-hidden', 'true');
        document.removeEventListener("keydown", handleTabKey);
        document.removeEventListener("keydown", closeOnEscape);
        hamburger?.focus();
    }
};

const toggleMenu = () => {
    // Close external elements
    document.querySelector("[data-lp-point='close']")?.click();
    document.querySelector('.LPMslider [aria-expanded="true"]')?.click();

    // Toggle menu
    hamburger.classList.toggle("active");
    navMenu.classList.toggle("active");

    const menuOpen = navMenu.classList.contains("active");
    hamburger.setAttribute("aria-expanded", menuOpen);
    navMenu.setAttribute('aria-hidden', !menuOpen);

    if (menuOpen) {
        updateFocusableElements();
        firstFocusableElement?.focus();
        document.addEventListener("keydown", handleTabKey);
        document.addEventListener("keydown", closeOnEscape);
    } else {
        document.removeEventListener("keydown", handleTabKey);
        document.removeEventListener("keydown", closeOnEscape);
        hamburger.focus();
    }
};

const initStickyHeader = () => {
    const header = document.getElementById('masthead');
    if (!header) return;

    let lastScrollY = window.scrollY;

    const onScroll = () => {
        const currentScrollY = window.scrollY;
        const isMobile = window.innerWidth < 992;

        // Glass effect on scroll
        if (currentScrollY > 10) {
            header.classList.add('is-scrolled');
        } else {
            header.classList.remove('is-scrolled');
        }

        // Mobile hide/show on scroll direction
        if (isMobile) {
            if (currentScrollY > lastScrollY && currentScrollY > 80) {
                header.classList.add('header-hidden');
            } else {
                header.classList.remove('header-hidden');
            }
        } else {
            header.classList.remove('header-hidden');
        }

        lastScrollY = currentScrollY;
    };

    window.addEventListener('scroll', onScroll, { passive: true });
};

// Main hamburger functionality
export const initNavigation = () => {
    // Re-select elements on each init
    hamburger = document.querySelector(".hamburger");
    navMenu = document.querySelector(".menu-nav-mobile");

    if (hamburger) {
        // Remove existing listener to prevent duplicates if init is called multiple times
        hamburger.removeEventListener("click", toggleMenu);
        hamburger.addEventListener("click", toggleMenu);
    }

    initStickyHeader();
};

export default initNavigation;