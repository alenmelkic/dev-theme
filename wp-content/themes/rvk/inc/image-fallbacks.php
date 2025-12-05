<?php
/**
 * Image Fallbacks
 * 
 * Handles missing images and placeholders with accessibility
 * 
 * @package Dev_Theme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get placeholder image
 * 
 * @param string $type Placeholder type (featured, avatar, default)
 * @param array $args Additional arguments
 * @return string HTML img element
 */
function dev_theme_get_placeholder_image($type = 'default', $args = []) {
    $defaults = [
        'class' => '',
        'alt' => '',
        'role' => 'presentation',
        'loading' => 'lazy',
    ];
    $args = wp_parse_args($args, $defaults);
    
    // Get placeholder path
    $placeholder_path = get_template_directory_uri() . '/assets/images/placeholders/';
    
    $placeholders = [
        'featured' => $placeholder_path . 'featured.svg',
        'avatar' => $placeholder_path . 'avatar.svg',
        'default' => $placeholder_path . 'default.svg',
    ];
    
    $src = isset($placeholders[$type]) ? $placeholders[$type] : $placeholders['default'];
    
    // Set appropriate alt text for placeholders
    if (empty($args['alt'])) {
        $alt_texts = [
            'featured' => __('Featured image placeholder', 'dev-theme'),
            'avatar' => __('Avatar placeholder', 'dev-theme'),
            'default' => __('Image placeholder', 'dev-theme'),
        ];
        $args['alt'] = isset($alt_texts[$type]) ? $alt_texts[$type] : $alt_texts['default'];
    }
    
    // Build img tag
    $html = sprintf(
        '<img src="%s" alt="%s" class="%s" role="%s" loading="%s">',
        esc_url($src),
        esc_attr($args['alt']),
        esc_attr($args['class']),
        esc_attr($args['role']),
        esc_attr($args['loading'])
    );
    
    return $html;
}

/**
 * Handle missing image
 * 
 * @param int $attachment_id Attachment ID
 * @param string $context Context (featured, avatar, general)
 * @return string HTML for fallback image
 */
function dev_theme_handle_missing_image($attachment_id, $context = 'default') {
    // Log missing image
    if (WP_DEBUG) {
        error_log('Missing image: Attachment ID ' . $attachment_id . ' not found. Context: ' . $context);
    }
    
    // Return appropriate placeholder
    return dev_theme_get_placeholder_image($context);
}
