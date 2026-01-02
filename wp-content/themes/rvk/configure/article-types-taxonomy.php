<?php
/**
 * Register Article Types taxonomy
 */

// Register taxonomy
function rvk_register_article_types() {
    register_taxonomy('article-type', ['post'], [
        'hierarchical' => false,
        'public' => true,
        'show_ui' => false, // Hide default UI
        'show_in_rest' => true,
        'show_admin_column' => true,
        'show_in_nav_menus' => false,
        'show_tagcloud' => false,
        'meta_box_cb' => false, // Disable default meta box
        'labels' => [
            'name' => 'Article Types',
            'singular_name' => 'Article Type',
            'menu_name' => 'Article Types',
            'all_items' => 'All Article Types',
            'edit_item' => 'Edit Article Type',
            'view_item' => 'View Article Type',
            'update_item' => 'Update Article Type',
            'add_new_item' => 'Add New Article Type',
            'new_item_name' => 'New Article Type Name',
            'search_items' => 'Search Article Types',
            'not_found' => 'No article types found',
        ],
    ]);

    // Create default terms
    $terms = [
        'standard' => 'Standard',
        'video' => 'Video',
        'audio' => 'Audio',
        'galerija' => 'Galerija',
    ];

    foreach ($terms as $slug => $name) {
        if (!term_exists($slug, 'article-type')) {
            wp_insert_term($name, 'article-type', ['slug' => $slug]);
        }
    }
}
add_action('init', 'rvk_register_article_types');

// Enqueue Gutenberg panel script
function rvk_enqueue_article_type_panel() {
    $asset_file = get_template_directory() . '/dist/js/article-type-panel.js';

    if (file_exists($asset_file)) {
        wp_enqueue_script(
            'article-type-panel',
            get_template_directory_uri() . '/dist/js/article-type-panel.js',
            ['wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data'],
            filemtime($asset_file),
            true
        );
    }
}
add_action('enqueue_block_editor_assets', 'rvk_enqueue_article_type_panel');

// Save the article type selection
function rvk_save_article_type_meta_box($post_id) {
    // Security checks
    if (!isset($_POST['article_type_nonce']) || !wp_verify_nonce($_POST['article_type_nonce'], 'article_type_save')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Save the selected article type
    if (isset($_POST['article_type_selection']) && !empty($_POST['article_type_selection'])) {
        $term_id = intval($_POST['article_type_selection']);
        wp_set_object_terms($post_id, $term_id, 'article-type');
    } else {
        // Remove article type if none selected
        wp_delete_object_term_relationships($post_id, 'article-type');
    }
}
add_action('save_post', 'rvk_save_article_type_meta_box');

// Add post_class filter for CSS classes
function rvk_article_type_post_class($classes, $class, $post_id) {
    $type_slug = get_article_type_slug($post_id);
    $classes[] = 'post-type-' . $type_slug;

    if (get_post_meta($post_id, '_is_sponsored', true)) {
        $classes[] = 'post-sponsored';
    }

    return $classes;
}
add_filter('post_class', 'rvk_article_type_post_class', 10, 3);

// Add REST API fields
function rvk_register_article_type_rest_fields() {
    register_rest_field('post', 'article_type_slug', [
        'get_callback' => function($post) {
            return get_article_type_slug($post['id']);
        },
        'update_callback' => function($value, $post) {
            // Update the taxonomy with the provided slug
            if (!empty($value)) {
                wp_set_object_terms($post->ID, $value, 'article-type');
            }
        },
        'schema' => [
            'type' => 'string', 
            'context' => ['view', 'edit'],
            'enum' => ['standard', 'video', 'audio', 'galerija']
        ],
    ]);

    register_rest_field('post', 'article_type_label', [
        'get_callback' => function($post) {
            return get_article_type_label($post['id']);
        },
        'schema' => ['type' => 'string', 'context' => ['view', 'edit']],
    ]);

    register_rest_field('post', 'is_sponsored', [
        'get_callback' => function($post) {
            return (bool) get_post_meta($post['id'], '_is_sponsored', true);
        },
        'update_callback' => function($value, $post) {
            update_post_meta($post->ID, '_is_sponsored', $value ? 1 : 0);
        },
        'schema' => ['type' => 'boolean', 'context' => ['view', 'edit']],
    ]);
}
add_action('rest_api_init', 'rvk_register_article_type_rest_fields');
