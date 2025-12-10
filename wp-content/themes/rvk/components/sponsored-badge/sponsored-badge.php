<?php
/**
 * Sponsored Badge Component
 * Small badge for sponsored posts in archives
 */

$post_id = $component_args['post_id'] ?? get_the_ID();

if (!is_sponsored_post($post_id)) {
    return;
}

use_component('sponsored-badge');
?>

<div class="sponsored-badge">
    <span class="dashicons dashicons-star-filled"></span>
    <span>Sponsored</span>
</div>
