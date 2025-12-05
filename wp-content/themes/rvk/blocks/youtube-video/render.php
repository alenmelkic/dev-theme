<?php
/**
 * YouTube Video Block - Server-side rendering
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$video_url = $attributes['videoUrl'] ?? '';
$video_id = $attributes['videoId'] ?? '';
$alignment = $attributes['alignment'] ?? 'center';
$caption = $attributes['caption'] ?? '';

if ( empty( $video_id ) ) {
	return;
}

// Enqueue component styles and scripts
get_component(
	'youtube-video-player',
	array(
		'video_id'  => $video_id,
		'alignment' => $alignment,
		'caption'   => $caption,
	)
);
