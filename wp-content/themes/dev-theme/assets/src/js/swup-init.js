import Swup from 'swup';
import SwupScriptsPlugin from '@swup/scripts-plugin';
import SwupBodyClassPlugin from '@swup/body-class-plugin';

/**
 * Initialize Swup for PJAX navigation
 */
const initSwup = () => {
    const swup = new Swup({
        containers: ["#swup"],
        plugins: [
            new SwupScriptsPlugin({
                head: true,
                body: true
            }),
            new SwupBodyClassPlugin()
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

        // Re-init other scripts if needed
        console.log('Swup content replaced');
    });

    return swup;
};

export default initSwup;
