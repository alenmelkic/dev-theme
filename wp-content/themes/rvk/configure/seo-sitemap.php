<?php
/**
 * SEO Sitemap Controller
 * Controls WordPress core sitemap based on SEO settings
 * Tailored by Alen Melkić
 */

// Security: Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class RVK_SEO_Sitemap {

    public function __construct() {
        // Check if sitemap is enabled
        $sitemap_enabled = get_option('rvk_seo_sitemap_enabled', true);

        if (!$sitemap_enabled) {
            // Disable WordPress sitemap
            add_filter('wp_sitemaps_enabled', '__return_false');
        } else {
            // Filter sitemap based on settings
            add_filter('wp_sitemaps_add_provider', array($this, 'filter_sitemap_providers'), 10, 2);
            add_filter('wp_sitemaps_posts_query_args', array($this, 'filter_posts_query'), 10, 2);
            add_filter('wp_sitemaps_taxonomies_query_args', array($this, 'filter_taxonomies_query'), 10, 2);
            add_filter('wp_sitemaps_max_urls', array($this, 'set_max_urls'));

            // Add images to sitemap if enabled
            if (get_option('rvk_seo_sitemap_include_images', true)) {
                add_filter('wp_sitemaps_posts_entry', array($this, 'add_images_to_sitemap'), 10, 3);
            }

            // Exclude posts with noindex
            add_filter('wp_sitemaps_posts_query_args', array($this, 'exclude_noindex_posts'), 10, 2);
        }
    }

    /**
     * Filter sitemap providers to exclude selected post types and taxonomies
     */
    public function filter_sitemap_providers($provider, $name) {
        $exclude_post_types = get_option('rvk_seo_sitemap_exclude_post_types', array());
        $exclude_taxonomies = get_option('rvk_seo_sitemap_exclude_taxonomies', array());

        // Remove post type providers
        if ($provider instanceof WP_Sitemaps_Posts && in_array($name, $exclude_post_types)) {
            return false;
        }

        // Remove taxonomy providers
        if ($provider instanceof WP_Sitemaps_Taxonomies && in_array($name, $exclude_taxonomies)) {
            return false;
        }

        return $provider;
    }

    /**
     * Filter posts query to exclude selected post types
     */
    public function filter_posts_query($args, $post_type) {
        $exclude_post_types = get_option('rvk_seo_sitemap_exclude_post_types', array());

        if (in_array($post_type, $exclude_post_types)) {
            $args['post__in'] = array(0); // Return no posts
        }

        return $args;
    }

    /**
     * Filter taxonomies query to exclude selected taxonomies
     */
    public function filter_taxonomies_query($args, $taxonomy) {
        $exclude_taxonomies = get_option('rvk_seo_sitemap_exclude_taxonomies', array());

        if (in_array($taxonomy, $exclude_taxonomies)) {
            $args['include'] = array(0); // Return no terms
        }

        return $args;
    }

    /**
     * Set maximum URLs per sitemap page
     */
    public function set_max_urls($max_urls) {
        $custom_max = get_option('rvk_seo_sitemap_entries_per_page', 2000);
        return absint($custom_max);
    }

    /**
     * Exclude posts with noindex meta from sitemap
     */
    public function exclude_noindex_posts($args, $post_type) {
        // Add meta query to exclude posts with noindex
        if (!isset($args['meta_query'])) {
            $args['meta_query'] = array();
        }

        $args['meta_query'][] = array(
            'relation' => 'OR',
            array(
                'key' => '_seo_noindex',
                'compare' => 'NOT EXISTS'
            ),
            array(
                'key' => '_seo_noindex',
                'value' => '1',
                'compare' => '!='
            )
        );

        return $args;
    }

    /**
     * Add images to sitemap entries
     */
    public function add_images_to_sitemap($sitemap_entry, $post, $post_type) {
        // Get featured image
        $thumbnail_id = get_post_thumbnail_id($post->ID);

        if ($thumbnail_id) {
            $image_url = wp_get_attachment_image_url($thumbnail_id, 'full');

            if ($image_url) {
                $sitemap_entry['images'] = array(
                    array(
                        'loc' => $image_url,
                        'title' => get_the_title($thumbnail_id),
                        'caption' => wp_get_attachment_caption($thumbnail_id)
                    )
                );
            }
        }

        // Get all images from post content
        $content = get_post_field('post_content', $post->ID);
        preg_match_all('/<img[^>]+src=[\'"]([^\'"]+)[\'"][^>]*>/i', $content, $matches);

        if (!empty($matches[1])) {
            if (!isset($sitemap_entry['images'])) {
                $sitemap_entry['images'] = array();
            }

            foreach ($matches[1] as $image_url) {
                // Skip if already added (featured image)
                $already_added = false;
                foreach ($sitemap_entry['images'] as $existing_image) {
                    if ($existing_image['loc'] === $image_url) {
                        $already_added = true;
                        break;
                    }
                }

                if (!$already_added) {
                    $attachment_id = attachment_url_to_postid($image_url);
                    $sitemap_entry['images'][] = array(
                        'loc' => $image_url,
                        'title' => $attachment_id ? get_the_title($attachment_id) : '',
                        'caption' => $attachment_id ? wp_get_attachment_caption($attachment_id) : ''
                    );
                }
            }
        }

        return $sitemap_entry;
    }

    /**
     * Get sitemap statistics
     */
    public static function get_sitemap_stats() {
        $stats = array();

        // Count posts in sitemap
        $post_types = get_post_types(array('public' => true), 'names');
        $exclude_post_types = get_option('rvk_seo_sitemap_exclude_post_types', array());

        $stats['post_types'] = array();
        foreach ($post_types as $post_type) {
            if ($post_type === 'attachment') continue;
            if (in_array($post_type, $exclude_post_types)) continue;

            $count = wp_count_posts($post_type);
            $stats['post_types'][$post_type] = $count->publish;
        }

        // Count taxonomies in sitemap
        $taxonomies = get_taxonomies(array('public' => true), 'names');
        $exclude_taxonomies = get_option('rvk_seo_sitemap_exclude_taxonomies', array());

        $stats['taxonomies'] = array();
        foreach ($taxonomies as $taxonomy) {
            if ($taxonomy === 'post_format') continue;
            if (in_array($taxonomy, $exclude_taxonomies)) continue;

            $count = wp_count_terms(array('taxonomy' => $taxonomy));
            $stats['taxonomies'][$taxonomy] = $count;
        }

        return $stats;
    }
}

// Initialize sitemap controller
new RVK_SEO_Sitemap();
