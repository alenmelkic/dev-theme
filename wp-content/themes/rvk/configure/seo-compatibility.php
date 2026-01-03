<?php
/**
 * SEO Plugin Compatibility
 * Detects and manages compatibility with other SEO plugins (Yoast, RankMath, AIOSEO)
 * Tailored by Alen Melkić
 */

// Security: Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Detect active SEO plugins
 *
 * @return array Array of detected SEO plugins
 */
function rvk_detect_seo_plugins() {
    $detected_plugins = [];

    // Check for Yoast SEO
    if (defined('WPSEO_VERSION') || class_exists('WPSEO_Frontend')) {
        $detected_plugins[] = [
            'slug' => 'yoast',
            'name' => 'Yoast SEO',
            'version' => defined('WPSEO_VERSION') ? WPSEO_VERSION : 'unknown'
        ];
    }

    // Check for Rank Math
    if (defined('RANK_MATH_VERSION') || class_exists('RankMath')) {
        $detected_plugins[] = [
            'slug' => 'rankmath',
            'name' => 'Rank Math',
            'version' => defined('RANK_MATH_VERSION') ? RANK_MATH_VERSION : 'unknown'
        ];
    }

    // Check for All in One SEO Pack
    if (defined('AIOSEO_VERSION') || class_exists('All_in_One_SEO_Pack')) {
        $detected_plugins[] = [
            'slug' => 'aioseo',
            'name' => 'All in One SEO',
            'version' => defined('AIOSEO_VERSION') ? AIOSEO_VERSION : 'unknown'
        ];
    }

    // Check for SEOPress
    if (defined('SEOPRESS_VERSION') || class_exists('SEOPress')) {
        $detected_plugins[] = [
            'slug' => 'seopress',
            'name' => 'SEOPress',
            'version' => defined('SEOPRESS_VERSION') ? SEOPRESS_VERSION : 'unknown'
        ];
    }

    // Check for The SEO Framework
    if (defined('THE_SEO_FRAMEWORK_VERSION') || function_exists('the_seo_framework')) {
        $detected_plugins[] = [
            'slug' => 'seoframework',
            'name' => 'The SEO Framework',
            'version' => defined('THE_SEO_FRAMEWORK_VERSION') ? THE_SEO_FRAMEWORK_VERSION : 'unknown'
        ];
    }

    return $detected_plugins;
}

/**
 * Check if any SEO plugin is active
 *
 * @return bool True if any SEO plugin is detected
 */
function rvk_has_seo_plugin() {
    $plugins = rvk_detect_seo_plugins();
    return !empty($plugins);
}

/**
 * Determine if theme should output meta tags
 * Returns false if another SEO plugin is handling meta tags
 *
 * @return bool True if theme should output meta tags
 */
function rvk_should_output_meta_tags() {
    // Check if user has explicitly disabled theme SEO
    $theme_seo_disabled = get_option('rvk_seo_disable_output', false);
    if ($theme_seo_disabled) {
        return false;
    }

    // Check for SEO plugins
    $detected_plugins = rvk_detect_seo_plugins();

    // If no plugins detected, theme handles SEO
    if (empty($detected_plugins)) {
        return true;
    }

    // Check if user wants theme SEO to override plugins
    $override_plugins = get_option('rvk_seo_override_plugins', false);
    if ($override_plugins) {
        return true;
    }

    // By default, defer to SEO plugins
    return false;
}

/**
 * Import Yoast SEO data for a post
 *
 * @param int $post_id Post ID
 * @return bool True on success, false on failure
 */
