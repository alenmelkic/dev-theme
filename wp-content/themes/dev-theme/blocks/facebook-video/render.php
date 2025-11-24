<?php
/**
 * Facebook Video Block Render Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get attributes
$video_url = $attributes['videoUrl'] ?? '';
$video_id = $attributes['videoId'] ?? '';
$thumbnail_url = $attributes['thumbnailUrl'] ?? '';
$alignment = $attributes['alignment'] ?? 'center';
$caption = $attributes['caption'] ?? '';

if (empty($video_url) || empty($video_id)) {
    return;
}

// Use the custom player component (this will auto-enqueue CSS)
get_component('facebook-video-player', [
    'video_url' => $video_url,
    'video_id' => $video_id,
    'thumbnail_url' => $thumbnail_url,
    'alignment' => $alignment,
    'caption' => $caption
]);
