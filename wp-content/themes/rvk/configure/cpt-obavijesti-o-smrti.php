<?php
/**
 * Custom Post Type: Obavijesti o Smrti
 * Auto-deletes posts after 42 days at 02:00 AM
 */

// Security: Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Register Custom Post Type
function register_obavijesti_o_smrti_cpt() {
    $labels = array(
        'name'                  => _x('Obavijesti o Smrti', 'Post Type General Name', 'dev-theme'),
        'singular_name'         => _x('Obavijest o Smrti', 'Post Type Singular Name', 'dev-theme'),
        'menu_name'             => __('Obavijesti o Smrti', 'dev-theme'),
        'name_admin_bar'        => __('Obavijest o Smrti', 'dev-theme'),
        'archives'              => __('Obavijesti o Smrti', 'dev-theme'),
        'attributes'            => __('Atributi', 'dev-theme'),
        'parent_item_colon'     => __('Nadređena obavijest:', 'dev-theme'),
        'all_items'             => __('Sve obavijesti', 'dev-theme'),
        'add_new_item'          => __('Dodaj novu obavijest', 'dev-theme'),
        'add_new'               => __('Dodaj novu', 'dev-theme'),
        'new_item'              => __('Nova obavijest', 'dev-theme'),
        'edit_item'             => __('Uredi obavijest', 'dev-theme'),
        'update_item'           => __('Ažuriraj obavijest', 'dev-theme'),
        'view_item'             => __('Pogledaj obavijest', 'dev-theme'),
        'view_items'            => __('Pogledaj obavijesti', 'dev-theme'),
        'search_items'          => __('Pretraži obavijesti', 'dev-theme'),
        'not_found'             => __('Nije pronađeno', 'dev-theme'),
        'not_found_in_trash'    => __('Nije pronađeno u smeću', 'dev-theme'),
        'featured_image'        => __('Istaknuta slika', 'dev-theme'),
        'set_featured_image'    => __('Postavi istaknutu sliku', 'dev-theme'),
        'remove_featured_image' => __('Ukloni istaknutu sliku', 'dev-theme'),
        'use_featured_image'    => __('Koristi kao istaknutu sliku', 'dev-theme'),
        'insert_into_item'      => __('Umetni u obavijest', 'dev-theme'),
        'uploaded_to_this_item' => __('Učitano u ovu obavijest', 'dev-theme'),
        'items_list'            => __('Lista obavijesti', 'dev-theme'),
        'items_list_navigation' => __('Navigacija liste obavijesti', 'dev-theme'),
        'filter_items_list'     => __('Filtriraj listu obavijesti', 'dev-theme'),
    );

    $args = array(
        'label'                 => __('Obavijest o Smrti', 'dev-theme'),
        'description'           => __('Obavijesti o smrti koje se automatski brišu nakon 42 dana', 'dev-theme'),
        'labels'                => $labels,
        'supports'              => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'revisions'),
        'taxonomies'            => array('obavijest_tag'),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 6,
        'menu_icon'             => 'dashicons-rest-api',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true,
        'rest_base'             => 'obavijesti-o-smrti',
        'rest_controller_class' => 'WP_REST_Posts_Controller',
        'show_in_graphql'       => true,
        'graphql_single_name'   => 'ObavijestOSmrti',
        'graphql_plural_name'   => 'ObavijestOSmrtiItems',
        'rewrite'               => array(
            'slug'       => 'obavijesti-o-smrti',
            'with_front' => false,
        ),
    );

    register_post_type('obavijesti-o-smrti', $args);
}
add_action('init', 'register_obavijesti_o_smrti_cpt', 0);

