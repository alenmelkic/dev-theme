/**
 * Servicne Informacije - Filter Enhancement
 * Auto-submit filter form on tag change
 */

(function () {
    'use strict';

    // Wait for DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    function init() {
        const filterForm = document.getElementById('filter-form');

        if (!filterForm) {
            return;
        }

        // Auto-submit on tag filter change
        const tagFilter = document.getElementById('tag-filter');
        if (tagFilter) {
            tagFilter.addEventListener('change', function () {
                filterForm.submit();
            });
        }

        // Add loading state on submit
        filterForm.addEventListener('submit', function () {
            const grid = document.querySelector('.servicne-informacije-grid');
            if (grid) {
                grid.classList.add('loading');
                grid.style.opacity = '0.5';
            }
        });
    }

})();
