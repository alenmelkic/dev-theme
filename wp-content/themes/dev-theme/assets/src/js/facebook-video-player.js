/**
 * Facebook Video Player Controller
 * Uses lazy loading with Intersection Observer for performance
 */

// Global state for Facebook SDK
let fbSdkLoading = false;
let fbSdkLoaded = false;
const fbSdkCallbacks = [];

/**
 * Load Facebook SDK (singleton pattern)
 */
function loadFacebookSDK(callback) {
    if (fbSdkLoaded) {
        callback();
        return;
    }

    fbSdkCallbacks.push(callback);

    if (fbSdkLoading) return;

    fbSdkLoading = true;

    // Initialize Facebook SDK
    window.fbAsyncInit = function () {
        FB.init({
            xfbml: true,
            version: 'v18.0'
        });
        fbSdkLoaded = true;
        fbSdkCallbacks.forEach(cb => cb());
        fbSdkCallbacks.length = 0;
    };

    // Load SDK script
    const script = document.createElement('script');
    script.src = 'https://connect.facebook.net/en_US/sdk.js';
    script.async = true;
    script.defer = true;
    script.crossOrigin = 'anonymous';
    document.body.appendChild(script);
}

class FacebookVideoPlayer {
    constructor(playerElement) {
        this.player = playerElement;
        this.videoUrl = playerElement.dataset.videoUrl;
        this.videoId = playerElement.dataset.videoId;
        this.isLoaded = false;
        this.wasRadioPlaying = false;

        // UI Elements
        this.ui = {
            placeholder: playerElement.querySelector('.fb-video-placeholder-container'),
            embed: playerElement.querySelector('.fb-video-embed')
        };

        this.init();
    }

    init() {
        // Set up Intersection Observer for lazy loading
        // Load video when it's 200px away from viewport
        const options = {
            root: null,
            rootMargin: '200px', // Start loading before it's visible
            threshold: 0
        };

        this.observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && !this.isLoaded) {
                    this.loadVideo();
                    this.observer.disconnect(); // Stop observing once loaded
                }
            });
        }, options);

        this.observer.observe(this.player);
    }

    loadVideo() {
        if (this.isLoaded) return;

        this.isLoaded = true;

        // Pause radio if playing
        this.pauseRadio();

        // Load and embed video
        this.embedVideo();
    }

    embedVideo() {
        if (!this.ui.embed) return;

        // Load Facebook SDK
        loadFacebookSDK(() => {
            // Create Facebook video embed
            const embedHtml = `
                <div class="fb-video" 
                     data-href="${this.videoUrl}" 
                     data-width="auto" 
                     data-allowfullscreen="true"
                     data-autoplay="false"
                     data-show-captions="false">
                </div>
            `;

            this.ui.embed.innerHTML = embedHtml;
            this.ui.embed.style.display = 'block';

            // Hide placeholder
            if (this.ui.placeholder) {
                this.ui.placeholder.style.display = 'none';
            }

            // Parse XFBML to render the video
            if (window.FB && window.FB.XFBML) {
                FB.XFBML.parse(this.ui.embed);
            }
        });
    }

    pauseRadio() {
        // Pause radio player if it's playing
        try {
            if (window.radioPlayer && window.radioPlayer.isPlaying) {
                window.radioPlayer.pause();
                this.wasRadioPlaying = true;
            }
        } catch (e) {
            console.warn('Radio player interaction failed', e);
        }
    }

    resumeRadio() {
        // Resume radio if it was playing before
        try {
            if (this.wasRadioPlaying && window.radioPlayer) {
                window.radioPlayer.resume();
                this.wasRadioPlaying = false;
            }
        } catch (e) {
            console.warn('Radio player interaction failed', e);
        }
    }

    destroy() {
        // Cleanup
        if (this.observer) {
            this.observer.disconnect();
        }
    }
}

// Player registry to track instances
const playerInstances = new Map();

/**
 * Initialize all Facebook video players on page
 */
export function initFacebookVideoPlayers() {
    const players = document.querySelectorAll('.fb-video-player[data-lazy-load="true"]');

    players.forEach(player => {
        const playerId = player.id;

        // Skip if already initialized
        if (playerInstances.has(playerId)) {
            return;
        }

        // Create new instance and store it
        const instance = new FacebookVideoPlayer(player);
        playerInstances.set(playerId, instance);
    });
}

/**
 * Cleanup players that no longer exist in DOM
 */
export function cleanupFacebookVideoPlayers() {
    const currentPlayerIds = new Set(
        Array.from(document.querySelectorAll('.fb-video-player')).map(p => p.id)
    );

    // Remove instances that are no longer in DOM
    for (const [playerId, instance] of playerInstances.entries()) {
        if (!currentPlayerIds.has(playerId)) {
            instance.destroy();
            playerInstances.delete(playerId);
        }
    }
}
