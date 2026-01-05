<?php
/**
 * AdSense Helpers
 * Functions for displaying AdSense ads on the frontend
 */

// Security: Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueue AdSense script in footer with Swup compatibility
 *
 * @return void
 */
function rvk_enqueue_adsense_script() {
    $settings = get_option('rvk_adsense_settings', array());

    if (empty($settings['enable_adsense']) || empty($settings['publisher_id'])) {
        return;
    }

    // Only load on singular posts/pages where ads might display
    if (!is_singular()) {
        return;
    }

    // Check if any position is enabled and should show
    $should_load = false;
    foreach (['position_a', 'position_b', 'position_c'] as $position) {
        if (rvk_should_show_ad($position)) {
            $should_load = true;
            break;
        }
    }

    if (!$should_load) {
        return;
    }

    $publisher_id = $settings['publisher_id'];

    ?>
    <script async
            data-swup-ignore-script
            src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=<?php echo esc_attr($publisher_id); ?>"
            crossorigin="anonymous">
    </script>
    <?php
}
add_action('wp_footer', 'rvk_enqueue_adsense_script', 5);

/**
 * Check ad visibility based on current page context
 * Reuses logic from marketing visibility checks
 *
 * @param array $visibility Visibility settings
 * @return bool
 */
function rvk_check_ad_visibility($visibility) {
    if (empty($visibility)) {
        return false;
    }

    // Check page type
    if (is_page() && !empty($visibility['pages'])) {
        return true;
    }

    if (is_singular('post') && !empty($visibility['posts'])) {
        // Check if specific categories or tags are selected
        if (!empty($visibility['categories']) || !empty($visibility['tags'])) {
            $post_categories = wp_get_post_categories(get_the_ID());
            $post_tags = wp_get_post_tags(get_the_ID(), array('fields' => 'ids'));

            // Check categories
            if (!empty($visibility['categories'])) {
                $has_category = array_intersect($visibility['categories'], $post_categories);
                if (!empty($has_category)) {
                    return true;
                }
            }

            // Check tags
            if (!empty($visibility['tags'])) {
                $has_tag = array_intersect($visibility['tags'], $post_tags);
                if (!empty($has_tag)) {
                    return true;
                }
            }

            // If categories/tags are specified but post doesn't match, don't show
            return false;
        }

        return true;
    }

    if (is_singular('obavijesti-o-smrti') && !empty($visibility['obavijesti'])) {
        return true;
    }

    if (is_singular('servicne-informacije') && !empty($visibility['servisne'])) {
        return true;
    }

    return false;
}

/**
 * Check if ad should be displayed at specific position
 *
 * @param string $position Position key (position_a, position_b, position_c)
 * @return bool
 */
function rvk_should_show_ad($position) {
    $settings = get_option('rvk_adsense_settings', array());

    if (empty($settings['enable_adsense']) || empty($settings['publisher_id'])) {
        return false;
    }

    $pos_settings = $settings[$position] ?? array();

    if (empty($pos_settings['enabled']) || empty($pos_settings['ad_slot_id'])) {
        return false;
    }

    // Check visibility
    return rvk_check_ad_visibility($pos_settings['visibility'] ?? array());
}

/**
 * A/B Testing: Select variant
 *
 * @param array $ab_config A/B testing configuration
 * @return string 'a' or 'b'
 */
function rvk_select_ab_variant($ab_config) {
    if (empty($ab_config['enabled'])) {
        return 'a';
    }

    $weight = absint($ab_config['variant_a_weight'] ?? 50);
    $random = rand(1, 100);

    return ($random <= $weight) ? 'a' : 'b';
}

/**
 * Get responsive configuration for ad
 *
 * @param array $responsive Responsive settings
 * @return array
 */
