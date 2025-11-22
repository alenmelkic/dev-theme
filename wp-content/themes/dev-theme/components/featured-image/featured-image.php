<?php
/**
 * Featured Image Component
 * 
 * @param int $post_id Post ID (default: current post)
 * @param string $size Image size (default: 'large')
 * @param string $variant CSS variant class (default: 'post')
 * @param string $loading Loading attribute (default: 'lazy')
 */

$post_id = $post_id ?? get_the_ID();
$size = $size ?? 'large';
$variant = $variant ?? 'post';
$loading = $loading ?? 'lazy';

if (!has_post_thumbnail($post_id)) {
    return;
}

$image_id = get_post_thumbnail_id($post_id);
$image_url = wp_get_attachment_image_url($image_id, $size);
$image_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true) ?: get_the_title($post_id);
?>

<div class="<?php echo esc_attr($variant); ?>-featured-image">
    <img 
        src="<?php echo esc_url($image_url); ?>" 
        alt="<?php echo esc_attr($image_alt); ?>"
        loading="<?php echo esc_attr($loading); ?>"
    >
</div>