function rvk_import_yoast_data($post_id) {
    if (empty($post_id)) {
        return false;
    }

    $imported = false;

    // Import title
    $yoast_title = get_post_meta($post_id, '_yoast_wpseo_title', true);
    if (!empty($yoast_title)) {
        rvk_update_seo_meta($post_id, 'title', $yoast_title);
        $imported = true;
    }

    // Import meta description
    $yoast_desc = get_post_meta($post_id, '_yoast_wpseo_metadesc', true);
    if (!empty($yoast_desc)) {
        rvk_update_seo_meta($post_id, 'description', $yoast_desc);
        $imported = true;
    }

    // Import focus keyword
    $yoast_keyword = get_post_meta($post_id, '_yoast_wpseo_focuskw', true);
    if (!empty($yoast_keyword)) {
        rvk_update_seo_meta($post_id, 'keywords', $yoast_keyword);
        $imported = true;
    }

    // Import canonical URL
    $yoast_canonical = get_post_meta($post_id, '_yoast_wpseo_canonical', true);
    if (!empty($yoast_canonical)) {
        rvk_update_seo_meta($post_id, 'canonical', $yoast_canonical);
        $imported = true;
    }

    // Import noindex setting
    $yoast_noindex = get_post_meta($post_id, '_yoast_wpseo_meta-robots-noindex', true);
    if ($yoast_noindex === '1') {
        rvk_update_seo_meta($post_id, 'noindex', true);
        $imported = true;
    }

    // Import nofollow setting
    $yoast_nofollow = get_post_meta($post_id, '_yoast_wpseo_meta-robots-nofollow', true);
    if ($yoast_nofollow === '1') {
        rvk_update_seo_meta($post_id, 'nofollow', true);
        $imported = true;
    }

    // Import Open Graph title
    $yoast_og_title = get_post_meta($post_id, '_yoast_wpseo_opengraph-title', true);
    if (!empty($yoast_og_title)) {
        rvk_update_seo_meta($post_id, 'og_title', $yoast_og_title);
        $imported = true;
    }

    // Import Open Graph description
    $yoast_og_desc = get_post_meta($post_id, '_yoast_wpseo_opengraph-description', true);
    if (!empty($yoast_og_desc)) {
        rvk_update_seo_meta($post_id, 'og_description', $yoast_og_desc);
        $imported = true;
    }

    // Import Open Graph image
    $yoast_og_image = get_post_meta($post_id, '_yoast_wpseo_opengraph-image-id', true);
    if (!empty($yoast_og_image)) {
        rvk_update_seo_meta($post_id, 'og_image', $yoast_og_image);
        $imported = true;
    }

    // Import Twitter title
    $yoast_twitter_title = get_post_meta($post_id, '_yoast_wpseo_twitter-title', true);
    if (!empty($yoast_twitter_title)) {
        rvk_update_seo_meta($post_id, 'twitter_title', $yoast_twitter_title);
        $imported = true;
    }

    // Import Twitter description
    $yoast_twitter_desc = get_post_meta($post_id, '_yoast_wpseo_twitter-description', true);
    if (!empty($yoast_twitter_desc)) {
        rvk_update_seo_meta($post_id, 'twitter_description', $yoast_twitter_desc);
        $imported = true;
    }

    // Import Twitter image
    $yoast_twitter_image = get_post_meta($post_id, '_yoast_wpseo_twitter-image-id', true);
    if (!empty($yoast_twitter_image)) {
        rvk_update_seo_meta($post_id, 'twitter_image', $yoast_twitter_image);
        $imported = true;
    }

    return $imported;
}

/**
 * Import Rank Math data for a post
 *
 * @param int $post_id Post ID
 * @return bool True on success, false on failure
 */
function rvk_import_rankmath_data($post_id) {
    if (empty($post_id)) {
        return false;
    }

    $imported = false;

    // Import title
    $rm_title = get_post_meta($post_id, 'rank_math_title', true);
    if (!empty($rm_title)) {
        rvk_update_seo_meta($post_id, 'title', $rm_title);
        $imported = true;
    }

    // Import meta description
    $rm_desc = get_post_meta($post_id, 'rank_math_description', true);
    if (!empty($rm_desc)) {
        rvk_update_seo_meta($post_id, 'description', $rm_desc);
        $imported = true;
    }

    // Import focus keyword
    $rm_keyword = get_post_meta($post_id, 'rank_math_focus_keyword', true);
    if (!empty($rm_keyword)) {
        rvk_update_seo_meta($post_id, 'keywords', $rm_keyword);
        $imported = true;
    }

    // Import canonical URL
    $rm_canonical = get_post_meta($post_id, 'rank_math_canonical_url', true);
    if (!empty($rm_canonical)) {
        rvk_update_seo_meta($post_id, 'canonical', $rm_canonical);
        $imported = true;
    }

    // Import robots settings
    $rm_robots = get_post_meta($post_id, 'rank_math_robots', true);
    if (is_array($rm_robots)) {
        if (in_array('noindex', $rm_robots)) {
            rvk_update_seo_meta($post_id, 'noindex', true);
            $imported = true;
        }
        if (in_array('nofollow', $rm_robots)) {
            rvk_update_seo_meta($post_id, 'nofollow', true);
            $imported = true;
        }
    }

    return $imported;
}

