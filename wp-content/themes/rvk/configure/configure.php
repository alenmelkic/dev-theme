<?php
/**
 * Theme Configuration
 */

// Security: Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// MENUS
function _custom_theme_register_menu() {
    register_nav_menus(
        array(
            'menu-desktop' => __( 'Desktop Menu' ),
            'menu-mobile'  => __( 'Mobile Menu' ),
        )
    );
}
add_action( 'init', '_custom_theme_register_menu' );

function custom_setup() {
    // Images
    add_theme_support( 'post-thumbnails' );

    // Title tags
    add_theme_support('title-tag');

    // Languages
    load_theme_textdomain('dev-theme', get_template_directory() . '/languages');

    // HTML 5 - Example : deletes type="*" in scripts and style tags
    add_theme_support( 'html5', [ 'script', 'style' ] );

    // Remove SVG and global styles
    remove_action('wp_enqueue_scripts', 'wp_enqueue_global_styles');
    remove_action('wp_body_open', 'wp_global_styles_render_svg_filters' );

    // Remove wp_footer actions which add's global inline styles
    remove_action('wp_footer', 'wp_enqueue_global_styles', 1);

    // Remove render_block filters which adds unnecessary stuff
    remove_filter('render_block', 'wp_render_duotone_support');
    remove_filter('render_block', 'wp_restore_group_inner_container');
    remove_filter('render_block', 'wp_render_layout_support_flag');

    // Remove useless WP image sizes
    remove_image_size( '1536x1536' );
    remove_image_size( '2048x2048' );

    // Custom image sizes
    // add_image_size( '424x424', 424, 424, true );
    // add_image_size( '1920', 1920, 9999 );
}
add_action('after_setup_theme', 'custom_setup');

// remove default image sizes to avoid overcharging server - comment line if you need size
function remove_default_image_sizes( $sizes) {
    unset( $sizes['large']);
    unset( $sizes['medium']);
    unset( $sizes['medium_large']);
    return $sizes;
}
add_filter('intermediate_image_sizes_advanced', 'remove_default_image_sizes');

// disabling big image sizes scaled
add_filter( 'big_image_size_threshold', '__return_false' );

// Giving credits
function remove_footer_admin () {
    echo 'Tailored by <a href="#" target="_blank">Alen Melkic</a>';
}
add_filter('admin_footer_text', 'remove_footer_admin');

// Move Yoast to bottom
function yoasttobottom() {
    return 'low';
}
add_filter( 'wpseo_metabox_prio', 'yoasttobottom');

// Remove WP Emoji
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'admin_print_styles', 'print_emoji_styles' );
remove_action('wp_head', 'wp_generator');

// delete wp-embed.js from footer
function my_deregister_scripts() {
    wp_deregister_script( 'wp-embed' );
}
add_action( 'wp_footer', 'my_deregister_scripts' );

// delete jquery migrate
function dequeue_jquery_migrate( &$scripts){
    if(!is_admin()){
        $scripts->remove( 'jquery');
        $scripts->add('jquery', 'https://code.jquery.com/jquery-3.6.1.min.js', null, null, true );
    }
}
add_filter( 'wp_default_scripts', 'dequeue_jquery_migrate' );

// add SVG to allowed file uploads with basic security scanning
function add_file_types_to_uploads($mime_types) {
    $mime_types['svg'] = 'image/svg+xml';
    return $mime_types;
}
add_action('upload_mimes', 'add_file_types_to_uploads', 1, 1);

// Sanitize SVG uploads to prevent XSS
function rvk_sanitize_svg_upload($file) {
    if ($file['type'] === 'image/svg+xml' && file_exists($file['tmp_name'])) {
        $svg_content = file_get_contents($file['tmp_name']);
        
        // Simple check for scripts and common XSS patterns in SVG
        $forbidden_patterns = [
            '/<script/i',
            '/on[a-z]+\s*=/i', // Event handlers like onmouseover, onload
            '/<iframe/i',
            '/<object/i',
            '/<embed/i',
            '/javascript:/i'
        ];

        foreach ($forbidden_patterns as $pattern) {
            if (preg_match($pattern, $svg_content)) {
                $file['error'] = 'Security check failed: Malicious content detected in SVG file.';
                break;
            }
        }
    }
    return $file;
}
add_filter('wp_handle_upload_prefilter', 'rvk_sanitize_svg_upload');

//disable update emails
add_filter( 'auto_plugin_update_send_email', '__return_false' );
add_filter( 'auto_theme_update_send_email', '__return_false' );

