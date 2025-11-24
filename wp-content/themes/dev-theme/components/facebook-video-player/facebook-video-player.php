<?php
/**
 * Facebook Video Player Component
 * 
 * Lazy loading with Intersection Observer for optimal performance.
 * 
 * @param array $args {
 *     @type string $video_url  Facebook video URL
 *     @type string $video_id   Extracted video ID
 *     @type string $alignment  Block alignment (left, center, right)
 *     @type string $caption    Optional caption
 * }
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$video_url = $args['video_url'] ?? '';
$video_id = $args['video_id'] ?? '';
$thumbnail_url = $args['thumbnail_url'] ?? '';
$alignment = $args['alignment'] ?? 'center';
$caption = $args['caption'] ?? '';
$player_id = 'fb-video-' . uniqid();

if ( empty( $video_url ) || empty( $video_id ) ) {
	return;
}

// Alignment class
$align_class = 'align-' . esc_attr( $alignment );
?>

<div class="fb-video-player <?php echo esc_attr( $align_class ); ?>" 
     id="<?php echo esc_attr( $player_id ); ?>" 
     data-video-url="<?php echo esc_attr( $video_url ); ?>"
     data-video-id="<?php echo esc_attr( $video_id ); ?>"
     data-lazy-load="true">
	
	<!-- Lazy loading placeholder -->
	<div class="fb-video-placeholder-container">
		<div class="fb-video-loading-state">
			<div class="fb-loading-spinner"></div>
			<div class="fb-loading-text">Loading video...</div>
		</div>
	</div>
	
	<!-- Video container (populated by JavaScript when visible) -->
	<div class="fb-video-embed"></div>
	
	<?php if ( ! empty( $caption ) ) : ?>
		<div class="fb-video-caption">
			<?php echo esc_html( $caption ); ?>
		</div>
	<?php endif; ?>
</div>
