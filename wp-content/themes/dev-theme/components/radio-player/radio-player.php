<?php
/**
 * Radio Player Component
 * Persistent bottom bar player
 */

$stream_url = 'https://rvk2021.radioca.st/stream?type=http&nocache=4';
?>

<div id="radio-player-bar" class="radio-player-bar">
    <div class="container">
        <div class="player-content">
            <!-- Play/Pause Button -->
            <button id="radio-play-btn" class="play-btn" aria-label="Play Radio">
                <span class="icon-play">▶</span>
                <span class="icon-pause" style="display: none;">⏸</span>
            </button>

            <!-- Live Indicator -->
            <div class="live-indicator">
                <span class="dot"></span>
                <span class="text">UŽIVO</span>
            </div>

            <!-- Track Info -->
            <div class="track-info">
                <span class="station-name">Radio Velika Kladuša</span>
                <span class="current-track">Uživo...</span>
            </div>

            <!-- Volume Control -->
            <div class="volume-control">
                <input type="range" id="radio-volume" min="0" max="1" step="0.1" value="1" aria-label="Volume">
            </div>

            <!-- Audio Element (Hidden) -->
            <audio id="radio-audio" preload="none">
                <source src="<?php echo esc_url($stream_url); ?>" type="audio/mpeg">
            </audio>
        </div>
    </div>
</div>
