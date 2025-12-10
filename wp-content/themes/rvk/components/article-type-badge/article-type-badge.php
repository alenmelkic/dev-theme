<?php
/**
 * Article Type Badge Component
 * Displays badge for article type (Video, Audio, Gallery)
 */

$post_id = $component_args['post_id'] ?? get_the_ID();
$variant = $component_args['variant'] ?? 'block'; // 'block' or 'inline'
$type = get_article_type($post_id);

if (!$type) {
    return; // Don't show for standard articles
}

use_component('article-type-badge');

$icon = get_article_type_icon($post_id);
?>

<div class="article-type-badge article-type-badge--<?php echo esc_attr($variant); ?> article-type-badge--<?php echo esc_attr($type->slug); ?>">
    <?php if ($icon) : ?>
        <span class="dashicons <?php echo esc_attr($icon); ?>"></span>
    <?php endif; ?>
    <span class="article-type-badge__label"><?php echo esc_html($type->name); ?></span>
</div>
