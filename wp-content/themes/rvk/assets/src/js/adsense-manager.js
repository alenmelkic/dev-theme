/**
 * AdSense Manager
 * Handles lazy loading, analytics tracking, and Swup integration for AdSense ads
 */

class AdSenseManager {
    constructor() {
        this.observerConfig = {
            root: null,
            rootMargin: '200px', // Start loading 200px before viewport
            threshold: 0.01
        };

        this.observer = null;
        this.initialized = false;
        this.trackedAds = new Set(); // Track which ads we've already tracked

        this.init();
    }

    init() {
        if (this.initialized) return;

        // Wait for DOM ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setup());
        } else {
            this.setup();
        }

        this.initialized = true;
    }

    setup() {
        this.initLazyLoading();
        this.initAnalytics();

        // Re-init on Swup page change
        if (window.swup) {
            window.swup.hooks.on('content:replace', () => {
                // Clear tracked ads set on page change
                this.trackedAds.clear();
                this.initLazyLoading();
                this.initAnalytics();
            });
        }
    }

    initLazyLoading() {
        const lazyAds = document.querySelectorAll('.adsbygoogle[data-lazy-load="true"]');

        if (lazyAds.length === 0) return;

        // Create IntersectionObserver
        if (this.observer) {
            this.observer.disconnect();
        }

        this.observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.loadAd(entry.target);
                    this.observer.unobserve(entry.target);
                }
            });
        }, this.observerConfig);

        // Observe all lazy ads
        lazyAds.forEach(ad => this.observer.observe(ad));
    }

    loadAd(adElement) {
        // Hide placeholder
        const adId = adElement.id;
        const placeholder = document.getElementById(`${adId}-placeholder`);
        if (placeholder) {
            placeholder.style.display = 'none';
        }

        // Show ad container
        adElement.style.display = 'block';

        // Initialize AdSense
        try {
            (window.adsbygoogle = window.adsbygoogle || []).push({});

            // Track impression after a short delay (allow ad to render)
            setTimeout(() => {
                this.trackImpression(adElement);
            }, 1000);
        } catch (e) {
            console.error('AdSense error:', e);
        }
    }

    initAnalytics() {
        // Track all visible ads (non-lazy loaded)
        const visibleAds = document.querySelectorAll('.adsbygoogle:not([data-lazy-load="true"])');

        visibleAds.forEach(ad => {
            // Wait for ad to render
            setTimeout(() => {
                this.trackImpression(ad);
            }, 1000);
        });
    }

    trackImpression(adElement) {
        const position = adElement.dataset.position;
        const adSlot = adElement.dataset.adSlot;

        // Create unique identifier for this ad
        const adKey = `${position}-${adSlot}`;

        // Skip if already tracked
        if (this.trackedAds.has(adKey)) {
            return;
        }

        // Mark as tracked
        this.trackedAds.add(adKey);

        const abVariant = adElement.dataset.abVariant || 'none';

        // Google Analytics 4
        if (typeof gtag !== 'undefined') {
            gtag('event', 'ad_impression', {
                ad_position: position,
                ad_slot: adSlot,
                ab_variant: abVariant,
                event_category: 'AdSense',
                event_label: position,
                non_interaction: true
            });
        }

        // Google Tag Manager
        if (typeof dataLayer !== 'undefined') {
            dataLayer.push({
                event: 'adsense_impression',
                adPosition: position,
                adSlot: adSlot,
                adType: 'adsense',
                abVariant: abVariant
            });
        }

        // Debug log
        if (window.location.hostname === 'localhost' || window.location.hostname.includes('127.0.0.1')) {
            console.log(`[AdSense] Impression tracked: ${position} (Slot: ${adSlot}, Variant: ${abVariant})`);
        }
    }

    // Cleanup method
    destroy() {
        if (this.observer) {
            this.observer.disconnect();
        }
        this.trackedAds.clear();
    }
}

// Initialize
const adsenseManager = new AdSenseManager();

// Export for testing/debugging
export default AdSenseManager;
