<?php
/**
 * SEO Helper Functions
 * Utility functions for SEO/AEO optimization
 * Tailored by Alen Melkić
 */

// Security: Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get SEO meta value for a post
 *
 * @param int $post_id Post ID
 * @param string $key Meta key (without _seo_ prefix if omitted)
 * @param mixed $default Default value if not found
 * @return mixed Meta value or default
 */
function rvk_get_seo_meta($post_id, $key, $default = '') {
    if (empty($post_id)) {
        return $default;
    }

    // Add _seo_ prefix if not present
    if (strpos($key, '_seo_') !== 0) {
        $key = '_seo_' . $key;
    }

    $value = get_post_meta($post_id, $key, true);
    return !empty($value) ? $value : $default;
}

/**
 * Update SEO meta value for a post
 *
 * @param int $post_id Post ID
 * @param string $key Meta key (without _seo_ prefix if omitted)
 * @param mixed $value Value to store
 * @return bool|int Meta ID if the key didn't exist, true on successful update, false on failure
 */
function rvk_update_seo_meta($post_id, $key, $value) {
    if (empty($post_id)) {
        return false;
    }

    // Add _seo_ prefix if not present
    if (strpos($key, '_seo_') !== 0) {
        $key = '_seo_' . $key;
    }

    return update_post_meta($post_id, $key, $value);
}

/**
 * Get SEO meta value for a term (category/tag)
 *
 * @param int $term_id Term ID
 * @param string $key Meta key (without _seo_ prefix if omitted)
 * @param mixed $default Default value if not found
 * @return mixed Meta value or default
 */
function rvk_get_term_seo_meta($term_id, $key, $default = '') {
    if (empty($term_id)) {
        return $default;
    }

    // Add _seo_ prefix if not present
    if (strpos($key, '_seo_term_') !== 0) {
        $key = '_seo_term_' . $key;
    }

    $value = get_term_meta($term_id, $key, true);
    return !empty($value) ? $value : $default;
}

/**
 * Update SEO meta value for a term
 *
 * @param int $term_id Term ID
 * @param string $key Meta key (without _seo_term_ prefix if omitted)
 * @param mixed $value Value to store
 * @return bool|int Meta ID if the key didn't exist, true on successful update, false on failure
 */
function rvk_update_term_seo_meta($term_id, $key, $value) {
    if (empty($term_id)) {
        return false;
    }

    // Add _seo_term_ prefix if not present
    if (strpos($key, '_seo_term_') !== 0) {
        $key = '_seo_term_' . $key;
    }

    return update_term_meta($term_id, $key, $value);
}

/**
 * Sanitize SEO title
 *
 * @param string $title Title to sanitize
 * @return string Sanitized title
 */
function rvk_sanitize_seo_title($title) {
    $title = wp_strip_all_tags($title);
    $title = trim($title);

    // Limit to 60 characters (recommended for SEO)
    if (strlen($title) > 60) {
        $title = substr($title, 0, 60);
        // Try to break at word boundary
        $last_space = strrpos($title, ' ');
        if ($last_space !== false && $last_space > 40) {
            $title = substr($title, 0, $last_space);
        }
    }

    return $title;
}

/**
 * Sanitize meta description
 *
 * @param string $description Description to sanitize
 * @return string Sanitized description
 */
function rvk_sanitize_meta_description($description) {
    $description = wp_strip_all_tags($description);
    $description = trim($description);

    // Limit to 160 characters (recommended for SEO)
    if (strlen($description) > 160) {
        $description = substr($description, 0, 160);
        // Try to break at word boundary
        $last_space = strrpos($description, ' ');
        if ($last_space !== false && $last_space > 130) {
            $description = substr($description, 0, $last_space);
        }
        $description .= '...';
    }

    return $description;
}

/**
 * Get post hashtags from tags
 * Extracts post tags and formats them as hashtags for social sharing
 *
 * @param int $post_id Post ID
 * @param int $limit Maximum number of hashtags (default 5)
 * @return string Space-separated hashtags
 */