// Register Custom Taxonomy: Tags for Obavijesti
function register_obavijest_tag_taxonomy() {
    $labels = array(
        'name'                       => _x('Oznake', 'Taxonomy General Name', 'dev-theme'),
        'singular_name'              => _x('Oznaka', 'Taxonomy Singular Name', 'dev-theme'),
        'menu_name'                  => __('Oznake', 'dev-theme'),
        'all_items'                  => __('Sve oznake', 'dev-theme'),
        'parent_item'                => __('Nadređena oznaka', 'dev-theme'),
        'parent_item_colon'          => __('Nadređena oznaka:', 'dev-theme'),
        'new_item_name'              => __('Naziv nove oznake', 'dev-theme'),
        'add_new_item'               => __('Dodaj novu oznaku', 'dev-theme'),
        'edit_item'                  => __('Uredi oznaku', 'dev-theme'),
        'update_item'                => __('Ažuriraj oznaku', 'dev-theme'),
        'view_item'                  => __('Pogledaj oznaku', 'dev-theme'),
        'separate_items_with_commas' => __('Odvoji oznake zarezom', 'dev-theme'),
        'add_or_remove_items'        => __('Dodaj ili ukloni oznake', 'dev-theme'),
        'choose_from_most_used'      => __('Odaberi od najkorištenijih', 'dev-theme'),
        'popular_items'              => __('Popularne oznake', 'dev-theme'),
        'search_items'               => __('Pretraži oznake', 'dev-theme'),
        'not_found'                  => __('Nije pronađeno', 'dev-theme'),
        'no_terms'                   => __('Nema oznaka', 'dev-theme'),
        'items_list'                 => __('Lista oznaka', 'dev-theme'),
        'items_list_navigation'      => __('Navigacija liste oznaka', 'dev-theme'),
    );

    $args = array(
        'labels'                     => $labels,
        'hierarchical'               => false,
        'public'                     => true,
        'show_ui'                    => true,
        'show_admin_column'          => true,
        'show_in_nav_menus'          => true,
        'show_tagcloud'              => true,
        'show_in_rest'               => true,
        'rest_base'                  => 'obavijest-oznake',
        'rest_controller_class'      => 'WP_REST_Terms_Controller',
        'show_in_graphql'            => true,
        'graphql_single_name'        => 'ObavijestOznaka',
        'graphql_plural_name'        => 'ObavijestOznake',
        'rewrite'                    => array(
            'slug' => 'obavijest-oznaka',
        ),
    );

    register_taxonomy('obavijest_tag', array('obavijesti-o-smrti'), $args);
}
add_action('init', 'register_obavijest_tag_taxonomy', 0);

// Flush rewrite rules on theme activation (only once)
function obavijesti_o_smrti_flush_rewrites() {
    // Check if we've already flushed for this CPT
    if (get_option('obavijesti_o_smrti_flush_rewrite_rules') !== 'done') {
        register_obavijesti_o_smrti_cpt();
        register_obavijest_tag_taxonomy();
        flush_rewrite_rules();
        update_option('obavijesti_o_smrti_flush_rewrite_rules', 'done');
    }
}
add_action('after_switch_theme', 'obavijesti_o_smrti_flush_rewrites');

/**
 * Auto-delete posts after 42 days at 02:00 AM
 */

// Schedule the cron event at 02:00 AM
function schedule_obavijesti_cleanup() {
    if (!wp_next_scheduled('obavijesti_daily_cleanup')) {
        // Schedule for 02:00 AM daily
        $timestamp = strtotime('tomorrow 02:00:00');
        wp_schedule_event($timestamp, 'daily', 'obavijesti_daily_cleanup');
    }
}
add_action('wp', 'schedule_obavijesti_cleanup');

// The cleanup function
function delete_old_obavijesti() {
    $args = array(
        'post_type'      => 'obavijesti-o-smrti',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'date_query'     => array(
            array(
                'before' => '42 days ago',
                'inclusive' => false,
            ),
        ),
    );

    $old_posts = get_posts($args);

    foreach ($old_posts as $post) {
        // Permanently delete the post (not trash)
        wp_delete_post($post->ID, true);
        
        // Log the deletion (optional, for debugging)
        error_log(sprintf(
            'Auto-deleted Obavijest o Smrti: ID=%d, Title=%s, Published=%s',
            $post->ID,
            $post->post_title,
            $post->post_date
        ));
    }

    // Return count for testing purposes
    return count($old_posts);
}
add_action('obavijesti_daily_cleanup', 'delete_old_obavijesti');

