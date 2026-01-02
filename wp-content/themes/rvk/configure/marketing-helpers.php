<?php
/**
 * Marketing Helpers
 * Functions for displaying banners on the frontend
 */

// Security: Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Check if top banner should be displayed on current page
 * 
 * @return bool
 */
function rvk_should_show_top_banner() {
    $settings = get_option('rvk_marketing_banners', array());
    
    if (empty($settings['top_banner'])) {
        return false;
    }
    
    $visibility = $settings['top_banner']['visibility'] ?? array();
    
    // Check if any images are set
    if (empty($settings['top_banner']['desktop_image']) && 
        empty($settings['top_banner']['tablet_image']) && 
        empty($settings['top_banner']['mobile_image'])) {
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
    
    // Check archive pages
    if (is_post_type_archive('obavijesti-o-smrti') && !empty($visibility['obavijesti'])) {
        return true;
    }
    
    if (is_post_type_archive('servicne-informacije') && !empty($visibility['servisne'])) {
        return true;
    }
    
    // Check category and tag archives
    if (is_category() && !empty($visibility['categories'])) {
        $current_category = get_queried_object_id();
        if (in_array($current_category, $visibility['categories'])) {
            return true;
        }
    }
    
    if (is_tag() && !empty($visibility['tags'])) {
        $current_tag = get_queried_object_id();
        if (in_array($current_tag, $visibility['tags'])) {
            return true;
        }
    }
    
    return false;
}

/**
 * Get top banner data
 * 
 * @return array|null
 */
function rvk_get_top_banner() {
    if (!rvk_should_show_top_banner()) {
        return null;
    }
    
    $settings = get_option('rvk_marketing_banners', array());
    return $settings['top_banner'] ?? null;
}

/**
 * Display top banner
 * WCAG 2.1 AA Compliant
 * 
 * @return void
 */
function rvk_display_top_banner() {
    $banner = rvk_get_top_banner();
    
    if (!$banner) {
        return;
    }
    
    // Use 'large' size for desktop/tablet (1024px), 'medium' for mobile (768px)
    $desktop_image = !empty($banner['desktop_image']) ? wp_get_attachment_image_url($banner['desktop_image'], 'large') : '';
    $tablet_image = !empty($banner['tablet_image']) ? wp_get_attachment_image_url($banner['tablet_image'], 'large') : '';
    $mobile_image = !empty($banner['mobile_image']) ? wp_get_attachment_image_url($banner['mobile_image'], 'medium') : '';
    $link = !empty($banner['link']) ? esc_url($banner['link']) : '';
    
    // Use custom alt text if provided, otherwise fallback to default
    $alt_text = !empty($banner['alt_text']) ? $banner['alt_text'] : 'Promotivni banner';
    
    // Fallback logic: if specific size is missing, use next available
    if (!$mobile_image) {
        $mobile_image = $tablet_image ?: $desktop_image;
    }
    if (!$tablet_image) {
        $tablet_image = $desktop_image ?: $mobile_image;
    }
    if (!$desktop_image) {
        $desktop_image = $tablet_image ?: $mobile_image;
    }
    
    if (!$desktop_image && !$tablet_image && !$mobile_image) {
        return;
    }
    
    ?>
    <section>
        <div class="container">
            <div class="col-xl-10 mx-auto">
                <aside class="top-banner rounded-3 overflow-hidden" role="complementary" aria-label="Promotivni banner">
                    <?php if ($link): ?>
                        <a href="<?php echo $link; ?>" 
                        class="top-banner__link" 
                        <?php if (strpos($link, home_url()) === false): ?>
                            target="_blank" 
                            rel="noopener noreferrer"
                        <?php endif; ?>
                        aria-label="<?php echo esc_attr($alt_text); ?>">
                    <?php endif; ?>
                    
                    <picture class="top-banner__picture">
                        <?php if ($mobile_image): ?>
                            <source media="(max-width: 767px)" srcset="<?php echo esc_url($mobile_image); ?>">
                        <?php endif; ?>
                        <?php if ($tablet_image): ?>
                            <source media="(max-width: 1023px)" srcset="<?php echo esc_url($tablet_image); ?>">
                        <?php endif; ?>
                        <img src="<?php echo esc_url($desktop_image); ?>" 
                            alt="<?php echo esc_attr($alt_text); ?>" 
                            class="top-banner__image"
                            loading="lazy"
                            decoding="async">
                    </picture>
                    
                    <?php if ($link): ?>
                        </a>
                    <?php endif; ?>
                </aside>

            </div>
        </div>
    </section>
    
    <?php
}

/**
 * Get small banners
 * 
 * @param int|null $limit Optional limit
 * @return array
 */
function rvk_get_small_banners($limit = null) {
    $settings = get_option('rvk_marketing_banners', array());
    $banners = $settings['small_banners'] ?? array();
    
    if ($limit && is_numeric($limit)) {
        $banners = array_slice($banners, 0, $limit);
    }
    
    return $banners;
}

/**
 * Display small banners
 * WCAG 2.1 AA Compliant
 * 
 * @param int|null $limit Optional limit
 * @return void
 */
function rvk_display_small_banners($limit = null) {
    $banners = rvk_get_small_banners($limit);

    if (empty($banners)) {
        return;
    }

    ?>
    <aside class="small-banners" role="complementary" aria-label="Mali banneri">
        <?php foreach ($banners as $index => $banner): ?>
            <?php
            // Get the attachment file path to verify it exists
            $attachment_id = $banner['image'];
            $image_url = wp_get_attachment_image_url($attachment_id, 'medium');

            // If image URL is not found, try to get the full size
            if (!$image_url) {
                $image_url = wp_get_attachment_image_url($attachment_id, 'full');
            }

            // Additional check: Verify the file exists
            if ($image_url) {
                $upload_dir = wp_upload_dir();
                $file_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $image_url);

                // If the file doesn't exist, skip this banner
                if (!file_exists($file_path)) {
                    $image_url = false;
                }
            }

            $link = !empty($banner['link']) ? esc_url($banner['link']) : '';

            // Use custom alt text if provided, otherwise fallback
            $alt_text = !empty($banner['alt_text']) ? $banner['alt_text'] : 'Mali banner ' . ($index + 1);

            if (!$image_url) {
                continue;
            }
            ?>
            
            <div class="small-banner__item">
                <?php if ($link): ?>
                    <a href="<?php echo $link; ?>" 
                       class="small-banner__link" 
                       <?php if (strpos($link, home_url()) === false): ?>
                           target="_blank" 
                           rel="noopener noreferrer"
                       <?php endif; ?>
                       aria-label="<?php echo esc_attr($alt_text); ?>">
                <?php endif; ?>
                
                <img src="<?php echo esc_url($image_url); ?>" 
                     alt="<?php echo esc_attr($alt_text); ?>" 
                     class="small-banner__image"
                     loading="lazy"
                     decoding="async"
                     width="320"
                     height="auto">
                
                <?php if ($link): ?>
                    </a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </aside>
    <?php
}
