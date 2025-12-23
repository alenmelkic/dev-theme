<?php
/**
 * Page Options Meta Box
 * Allows hiding the page title on the frontend while keeping it for SEO/Accessiblity.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add Meta Box to Pages
 */
function rvk_add_page_options_meta_box() {
    add_meta_box(
        'rvk_page_options',          // ID
        'Opcije Stranice',           // Title
        'rvk_page_options_callback',  // Callback
        'page',                      // Screen (Pages only)
        'side',                      // Context
        'default'                    // Priority
    );
}
add_action( 'add_meta_boxes', 'rvk_add_page_options_meta_box' );

/**
 * Meta Box Callback
 */
function rvk_page_options_callback( $post ) {
    // Add nonce for security
    wp_nonce_field( 'rvk_page_options_nonce', 'rvk_page_options_nonce_field' );

    // Get current value
    $value = get_post_meta( $post->ID, '_hide_page_title', true );
    ?>
    <p>
        <label for="rvk_hide_page_title">
            <input type="checkbox" name="rvk_hide_page_title" id="rvk_hide_page_title" value="1" <?php checked( $value, '1' ); ?>>
            Sakrij naslov na vrhu stranice
        </label>
    </p>
    <?php
}

/**
 * Save Meta Box Data
 */
function rvk_save_page_options_meta_box_data( $post_id ) {
    // Check if nonce is set
    if ( ! isset( $_POST['rvk_page_options_nonce_field'] ) ) {
        return;
    }

    // Verify nonce
    if ( ! wp_verify_nonce( $_POST['rvk_page_options_nonce_field'], 'rvk_page_options_nonce' ) ) {
        return;
    }

    // Check if this is an autosave
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    // Check permissions
    if ( isset( $_POST['post_type'] ) && 'page' === $_POST['post_type'] ) {
        if ( ! current_user_can( 'edit_page', $post_id ) ) {
            return;
        }
    } else {
        return;
    }

    // Save or delete the meta value
    if ( isset( $_POST['rvk_hide_page_title'] ) ) {
        update_post_meta( $post_id, '_hide_page_title', '1' );
    } else {
        delete_post_meta( $post_id, '_hide_page_title' );
    }
}
add_action( 'save_post', 'rvk_save_page_options_meta_box_data' );