function rvk_get_responsive_ad_config($responsive) {
    $desktop = $responsive['desktop'] ?? array('enabled' => true, 'width' => 728, 'height' => 90);
    $tablet = $responsive['tablet'] ?? array('enabled' => true, 'width' => 468, 'height' => 60);
    $mobile = $responsive['mobile'] ?? array('enabled' => true, 'width' => 320, 'height' => 50);

    // Calculate min-height for placeholder (mobile height as minimum)
    $min_height = $mobile['height'] ?? 50;

    // Generate data attributes for responsive sizing
    $data_attrs = '';
    if ($desktop['enabled']) {
        $data_attrs .= sprintf('data-desktop-width="%d" data-desktop-height="%d" ',
                              $desktop['width'], $desktop['height']);
    }
    if ($tablet['enabled']) {
        $data_attrs .= sprintf('data-tablet-width="%d" data-tablet-height="%d" ',
                              $tablet['width'], $tablet['height']);
    }
    if ($mobile['enabled']) {
        $data_attrs .= sprintf('data-mobile-width="%d" data-mobile-height="%d" ',
                              $mobile['width'], $mobile['height']);
    }

    return array(
        'min_height' => $min_height,
        'data_attributes' => $data_attrs,
    );
}

/**
 * Display AdSense ad at specified position
 * WCAG 2.1 AA Compliant
 *
 * @param string $position Position key (position_a, position_b, position_c)
 * @return void
 */
function rvk_display_adsense_ad($position) {
    if (!rvk_should_show_ad($position)) {
        return;
    }

    $settings = get_option('rvk_adsense_settings', array());
    $pos_settings = $settings[$position];

    // A/B Testing: Select variant
    $ad_slot_id = $pos_settings['ad_slot_id'];
    $ad_format = $pos_settings['ad_format'];
    $variant = 'a';

    if (!empty($pos_settings['ab_testing']['enabled'])) {
        $variant = rvk_select_ab_variant($pos_settings['ab_testing']);
        if ($variant === 'b' && !empty($pos_settings['ab_testing']['variant_b_slot_id'])) {
            $ad_slot_id = $pos_settings['ab_testing']['variant_b_slot_id'];
            $ad_format = $pos_settings['ab_testing']['variant_b_format'];
        }
    }

    $lazy_load = $pos_settings['lazy_load'] ?? false;
    $full_width = $pos_settings['full_width_responsive'] ?? true;
    $publisher_id = $settings['publisher_id'];

    // Generate unique ID for this ad instance
    $ad_id = 'rvk-ad-' . $position . '-' . uniqid();

    // Calculate responsive sizes
    $responsive_config = rvk_get_responsive_ad_config($pos_settings['responsive'] ?? array());

    ?>
    <aside class="rvk-adsense-container rvk-adsense-<?php echo esc_attr($position); ?>"
          role="complementary"
          aria-label="Advertisement">

        <?php if ($lazy_load): ?>
            <!-- Placeholder for lazy loading (prevents CLS) -->
            <div id="<?php echo esc_attr($ad_id); ?>-placeholder"
                 class="rvk-ad-placeholder"
                 style="min-height: <?php echo esc_attr($responsive_config['min_height']); ?>px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; margin: 20px 0;">
                <span style="color: #999; font-size: 12px;">Advertisement</span>
            </div>
        <?php endif; ?>

        <ins class="adsbygoogle"
             id="<?php echo esc_attr($ad_id); ?>"
             style="display:block<?php echo $lazy_load ? ';display:none;' : ''; ?>"
             data-ad-client="<?php echo esc_attr($publisher_id); ?>"
             data-ad-slot="<?php echo esc_attr($ad_slot_id); ?>"
             data-ad-format="<?php echo esc_attr($ad_format); ?>"
             <?php if ($full_width): ?>
             data-full-width-responsive="true"
             <?php endif; ?>
             <?php if ($lazy_load): ?>
             data-lazy-load="true"
             <?php endif; ?>
             data-position="<?php echo esc_attr($position); ?>"
             data-ab-variant="<?php echo esc_attr($variant); ?>"
             <?php echo $responsive_config['data_attributes']; ?>
             ></ins>

        <?php if (!$lazy_load): ?>
        <script>
            (adsbygoogle = window.adsbygoogle || []).push({});
        </script>
        <?php endif; ?>
    </aside>
    <?php
}
