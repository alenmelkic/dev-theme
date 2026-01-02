<?php
/**
 * Article Type Overlay Component
 * Icon overlay on featured images in archive listings
 */

$post_id = $component_args['post_id'] ?? get_the_ID();
$type = get_article_type($post_id);

if (!$type) {
    return; // Don't show for standard articles
}

use_component('article-type-overlay');

$icon = get_article_type_icon($post_id);
?>

<div class="article-type-overlay article-type-overlay--<?php echo esc_attr($type->slug); ?>">
    <?php if ($icon) : ?>
        <span class="dashicons <?php echo esc_attr($icon); ?>"></span>
    <?php endif; ?>
</div>
