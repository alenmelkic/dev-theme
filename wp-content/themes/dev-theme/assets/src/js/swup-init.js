import Swup from 'swup';
import SwupHeadPlugin from '@swup/head-plugin';
import SwupScriptsPlugin from '@swup/scripts-plugin';
import SwupBodyClassPlugin from '@swup/body-class-plugin';
import SwupProgressPlugin from '@swup/progress-plugin';
import SwupA11yPlugin from '@swup/a11y-plugin';

/**
 * Initialize Swup for PJAX navigation with SEO optimizations
 */
const initSwup = () => {
    const swup = new Swup({
        containers: ["#swup"],
        plugins: [
            // SEO: Updates meta tags, title, Open Graph, canonical URLs
            new SwupHeadPlugin(),

            // Scripts: Re-run scripts in head and body
            new SwupScriptsPlugin({
                head: true,
                body: true
            }),

            // Body classes: Update body classes on page change
            new SwupBodyClassPlugin(),

            // Progress: Show loading indicator
            new SwupProgressPlugin(),

            // Accessibility: Announce page changes to screen readers
            new SwupA11yPlugin()
        ],
        animateHistoryBrowsing: true
    });

    // Re-initialize scripts on content replace
    swup.hooks.on('content:replace', () => {
        // Re-init navigation (mobile menu, dropdowns)
        if (window.initNavigation) {
            window.initNavigation();
        } else {
            // Fallback if function not exposed
            const event = new Event('DOMContentLoaded');
            document.dispatchEvent(event);
        }
    });

    // Analytics tracking for page views
    swup.hooks.on('page:view', () => {
        // Google Analytics 4
        if (typeof gtag !== 'undefined') {
            gtag('event', 'page_view', {
                page_path: window.location.pathname + window.location.search,
                page_title: document.title
            });
        }

        // Google Tag Manager
        if (typeof dataLayer !== 'undefined') {
            dataLayer.push({
                event: 'pageview',
                page: {
                    path: window.location.pathname + window.location.search,
                    title: document.title,
                    url: window.location.href
                }
            });
        }
    });

    return swup;
};

export default initSwup;
