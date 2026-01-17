<?php
/**
 * Sponsored content meta box
 */

// Enqueue admin styles
function rvk_enqueue_sponsored_meta_box_styles() {
    $screen = get_current_screen();
    if ($screen && $screen->post_type === 'post') {
        wp_enqueue_style(
            'rvk-sponsored-meta-box',
            get_template_directory_uri() . '/dist/admin/css/sponsored-meta-box.min.css',
            [],
            filemtime(get_template_directory() . '/dist/admin/css/sponsored-meta-box.min.css')
        );
    }
}
add_action('admin_enqueue_scripts', 'rvk_enqueue_sponsored_meta_box_styles');

// Add meta box
function rvk_add_sponsored_meta_box() {
    add_meta_box(
        'rvk_sponsored_meta_box',
        'Sponzorirani Sadržaj',
        'rvk_sponsored_meta_box_callback',
        'post',
        'side',
        'high'
    );
}
add_action('add_meta_boxes', 'rvk_add_sponsored_meta_box');

// Meta box callback
function rvk_sponsored_meta_box_callback($post) {
    wp_nonce_field('rvk_sponsored_nonce', 'rvk_sponsored_nonce');
    $is_sponsored = get_post_meta($post->ID, '_is_sponsored', true);
    ?>
    <div class="components-panel__body is-opened">
        <div class="components-panel__row">
            <label class="components-base-control">
                <input type="checkbox" name="rvk_is_sponsored" value="1" <?php checked($is_sponsored, 1); ?> class="components-checkbox-control__input" />
                <span>Označi kao sponzorirani sadržaj</span>
            </label>
        </div>
        <p class="description">
            Sponzorirani članci će prikazati napomenu na početku i kraju članka.
        </p>
    </div>
    <?php
}

// Save meta box data
function rvk_save_sponsored_meta_box($post_id) {
    if (!isset($_POST['rvk_sponsored_nonce']) || !wp_verify_nonce($_POST['rvk_sponsored_nonce'], 'rvk_sponsored_nonce')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $is_sponsored = isset($_POST['rvk_is_sponsored']) ? 1 : 0;
    update_post_meta($post_id, '_is_sponsored', $is_sponsored);
}
add_action('save_post', 'rvk_save_sponsored_meta_box');