// Add custom REST API fields for days remaining
function add_obavijesti_days_remaining_to_rest_api() {
    register_rest_field(
        'obavijesti-o-smrti',
        'days_remaining',
        array(
            'get_callback' => function($post) {
                $publish_date = strtotime($post['date']);
                $current_date = time();
                $days_elapsed = floor(($current_date - $publish_date) / DAY_IN_SECONDS);
                $days_remaining = 42 - $days_elapsed;
                
                return max(0, $days_remaining);
            },
            'schema' => array(
                'description' => __('Broj dana preostalih prije automatskog brisanja', 'dev-theme'),
                'type'        => 'integer',
            ),
        )
    );

    register_rest_field(
        'obavijesti-o-smrti',
        'is_expiring_soon',
        array(
            'get_callback' => function($post) {
                $publish_date = strtotime($post['date']);
                $current_date = time();
                $days_elapsed = floor(($current_date - $publish_date) / DAY_IN_SECONDS);
                $days_remaining = 42 - $days_elapsed;
                
                return $days_remaining <= 7 && $days_remaining > 0;
            },
            'schema' => array(
                'description' => __('Da li obavijest uskoro ističe (7 dana ili manje)', 'dev-theme'),
                'type'        => 'boolean',
            ),
        )
    );

    // Add author name
    register_rest_field(
        'obavijesti-o-smrti',
        'author_name',
        array(
            'get_callback' => function($post) {
                $author_id = isset($post['author']) ? $post['author'] : get_post_field('post_author', $post['id']);
                $author = get_userdata($author_id);
                
                if (!$author) {
                    return '';
                }
                
                // Get first and last name
                $first_name = get_user_meta($author_id, 'first_name', true);
                $last_name = get_user_meta($author_id, 'last_name', true);
                
                // If both exist, combine them
                if ($first_name && $last_name) {
                    return trim($first_name . ' ' . $last_name);
                }
                
                // If only first name exists
                if ($first_name) {
                    return $first_name;
                }
                
                // If only last name exists
                if ($last_name) {
                    return $last_name;
                }
                
                // Fallback to display name if no first/last name is set
                return $author->display_name;
            },
            'schema' => array(
                'description' => __('Puno ime autora', 'dev-theme'),
                'type'        => 'string',
            ),
        )
    );

    // Add author avatar
    register_rest_field(
        'obavijesti-o-smrti',
        'author_avatar',
        array(
            'get_callback' => function($post) {
                $author_id = isset($post['author']) ? $post['author'] : get_post_field('post_author', $post['id']);
                return get_avatar_url($author_id, array('size' => 96));
            },
            'schema' => array(
                'description' => __('URL avatara autora', 'dev-theme'),
                'type'        => 'string',
            ),
        )
    );
}
add_action('rest_api_init', 'add_obavijesti_days_remaining_to_rest_api');

// Register custom fields in GraphQL schema
function register_obavijesti_graphql_fields() {
    if (!function_exists('register_graphql_field')) {
        return;
    }

    register_graphql_field('ObavijestOSmrti', 'daysRemaining', array(
        'type'        => 'Int',
        'description' => __('Broj dana preostalih prije automatskog brisanja', 'dev-theme'),
        'resolve'     => function($post) {
            $publish_date = strtotime(get_post_field('post_date', $post->databaseId));
            $days_elapsed = floor((time() - $publish_date) / DAY_IN_SECONDS);
            return max(0, 42 - $days_elapsed);
        },
    ));

    register_graphql_field('ObavijestOSmrti', 'isExpiringSoon', array(
        'type'        => 'Boolean',
        'description' => __('Da li obavijest uskoro ističe (7 dana ili manje)', 'dev-theme'),
        'resolve'     => function($post) {
            $publish_date   = strtotime(get_post_field('post_date', $post->databaseId));
            $days_elapsed   = floor((time() - $publish_date) / DAY_IN_SECONDS);
            $days_remaining = 42 - $days_elapsed;
            return $days_remaining <= 7 && $days_remaining > 0;
        },
    ));
}
add_action('graphql_register_types', 'register_obavijesti_graphql_fields');