// Disable specific blocks
function custom_allowed_block_types( $allowed_blocks, $editor_context ) {
    // List of blocks - set to false to disable, true to enable
    $disabled_blocks = array(
        // === EMBED BLOCKS ===
        'core/embed' => false,
        
        // === WIDGET BLOCKS ===
        'core/legacy-widget' => false,
        'core/widget-group' => false,
        'core/archives' => false,
        'core/calendar' => false,
        'core/categories' => false,
        'core/html' => true,  // Custom HTML
        'core/latest-comments' => false,
        'core/latest-posts' => false,
        'core/page-list' => false,
        'core/page-list-item' => false,
        'core/rss' => false,
        'core/search' => false,
        'core/shortcode' => false,
        'core/social-link' => false,
        'core/social-links' => false,
        'core/tag-cloud' => false,
        
        // === DESIGN BLOCKS ===
        'core/accordion' => false,
        'core/accordion-item' => false,
        'core/accordion-heading' => false,
        'core/accordion-panel' => false,
        'core/button' => false,
        'core/buttons' => false,
        'core/column' => true,
        'core/columns' => true,
        'core/comment-template' => false,
        'core/group' => false,
        'core/home-link' => false,
        'core/more' => false,
        'core/navigation-link' => false,
        'core/navigation-submenu' => false,
        'core/nextpage' => false,
        'core/separator' => true,
        'core/spacer' => true,
        'core/text-columns' => false,
        
        // === THEME BLOCKS ===
        'core/avatar' => false,
        'core/comment-author-name' => false,
        'core/comment-content' => false,
        'core/comment-date' => false,
        'core/comment-edit-link' => false,
        'core/comment-reply-link' => false,
        'core/comments' => false,
        'core/comments-pagination' => false,
        'core/comments-pagination-next' => false,
        'core/comments-pagination-numbers' => false,
        'core/comments-pagination-previous' => false,
        'core/comments-title' => false,
        'core/loginout' => false,
        'core/navigation' => false,
        'core/pattern' => false,
        'core/post-author' => false,
        'core/post-author-biography' => false,
        'core/post-author-name' => false,
        'core/post-comments' => false,
        'core/post-comments-count' => false,
        'core/post-comments-form' => false,
        'core/post-comments-link' => false,
        'core/post-content' => false,
        'core/post-date' => false,
        'core/post-excerpt' => false,
        'core/post-featured-image' => false,
        'core/post-navigation-link' => false,
        'core/post-template' => false,
        'core/post-terms' => false,
        'core/post-time-to-read' => false,
        'core/post-title' => false,
        'core/query' => false,
        'core/query-no-results' => false,
        'core/query-pagination' => false,
        'core/query-pagination-next' => false,
        'core/query-pagination-numbers' => false,
        'core/query-pagination-previous' => false,
        'core/query-title' => false,
        'core/query-total' => false,
        'core/read-more' => false,
        'core/site-logo' => false,
        'core/site-tagline' => false,
        'core/site-title' => false,
        'core/template-part' => false,
        'core/term-count' => false,
        'core/term-description' => false,
        'core/term-name' => false,
        'core/term-template' => false,
        'core/terms-query' => false,
        
        // === MEDIA BLOCKS ===
        'core/audio' => false,
        'core/cover' => false,
        'core/file' => false,
        'core/gallery' => false,
        'core/image' => true,
        'core/media-text' => false,
        'core/video' => false,
        
        // Custom Media Blocks - RVK
        'dev-theme/soundcloud' => true,
        'dev-theme/facebook-video' => true,
        'dev-theme/youtube-video' => true,
        'dev-theme/category-articles' => true,
        'dev-theme/image-gallery' => true,
        'dev-theme/mini-banners' => true,
        
        // === TEXT BLOCKS ===
        'core/code' => false,
        'core/details' => true,
        'core/footnotes' => false,
        'core/freeform' => false,  // Classic block
        'core/heading' => true,
        'core/list' => false,
        'core/list-item' => false,
        'core/math' => false,
        'core/missing' => false,
        'core/paragraph' => true,
        'core/preformatted' => false,
        'core/pullquote' => true,
        'core/quote' => true,
        'core/table' => true,
        'core/verse' => false,
        
        // === REUSABLE BLOCKS ===
        'core/block' => true,  // Pattern/Reusable blocks
    );
    
    // Get all registered blocks
    $registered_blocks = WP_Block_Type_Registry::get_instance()->get_all_registered();
    $allowed = array();
    
    // Loop through all registered blocks
    foreach ( $registered_blocks as $block_name => $block_type ) {
        // Only allow blocks that are explicitly set to true
        if ( isset( $disabled_blocks[ $block_name ] ) && $disabled_blocks[ $block_name ] === true ) {
            $allowed[] = $block_name;
        }
    }
    
    return $allowed;
}
add_filter( 'allowed_block_types_all', 'custom_allowed_block_types', 10, 2 );
