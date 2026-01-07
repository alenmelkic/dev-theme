<?php
/**
 * SEO Meta Tags Output
 * Handles output of meta tags, Open Graph, Twitter Cards, and structured data
 * Tailored by Alen Melkić
 */

// Security: Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Remove default WordPress meta tags to prevent duplicates
 */
function rvk_remove_default_meta_tags() {
    // Remove default WordPress generator meta
    remove_action('wp_head', 'wp_generator');

    // Remove WordPress default meta description (if any theme adds it)
    remove_action('wp_head', 'rel_canonical');

    // Remove WordPress default noindex robots meta (we handle it ourselves)
    remove_action('wp_head', 'noindex', 1);
    remove_action('wp_head', 'wp_no_robots');

    // Prevent WordPress from outputting robots meta tag
    add_filter('wp_robots', '__return_empty_array', 999);

    // Remove unnecessary WordPress bloat from <head>
    remove_action('wp_head', 'rsd_link'); // RSD link
    remove_action('wp_head', 'wlwmanifest_link'); // Windows Live Writer
    remove_action('wp_head', 'wp_shortlink_wp_head'); // Shortlink
    remove_action('wp_head', 'wp_oembed_add_discovery_links'); // oEmbed discovery
    remove_action('wp_head', 'rest_output_link_wp_head'); // REST API link
    remove_action('wp_head', 'wp_resource_hints', 2); // DNS prefetch hints

    // Remove XFN profile link (rarely used)
    remove_action('wp_head', 'index_rel_link');
    remove_action('wp_head', 'parent_post_rel_link', 10);
    remove_action('wp_head', 'start_post_rel_link', 10);
    remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10);

    // Remove pingback header
    add_filter('xmlrpc_enabled', '__return_false');
    add_filter('wp_headers', function($headers) {
        unset($headers['X-Pingback']);
        return $headers;
    });
}
add_action('init', 'rvk_remove_default_meta_tags');

/**
 * Output all SEO meta tags in <head>
 * Hooked to wp_head with priority 1 to run early
 */
function rvk_output_seo_meta_tags() {
    // Check compatibility - don't output if SEO plugin is handling it
    if (!rvk_should_output_meta_tags()) {
        return;
    }

    // Get current post/page/term
    $post_id = get_queried_object_id();
    $is_singular = is_singular();
    $is_archive = is_archive();
    $is_home = is_home() || is_front_page();

    // Output meta tags
    rvk_output_basic_meta_tags($post_id, $is_singular, $is_archive, $is_home);
    rvk_output_open_graph_tags($post_id, $is_singular, $is_archive, $is_home);
    rvk_output_twitter_card_tags($post_id, $is_singular, $is_archive, $is_home);
    rvk_output_additional_meta_tags($post_id, $is_singular, $is_archive, $is_home);
}
add_action('wp_head', 'rvk_output_seo_meta_tags', 1);

/**
 * Output basic meta tags (description, canonical, robots)
 *
 * @param int $post_id Post/Page/Term ID
 * @param bool $is_singular Is singular post/page
 * @param bool $is_archive Is archive page
 * @param bool $is_home Is home page
 */
function rvk_output_basic_meta_tags($post_id, $is_singular, $is_archive, $is_home) {
    // Meta description
    $description = '';

    if ($is_singular) {
        $description = rvk_get_meta_description($post_id);
    } elseif ($is_archive) {
        if (is_category() || is_tag() || is_tax()) {
            $term = get_queried_object();
            $description = rvk_get_term_seo_meta($term->term_id, 'description', term_description($term->term_id));
            $description = rvk_sanitize_meta_description($description);
        } elseif (is_author()) {
            $description = get_the_author_meta('description');
        } else {
            $description = get_bloginfo('description');
        }
    } elseif ($is_home) {
        $description = get_option('rvk_seo_homepage_description', get_bloginfo('description'));
    } else {
        $description = get_bloginfo('description');
    }

    if (!empty($description)) {
        echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
    }

    // Canonical URL
    $canonical = '';

    if ($is_singular) {
        $canonical = rvk_get_canonical_url($post_id);
    } elseif ($is_archive) {
        global $wp;
        $canonical = home_url($wp->request);
    } elseif ($is_home) {
        $canonical = home_url('/');
    }

    if (!empty($canonical)) {
        echo '<link rel="canonical" href="' . esc_url($canonical) . '">' . "\n";
    }

    // Robots meta
    if ($is_singular) {
        $robots = rvk_get_robots_meta($post_id);
        echo '<meta name="robots" content="' . esc_attr($robots) . '">' . "\n";
    }
}

