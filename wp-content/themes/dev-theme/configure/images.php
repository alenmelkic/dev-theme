<?php
/**
 * Image Optimization Configuration
 *
 * Registers custom image sizes and configures image optimization settings.
 * Only processes JPEG and PNG images (excludes SVG, GIF, etc.)
 * Converts to WebP format for better compression.
 *
 * @package Dev_Theme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register custom image sizes for responsive images
 */
function dev_theme_register_image_sizes() {
    // Desktop - 1440px max width (large desktop screens)
    add_image_size('desktop', 1440, 9999, false);
    
    // Tablet - 1024px max width (iPad and similar tablets)
    add_image_size('tablet', 1024, 9999, false);
    
    // Mobile - 640px max width (modern smartphones)
    add_image_size('mobile', 640, 9999, false);
    
    // Mobile Small - 480px max width (older/smaller phones)
    add_image_size('mobile-small', 480, 9999, false);
    
    // Thumb - 100x100px cropped (author images, small cards)
    add_image_size('thumb', 100, 100, true);
}
add_action('after_setup_theme', 'dev_theme_register_image_sizes');

/**
 * Image optimization quality settings
 */
define('DEV_THEME_WEBP_QUALITY', 85);      // WebP quality (85 is good balance)
define('DEV_THEME_JPEG_QUALITY', 80);      // JPEG fallback quality
define('DEV_THEME_PNG_QUALITY', 80);       // PNG fallback quality

/**
 * Supported image formats for processing
 * Only JPEG and PNG will be optimized
 * SVG, GIF, WebP, AVIF, ICO are excluded
 */
define('DEV_THEME_SUPPORTED_FORMATS', [
    'image/jpeg',
    'image/png'
]);

/**
 * Enable/disable features
 */
define('DEV_THEME_ENABLE_WEBP', true);           // Enable WebP generation
define('DEV_THEME_ENABLE_OPTIMIZATION', true);   // Enable image optimization
define('DEV_THEME_ENABLE_LAZY_LOAD', true);      // Enable lazy loading by default
define('DEV_THEME_DEBUG_LOGGING', false);        // Enable debug logging (disable in production)

/**
 * Add custom image sizes to the media library size dropdown
 */
function dev_theme_custom_image_sizes($sizes) {
    return array_merge($sizes, [
        'desktop' => __('Desktop (1440px)', 'dev-theme'),
        'tablet' => __('Tablet (1024px)', 'dev-theme'),
        'mobile' => __('Mobile (640px)', 'dev-theme'),
        'mobile-small' => __('Mobile Small (480px)', 'dev-theme'),
        'thumb' => __('Thumbnail (100x100)', 'dev-theme'),
    ]);
}
add_filter('image_size_names_choose', 'dev_theme_custom_image_sizes');

/**
 * Set default JPEG quality for WordPress
 */
function dev_theme_jpeg_quality($quality, $context) {
    return DEV_THEME_JPEG_QUALITY;
}
add_filter('jpeg_quality', 'dev_theme_jpeg_quality', 10, 2);
add_filter('wp_editor_set_quality', 'dev_theme_jpeg_quality', 10, 2);

/**
 * Force WordPress to use GD library instead of Imagick
 * This is necessary on XAMPP where Imagick can't read JPEG files
 */
function dev_theme_force_gd_editor($editors) {
    // Remove Imagick, keep only GD
    return array('WP_Image_Editor_GD');
}
add_filter('wp_image_editors', 'dev_theme_force_gd_editor');
