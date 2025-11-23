/**
 * Custom SoundCloud Player Controller
 * Controls the custom UI using SoundCloud Widget API
 */

class SoundCloudCustomPlayer {
    constructor(playerElement) {
        this.player = playerElement;
        this.iframeId = playerElement.dataset.iframeId;
        this.iframe = document.getElementById(this.iframeId);
        this.widget = null;
        this.isPlaying = false;
        this.duration = 0;
        this.currentTime = 0;
        this.volume = 100;
        this.isMuted = false;
        this.wasRadioPlaying = false;

        // UI Elements
        this.ui = {
            playBtn: playerElement.querySelector('.sc-play-btn'),
            progressBar: playerElement.querySelector('.sc-progress-bar'),
            progressFill: playerElement.querySelector('.sc-progress-fill'),
            progressHandle: playerElement.querySelector('.sc-progress-handle'),
            timeCurrent: playerElement.querySelector('.sc-time-current'),
            timeDuration: playerElement.querySelector('.sc-time-duration'),
            volumeBtn: playerElement.querySelector('.sc-volume-btn'),
            volumeSlider: playerElement.querySelector('.sc-volume-slider'),
            volumeFill: playerElement.querySelector('.sc-volume-fill'),
            volumeHandle: playerElement.querySelector('.sc-volume-handle'),
            trackTitle: playerElement.querySelector('.sc-track-title'),
            trackArtist: playerElement.querySelector('.sc-track-artist'),
            artworkImg: playerElement.querySelector('.sc-artwork-img'),
            loading: playerElement.querySelector('.sc-loading')
        };

        this.init();
    }

    init() {
        // Wait for SoundCloud Widget API to load
        if (window.SC && window.SC.Widget) {
            this.initWidget();
        } else {
            // Load Widget API if not already loaded
            if (!document.querySelector('script[src*="soundcloud.com/player/api.js"]')) {
                const script = document.createElement('script');
                script.src = 'https://w.soundcloud.com/player/api.js';
                script.onload = () => {
                    // Trigger a custom event or just wait for next init cycle
                    // But since we are inside an instance, we can just retry
                    this.initWidget();
                };
                script.onerror = () => {
                    this.hideLoading();
                };
                document.body.appendChild(script);
            } else {
                // Script already loading, wait for it
                const checkSC = setInterval(() => {
                    if (window.SC && window.SC.Widget) {
                        clearInterval(checkSC);
                        this.initWidget();
                    }
                }, 100);
            }
        }
    }

    initWidget() {
        try {
            this.widget = window.SC.Widget(this.iframe);

            // Bind widget events
            this.widget.bind(window.SC.Widget.Events.READY, () => {
                this.onReady();
            });

            this.widget.bind(window.SC.Widget.Events.PLAY, () => {
                this.onPlay();
            });

            this.widget.bind(window.SC.Widget.Events.PAUSE, () => {
                this.onPause();
            });

            this.widget.bind(window.SC.Widget.Events.FINISH, () => {
                this.onFinish();
            });

            this.widget.bind(window.SC.Widget.Events.PLAY_PROGRESS, (data) => {
                this.onPlayProgress(data);
            });

            // Bind UI events
            this.bindUIEvents();
        } catch (error) {
            console.error('SC Widget Error:', error);
            this.hideLoading();
        }
    }

    onReady() {
        this.hideLoading();

        // Get track info
        this.widget.getCurrentSound((sound) => {
            if (sound) {
                this.ui.trackTitle.textContent = sound.title || 'Unknown Track';
                this.ui.trackArtist.textContent = sound.user.username || 'Unknown Artist';

                // Set artwork
                if (sound.artwork_url) {
                    this.ui.artworkImg.src = sound.artwork_url.replace('-large', '-t500x500');
                    this.ui.artworkImg.style.display = 'block';
                    this.player.querySelector('.sc-artwork-placeholder').style.display = 'none';
                }
            }
        });

        // Get duration
        this.widget.getDuration((duration) => {
            this.duration = duration;
            this.ui.timeDuration.textContent = this.formatTime(duration);
        });
    }

