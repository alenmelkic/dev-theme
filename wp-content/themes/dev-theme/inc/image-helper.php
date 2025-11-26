<?php
/**
 * Image Helper Functions
 * 
 * Provides accessible responsive image delivery with WCAG 2.1 AA compliance:
 * - Generates <picture> elements with AVIF and fallback sources
 * - Enforces alt text requirement
 * - Supports decorative images
 * - Adds width/height to prevent layout shift
 * - Lazy loading with fetchpriority support
 * 
 * @package Dev_Theme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get responsive image with WCAG 2.1 AA accessibility
 * 
 * @param int $attachment_id Attachment ID
 * @param string $size Image size (thumbnail, medium, large, full, or custom)
 * @param array $args Additional arguments
 *   - string 'class' CSS classes
 *   - string 'sizes' Sizes attribute for responsive images
 *   - string 'alt' Alt text (required for accessibility)
 *   - string 'loading' Loading attribute (lazy, eager)
 *   - string 'fetchpriority' Fetch priority (high, low, auto)
 *   - string 'decoding' Decoding attribute (async, sync, auto)
 *   - string 'role' ARIA role (presentation for decorative images)
 * @return string HTML picture element
 */
function get_responsive_image($attachment_id, $size = 'full', $args = []) {
    // Validate attachment ID
    if (empty($attachment_id)) {
        return dev_theme_get_placeholder_image('default', $args);
    }
    
    // Get image metadata
    $image_meta = wp_get_attachment_metadata($attachment_id);
    $image_src = wp_get_attachment_image_src($attachment_id, $size);
    
    if (!$image_src) {
        return dev_theme_get_placeholder_image('default', $args);
    }
    
    // Parse arguments with defaults
    $defaults = [
        'class' => '',
        'sizes' => '(max-width: 768px) 100vw, 50vw',
        'alt' => '',
        'loading' => DEV_THEME_ENABLE_LAZY_LOAD ? 'lazy' : 'eager',
        'fetchpriority' => 'auto',
        'decoding' => 'async',
        'role' => '',
    ];
    $args = wp_parse_args($args, $defaults);
    
    // Get alt text - WCAG 2.1 AA requirement
    $alt_text = $args['alt'];
    if (empty($alt_text)) {
        $alt_text = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
    }
    
    // Validate alt text for non-decorative images
    if (empty($alt_text) && $args['role'] !== 'presentation') {
        // Fallback to attachment title or post title
        $alt_text = get_the_title($attachment_id);
        
        // Log warning for missing alt text
        if (WP_DEBUG) {
            error_log('Warning: Image ID ' . $attachment_id . ' missing alt text. Using title as fallback.');
        }
    }
    
    // Get image dimensions
    $width = $image_src[1];
    $height = $image_src[2];
    
    // Get upload directory
    $upload_dir = wp_upload_dir();
    $file_path = get_attached_file($attachment_id);
    $file_url = $image_src[0];
    
    // Build srcset for different formats
    $avif_srcset = dev_theme_build_srcset($attachment_id, $size, 'avif');
    $original_srcset = dev_theme_build_srcset($attachment_id, $size, 'original');
    
    // Build picture element
    $html = '<picture>';
    
    // AVIF source (modern browsers)
    if (!empty($avif_srcset) && DEV_THEME_ENABLE_AVIF) {
        $html .= sprintf(
            '<source type="image/avif" srcset="%s" sizes="%s">',
            esc_attr($avif_srcset),
            esc_attr($args['sizes'])
        );
    }
    
    // Build img attributes
    $img_attrs = [
        'src' => esc_url($file_url),
        'alt' => esc_attr($alt_text),
        'width' => esc_attr($width),
        'height' => esc_attr($height),
        'loading' => esc_attr($args['loading']),
        'decoding' => esc_attr($args['decoding']),
    ];
    
    // Add optional attributes
    if (!empty($args['class'])) {
        $img_attrs['class'] = esc_attr($args['class']);
    }
    
    if (!empty($original_srcset)) {
        $img_attrs['srcset'] = esc_attr($original_srcset);
        $img_attrs['sizes'] = esc_attr($args['sizes']);
    }
    
    if ($args['fetchpriority'] !== 'auto') {
        $img_attrs['fetchpriority'] = esc_attr($args['fetchpriority']);
    }
    
    if (!empty($args['role'])) {
        $img_attrs['role'] = esc_attr($args['role']);
    }
    
    // Build img tag
    $html .= '<img';
    foreach ($img_attrs as $attr => $value) {
        $html .= sprintf(' %s="%s"', $attr, $value);
    }
    $html .= '>';
    
    $html .= '</picture>';
    
    return $html;
}

/**
 * Build srcset for responsive images
 * 
 * @param int $attachment_id Attachment ID
 * @param string $size Base image size
 * @param string $format Format (avif or original)
 * @return string Srcset string
 */
function dev_theme_build_srcset($attachment_id, $size, $format = 'original') {
    $srcset = [];
    $file_path = get_attached_file($attachment_id);
    $upload_dir = wp_upload_dir();
    $base_url = dirname(wp_get_attachment_url($attachment_id));
    
    // Get all available sizes
    $sizes = ['thumb', 'mobile-small', 'mobile', 'tablet', 'desktop', 'full'];
    $image_meta = wp_get_attachment_metadata($attachment_id);
    
    foreach ($sizes as $size_name) {
        $image_src = wp_get_attachment_image_src($attachment_id, $size_name);
        
        if ($image_src) {
            $width = $image_src[1];
            $file_url = $image_src[0];
            
            // Check if AVIF version exists
            if ($format === 'avif') {
                $avif_url = preg_replace('/\.(jpe?g|png)$/i', '.avif', $file_url);
                $avif_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $avif_url);
                
                if (file_exists($avif_path)) {
                    $srcset[] = $avif_url . ' ' . $width . 'w';
                }
            } else {
                $srcset[] = $file_url . ' ' . $width . 'w';
            }
        }
    }
    
    return !empty($srcset) ? implode(', ', $srcset) : '';
}
