<?php
/**
 * Hide specific categories from public display
 * 
 * This file contains filters to hide the "Slider" category from:
 * - Category listings
 * - Post meta displays
 * - Admin category dropdowns (for non-admins)
 * - Navigation menus
 */

// Security: Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get the slug of the hidden category
 * Change this if your category has a different slug
 */
function get_hidden_category_slug() {
    return 'slider'; // Change this to match your category slug
}

/**
 * Get the hidden category object
 */
function get_hidden_category() {
    return get_category_by_slug(get_hidden_category_slug());
}

/**
 * Filter categories to exclude hidden category from get_categories()
 */
add_filter('get_terms', 'exclude_hidden_category_from_listings', 10, 4);
function exclude_hidden_category_from_listings($terms, $taxonomies, $args, $term_query) {
    // Only filter category taxonomy
    if (!in_array('category', (array) $taxonomies)) {
        return $terms;
    }
    
    // Don't filter in admin area (so admins can still see and manage it)
    if (is_admin()) {
        return $terms;
    }
    
    $hidden_category = get_hidden_category();
    if (!$hidden_category) {
        return $terms;
    }
    
    // Remove the hidden category from the results
    foreach ($terms as $key => $term) {
        if ($term->term_id === $hidden_category->term_id) {
            unset($terms[$key]);
        }
    }
    
    return $terms;
}

/**
 * Filter categories displayed on single posts
 * This filters get_the_category() results
 */
add_filter('get_the_categories', 'exclude_hidden_category_from_post_display', 10, 2);
function exclude_hidden_category_from_post_display($categories, $post_id) {
    $hidden_category = get_hidden_category();
    if (!$hidden_category) {
        return $categories;
    }
    
    // Remove hidden category from the array
    foreach ($categories as $key => $category) {
        if ($category->term_id === $hidden_category->term_id) {
            unset($categories[$key]);
        }
    }
    
    return array_values($categories); // Re-index array
}

/**
 * Hide posts that ONLY have the hidden category
 * (Optional - uncomment if you want to hide these posts from archives)
 */
// add_action('pre_get_posts', 'exclude_hidden_category_only_posts');
// function exclude_hidden_category_only_posts($query) {
//     // Only on frontend, main query, and not in admin
//     if (is_admin() || !$query->is_main_query()) {
//         return;
//     }
//     
//     $hidden_category = get_hidden_category();
//     if (!$hidden_category) {
//         return;
//     }
//     
//     // Exclude the hidden category from category archives and home page
//     if ($query->is_home() || $query->is_category() || $query->is_archive()) {
//         $tax_query = $query->get('tax_query') ?: array();
//         
//         $tax_query[] = array(
//             'taxonomy' => 'category',
//             'field'    => 'term_id',
//             'terms'    => array($hidden_category->term_id),
//             'operator' => 'NOT IN',
//         );
//         
//         $query->set('tax_query', $tax_query);
//     }
// }

/**
 * Remove hidden category from navigation menus
 */
add_filter('wp_get_nav_menu_items', 'exclude_hidden_category_from_menus', 10, 3);
function exclude_hidden_category_from_menus($items, $menu, $args) {
    $hidden_category = get_hidden_category();
    if (!$hidden_category) {
        return $items;
    }
    
    foreach ($items as $key => $item) {
        // Check if this menu item is a category link
        if ($item->object === 'category' && $item->object_id == $hidden_category->term_id) {
            unset($items[$key]);
        }
    }
    
    return $items;
}