/**
 * Output Open Graph meta tags
 *
 * @param int $post_id Post/Page/Term ID
 * @param bool $is_singular Is singular post/page
 * @param bool $is_archive Is archive page
 * @param bool $is_home Is home page
 */
function rvk_output_open_graph_tags($post_id, $is_singular, $is_archive, $is_home) {
    // OG Locale
    $locale = get_locale();
    $og_locale = str_replace('-', '_', $locale);
    echo '<meta property="og:locale" content="' . esc_attr($og_locale) . '">' . "\n";

    // OG Site Name
    echo '<meta property="og:site_name" content="' . esc_attr(get_bloginfo('name')) . '">' . "\n";

    // OG Type
    $og_type = $is_singular ? 'article' : 'website';
    echo '<meta property="og:type" content="' . esc_attr($og_type) . '">' . "\n";

    // OG Title
    $og_title = '';

    if ($is_singular) {
        $og_title = rvk_get_seo_meta($post_id, 'og_title');
        if (empty($og_title)) {
            $og_title = rvk_get_seo_meta($post_id, 'title', get_the_title($post_id));
        }
    } elseif ($is_archive) {
        if (is_category() || is_tag() || is_tax()) {
            $term = get_queried_object();
            $og_title = rvk_get_term_seo_meta($term->term_id, 'title', $term->name . ' Archives');
        } elseif (is_author()) {
            $og_title = get_the_author_meta('display_name') . ' - ' . get_bloginfo('name');
        } else {
            $og_title = get_bloginfo('name');
        }
    } elseif ($is_home) {
        $og_title = get_bloginfo('name');
    }

    if (!empty($og_title)) {
        echo '<meta property="og:title" content="' . esc_attr($og_title) . '">' . "\n";
    }

    // OG Description
    $og_description = '';

    if ($is_singular) {
        $og_description = rvk_get_seo_meta($post_id, 'og_description');
        if (empty($og_description)) {
            $og_description = rvk_get_meta_description($post_id);
        }
        
        // Append hashtags for posts (for social media sharing)
        $post_type = get_post_type($post_id);
        $hashtags = rvk_get_post_hashtags($post_id, 5);
        
        // Debug: Output as HTML comment
        echo '<!-- DEBUG: Post Type: ' . esc_html($post_type) . ', Hashtags: ' . esc_html($hashtags) . ' -->' . "\n";
        
        if ($post_type === 'post') {
            if (!empty($hashtags)) {
                $og_description .= "\n" . $hashtags;
            }
        }
    } elseif ($is_archive) {
        if (is_category() || is_tag() || is_tax()) {
            $term = get_queried_object();
            $og_description = rvk_get_term_seo_meta($term->term_id, 'description', term_description($term->term_id));
            $og_description = rvk_sanitize_meta_description($og_description);
        } elseif (is_author()) {
            $og_description = get_the_author_meta('description');
        } else {
            $og_description = get_bloginfo('description');
        }
    } elseif ($is_home) {
        $og_description = get_option('rvk_seo_homepage_description', get_bloginfo('description'));
    }

    if (!empty($og_description)) {
        echo '<meta property="og:description" content="' . esc_attr($og_description) . '">' . "\n";
    }

    // OG URL
    $og_url = '';

    if ($is_singular) {
        $og_url = get_permalink($post_id);
    } elseif ($is_archive || $is_home) {
        global $wp;
        $og_url = home_url($wp->request);
        if ($is_home) {
            $og_url = home_url('/');
        }
    }

    if (!empty($og_url)) {
        echo '<meta property="og:url" content="' . esc_url($og_url) . '">' . "\n";
    }

    // OG Image
    $og_image = false;

    if ($is_singular) {
        $og_image = rvk_get_og_image($post_id);
    } elseif ($is_archive) {
        if (is_category() || is_tag() || is_tax()) {
            $term = get_queried_object();
            $term_og_image_id = rvk_get_term_seo_meta($term->term_id, 'og_image');
            if ($term_og_image_id) {
                $og_image = wp_get_attachment_image_url($term_og_image_id, 'large');
            }
        }
    }

    // Fallback to default OG image
    if (!$og_image) {
        $default_og_image_id = get_option('rvk_seo_default_og_image');
        if ($default_og_image_id) {
            $og_image = wp_get_attachment_image_url($default_og_image_id, 'large');
        }
    }

    if ($og_image) {
        echo '<meta property="og:image" content="' . esc_url($og_image) . '">' . "\n";

        // Get image dimensions
        $image_id = attachment_url_to_postid($og_image);
        if ($image_id) {
            $image_meta = wp_get_attachment_metadata($image_id);
            if (isset($image_meta['width']) && isset($image_meta['height'])) {
                echo '<meta property="og:image:width" content="' . esc_attr($image_meta['width']) . '">' . "\n";
                echo '<meta property="og:image:height" content="' . esc_attr($image_meta['height']) . '">' . "\n";
            }
        }
    }

    // Article-specific OG tags
    if ($is_singular && get_post_type() === 'post') {
        // Article published time
        echo '<meta property="article:published_time" content="' . esc_attr(get_the_date('c', $post_id)) . '">' . "\n";

        // Article modified time
        echo '<meta property="article:modified_time" content="' . esc_attr(get_the_modified_date('c', $post_id)) . '">' . "\n";

        // Article author (use full name - security: don't expose username or author URL)
        $author_id = get_post_field('post_author', $post_id);

        // Prefer first name + last name, fallback to display name
        $first_name = get_the_author_meta('first_name', $author_id);
        $last_name = get_the_author_meta('last_name', $author_id);

        if (!empty($first_name) && !empty($last_name)) {
            $author_name = trim($first_name . ' ' . $last_name);
        } else {
            $author_name = get_the_author_meta('display_name', $author_id);
        }

        echo '<meta property="article:author" content="' . esc_attr($author_name) . '">' . "\n";

        // Article section (category)
        $categories = get_the_category($post_id);
        if (!empty($categories)) {
            echo '<meta property="article:section" content="' . esc_attr($categories[0]->name) . '">' . "\n";
        }

        // Article tags
        $tags = get_the_tags($post_id);
        if ($tags) {
            foreach ($tags as $tag) {
                echo '<meta property="article:tag" content="' . esc_attr($tag->name) . '">' . "\n";
            }
        }
    }
}

