<?php

/**
 * Register Custom Post Type: Servicne informacije
 * Auto-deletes posts after 15 days
 */

// Register Custom Post Type
function register_servicne_informacije_cpt() {
    $labels = array(
        'name'                  => _x('Servicne informacije', 'Post Type General Name', 'dev-theme'),
        'singular_name'         => _x('Servicna informacija', 'Post Type Singular Name', 'dev-theme'),
        'menu_name'             => __('Servicne informacije', 'dev-theme'),
        'name_admin_bar'        => __('Servicna informacija', 'dev-theme'),
        'archives'              => __('Arhiva informacija', 'dev-theme'),
        'attributes'            => __('Atributi', 'dev-theme'),
        'parent_item_colon'     => __('Nadređena informacija:', 'dev-theme'),
        'all_items'             => __('Sve informacije', 'dev-theme'),
        'add_new_item'          => __('Dodaj novu informaciju', 'dev-theme'),
        'add_new'               => __('Dodaj novu', 'dev-theme'),
        'new_item'              => __('Nova informacija', 'dev-theme'),
        'edit_item'             => __('Uredi informaciju', 'dev-theme'),
        'update_item'           => __('Ažuriraj informaciju', 'dev-theme'),
        'view_item'             => __('Pogledaj informaciju', 'dev-theme'),
        'view_items'            => __('Pogledaj informacije', 'dev-theme'),
        'search_items'          => __('Pretraži informacije', 'dev-theme'),
        'not_found'             => __('Nije pronađeno', 'dev-theme'),
        'not_found_in_trash'    => __('Nije pronađeno u smeću', 'dev-theme'),
        'featured_image'        => __('Istaknuta slika', 'dev-theme'),
        'set_featured_image'    => __('Postavi istaknutu sliku', 'dev-theme'),
        'remove_featured_image' => __('Ukloni istaknutu sliku', 'dev-theme'),
        'use_featured_image'    => __('Koristi kao istaknutu sliku', 'dev-theme'),
        'insert_into_item'      => __('Umetni u informaciju', 'dev-theme'),
        'uploaded_to_this_item' => __('Učitano u ovu informaciju', 'dev-theme'),
        'items_list'            => __('Lista informacija', 'dev-theme'),
        'items_list_navigation' => __('Navigacija liste informacija', 'dev-theme'),
        'filter_items_list'     => __('Filtriraj listu informacija', 'dev-theme'),
    );

    $args = array(
        'label'                 => __('Servicna informacija', 'dev-theme'),
        'description'           => __('Servicne informacije koje se automatski brišu nakon 15 dana', 'dev-theme'),
        'labels'                => $labels,
        'supports'              => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'revisions'),
        'taxonomies'            => array('servicne_tag'),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 5,
        'menu_icon'             => 'dashicons-info',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true,
        'rest_base'             => 'servicne-informacije',
        'rest_controller_class' => 'WP_REST_Posts_Controller',
        'rewrite'               => array(
            'slug'       => 'servicne-informacije',
            'with_front' => false,
        ),
    );

    register_post_type('servicne-informacije', $args);
}
add_action('init', 'register_servicne_informacije_cpt', 0);

// Register Custom Taxonomy: Tags
function register_servicne_tag_taxonomy() {
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
        'rest_base'                  => 'servicne-oznake',
        'rest_controller_class'      => 'WP_REST_Terms_Controller',
        'rewrite'                    => array(
            'slug' => 'servicne-oznaka',
        ),
    );

    register_taxonomy('servicne_tag', array('servicne-informacije'), $args);
}
add_action('init', 'register_servicne_tag_taxonomy', 0);

/**
 * Auto-delete posts after 15 days
 */

// Schedule the cron event
function schedule_servicne_informacije_cleanup() {
    if (!wp_next_scheduled('servicne_informacije_daily_cleanup')) {
        wp_schedule_event(time(), 'daily', 'servicne_informacije_daily_cleanup');
    }
}
add_action('wp', 'schedule_servicne_informacije_cleanup');

// The cleanup function
function delete_old_servicne_informacije() {
    $args = array(
        'post_type'      => 'servicne-informacije',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'date_query'     => array(
            array(
                'before' => '15 days ago',
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
            'Auto-deleted Servicna informacija: ID=%d, Title=%s, Published=%s',
            $post->ID,
            $post->post_title,
            $post->post_date
        ));
    }

    // Return count for testing purposes
    return count($old_posts);
}
add_action('servicne_informacije_daily_cleanup', 'delete_old_servicne_informacije');

// Add custom REST API field for days remaining
function add_days_remaining_to_rest_api() {
    register_rest_field(
        'servicne-informacije',
        'days_remaining',
        array(
            'get_callback' => function($post) {
                $publish_date = strtotime($post['date']);
                $current_date = time();
                $days_elapsed = floor(($current_date - $publish_date) / DAY_IN_SECONDS);
                $days_remaining = 15 - $days_elapsed;
                
                return max(0, $days_remaining);
            },
            'schema' => array(
                'description' => __('Broj dana preostalih prije automatskog brisanja', 'dev-theme'),
                'type'        => 'integer',
            ),
        )
    );

    register_rest_field(
        'servicne-informacije',
        'is_expiring_soon',
        array(
            'get_callback' => function($post) {
                $publish_date = strtotime($post['date']);
                $current_date = time();
                $days_elapsed = floor(($current_date - $publish_date) / DAY_IN_SECONDS);
                $days_remaining = 15 - $days_elapsed;
                
                return $days_remaining <= 3 && $days_remaining > 0;
            },
            'schema' => array(
                'description' => __('Da li informacija uskoro ističe (3 dana ili manje)', 'dev-theme'),
                'type'        => 'boolean',
            ),
        )
    );

    // Add author name
    register_rest_field(
        'servicne-informacije',
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
        'servicne-informacije',
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
add_action('rest_api_init', 'add_days_remaining_to_rest_api');