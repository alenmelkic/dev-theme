<?php
/**
 * User Profile Customizations
 * 
 * Adds custom fields to user profile and handles custom avatar logic.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueue media uploader on profile pages
 */
function dev_theme_enqueue_profile_media($hook) {
    if ($hook === 'profile.php' || $hook === 'user-edit.php') {
        wp_enqueue_media();
        // JS is inline in dev_theme_add_user_profile_fields
    }
}
add_action('admin_enqueue_scripts', 'dev_theme_enqueue_profile_media');

/**
 * Add custom fields to user profile
 */
function dev_theme_add_user_profile_fields($user) {
    $custom_avatar_id = get_the_author_meta('dev_theme_custom_avatar', $user->ID);
    $custom_avatar_url = $custom_avatar_id ? wp_get_attachment_url($custom_avatar_id) : '';
    ?>
    <h3><?php _e('Profilna Slika', 'dev-theme'); ?></h3>

    <table class="form-table">
        <tr>
            <th><label for="dev_theme_custom_avatar"><?php _e('Custom Avatar', 'dev-theme'); ?></label></th>
            <td>
                <div class="dev-theme-avatar-preview" style="margin-bottom: 10px;">
                    <?php if ($custom_avatar_url) : ?>
                        <img src="<?php echo esc_url($custom_avatar_url); ?>" alt="Avatar Preview" style="max-width: 150px; height: auto; border-radius: 50%;">
                    <?php else : ?>
                        <img src="<?php echo get_avatar_url($user->ID, ['force_default' => true]); ?>" alt="Default Avatar" style="max-width: 150px; height: auto; border-radius: 50%; opacity: 0.5;">
                    <?php endif; ?>
                </div>

                <input type="hidden" name="dev_theme_custom_avatar" id="dev_theme_custom_avatar" value="<?php echo esc_attr($custom_avatar_id); ?>" class="regular-text" />
                
                <button type="button" class="button button-secondary" id="dev_theme_upload_avatar_btn">
                    <?php _e('Upload Photo', 'dev-theme'); ?>
                </button>
                
                <button type="button" class="button button-secondary" id="dev_theme_remove_avatar_btn" style="<?php echo $custom_avatar_url ? '' : 'display:none;'; ?>">
                    <?php _e('Remove Photo', 'dev-theme'); ?>
                </button>

                <p class="description">
                    <?php _e('Upload a custom profile photo to replace Gravatar.', 'dev-theme'); ?>
                </p>

                <script>
                jQuery(document).ready(function($){
                    var mediaUploader;
                    
                    $('#dev_theme_upload_avatar_btn').click(function(e) {
                        e.preventDefault();
                        
                        if (mediaUploader) {
                            mediaUploader.open();
                            return;
                        }
                        
                        mediaUploader = wp.media.frames.file_frame = wp.media({
                            title: '<?php _e("Choose Profile Photo", "dev-theme"); ?>',
                            button: {
                                text: '<?php _e("Use this photo", "dev-theme"); ?>'
                            },
                            multiple: false
                        });
                        
                        mediaUploader.on('select', function() {
                            var attachment = mediaUploader.state().get('selection').first().toJSON();
                            $('#dev_theme_custom_avatar').val(attachment.id);
                            $('.dev-theme-avatar-preview img').attr('src', attachment.url).css('opacity', 1);
                            $('#dev_theme_remove_avatar_btn').show();
                        });
                        
                        mediaUploader.open();
                    });
                    
                    $('#dev_theme_remove_avatar_btn').click(function(e) {
                        e.preventDefault();
                        $('#dev_theme_custom_avatar').val('');
                        $('.dev-theme-avatar-preview img').attr('src', '<?php echo get_avatar_url($user->ID, ['force_default' => true]); ?>').css('opacity', 0.5);
                        $(this).hide();
                    });
                });
                </script>
            </td>
        </tr>
    </table>
    <?php
}
add_action('show_user_profile', 'dev_theme_add_user_profile_fields');
add_action('edit_user_profile', 'dev_theme_add_user_profile_fields');

/**
 * Save custom fields
 */
function dev_theme_save_user_profile_fields($user_id) {
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }
    
    if (isset($_POST['dev_theme_custom_avatar'])) {
        update_user_meta($user_id, 'dev_theme_custom_avatar', sanitize_text_field($_POST['dev_theme_custom_avatar']));
    }
}
add_action('personal_options_update', 'dev_theme_save_user_profile_fields');
add_action('edit_user_profile_update', 'dev_theme_save_user_profile_fields');

/**
 * Filter get_avatar to use custom image
 */
function dev_theme_filter_avatar($avatar, $id_or_email, $size, $default, $alt, $args) {
    $user_id = false;

    if (is_numeric($id_or_email)) {
        $user_id = (int) $id_or_email;
    } elseif (is_object($id_or_email)) {
        if (!empty($id_or_email->user_id)) {
            $user_id = (int) $id_or_email->user_id;
        }
    } elseif (is_string($id_or_email)) {
        $user = get_user_by('email', $id_or_email);
        if ($user) {
            $user_id = $user->ID;
        }
    }

    if ($user_id) {
        $custom_avatar_id = get_user_meta($user_id, 'dev_theme_custom_avatar', true);
        
        if ($custom_avatar_id) {
            $image = wp_get_attachment_image_src($custom_avatar_id, [$size, $size]);
            
            if ($image) {
                $avatar = sprintf(
                    '<img alt="%s" src="%s" class="avatar avatar-%d photo" height="%d" width="%d" loading="lazy" decoding="async">',
                    esc_attr($alt),
                    esc_url($image[0]),
                    (int) $size,
                    (int) $size,
                    (int) $size
                );
            }
        }
    }

    return $avatar;
}
add_filter('get_avatar', 'dev_theme_filter_avatar', 10, 6);