/**
 * Output Twitter Card meta tags
 *
 * @param int $post_id Post/Page/Term ID
 * @param bool $is_singular Is singular post/page
 * @param bool $is_archive Is archive page
 * @param bool $is_home Is home page
 */
function rvk_output_twitter_card_tags($post_id, $is_singular, $is_archive, $is_home) {
    // Twitter Card type
    $card_type = 'summary_large_image';
    echo '<meta name="twitter:card" content="' . esc_attr($card_type) . '">' . "\n";

    // Twitter Site handle
    $twitter_handle = get_option('rvk_seo_twitter_handle', '');
    if (!empty($twitter_handle)) {
        // Ensure @ prefix
        if (strpos($twitter_handle, '@') !== 0) {
            $twitter_handle = '@' . $twitter_handle;
        }
        echo '<meta name="twitter:site" content="' . esc_attr($twitter_handle) . '">' . "\n";
    }

    // Twitter Title
    $twitter_title = '';

    if ($is_singular) {
        $twitter_title = rvk_get_seo_meta($post_id, 'twitter_title');
        if (empty($twitter_title)) {
            $twitter_title = rvk_get_seo_meta($post_id, 'og_title');
        }
        if (empty($twitter_title)) {
            $twitter_title = rvk_get_seo_meta($post_id, 'title', get_the_title($post_id));
        }
    } elseif ($is_archive) {
        if (is_category() || is_tag() || is_tax()) {
            $term = get_queried_object();
            $twitter_title = $term->name . ' Archives';
        } else {
            $twitter_title = get_bloginfo('name');
        }
    } elseif ($is_home) {
        $twitter_title = get_bloginfo('name');
    }

    if (!empty($twitter_title)) {
        echo '<meta name="twitter:title" content="' . esc_attr($twitter_title) . '">' . "\n";
    }

    // Twitter Description
    $twitter_description = '';

    if ($is_singular) {
        $twitter_description = rvk_get_seo_meta($post_id, 'twitter_description');
        if (empty($twitter_description)) {
            $twitter_description = rvk_get_meta_description($post_id);
        }
        
        // Append hashtags for posts (for social media sharing)
        if (get_post_type() === 'post') {
            $hashtags = rvk_get_post_hashtags($post_id, 5);
            if (!empty($hashtags)) {
                $twitter_description .= "\n" . $hashtags;
            }
        }
    } elseif ($is_archive) {
        if (is_category() || is_tag() || is_tax()) {
            $term = get_queried_object();
            $twitter_description = rvk_get_term_seo_meta($term->term_id, 'description', term_description($term->term_id));
            $twitter_description = rvk_sanitize_meta_description($twitter_description);
        } else {
            $twitter_description = get_bloginfo('description');
        }
    } elseif ($is_home) {
        $twitter_description = get_option('rvk_seo_homepage_description', get_bloginfo('description'));
    }

    if (!empty($twitter_description)) {
        echo '<meta name="twitter:description" content="' . esc_attr($twitter_description) . '">' . "\n";
    }

    // Twitter Image
    $twitter_image = false;

    if ($is_singular) {
        $twitter_image_id = rvk_get_seo_meta($post_id, 'twitter_image');
        if ($twitter_image_id) {
            $twitter_image = wp_get_attachment_image_url($twitter_image_id, 'large');
        }

        // Fallback to OG image
        if (!$twitter_image) {
            $twitter_image = rvk_get_og_image($post_id);
        }
    }

    // Fallback to default OG image
    if (!$twitter_image) {
        $default_og_image_id = get_option('rvk_seo_default_og_image');
        if ($default_og_image_id) {
            $twitter_image = wp_get_attachment_image_url($default_og_image_id, 'large');
        }
    }

    if ($twitter_image) {
        echo '<meta name="twitter:image" content="' . esc_url($twitter_image) . '">' . "\n";
    }

    // Twitter Creator (author for posts)
    if ($is_singular && get_post_type() === 'post') {
        $author_twitter = get_the_author_meta('twitter', get_post_field('post_author', $post_id));
        if (!empty($author_twitter)) {
            if (strpos($author_twitter, '@') !== 0) {
                $author_twitter = '@' . $author_twitter;
            }
            echo '<meta name="twitter:creator" content="' . esc_attr($author_twitter) . '">' . "\n";
        }
    }
}