    hideLoading() {
        if (this.ui.loading) {
            this.ui.loading.style.display = 'none';
        }
    }

    onPlay() {
        this.isPlaying = true;
        this.ui.playBtn.classList.add('playing');

        // Pause radio if playing
        try {
            if (window.radioPlayer && window.radioPlayer.isPlaying) {
                window.radioPlayer.pause();
                this.wasRadioPlaying = true;
            }
        } catch (e) {
            console.warn('Radio player interaction failed', e);
        }
    }

    onPause() {
        this.isPlaying = false;
        this.ui.playBtn.classList.remove('playing');

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

    onFinish() {
        this.isPlaying = false;
        this.ui.playBtn.classList.remove('playing');
        this.currentTime = 0;
        this.updateProgress(0);

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

    onPlayProgress(data) {
        this.currentTime = data.currentPosition;
        const progress = (data.currentPosition / this.duration) * 100;
        this.updateProgress(progress);
        this.ui.timeCurrent.textContent = this.formatTime(data.currentPosition);
    }

    bindUIEvents() {
        // Play/Pause button
        this.ui.playBtn.addEventListener('click', () => {
            this.widget.toggle();
        });

        // Progress bar seek
        this.ui.progressBar.addEventListener('click', (e) => {
            const rect = this.ui.progressBar.getBoundingClientRect();
            const percent = (e.clientX - rect.left) / rect.width;
            const seekTo = percent * this.duration;
            this.widget.seekTo(seekTo);
        });

        // Volume button
        this.ui.volumeBtn.addEventListener('click', () => {
            this.toggleMute();
        });

        // Volume slider
        this.ui.volumeSlider.addEventListener('click', (e) => {
            const rect = this.ui.volumeSlider.getBoundingClientRect();
            const percent = (e.clientX - rect.left) / rect.width;
            this.setVolume(percent * 100);
        });
    }

    updateProgress(percent) {
        this.ui.progressFill.style.width = `${percent}%`;
        this.ui.progressHandle.style.left = `${percent}%`;
    }

    toggleMute() {
        if (this.isMuted) {
            this.widget.setVolume(this.volume);
            this.ui.volumeBtn.classList.remove('muted');
            this.isMuted = false;
        } else {
            this.widget.setVolume(0);
            this.ui.volumeBtn.classList.add('muted');
            this.isMuted = true;
        }
    }

    setVolume(volume) {
        this.volume = Math.max(0, Math.min(100, volume));
        this.widget.setVolume(this.volume);
        this.ui.volumeFill.style.width = `${this.volume}%`;
        this.ui.volumeHandle.style.left = `${this.volume}%`;

        if (this.volume === 0) {
            this.ui.volumeBtn.classList.add('muted');
            this.isMuted = true;
        } else {
            this.ui.volumeBtn.classList.remove('muted');
            this.isMuted = false;
        }
    }

    formatTime(ms) {
        const seconds = Math.floor(ms / 1000);
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        return `${mins}:${secs.toString().padStart(2, '0')}`;
    }
}

// Player registry to track instances
const playerInstances = new Map();

// Initialize all players on page
export function initSoundCloudPlayers() {
    const players = document.querySelectorAll('.sc-custom-player');

    players.forEach(player => {
        const playerId = player.id;

        // Skip if already initialized
        if (playerInstances.has(playerId)) {
            return;
        }

        // Create new instance and store it
        const instance = new SoundCloudCustomPlayer(player);
        playerInstances.set(playerId, instance);
    });
}

// Cleanup players that no longer exist in DOM
export function cleanupSoundCloudPlayers() {
    const currentPlayerIds = new Set(
        Array.from(document.querySelectorAll('.sc-custom-player')).map(p => p.id)
    );

    // Remove instances that are no longer in DOM
    for (const [playerId, instance] of playerInstances.entries()) {
        if (!currentPlayerIds.has(playerId)) {
            playerInstances.delete(playerId);
        }
    }
}
