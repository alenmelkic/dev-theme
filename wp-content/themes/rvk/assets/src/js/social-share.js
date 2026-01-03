/**
 * Social Share Buttons - Frontend Functionality
 * Handles social sharing interactions
 */

(function() {
    'use strict';

    /**
     * Initialize social share buttons
     */
    function initSocialShare() {
        const shareButtons = document.querySelectorAll('.rvk-social-share-buttons .share-button');

        if (!shareButtons.length) {
            return;
        }

        shareButtons.forEach(button => {
            const platform = button.getAttribute('data-platform');

            // Handle copy link button
            if (button.classList.contains('share-copy')) {
                button.addEventListener('click', handleCopyLink);
            }
            // Handle social platform buttons (open in popup)
            else if (platform && button.tagName === 'A') {
                button.addEventListener('click', handleSocialShare);
            }
        });
    }

    /**
     * Handle social platform share (open in popup)
     * @param {Event} e Click event
     */
    function handleSocialShare(e) {
        e.preventDefault();

        const url = this.href;
        const platform = this.getAttribute('data-platform');

        // Platform-specific popup dimensions
        const popupSizes = {
            facebook: { width: 600, height: 400 },
            twitter: { width: 550, height: 420 },
            linkedin: { width: 600, height: 600 },
            whatsapp: { width: 550, height: 600 },
            email: { width: 600, height: 400 }
        };

        const size = popupSizes[platform] || { width: 600, height: 400 };

        // Calculate popup position (center of screen)
        const left = (window.screen.width - size.width) / 2;
        const top = (window.screen.height - size.height) / 2;

        const popupParams = `width=${size.width},height=${size.height},left=${left},top=${top},toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes`;

        // Open popup window
        const popup = window.open(url, `share-${platform}`, popupParams);

        if (popup) {
            popup.focus();

            // Track share event (optional - for analytics)
            trackShareEvent(platform, url);
        }

        return false;
    }

    /**
     * Handle copy link to clipboard
     * @param {Event} e Click event
     */
    function handleCopyLink(e) {
        e.preventDefault();

        const url = this.getAttribute('data-url');
        const button = this;
        const label = button.querySelector('.share-label');
        const originalText = label ? label.textContent : '';

        // Copy to clipboard
        if (navigator.clipboard && navigator.clipboard.writeText) {
            // Modern clipboard API
            navigator.clipboard.writeText(url)
                .then(() => {
                    showCopySuccess(button, label, originalText);
                })
                .catch(err => {
                    console.error('Failed to copy:', err);
                    fallbackCopyToClipboard(url, button, label, originalText);
                });
        } else {
            // Fallback for older browsers
            fallbackCopyToClipboard(url, button, label, originalText);
        }
    }

    /**
     * Show copy success state
     * @param {Element} button Button element
     * @param {Element} label Label element
     * @param {string} originalText Original button text
     */
    function showCopySuccess(button, label, originalText) {
        // Add copied class
        button.classList.add('copied');

        // Update label
        if (label) {
            label.textContent = 'Kopirano!';
        }

        // Track copy event
        trackShareEvent('copy', button.getAttribute('data-url'));

        // Reset after 2 seconds
        setTimeout(() => {
            button.classList.remove('copied');
            if (label) {
                label.textContent = originalText;
            }
        }, 2000);
    }

    /**
     * Fallback copy method for older browsers
     * @param {string} url URL to copy
     * @param {Element} button Button element
     * @param {Element} label Label element
     * @param {string} originalText Original button text
     */
    function fallbackCopyToClipboard(url, button, label, originalText) {
        // Create temporary input
        const tempInput = document.createElement('textarea');
        tempInput.value = url;
        tempInput.style.position = 'fixed';
        tempInput.style.opacity = '0';
        tempInput.style.top = '0';
        tempInput.style.left = '0';

        document.body.appendChild(tempInput);

        // Select and copy
        tempInput.select();
        tempInput.setSelectionRange(0, 99999); // For mobile devices

        try {
            const successful = document.execCommand('copy');
            if (successful) {
                showCopySuccess(button, label, originalText);
            } else {
                console.error('Copy command failed');
            }
        } catch (err) {
            console.error('Fallback copy failed:', err);
        }

        // Remove temporary input
        document.body.removeChild(tempInput);
    }

    /**
     * Track share event (for analytics)
     * @param {string} platform Platform name
     * @param {string} url Shared URL
     */
    function trackShareEvent(platform, url) {
        // Google Analytics (GA4)
        if (typeof gtag === 'function') {
            gtag('event', 'share', {
                method: platform,
                content_type: 'article',
                item_id: url
            });
        }

        // Google Analytics (Universal Analytics)
        if (typeof ga === 'function') {
            ga('send', 'event', 'Social Share', platform, url);
        }

        // Facebook Pixel
        if (typeof fbq === 'function') {
            fbq('track', 'Share', {
                platform: platform,
                url: url
            });
        }

        // Custom event for theme
        document.dispatchEvent(new CustomEvent('rvk-social-share', {
            detail: {
                platform: platform,
                url: url,
                timestamp: new Date().toISOString()
            }
        }));
    }

    /**
     * Initialize on DOM ready
     */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSocialShare);
    } else {
        initSocialShare();
    }

    /**
     * Re-initialize after SWUP page transitions (if SWUP is active)
     */
    if (typeof Swup !== 'undefined') {
        document.addEventListener('swup:contentReplaced', initSocialShare);
    }

})();
