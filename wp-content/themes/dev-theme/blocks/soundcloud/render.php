<?php
/**
 * SoundCloud Block Render Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get attributes
$url = $attributes['url'] ?? '';
$height = $attributes['height'] ?? '166';
$visual = $attributes['visual'] ?? false;

if (empty($url)) {
    return;
}

// Use the custom player component
get_template_part('components/soundcloud-custom-player/soundcloud-custom-player', null, [
    'url' => $url,
    'height' => $height,
    'visual' => $visual
]);
