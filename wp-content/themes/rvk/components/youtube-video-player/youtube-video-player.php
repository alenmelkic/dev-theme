<?php
/**
 * YouTube Video Player Component
 * 
 * Lite-youtube approach: Shows thumbnail, loads player on click
 * Ultra-lightweight and fast performance
 * 
 * @param array $args {
 *     @type string $video_id   YouTube video ID
 *     @type string $alignment  Block alignment (left, center, right)
 *     @type string $caption    Optional caption
 * }
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$video_id = $args['video_id'] ?? '';
$alignment = $args['alignment'] ?? 'center';
$caption = $args['caption'] ?? '';
$player_id = 'yt-video-' . uniqid();

if ( empty( $video_id ) ) {
	return;
}

// Alignment class
$align_class = 'align-' . esc_attr( $alignment );

// YouTube thumbnail URL (high quality)
$thumbnail_url = "https://i.ytimg.com/vi/{$video_id}/hqdefault.jpg";
?>

<div class="yt-video-player <?php echo esc_attr( $align_class ); ?>" 
     id="<?php echo esc_attr( $player_id ); ?>" 
     data-video-id="<?php echo esc_attr( $video_id ); ?>">
	
	<!-- Lite YouTube: Thumbnail with play button -->
	<div class="yt-video-facade" role="button" tabindex="0" aria-label="Play video">
		<img 
			src="<?php echo esc_url( $thumbnail_url ); ?>" 
			alt="YouTube video thumbnail"
			class="yt-video-thumbnail"
			loading="lazy"
		/>
		
		<!-- Play button overlay -->
		<button class="yt-video-play-btn" aria-label="Play">
			<svg class="yt-play-icon" viewBox="0 0 68 48">
				<path d="M66.52,7.74c-0.78-2.93-2.49-5.41-5.42-6.19C55.79,.13,34,0,34,0S12.21,.13,6.9,1.55 C3.97,2.33,2.27,4.81,1.48,7.74C0.06,13.05,0,24,0,24s0.06,10.95,1.48,16.26c0.78,2.93,2.49,5.41,5.42,6.19 C12.21,47.87,34,48,34,48s21.79-0.13,27.1-1.55c2.93-0.78,4.64-3.26,5.42-6.19C67.94,34.95,68,24,68,24S67.94,13.05,66.52,7.74z" fill="#f00"></path>
				<path d="M 45,24 27,14 27,34" fill="#fff"></path>
			</svg>
		</button>
	</div>
	
	<!-- Video container (populated by JavaScript on click) -->
	<div class="yt-video-embed"></div>
	
	<?php if ( ! empty( $caption ) ) : ?>
		<div class="yt-video-caption">
			<?php echo esc_html( $caption ); ?>
		</div>
	<?php endif; ?>
</div>
