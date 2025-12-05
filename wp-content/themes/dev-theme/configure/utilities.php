<?php
/**
 * Utility Functions
 */

// Security: Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

function get_static_dir() {
	return get_template_directory_uri() . '/static';
}

/**
 * Calculate estimated reading time
 * 
 * @param int $post_id Post ID (default: current post)
 * @return string Formatted reading time (e.g., "3 min read")
 */
function get_reading_time($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    
    $content = get_post_field('post_content', $post_id);
    $word_count = str_word_count(strip_tags($content));
    
    // Average reading speed: 200 words per minute
    $minutes = ceil($word_count / 200);
    
    // Return formatted string
    if ($minutes < 1) {
        return '< 1 min čitanja';
    } elseif ($minutes === 1) {
        return '1 min čitanja';
    } else {
        return $minutes . ' min čitanja';
    }
}
