<?php
/**
 * Sponsored Disclaimer Component
 * Displays "Sponsored Content" notice
 */

$post_id = $component_args['post_id'] ?? get_the_ID();
$position = $component_args['position'] ?? 'top'; // 'top' or 'bottom'

if (!is_sponsored_post($post_id)) {
    return; // Not sponsored
}

use_component('sponsored-disclaimer');
?>

<div class="sponsored-disclaimer sponsored-disclaimer--<?php echo esc_attr($position); ?>">
    <span class="dashicons dashicons-star-filled"></span>
    <span class="sponsored-disclaimer__text">Sponzorirani Sadržaj</span>
</div>
