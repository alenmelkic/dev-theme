<?php
/**
 * Radio Player Component
 * Persistent bottom bar player
 */

$stream_url = 'https://rvk2021.radioca.st/stream?type=http&nocache=4';
$logo_id = get_option('dev_theme_logo');
$logo_url = $logo_id ? wp_get_attachment_url($logo_id) : '';
?>

<div id="radio-player-bar" class="radio-player-bar">
    <div class="container">
        <div class="player-content">
            <!-- Gramophone Logo & Play Button -->
            <div id="radio-play-btn" class="gramophone-logo">
                <div class="record-wrapper <?php echo $logo_url ? '' : 'no-logo'; ?>">
                    <?php if ( $logo_url ) : ?>
                        <img src="<?php echo esc_url( $logo_url ); ?>" alt="RVK Logo" class="record-logo">
                    <?php endif; ?>
                    
                    <!-- Play Overlay (Visible when stopped) -->
                    <div class="play-overlay">
                        <svg class="icon-play" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"></path></svg>
                    </div>
                </div>
            </div>

            <!-- Live Indicator & Equalizer -->
            <div class="live-status">
                <div class="equalizer">
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="bar"></span>
                </div>
                <span class="text">UŽIVO</span>
            </div>

            <!-- Track Info -->
            <div class="track-info">
                <span class="station-name">Radio Velika Kladuša</span>
                <span class="current-track">Uživo na 88.6 MHz</span>
            </div>

            <!-- Volume Control -->
            <div class="volume-control d-none d-md-flex">
                <svg class="volume-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"></path></svg>
                <input type="range" id="radio-volume" min="0" max="1" step="0.1" value="1" aria-label="Volume">
            </div>

            <!-- Audio Element (Hidden) -->
            <audio id="radio-audio" preload="none">
                <source src="<?php echo esc_url($stream_url); ?>" type="audio/mpeg">
            </audio>
        </div>
    </div>
</div>
