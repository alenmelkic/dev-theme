/**
 * Audio Manager
 * Handles Radio Player logic and state persistence
 */

const player = {
    audio: null,
    playerBar: null,
    volumeInput: null,
    isPlaying: false,
    wasPlayingBeforeInterruption: false,
    hideTimer: null,
    eventsInitialized: false,

    init() {
        // Get persistent elements (outside Swup container)
        this.audio = document.getElementById('radio-audio');
        this.playerBar = document.getElementById('radio-player-bar');
        this.volumeInput = document.getElementById('radio-volume');

        if (!this.audio) return;

        this.loadState();
        this.bindEvents();
        // this.restoreSessionState(); // Disabled to prevent browser autoplay blocking
    },

    reinitHeader() {
        // Just update the UI state for the new button
        this.updateUI(this.isPlaying);
    },

    bindEvents() {
        // Prevent duplicate event binding
        if (this.eventsInitialized) {
            return;
        }
        this.eventsInitialized = true;

        // Play/Pause (Bottom Bar) - Delegated Event
        document.body.addEventListener('click', (e) => {
            const btn = e.target.closest('#radio-play-btn');
            if (btn) {
                this.togglePlay();
            }
        });

        // Play/Pause (Header) - Delegated Event
        document.body.addEventListener('click', (e) => {
            const btn = e.target.closest('#header-play-btn');
            if (btn) {
                this.togglePlay();
            }
        });

        // Volume
        this.volumeInput?.addEventListener('input', (e) => {
            this.setVolume(e.target.value);
        });

        // Audio Events
        this.audio.addEventListener('play', () => this.updateUI(true));
        this.audio.addEventListener('pause', () => this.updateUI(false));
        this.audio.addEventListener('error', (e) => console.error('Audio Error:', e));
    },

    togglePlay() {
        // Show player if hidden
        if (this.playerBar && !this.playerBar.classList.contains('is-visible')) {
            this.playerBar.classList.add('is-visible');
            document.body.classList.add('has-player');
        }

        if (this.audio.paused) {
            this.audio.play().catch(e => console.error('Play failed:', e));
        } else {
            this.audio.pause();
        }

        // Clear hide timer when toggling
        this.clearHideTimer();
    },

    setVolume(value) {
        this.audio.volume = value;
        localStorage.setItem('radio_volume', value);
    },

    updateUI(isPlaying) {
        this.isPlaying = isPlaying;

        // Update Bottom Player UI - Dynamic Lookup
        const playBtn = document.getElementById('radio-play-btn');
        if (playBtn) {
            const playIcon = playBtn.querySelector('.icon-play');
            const pauseIcon = playBtn.querySelector('.icon-pause');
            const indicator = document.querySelector('.live-indicator');

            if (isPlaying) {
                playIcon.style.display = 'none';
                pauseIcon.style.display = 'inline';
                if (indicator) indicator.style.opacity = '1';
            } else {
                playIcon.style.display = 'inline';
                pauseIcon.style.display = 'none';
                if (indicator) indicator.style.opacity = '0.5';
            }
        }

        // Update Header Button UI - Dynamic Lookup
        const headerBtn = document.getElementById('header-play-btn');
        if (headerBtn) {
            const headerText = headerBtn.querySelector('.text');
            const headerIcon = headerBtn.querySelector('.icon-play');

            if (isPlaying) {
                if (headerText) headerText.textContent = 'Playing Live';
                if (headerIcon) headerIcon.textContent = '⏸';
                headerBtn.classList.add('is-playing');
            } else {
                if (headerText) headerText.textContent = 'Listen Live';
                if (headerIcon) headerIcon.textContent = '▶';
                headerBtn.classList.remove('is-playing');
            }
        }

        // Save session state
        this.saveSessionState();

        // Handle auto-hide
        if (!isPlaying) {
            this.startHideTimer();
        } else {
            this.clearHideTimer();
        }
    },

    loadState() {
        // Restore volume
        const savedVolume = localStorage.getItem('radio_volume');
        if (savedVolume !== null) {
            this.audio.volume = savedVolume;
            if (this.volumeInput) this.volumeInput.value = savedVolume;
        }
    },

    saveSessionState() {
        // Use sessionStorage (clears on tab/browser close)
        sessionStorage.setItem('radio_isPlaying', this.isPlaying);
    },

    restoreSessionState() {
        // Restore playing state from session
        const wasPlaying = sessionStorage.getItem('radio_isPlaying') === 'true';
        if (wasPlaying) {
            // Show player bar immediately
            if (this.playerBar) {
                this.playerBar.classList.add('is-visible');
                document.body.classList.add('has-player');
            }

            // Try to auto-resume (may be blocked by browser)
            setTimeout(() => {
                this.audio.play()
                    .catch(() => {
                        // Update UI to show paused state since autoplay was blocked
                        this.updateUI(false);
                    });
            }, 500); // Small delay to ensure everything is loaded
        }
    },

    startHideTimer() {
        this.clearHideTimer();
        this.hideTimer = setTimeout(() => {
            if (!this.isPlaying && this.playerBar) {
                this.playerBar.classList.remove('is-visible');
                document.body.classList.remove('has-player');
            }
        }, 8000); // 8 seconds
    },

    clearHideTimer() {
        if (this.hideTimer) {
            clearTimeout(this.hideTimer);
            this.hideTimer = null;
        }
    },

    pause() {
        if (this.isPlaying) {
            this.togglePlay();
        }
    },

    resume() {
        if (!this.isPlaying) {
            this.togglePlay();
        }
    }
};

// Expose player globally for other scripts (like SoundCloud player)
window.radioPlayer = player;

export default function initAudioManager() {
    player.init();
    return player;
}
