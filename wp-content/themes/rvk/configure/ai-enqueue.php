<?php
/**
 * Enqueue AI Content Helper for Block Editor
 */

function enqueue_ai_content_helper() {
    // Only load in block editor
    if (!is_admin()) {
        return;
    }
    
    $screen = get_current_screen();
    if (!$screen || $screen->base !== 'post') {
        return;
    }
    
    // Check if AI is enabled
    if (!get_option('dev_theme_ai_enabled', true)) {
        return;
    }
    
    $js_file = get_template_directory() . '/dist/js/ai-content-helper.js';
    $css_file = get_template_directory() . '/dist/css/components/ai-content-helper.css';
    
    // Check if files exist
    if (!file_exists($js_file)) {
        error_log('AI Content Helper JS file not found: ' . $js_file);
        return;
    }
    
    // Enqueue the AI content helper script
    wp_enqueue_script(
        'ai-content-helper',
        get_template_directory_uri() . '/dist/js/ai-content-helper.js',
        array('wp-data', 'wp-editor', 'wp-element', 'wp-plugins', 'wp-edit-post', 'wp-api-fetch', 'wp-components'),
        filemtime($js_file),
        true
    );
    
    // Enqueue the AI content helper styles
    if (file_exists($css_file)) {
        wp_enqueue_style(
            'ai-content-helper',
            get_template_directory_uri() . '/dist/css/components/ai-content-helper.css',
            array(),
            filemtime($css_file)
        );
    }

    // Enqueue SEO Content Panel
    $seo_js_file = get_template_directory() . '/dist/js/seo-content-panel.js';

    if (file_exists($seo_js_file)) {
        wp_enqueue_script(
            'seo-content-panel',
            get_template_directory_uri() . '/dist/js/seo-content-panel.js',
            array('wp-data', 'wp-editor', 'wp-element', 'wp-plugins', 'wp-edit-post', 'wp-api-fetch', 'wp-components'),
            filemtime($seo_js_file),
            true
        );

        // Localize script with REST API data
        wp_localize_script('seo-content-panel', 'seoData', array(
            'apiUrl' => rest_url('dev-theme/v1/seo/'),
            'nonce' => wp_create_nonce('wp_rest'),
            'autoAnalysisEnabled' => get_option('rvk_seo_auto_analysis_enabled', true)
        ));
    }
}
add_action('admin_enqueue_scripts', 'enqueue_ai_content_helper');