function rvk_get_post_hashtags($post_id, $limit = 5) {
    $tags = get_the_tags($post_id);

    if (!$tags || is_wp_error($tags)) {
        return '';
    }

    $hashtags = array_map(function($tag) {
        // Remove spaces and special characters, capitalize words
        $name = $tag->name;
        $name = ucwords(strtolower($name)); // Capitalize each word
        $name = preg_replace('/[^a-zA-Z0-9]/', '', $name); // Remove special chars
        return '#' . $name;
    }, $tags);

    // Limit number of hashtags
    $hashtags = array_slice($hashtags, 0, $limit);

    return implode(' ', $hashtags);
}

/**
 * Get formatted hashtags for Twitter (comma-separated, no # symbol)
 *
 * @param int $post_id Post ID
 * @param int $limit Maximum number of hashtags (default 5)
 * @return string Comma-separated hashtags without # symbol
 */
function rvk_get_twitter_hashtags($post_id, $limit = 5) {
    $tags = get_the_tags($post_id);

    if (!$tags || is_wp_error($tags)) {
        return '';
    }

    $hashtags = array_map(function($tag) {
        $name = $tag->name;
        $name = ucwords(strtolower($name));
        $name = preg_replace('/[^a-zA-Z0-9]/', '', $name);
        return $name; // No # symbol for Twitter API
    }, $tags);

    // Limit number of hashtags
    $hashtags = array_slice($hashtags, 0, $limit);

    return implode(',', $hashtags);
}

/**
 * Get SEO-optimized title with template
 *
 * @param int $post_id Post ID
 * @param string $template Title template (e.g., "{title} | {sitename}")
 * @return string Formatted title
 */
function rvk_get_seo_title($post_id = null, $template = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }

    if (!$post_id) {
        return get_bloginfo('name');
    }

    // Get custom SEO title or fallback to post title
    $title = rvk_get_seo_meta($post_id, 'title', get_the_title($post_id));

    // Use template if provided
    if ($template) {
        $title = str_replace(
            ['{title}', '{sitename}'],
            [$title, get_bloginfo('name')],
            $template
        );
    }

    return $title;
}

/**
 * Get SEO meta description
 *
 * @param int $post_id Post ID
 * @return string Meta description
 */
function rvk_get_meta_description($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }

    if (!$post_id) {
        return get_bloginfo('description');
    }

    // Get custom meta description
    $description = rvk_get_seo_meta($post_id, 'description');

    // Fallback to excerpt
    if (empty($description)) {
        $description = get_the_excerpt($post_id);
    }

    // Fallback to trimmed content
    if (empty($description)) {
        $post = get_post($post_id);
        if ($post) {
            $content = wp_strip_all_tags($post->post_content);
            $description = wp_trim_words($content, 30, '...');
        }
    }

    // Final fallback
    if (empty($description)) {
        $description = get_bloginfo('description');
    }

    return rvk_sanitize_meta_description($description);
}

/**
 * Get canonical URL for current page/post
 *
 * @param int $post_id Post ID
 * @return string Canonical URL
 */
function rvk_get_canonical_url($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }

    // Check for custom canonical URL
    $custom_canonical = rvk_get_seo_meta($post_id, 'canonical');
    if (!empty($custom_canonical)) {
        return esc_url($custom_canonical);
    }

    // Default to permalink
    if ($post_id) {
        return get_permalink($post_id);
    }

    // Fallback to current URL
    global $wp;
    return home_url($wp->request);
}

/**
 * Get Open Graph image URL
 *
 * @param int $post_id Post ID
 * @return string|false Image URL or false if not found
 */
function rvk_get_og_image($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }

    // Check for custom OG image
    $og_image_id = rvk_get_seo_meta($post_id, 'og_image');
    if ($og_image_id) {
        $image_url = wp_get_attachment_image_url($og_image_id, 'large');
        if ($image_url) {
            return $image_url;
        }
    }

    // Fallback to featured image
    if ($post_id && has_post_thumbnail($post_id)) {
        return get_the_post_thumbnail_url($post_id, 'large');
    }

    // Fallback to default OG image from settings
    $default_og_image = get_option('rvk_seo_default_og_image');
    if ($default_og_image) {
        $image_url = wp_get_attachment_image_url($default_og_image, 'large');
        if ($image_url) {
            return $image_url;
        }
    }

    return false;
}

