<?php
/**
 * Article Type Helper Functions
 */

/**
 * Get article type term object
 */
function get_article_type($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }

    $terms = get_the_terms($post_id, 'article-type');

    if (empty($terms) || is_wp_error($terms)) {
        return false;
    }

    return $terms[0];
}

/**
 * Get article type slug
 */
function get_article_type_slug($post_id = null) {
    $type = get_article_type($post_id);
    return $type ? $type->slug : 'standard';
}

/**
 * Get article type label
 */
function get_article_type_label($post_id = null) {
    $type = get_article_type($post_id);
    return $type ? $type->name : 'Standard';
}

/**
 * Get article type icon class
 */
function get_article_type_icon($post_id = null) {
    $slug = get_article_type_slug($post_id);

    $icons = [
        'video'    => 'dashicons-video-alt3',
        'audio'    => 'dashicons-format-audio',
        'galerija' => 'dashicons-format-gallery',
        'standard' => '',
    ];

    return $icons[$slug] ?? '';
}

/**
 * Check if post is sponsored
 */
function is_sponsored_post($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }

    return (bool) get_post_meta($post_id, '_is_sponsored', true);
}

/**
 * Detect article type from content blocks
 */
function rvk_detect_article_type_from_content($post_id) {
    $post = get_post($post_id);
    if (!$post) {
        return false;
    }

    $content = $post->post_content;

    // Check for video blocks
    if (has_block('custom/youtube-video', $post) || has_block('custom/facebook-video', $post)) {
        return 'video';
    }

    // Check for audio blocks
    if (has_block('custom/soundcloud', $post)) {
        return 'audio';
    }

    // Check for gallery block
    if (has_block('core/gallery', $post) || has_block('dev-theme/image-gallery', $post)) {
        return 'galerija';
    }

    return false;
}
