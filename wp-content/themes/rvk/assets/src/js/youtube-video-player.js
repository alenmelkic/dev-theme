/**
 * YouTube Video Player Controller
 * Lite-youtube approach: Click to load
 */

class YouTubeVideoPlayer {
    constructor(playerElement) {
        this.player = playerElement;

        // Sanitize videoId: only allow alphanumeric and hyphens
        const rawVideoId = playerElement.dataset.videoId || '';
        this.videoId = rawVideoId.replace(/[^a-zA-Z0-9_-]/g, '');

        if (this.videoId !== rawVideoId) {
            console.warn('YouTube Player: videoId contained invalid characters and was sanitized.');
        }

        this.isLoaded = false;
        this.wasRadioPlaying = false;

        // UI Elements
        this.ui = {
            facade: playerElement.querySelector('.yt-video-facade'),
            embed: playerElement.querySelector('.yt-video-embed')
        };

        this.init();
    }

    init() {
        if (!this.ui.facade) {
            console.warn('YouTube Player: No facade found for video', this.videoId);
            return;
        }



        // Click handler
        this.ui.facade.addEventListener('click', () => this.loadVideo());

        // Keyboard handler (Enter/Space)
        this.ui.facade.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.loadVideo();
            }
        });
    }

    loadVideo() {
        if (this.isLoaded) return;

        this.isLoaded = true;



        // Pause radio if playing
        this.pauseRadio();

        // Create and insert iframe
        this.embedVideo();
    }

    embedVideo() {
        if (!this.ui.embed) return;

        // Create iframe with autoplay
        const iframe = document.createElement('iframe');
        iframe.src = `https://www.youtube-nocookie.com/embed/${this.videoId}?autoplay=1&rel=0`;
        iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
        iframe.allowFullscreen = true;
        iframe.title = 'YouTube video player';

        // Show embed container and add iframe
        this.ui.embed.style.display = 'block';
        this.ui.embed.appendChild(iframe);

        // Hide facade
        if (this.ui.facade) {
            this.ui.facade.style.display = 'none';
        }
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
        // Cleanup event listeners
        if (this.ui.facade) {
            this.ui.facade.removeEventListener('click', this.loadVideo);
            this.ui.facade.removeEventListener('keydown', this.loadVideo);
        }
    }
}

// Player registry to track instances
const playerInstances = new Map();

/**
 * Initialize all YouTube video players on page
 */
export function initYouTubeVideoPlayers() {
    const players = document.querySelectorAll('.yt-video-player');



    players.forEach(player => {
        const playerId = player.id;

        // Skip if already initialized
        if (playerInstances.has(playerId)) {

            return;
        }



        // Create new instance and store it
        const instance = new YouTubeVideoPlayer(player);
        playerInstances.set(playerId, instance);
    });
}

/**
 * Cleanup players that no longer exist in DOM
 */
export function cleanupYouTubeVideoPlayers() {
    const currentPlayerIds = new Set(
        Array.from(document.querySelectorAll('.yt-video-player')).map(p => p.id)
    );

    // Remove instances that are no longer in DOM
    for (const [playerId, instance] of playerInstances.entries()) {
        if (!currentPlayerIds.has(playerId)) {
            instance.destroy();
            playerInstances.delete(playerId);
        }
    }
}
