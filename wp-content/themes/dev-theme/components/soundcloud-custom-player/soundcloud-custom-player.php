<?php
/**
 * Custom SoundCloud Player Component
 * 
 * @param array $args {
 *     @type string $url      SoundCloud track/playlist URL
 *     @type string $height   Player height (not used in custom player)
 *     @type bool   $visual   Visual mode (not used in custom player)
 * }
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$url = $args['url'] ?? '';
$player_id = 'sc-player-' . uniqid();
$iframe_id = 'sc-iframe-' . uniqid();

if ( empty( $url ) ) {
	return;
}

// Assets are now bundled in main.js and main.css for Swup compatibility
?>

<div class="sc-custom-player" id="<?php echo esc_attr( $player_id ); ?>" data-iframe-id="<?php echo esc_attr( $iframe_id ); ?>">
	<!-- Hidden SoundCloud iframe for Widget API -->
	<iframe 
		id="<?php echo esc_attr( $iframe_id ); ?>"
		class="sc-hidden-iframe"
		width="100%" 
		height="166" 
		scrolling="no" 
		frameborder="no" 
		allow="autoplay"
		src="https://w.soundcloud.com/player/?url=<?php echo urlencode( $url ); ?>&color=%23ff5500&auto_play=false&hide_related=true&show_comments=false&show_user=false&show_reposts=false&show_teaser=false&visual=false"
	></iframe>

	<!-- Custom Player UI -->
	<div class="sc-player-ui">
		<!-- Track Info -->
		<div class="sc-track-info">
			<div class="sc-artwork">
				<img src="" alt="Track artwork" class="sc-artwork-img">
				<div class="sc-artwork-placeholder">
					<svg width="40" height="40" viewBox="0 0 24 24" fill="currentColor">
						<path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z"/>
					</svg>
				</div>
			</div>
			<div class="sc-track-details">
				<div class="sc-track-title">Loading...</div>
				<div class="sc-track-artist">SoundCloud</div>
			</div>
		</div>

		<!-- Controls -->
		<div class="sc-controls">
			<button class="sc-btn sc-play-btn" aria-label="Play">
				<svg class="sc-icon-play" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
					<path d="M8 5v14l11-7z"/>
				</svg>
				<svg class="sc-icon-pause" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
					<path d="M6 4h4v16H6V4zm8 0h4v16h-4V4z"/>
				</svg>
			</button>

			<!-- Progress Bar -->
			<div class="sc-progress-container">
				<div class="sc-time sc-time-current">0:00</div>
				<div class="sc-progress-bar">
					<div class="sc-progress-track"></div>
					<div class="sc-progress-fill"></div>
					<div class="sc-progress-handle"></div>
				</div>
				<div class="sc-time sc-time-duration">0:00</div>
			</div>

			<!-- Volume Control -->
			<div class="sc-volume-control">
				<button class="sc-btn sc-volume-btn" aria-label="Mute">
					<svg class="sc-icon-volume" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
						<path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02z"/>
					</svg>
					<svg class="sc-icon-mute" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
						<path d="M16.5 12c0-1.77-1.02-3.29-2.5-4.03v2.21l2.45 2.45c.03-.2.05-.41.05-.63zm2.5 0c0 .94-.2 1.82-.54 2.64l1.51 1.51C20.63 14.91 21 13.5 21 12c0-4.28-2.99-7.86-7-8.77v2.06c2.89.86 5 3.54 5 6.71zM4.27 3L3 4.27 7.73 9H3v6h4l5 5v-6.73l4.25 4.25c-.67.52-1.42.93-2.25 1.18v2.06c1.38-.31 2.63-.95 3.69-1.81L19.73 21 21 19.73l-9-9L4.27 3zM12 4L9.91 6.09 12 8.18V4z"/>
					</svg>
				</button>
				<div class="sc-volume-slider">
					<div class="sc-volume-track"></div>
					<div class="sc-volume-fill"></div>
					<div class="sc-volume-handle"></div>
				</div>
			</div>
		</div>
	</div>

	<!-- Loading State -->
	<div class="sc-loading">
		<div class="sc-spinner"></div>
	</div>
</div>