/**
 * Check if post should be indexed
 *
 * @param int $post_id Post ID
 * @return bool True if should be indexed
 */
function rvk_should_index_post($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }

    if (!$post_id) {
        return true;
    }

    // Check custom noindex setting
    $noindex = rvk_get_seo_meta($post_id, 'noindex');
    if ($noindex === '1' || $noindex === true) {
        return false;
    }

    // Check post status
    $post_status = get_post_status($post_id);
    if ($post_status !== 'publish') {
        return false;
    }

    return true;
}

/**
 * Check if post should allow following links
 *
 * @param int $post_id Post ID
 * @return bool True if should follow
 */
function rvk_should_follow_post($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }

    if (!$post_id) {
        return true;
    }

    // Check custom nofollow setting
    $nofollow = rvk_get_seo_meta($post_id, 'nofollow');
    if ($nofollow === '1' || $nofollow === true) {
        return false;
    }

    return true;
}

/**
 * Get robots meta tag content
 *
 * @param int $post_id Post ID
 * @return string Robots meta content (e.g., "index, follow")
 */
function rvk_get_robots_meta($post_id = null) {
    $index = rvk_should_index_post($post_id) ? 'index' : 'noindex';
    $follow = rvk_should_follow_post($post_id) ? 'follow' : 'nofollow';

    return $index . ', ' . $follow;
}

/**
 * Calculate reading time for content
 * Already exists in utilities.php, but adding reference here for SEO context
 *
 * @param string $content Post content
 * @return int Reading time in minutes
 */
function rvk_calculate_reading_time($content) {
    $word_count = str_word_count(wp_strip_all_tags($content));
    $reading_time = ceil($word_count / 200); // Average reading speed: 200 words/minute
    return max(1, $reading_time); // Minimum 1 minute
}

/**
 * Get focus keywords as array
 *
 * @param int $post_id Post ID
 * @return array Array of focus keywords
 */
function rvk_get_focus_keywords($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }

    $keywords = rvk_get_seo_meta($post_id, 'keywords', '');

    if (empty($keywords)) {
        return [];
    }

    // Split by comma and trim
    $keywords_array = array_map('trim', explode(',', $keywords));

    // Remove empty values
    return array_filter($keywords_array);
}

/**
 * Generate share URL for social platforms
 *
 * @param string $platform Platform name (facebook, twitter, linkedin, whatsapp, email)
 * @param int $post_id Post ID
 * @return string Share URL
 */
function rvk_get_share_url($platform, $post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }

    $url = get_permalink($post_id);
    $title = get_the_title($post_id);
    $excerpt = get_the_excerpt($post_id);
    $hashtags = rvk_get_twitter_hashtags($post_id);

    switch ($platform) {
        case 'facebook':
            return 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode($url);

        case 'twitter':
            $text = $excerpt . ' ' . rvk_get_post_hashtags($post_id);
            $share_url = 'https://twitter.com/intent/tweet?text=' . urlencode($text) . '&url=' . urlencode($url);
            if (!empty($hashtags)) {
                $share_url .= '&hashtags=' . urlencode($hashtags);
            }
            return $share_url;

        case 'linkedin':
            return 'https://www.linkedin.com/sharing/share-offsite/?url=' . urlencode($url);

        case 'whatsapp':
            $text = $title . ' - ' . $excerpt;
            return 'https://wa.me/?text=' . urlencode($text . ' ' . $url);

        case 'email':
            $subject = $title;
            $body = $excerpt . "\n\n" . $url;
            return 'mailto:?subject=' . rawurlencode($subject) . '&body=' . rawurlencode($body);

        default:
            return $url;
    }
}
