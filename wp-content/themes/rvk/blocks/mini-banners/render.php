<?php
/**
 * Mini Banners Carousel - Server-side rendering
 *
 * @package Dev_Theme
 */

if (!defined('ABSPATH')) exit;

// Get attributes
$title = $attributes['title'] ?? 'Vaš brend, naša priča!';
$subtitle = $attributes['subtitle'] ?? '';

// Get all banners from marketing settings
$all_banners = rvk_get_small_banners();

if (empty($all_banners)) {
	return; // No banners, don't render
}

// Randomize order for splitting
shuffle($all_banners);

// Number of unique banners
$unique_count = count($all_banners);

/**
 * Helper to prepare banners for infinite scroll
 * Ensures just enough banners for seamless looping without bloating DOM
 */
$prepare_for_carousel = function($banners) {
	if (empty($banners)) return [];
	
	$count = count($banners);
	$result = $banners;
	
	// We need enough items to fill 3x the screen width (approx 12 banners visible max)
	// For 7 banners, 3x (21 items) is a healthy target.
	if ($count < 15) {
		$repeats = ceil(15 / $count);
		for ($i = 1; $i < $repeats; $i++) {
			$result = array_merge($result, $banners);
		}
	}
	
	// Ensure 2x duplication for the infinite scroll JS logic (Original + Clone)
	return array_merge($result, $result);
};

$carousel_banners = $prepare_for_carousel($all_banners);

// Generate unique ID
$carousel_id = 'mini-banners-' . wp_generate_password(6, false);

/**
 * Helper function to render banner item
 */
$render_banner_item = function($banner, $original_index) {
	$image_url = wp_get_attachment_image_url($banner['image'], 'medium') ?: wp_get_attachment_image_url($banner['image'], 'full');

	if (!$image_url) return '';

	$link = !empty($banner['link']) ? esc_url($banner['link']) : '';
	$alt_text = !empty($banner['alt_text']) ? $banner['alt_text'] : 'Marketing banner';

	ob_start();
	?>
	<div class="banner-item" role="listitem" data-id="<?php echo esc_attr($original_index); ?>">
		<?php if ($link): ?>
			<a href="<?php echo $link; ?>"
			   class="banner-link"
			   <?php if (strpos($link, home_url()) === false): ?>
				   target="_blank"
				   rel="noopener noreferrer"
			   <?php endif; ?>
			   aria-label="<?php echo esc_attr($alt_text); ?>">
		<?php endif; ?>

		<img src="<?php echo esc_url($image_url); ?>"
			 alt="<?php echo esc_attr($alt_text); ?>"
			 class="banner-image"
			 loading="lazy"
			 decoding="async">

		<?php if ($link): ?>
			</a>
		<?php endif; ?>
	</div>
	<?php
	return ob_get_clean();
};
?>

<div <?php echo get_block_wrapper_attributes([
	'class' => 'rvk-mini-banners-carousel',
	'id' => $carousel_id,
	'role' => 'region',
	'aria-label' => 'Marketing mini banners carousel'
]); ?>>

	<div class="container position-relative">
		<div class="px-3 px-lg-5 py-4 py-lg-5 bg-white rounded-4 shadow-sm">

			<!-- Title & Subtitle -->
			<div class="carousel-header text-center mb-5">
				<?php if (!empty($title)): ?>
					<h2 class="carousel-title position-relative mb-0"><?php echo esc_html($title); ?></h2>
				<?php endif; ?>

				<?php if (!empty($subtitle)): ?>
					<h3 class="carousel-subtitle"><?php echo esc_html($subtitle); ?></h3>
				<?php endif; ?>
			</div>

			<!-- Live Region for Screen Reader Announcements -->
			<div class="sr-only" role="status" aria-live="polite" aria-atomic="true"></div>

			<!-- Single Responsive Carousel -->
			<div class="carousel-container">
				<!-- Row 1 -->
				<div class="carousel-track" data-direction="ltr" role="list" aria-label="Marketing banners list 1">
					<div class="carousel-row">
						<?php 
						foreach ($carousel_banners as $banner): 
							$original_index = array_search($banner, $all_banners);
							echo $render_banner_item($banner, $original_index); 
						endforeach; 
						?>
					</div>
				</div>

				<!-- Row 2 -->
				<div class="carousel-track carousel-track-second" data-direction="rtl" role="list" aria-label="Marketing banners list 2">
					<div class="carousel-row">
						<?php 
						foreach ($carousel_banners as $banner): 
							$original_index = array_search($banner, $all_banners);
							echo $render_banner_item($banner, $original_index); 
						endforeach; 
						?>
					</div>
				</div>

				<div class="carousel-gradient-left" aria-hidden="true"></div>
				<div class="carousel-gradient-right" aria-hidden="true"></div>
			</div>

			<!-- Accessibility Controls -->
			<div class="carousel-controls d-flex flex-wrap justify-lg-content-between align-items-center mt-4">
				<div class="cta-a11y">
					<button type="button" 
							class="carousel-pause-btn shadow-sm" 
							aria-label="Pause carousel auto-scroll"
							data-state="playing">
						Pause
					</button>
				</div>
				
				<a href="/marketing" class="rvk-btn-primary shadow-lg">Dodajte svoj brend</a>
			</div>
		</div>
	</div>

	<!-- Skip Link Target -->
	<div id="after-<?php echo esc_attr($carousel_id); ?>"></div>
</div>
