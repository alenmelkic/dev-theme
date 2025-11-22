/**
 * Audio Manager
 * Handles Radio Player logic and state persistence
 */

const player = {
    audio: null,
    headerPlayBtn: null,
    playerBar: null,
    playBtn: null,
    volumeInput: null,
    isPlaying: false,

    init() {
        this.audio = document.getElementById('radio-audio');
        this.playBtn = document.getElementById('radio-play-btn');
        this.headerPlayBtn = document.getElementById('header-play-btn');
        this.volumeInput = document.getElementById('radio-volume');
        this.playerBar = document.getElementById('radio-player-bar');

        if (!this.audio || !this.playBtn) return;

        this.loadState();
        this.bindEvents();
    },

    reinitHeader() {
        this.headerPlayBtn = document.getElementById('header-play-btn');
        if (this.headerPlayBtn) {
            this.headerPlayBtn.addEventListener('click', () => this.togglePlay());
            this.updateUI(this.isPlaying);
        }
    },

    bindEvents() {
        // Play/Pause (Bottom Bar)
        this.playBtn.addEventListener('click', () => this.togglePlay());

        // Play/Pause (Header)
        if (this.headerPlayBtn) {
            this.headerPlayBtn.addEventListener('click', () => this.togglePlay());
        }

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
    },

    setVolume(value) {
        this.audio.volume = value;
        localStorage.setItem('radio_volume', value);
    },

    updateUI(isPlaying) {
        this.isPlaying = isPlaying;

        // Update Bottom Player UI
        const playIcon = this.playBtn.querySelector('.icon-play');
        const pauseIcon = this.playBtn.querySelector('.icon-pause');
        const indicator = document.querySelector('.live-indicator');

        if (isPlaying) {
            playIcon.style.display = 'none';
            pauseIcon.style.display = 'inline';
            indicator.style.opacity = '1';
        } else {
            playIcon.style.display = 'inline';
            pauseIcon.style.display = 'none';
            indicator.style.opacity = '0.5';
        }

        // Update Header Button UI
        if (this.headerPlayBtn) {
            const headerText = this.headerPlayBtn.querySelector('.text');
            const headerIcon = this.headerPlayBtn.querySelector('.icon-play');

            if (isPlaying) {
                headerText.textContent = 'Playing Live';
                headerIcon.textContent = '⏸'; // Simple text icon for now
                this.headerPlayBtn.classList.add('is-playing');
            } else {
                headerText.textContent = 'Listen Live';
                headerIcon.textContent = '▶';
                this.headerPlayBtn.classList.remove('is-playing');
            }
        }
    },

    loadState() {
        // Restore volume
        const savedVolume = localStorage.getItem('radio_volume');
        if (savedVolume !== null) {
            this.audio.volume = savedVolume;
            if (this.volumeInput) this.volumeInput.value = savedVolume;
        }
    }
};

export default function initAudioManager() {
    player.init();
    return player;
}
