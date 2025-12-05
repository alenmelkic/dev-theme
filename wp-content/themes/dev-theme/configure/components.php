<?php
/**
 * Component System
 * Handles registration and conditional loading of PHP components
 */

// Security: Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Track which components are used on the current page
global $used_components;
$used_components = array();

/**
 * Register component assets (CSS and JS)
 * 
 * @param string $component_name Component name (e.g., 'featured-image')
 * @param bool $has_css Whether component has CSS file
 * @param bool $has_js Whether component has JS file
 */
function register_component_assets($component_name, $has_css = true, $has_js = false) {
    $theme_dir = get_template_directory();
    $theme_uri = get_template_directory_uri();
    
    // Register CSS
    if ($has_css) {
        $css_file = $theme_dir . '/dist/css/components/' . $component_name . '.css';
        if (file_exists($css_file)) {
            wp_register_style(
                'component-' . $component_name,
                $theme_uri . '/dist/css/components/' . $component_name . '.css',
                array(),
                filemtime($css_file)
            );
        }
    }
    
    // Register JS
    if ($has_js) {
        $js_file = $theme_dir . '/dist/js/components/' . $component_name . '.js';
        if (file_exists($js_file)) {
            wp_register_script(
                'component-' . $component_name,
                $theme_uri . '/dist/js/components/' . $component_name . '.js',
                array(),
                filemtime($js_file),
                true // Load in footer
            );
        }
    }
}

/**
 * Mark a component as used and enqueue its assets
 * 
 * @param string $component_name Component name
 */
function use_component($component_name) {
    global $used_components;
    
    if (!in_array($component_name, $used_components)) {
        $used_components[] = $component_name;
        
        // Enqueue CSS in head
        if (wp_style_is('component-' . $component_name, 'registered')) {
            wp_enqueue_style('component-' . $component_name);
        }
        
        // Enqueue JS in footer
        if (wp_script_is('component-' . $component_name, 'registered')) {
            wp_enqueue_script('component-' . $component_name);
        }
    }
}

/**
 * Get and render a component
 *
 * @param string $component_name Component name
 * @param array $args Arguments to pass to component
 * @return void
 */
function get_component($component_name, $args = array()) {
    // Mark component as used
    use_component($component_name);

    // Make args available to component template
    // Components can access $args array directly instead of extract()
    // Example: $args['post_id'] instead of $post_id
    // For backward compatibility, we'll use a safer approach
    $component_args = $args;

    // Include component template
    $component_file = get_template_directory() . '/components/' . $component_name . '/' . $component_name . '.php';

    if (file_exists($component_file)) {
        include $component_file;
    } else {
        // Fallback: try without subdirectory
        $component_file = get_template_directory() . '/components/' . $component_name . '.php';
        if (file_exists($component_file)) {
            include $component_file;
        }
    }
}

/**
 * Register all components
 */
add_action('wp_enqueue_scripts', function() {
    // Register component assets (will be enqueued only when used)
    register_component_assets('featured-image', true, false);
    register_component_assets('post-title', true, false);
    register_component_assets('post-date', true, false);
    register_component_assets('post-terms', true, false);
    register_component_assets('author', true, false);
    register_component_assets('facebook-video-player', true, false);
    register_component_assets('youtube-video-player', true, false);
}, 5);
