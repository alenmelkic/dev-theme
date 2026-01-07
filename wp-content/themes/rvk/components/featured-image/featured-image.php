<?php
/**
 * Featured Image Component
 * 
 * Renders an accessible responsive featured image with:
 * - AVIF support with JPEG/PNG fallback
 * - Responsive sizes (desktop, tablet, mobile)
 * - WCAG 2.1 AA compliance (alt text enforcement)
 * - LCP optimization (eager loading, high priority)
 * 
 * @param int $post_id Post ID (default: current post)
 * @param string $size Image size (default: 'large')
 * @param string $variant CSS variant class (default: 'post')
 * @param string $loading Loading attribute (default: 'lazy')
 */

// Get args from component_args (passed by get_component)
$post_id = $component_args['post_id'] ?? get_the_ID();
$size = $component_args['size'] ?? 'large';
$variant = $component_args['variant'] ?? 'post';
$loading = $component_args['loading'] ?? 'lazy';
$class = $component_args['class'] ?? '';

// Get attachment ID
$image_id = get_post_thumbnail_id($post_id);

// Handle missing image
if (!$image_id) {
    // Optional: Show placeholder if desired, or just return
    // echo dev_theme_get_placeholder_image('featured', ['class' => $variant . '-featured-image']);
    return;
}

// Get accessible alt text (fallback to post title)
$image_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
if (empty($image_alt)) {
    $image_alt = get_the_title($post_id);
}

// Determine fetch priority based on loading strategy
$fetchpriority = ($loading === 'eager') ? 'high' : 'auto';

// Define responsive sizes attribute based on layout
// Default: 100vw on mobile, 50vw on tablet/desktop
$sizes_attr = '(max-width: 768px) 100vw, 50vw';

if ($variant === 'hero' || $variant === 'full') {
    $sizes_attr = '100vw';
}
?>

<div class="<?php echo esc_attr($variant); ?>-featured-image <?php echo esc_attr($class); ?>">
    <?php 
    echo get_responsive_image(
        $image_id, 
        $size, 
        [
            'class' => 'featured-image',
            'sizes' => $sizes_attr,
            'alt' => $image_alt,
            'loading' => $loading,
            'fetchpriority' => $fetchpriority,
            'decoding' => 'async'
        ]
    ); 
    ?>
</div>
