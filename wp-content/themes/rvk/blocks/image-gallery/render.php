<?php
/**
 * Image Gallery Block Render Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$images = $attributes['images'] ?? [];

if ( empty( $images ) ) {
    return;
}

// Unique ID for this gallery instance
$unique_id = 'pswp-gallery-' . wp_generate_password(6, false);
?>

<div <?php echo get_block_wrapper_attributes(['class' => 'rvk-image-gallery pswp-gallery', 'id' => $unique_id, 'role' => 'region', 'aria-label' => 'Galerija slika']); ?>>
    <div class="gallery-grid" role="list">
        <?php foreach ($images as $index => $img_attr) : 
            $img_id = $img_attr['id'];
            
            // Skip first image for loop if we want special layout, or just handle by index
            // Grid Layout (matching reference):
            // Image 0: Large (left)
            // Image 1, 2, 3, 4: Smaller (right)
            
            // Image sizes (Optimized for performance)
            // Hero image uses 'mobile' (640px), others use 'mobile-small' (480px)
            $thumb_size = ($index === 0) ? 'mobile' : 'mobile-small';
            $thumb = wp_get_attachment_image_src($img_id, $thumb_size);
            
            // Full view sizes (Responsive)
            // Desktop uses 'tablet' (1024px), Mobile uses 'mobile' (640px)
            $full_desktop = wp_get_attachment_image_src($img_id, 'tablet');
            $full_mobile = wp_get_attachment_image_src($img_id, 'mobile');
            
            if (!$thumb || !$full_desktop) continue;

            $alt = get_post_meta($img_id, '_wp_attachment_image_alt', true) ?: ($img_attr['alt'] ?? '');
            $caption = $img_attr['caption'] ?? '';
            
            // Visibility and Overlay Logic
            // Mobile: Show 2 images (0, 1), hide rest (2+)
            // Desktop: Show 5 images (0-4), hide rest (5+)
            $is_hidden_mobile = ($index >= 2);
            $is_hidden_desktop = ($index >= 5);
            $has_more_mobile = ($index === 1 && count($images) > 2);
            $has_more_desktop = ($index === 4 && count($images) > 5);
            $remaining_count_mobile = count($images) - 2;
            $remaining_count_desktop = count($images) - 5;
            ?>
            <?php
            // CSS Classes
            $css_classes = ['gallery-item', 'item-' . $index];

            // Mobile visibility: hide items 2+ on mobile
            if ($is_hidden_mobile) {
                $css_classes[] = 'd-none d-sm-block';
            }

            // Desktop visibility: hide items 5+ on desktop
            if ($is_hidden_desktop) {
                $css_classes[] = 'd-sm-none';
            }

            // More overlay classes
            if ($has_more_mobile) {
                $css_classes[] = 'has-more-overlay-mobile';
            }
            if ($has_more_desktop) {
                $css_classes[] = 'has-more-overlay-desktop';
            }

            // Accessibility labels
            $aria_label = sprintf('Prikaži sliku %d u punoj veličini', $index + 1);
            if ($has_more_mobile || $has_more_desktop) {
                $count = $has_more_mobile ? ($remaining_count_mobile + 1) : ($remaining_count_desktop + 1);
                $aria_label = sprintf('Prikaži sve slike. Još %d fotografija dostupno.', $count);
            }
            ?>
            <a href="<?php echo esc_url($full_desktop[0]); ?>"
               class="<?php echo esc_attr(implode(' ', $css_classes)); ?>"
               data-pswp-src="<?php echo esc_url($full_desktop[0]); ?>"
               data-pswp-width="<?php echo $full_desktop[1]; ?>"
               data-pswp-height="<?php echo $full_desktop[2]; ?>"
               data-mobile-src="<?php echo esc_url($full_mobile[0]); ?>"
               data-mobile-width="<?php echo $full_mobile[1]; ?>"
               data-mobile-height="<?php echo $full_mobile[2]; ?>"
               target="_blank"
               role="listitem"
               aria-label="<?php echo esc_attr($aria_label); ?>"
               <?php echo ($is_hidden_mobile || $is_hidden_desktop) ? 'tabindex="-1" aria-hidden="true"' : ''; ?>>
                <img src="<?php echo esc_url($thumb[0]); ?>" alt="<?php echo esc_attr($alt); ?>" loading="lazy" class="rounded-3 shadow-sm">

                <?php if ($has_more_mobile || $has_more_desktop): ?>
                    <div class="more-overlay <?php echo $has_more_mobile ? 'mobile-overlay' : ''; ?> <?php echo $has_more_desktop ? 'desktop-overlay' : ''; ?>">
                        <?php if ($has_more_mobile): ?>
                            <span class="d-sm-none">+<?php echo $remaining_count_mobile + 1; ?></span>
                        <?php endif; ?>
                        <?php if ($has_more_desktop): ?>
                            <span class="d-none d-sm-inline">+<?php echo $remaining_count_desktop + 1; ?></span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if ($caption): ?>
                    <span class="pswp-caption-content visually-hidden"><?php echo esc_html($caption); ?></span>
                <?php endif; ?>
            </a>
            
            <?php 
        endforeach; ?>
    </div>
</div>
