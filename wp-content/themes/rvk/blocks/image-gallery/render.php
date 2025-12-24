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
            $is_hidden = ($index >= 5);
            $has_more = ($index === 4 && count($images) > 5);
            $remaining_count = count($images) - 5;
            ?>
            <?php 
            // Accessibility labels
            $aria_label = sprintf('Prikaži sliku %d u punoj veličini', $index + 1);
            if ($has_more) {
                $aria_label = sprintf('Prikaži sve slike. Još %d fotografija dostupno.', $remaining_count + 1);
            }
            ?>
            <a href="<?php echo esc_url($full_desktop[0]); ?>" 
               class="gallery-item item-<?php echo $index; ?> <?php echo $is_hidden ? 'd-none' : ''; ?> <?php echo $has_more ? 'has-more-overlay' : ''; ?>"
               data-pswp-src="<?php echo esc_url($full_desktop[0]); ?>" 
               data-pswp-width="<?php echo $full_desktop[1]; ?>" 
               data-pswp-height="<?php echo $full_desktop[2]; ?>"
               data-mobile-src="<?php echo esc_url($full_mobile[0]); ?>"
               data-mobile-width="<?php echo $full_mobile[1]; ?>"
               data-mobile-height="<?php echo $full_mobile[2]; ?>"
               target="_blank"
               role="listitem"
               aria-label="<?php echo esc_attr($aria_label); ?>"
               <?php echo $is_hidden ? 'tabindex="-1" aria-hidden="true"' : ''; ?>>
                <img src="<?php echo esc_url($thumb[0]); ?>" alt="<?php echo esc_attr($alt); ?>" loading="lazy" class="rounded-3 shadow-sm">
                
                <?php if ($has_more): ?>
                    <div class="more-overlay">
                        <span>+<?php echo $remaining_count + 1; // +1 because we are on the 5th image which also counts towards the total ?></span>
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
