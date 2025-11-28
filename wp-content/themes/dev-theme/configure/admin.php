<?php

// Enqueue media uploader
add_action('admin_enqueue_scripts', 'dev_theme_enqueue_media');
function dev_theme_enqueue_media($hook) {
    if ($hook !== 'toplevel_page_dev-theme-settings') {
        return;
    }
    wp_enqueue_media();
}

// Add admin menu for theme settings
add_action('admin_menu', 'dev_theme_add_admin_menu');
function dev_theme_add_admin_menu() {
    add_menu_page(
        'Podešavanja',           // Page title
        'Podešavanja',           // Menu title
        'edit_theme_options',    // Capability
        'dev-theme-settings',    // Menu slug
        'dev_theme_brand_page',  // Callback for main page
        'dashicons-admin-generic', // Icon
        30                       // Position
    );

    add_submenu_page(
        'dev-theme-settings',    // Parent slug
        'Brand',                 // Page title
        'Brand',                 // Menu title
        'edit_theme_options',    // Capability
        'dev-theme-settings',    // Menu slug (same as parent to replace default)
        'dev_theme_brand_page'   // Callback
    );
}

// Register settings
add_action('admin_init', 'dev_theme_register_settings');
function dev_theme_register_settings() {
    register_setting('dev_theme_brand_settings', 'dev_theme_logo');
}

// Brand settings page
function dev_theme_brand_page() {
    // Handle form submission
    if (isset($_POST['dev_theme_save_logo']) && check_admin_referer('dev_theme_brand_settings')) {
        update_option('dev_theme_logo', sanitize_text_field($_POST['dev_theme_logo']));
        echo '<div class="notice notice-success is-dismissible"><p>Podešavanja su sačuvana!</p></div>';
    }

    $logo_id = get_option('dev_theme_logo');
    $logo_url = $logo_id ? wp_get_attachment_url($logo_id) : '';
    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">Brand Podešavanja</h1>
        <hr class="wp-header-end">

        <style>
            .dev-theme-brand-container {
                max-width: 800px;
                margin-top: 20px;
            }
            .dev-theme-card {
                background: #fff;
                border: 1px solid #c3c4c7;
                border-radius: 4px;
                padding: 30px;
                margin-bottom: 20px;
            }
            .dev-theme-logo-section {
                display: flex;
                gap: 30px;
                align-items: flex-start;
            }
            .dev-theme-logo-preview {
                flex: 0 0 250px;
                min-height: 200px;
                background: #f6f7f7;
                border: 2px dashed #c3c4c7;
                border-radius: 4px;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
                transition: all 0.3s ease;
            }
            .dev-theme-logo-preview.has-logo {
                background: #fff;
                border-style: solid;
            }
            .dev-theme-logo-preview img {
                max-width: 100%;
                max-height: 180px;
                height: auto;
                display: block;
            }
            .dev-theme-logo-preview-empty {
                text-align: center;
                color: #787c82;
            }
            .dev-theme-logo-preview-empty .dashicons {
                font-size: 48px;
                width: 48px;
                height: 48px;
                opacity: 0.3;
                margin-bottom: 10px;
            }
            .dev-theme-logo-controls {
                flex: 1;
            }
            .dev-theme-logo-controls h2 {
                margin-top: 0;
                font-size: 18px;
            }
            .dev-theme-logo-controls p {
                color: #646970;
                margin-bottom: 20px;
            }
            .dev-theme-button-group {
                display: flex;
                gap: 10px;
                margin-top: 15px;
            }
            .button-primary.dev-theme-upload-logo {
                background: #2271b1;
                border-color: #2271b1;
            }
            .button-primary.dev-theme-upload-logo:hover {
                background: #135e96;
                border-color: #135e96;
            }
        </style>

        <form method="post" action="">
            <?php wp_nonce_field('dev_theme_brand_settings'); ?>

            <div class="dev-theme-brand-container">
                <div class="dev-theme-card">
                    <div class="dev-theme-logo-section">
                        <div class="dev-theme-logo-preview <?php echo $logo_url ? 'has-logo' : ''; ?>" id="logoPreview">
                            <?php if ($logo_url): ?>
                                <img src="<?php echo esc_url($logo_url); ?>" alt="Logo" />
                            <?php else: ?>
                                <div class="dev-theme-logo-preview-empty">
                                    <span class="dashicons dashicons-format-image"></span>
                                    <div>Nema logoa</div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="dev-theme-logo-controls">
                            <h2>Logo Sajta</h2>
                            <p>Izaberite logo koji će se prikazivati u headeru sajta. Preporučena dimenzija: 200x60px ili sličan omjer.</p>

                            <input type="hidden" name="dev_theme_logo" id="dev_theme_logo" value="<?php echo esc_attr($logo_id); ?>" />

                            <div class="dev-theme-button-group">
                                <button type="button" class="button button-primary dev-theme-upload-logo">
                                    <span class="dashicons dashicons-upload" style="margin-top: 3px;"></span>
                                    <?php echo $logo_url ? 'Promeni Logo' : 'Dodaj Logo'; ?>
                                </button>

                                <?php if ($logo_url): ?>
                                    <button type="button" class="button dev-theme-remove-logo">
                                        <span class="dashicons dashicons-trash" style="margin-top: 3px;"></span>
                                        Ukloni Logo
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <?php submit_button('Sačuvaj podešavanja', 'primary', 'dev_theme_save_logo', false); ?>
            </div>
        </form>
    </div>

    <script type="text/javascript">
    jQuery(document).ready(function($){
        var mediaUploader;

        // Enqueue media uploader
        if (typeof wp !== 'undefined' && wp.media) {
            $('.dev-theme-upload-logo').on('click', function(e) {
                e.preventDefault();

                if (mediaUploader) {
                    mediaUploader.open();
                    return;
                }

                mediaUploader = wp.media({
                    title: 'Izaberi Logo',
                    button: {
                        text: 'Koristi ovaj logo'
                    },
                    library: {
                        type: 'image'
                    },
                    multiple: false
                });

                mediaUploader.on('select', function() {
                    var attachment = mediaUploader.state().get('selection').first().toJSON();
                    $('#dev_theme_logo').val(attachment.id);
                    $('#logoPreview').addClass('has-logo').html('<img src="' + attachment.url + '" alt="Logo" />');
                    $('.dev-theme-upload-logo').html('<span class="dashicons dashicons-upload" style="margin-top: 3px;"></span> Promeni Logo');

                    if ($('.dev-theme-remove-logo').length === 0) {
                        $('.dev-theme-button-group').append('<button type="button" class="button dev-theme-remove-logo"><span class="dashicons dashicons-trash" style="margin-top: 3px;"></span> Ukloni Logo</button>');
                    }
                });

                mediaUploader.open();
            });

            $(document).on('click', '.dev-theme-remove-logo', function(e) {
                e.preventDefault();
                $('#dev_theme_logo').val('');
                $('#logoPreview').removeClass('has-logo').html('<div class="dev-theme-logo-preview-empty"><span class="dashicons dashicons-format-image"></span><div>Nema logoa</div></div>');
                $('.dev-theme-upload-logo').html('<span class="dashicons dashicons-upload" style="margin-top: 3px;"></span> Dodaj Logo');
                $(this).remove();
            });
        }
    });
    </script>
    <?php
}