/**
 * Output additional meta tags
 *
 * @param int $post_id Post/Page/Term ID
 * @param bool $is_singular Is singular post/page
 * @param bool $is_archive Is archive page
 * @param bool $is_home Is home page
 */
function rvk_output_additional_meta_tags($post_id, $is_singular, $is_archive, $is_home) {
    // Generator meta (optional - can be disabled for security)
    $show_generator = get_option('rvk_seo_show_generator', true);
    if ($show_generator) {
        echo '<meta name="generator" content="RVK SEO/AEO v1.0">' . "\n";
    }

    // Note: Author meta removed - already included in Open Graph as article:author

    // Keywords meta (optional - not heavily used by search engines but useful for some)
    if ($is_singular) {
        $keywords = rvk_get_focus_keywords($post_id);
        if (!empty($keywords)) {
            echo '<meta name="keywords" content="' . esc_attr(implode(', ', $keywords)) . '">' . "\n";
        }
    }
}

/**
 * Add SEO-related body classes
 *
 * @param array $classes Existing body classes
 * @return array Modified body classes
 */
function rvk_seo_body_classes($classes) {
    if (is_singular()) {
        $post_id = get_the_ID();

        // Add class if has custom SEO
        if (rvk_get_seo_meta($post_id, 'title') || rvk_get_seo_meta($post_id, 'description')) {
            $classes[] = 'has-custom-seo';
        }

        // Add class if noindex
        if (!rvk_should_index_post($post_id)) {
            $classes[] = 'noindex';
        }
    }

    return $classes;
}
add_filter('body_class', 'rvk_seo_body_classes');