/**
 * Bulk import SEO data from detected plugin
 *
 * @param string $plugin_slug Plugin to import from ('yoast', 'rankmath', 'aioseo')
 * @param int $limit Number of posts to import per batch (default 50)
 * @return array Results with counts
 */
function rvk_bulk_import_seo_data($plugin_slug = 'yoast', $limit = 50) {
    $results = [
        'success' => 0,
        'failed' => 0,
        'skipped' => 0,
        'total' => 0
    ];

    // Get all posts
    $args = [
        'post_type' => ['post', 'page'],
        'posts_per_page' => $limit,
        'post_status' => 'any',
        'fields' => 'ids'
    ];

    $posts = get_posts($args);
    $results['total'] = count($posts);

    foreach ($posts as $post_id) {
        // Check if already has theme SEO data
        $has_theme_seo = rvk_get_seo_meta($post_id, 'title') || rvk_get_seo_meta($post_id, 'description');

        if ($has_theme_seo) {
            $results['skipped']++;
            continue;
        }

        // Import based on plugin
        $imported = false;
        switch ($plugin_slug) {
            case 'yoast':
                $imported = rvk_import_yoast_data($post_id);
                break;
            case 'rankmath':
                $imported = rvk_import_rankmath_data($post_id);
                break;
            // Add more plugins as needed
        }

        if ($imported) {
            $results['success']++;
        } else {
            $results['failed']++;
        }
    }

    return $results;
}

/**
 * Get compatibility status for admin display
 *
 * @return array Compatibility information
 */
function rvk_get_compatibility_status() {
    $detected_plugins = rvk_detect_seo_plugins();
    $should_output = rvk_should_output_meta_tags();

    return [
        'has_seo_plugin' => !empty($detected_plugins),
        'detected_plugins' => $detected_plugins,
        'theme_handles_seo' => $should_output,
        'message' => rvk_get_compatibility_message($detected_plugins, $should_output)
    ];
}

/**
 * Get compatibility message for admin display
 *
 * @param array $detected_plugins Detected SEO plugins
 * @param bool $should_output Whether theme should output meta tags
 * @return string Status message
 */
function rvk_get_compatibility_message($detected_plugins, $should_output) {
    if (empty($detected_plugins)) {
        return 'Nijedan SEO plugin nije detektovan. Tema upravlja svim SEO funkcijama.';
    }

    $plugin_names = array_map(function($plugin) {
        return $plugin['name'];
    }, $detected_plugins);

    $names_string = implode(', ', $plugin_names);

    if ($should_output) {
        return sprintf(
            'Detektovani plugini: %s. Tema override-uje SEO funkcije.',
            $names_string
        );
    } else {
        return sprintf(
            'Detektovani plugini: %s. Theme SEO funkcije su onemogućene da bi se izbegao konflikt. AI funkcije i dalje rade.',
            $names_string
        );
    }
}

/**
 * Display compatibility notice in admin
 */
function rvk_display_compatibility_notice() {
    $status = rvk_get_compatibility_status();

    if (!$status['has_seo_plugin']) {
        return;
    }

    $class = $status['theme_handles_seo'] ? 'notice-warning' : 'notice-info';

    ?>
    <div class="notice <?php echo esc_attr($class); ?>">
        <p>
            <strong>RVK SEO/AEO:</strong> <?php echo esc_html($status['message']); ?>
            <?php if (!$status['theme_handles_seo']): ?>
                <a href="<?php echo esc_url(admin_url('options-general.php?page=seo-aeo-settings')); ?>">
                    Konfiguriši
                </a>
            <?php endif; ?>
        </p>
    </div>
    <?php
}

// Hook to display compatibility notice
add_action('admin_notices', 'rvk_display_compatibility_notice');
