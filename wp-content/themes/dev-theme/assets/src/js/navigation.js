/**
 * Navigation Menu - Vanilla JavaScript
 * Handles mobile menu toggle and dropdown menus
 * No dependencies, pure vanilla JS
 */

(function () {
    'use strict';

    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    function init() {
        initMobileMenu();
        initDropdowns();
    }

    /**
     * Mobile menu toggle functionality
     */
    function initMobileMenu() {
        const mobileMenuToggle = document.querySelector('[data-toggle="mobile-menu"]');
        const mobileMenu = document.querySelector('.mobile-menu');

        if (!mobileMenuToggle || !mobileMenu) {
            return;
        }

        // Toggle mobile menu
        mobileMenuToggle.addEventListener('click', function (e) {
            e.stopPropagation();
            mobileMenu.classList.toggle('show');
            const isExpanded = mobileMenu.classList.contains('show');
            mobileMenuToggle.setAttribute('aria-expanded', isExpanded);
        });

        // Close mobile menu when clicking outside
        document.addEventListener('click', function (event) {
            if (!event.target.closest('.navbar') && mobileMenu.classList.contains('show')) {
                mobileMenu.classList.remove('show');
                mobileMenuToggle.setAttribute('aria-expanded', 'false');
            }
        });

        // Close mobile menu when clicking on nav links (not dropdown toggles)
        const navLinks = mobileMenu.querySelectorAll('.nav-link:not(.dropdown-toggle)');
        navLinks.forEach(link => {
            link.addEventListener('click', function () {
                if (mobileMenu.classList.contains('show')) {
                    mobileMenu.classList.remove('show');
                    mobileMenuToggle.setAttribute('aria-expanded', 'false');
                }
            });
        });
    }

    /**
     * Dropdown menu functionality
     */
    function initDropdowns() {
        const dropdownToggles = document.querySelectorAll('.dropdown-toggle');

        dropdownToggles.forEach(toggle => {
            const dropdown = toggle.nextElementSibling;
            const parentLi = toggle.closest('.nav-item');

            if (!dropdown || !parentLi) {
                return;
            }

            // Desktop: hover behavior
            if (window.innerWidth >= 992) {
                parentLi.addEventListener('mouseenter', function () {
                    dropdown.classList.add('show');
                    toggle.setAttribute('aria-expanded', 'true');
                });

                parentLi.addEventListener('mouseleave', function () {
                    dropdown.classList.remove('show');
                    toggle.setAttribute('aria-expanded', 'false');
                });
            }

            // Mobile: click behavior
            toggle.addEventListener('click', function (e) {
                if (window.innerWidth < 992) {
                    e.preventDefault();
                    e.stopPropagation();

                    // Close other dropdowns
                    const allDropdowns = document.querySelectorAll('.dropdown-menu.show');
                    allDropdowns.forEach(dd => {
                        if (dd !== dropdown) {
                            dd.classList.remove('show');
                        }
                    });

                    // Toggle this dropdown
                    dropdown.classList.toggle('show');
                    const isExpanded = dropdown.classList.contains('show');
                    toggle.setAttribute('aria-expanded', isExpanded);
                }
            });
        });

        // Handle window resize
        let resizeTimer;
        window.addEventListener('resize', function () {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function () {
                // Re-initialize dropdowns on resize
                const allDropdowns = document.querySelectorAll('.dropdown-menu.show');
                allDropdowns.forEach(dd => dd.classList.remove('show'));
            }, 250);
        });
    }

})();
