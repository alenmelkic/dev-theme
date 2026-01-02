<?php
/**
 * Marketing Settings Page
 * Manages Top Banners and Small Banners across the website
 */

// Security: Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Add Marketing submenu under Settings
function rvk_add_marketing_menu() {
    add_options_page(
        'Marketing',           // Page title
        'Marketing',           // Menu title
        'manage_options',      // Capability
        'rvk-marketing',       // Menu slug
        'rvk_marketing_page'   // Callback function
    );
}
add_action('admin_menu', 'rvk_add_marketing_menu');

// Enqueue admin scripts and styles for Marketing page
function rvk_marketing_admin_scripts($hook) {
    if ($hook !== 'settings_page_rvk-marketing') {
        return;
    }
    
    // Enqueue WordPress media uploader
    wp_enqueue_media();
    
    // Enqueue custom admin script
    wp_enqueue_script(
        'rvk-marketing-admin',
        get_template_directory_uri() . '/dist/marketing-admin.js',
        array('jquery'),
        filemtime(get_template_directory() . '/dist/marketing-admin.js'),
        true
    );
    
    // Enqueue custom admin styles
    wp_enqueue_style(
        'rvk-marketing-admin',
        get_template_directory_uri() . '/dist/css/admin/marketing.css',
        array(),
        filemtime(get_template_directory() . '/dist/css/admin/marketing.css')
    );
}
add_action('admin_enqueue_scripts', 'rvk_marketing_admin_scripts');

// Register settings
function rvk_register_marketing_settings() {
    register_setting('rvk_marketing_settings', 'rvk_marketing_banners', 'rvk_sanitize_marketing_settings');
}
add_action('admin_init', 'rvk_register_marketing_settings');

// Sanitize settings
function rvk_sanitize_marketing_settings($input) {
    $sanitized = array();
    
    // Sanitize Top Banner
    if (isset($input['top_banner'])) {
        $sanitized['top_banner'] = array(
            'desktop_image' => absint($input['top_banner']['desktop_image'] ?? 0),
            'tablet_image' => absint($input['top_banner']['tablet_image'] ?? 0),
            'mobile_image' => absint($input['top_banner']['mobile_image'] ?? 0),
            'link' => esc_url_raw($input['top_banner']['link'] ?? ''),
            'alt_text' => sanitize_text_field($input['top_banner']['alt_text'] ?? ''),
            'visibility' => array(
                'pages' => isset($input['top_banner']['visibility']['pages']),
                'posts' => isset($input['top_banner']['visibility']['posts']),
                'obavijesti' => isset($input['top_banner']['visibility']['obavijesti']),
                'servisne' => isset($input['top_banner']['visibility']['servisne']),
                'categories' => array_map('absint', $input['top_banner']['visibility']['categories'] ?? array()),
                'tags' => array_map('absint', $input['top_banner']['visibility']['tags'] ?? array()),
            )
        );
    }
    
    // Sanitize Small Banners
    if (isset($input['small_banners']) && is_array($input['small_banners'])) {
        $sanitized['small_banners'] = array();
        foreach ($input['small_banners'] as $banner) {
            if (!empty($banner['image'])) {
                $sanitized['small_banners'][] = array(
                    'image' => absint($banner['image']),
                    'link' => esc_url_raw($banner['link'] ?? ''),
                    'alt_text' => sanitize_text_field($banner['alt_text'] ?? ''),
                );
            }
        }
    }
    
    return $sanitized;
}

// Render Marketing settings page
function rvk_marketing_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    // Fix broken images
    if (isset($_POST['rvk_fix_images'])) {
        check_admin_referer('rvk_marketing_settings');
        $result = dev_theme_fix_broken_attachments();
        echo '<div class="notice notice-success"><p>Fixed ' . $result['fixed'] . ' image(s).</p></div>';
        if (!empty($result['issues'])) {
            echo '<div class="notice notice-warning"><p>Issues found:</p><ul>';
            foreach ($result['issues'] as $issue) {
                echo '<li>' . esc_html($issue) . '</li>';
            }
            echo '</ul></div>';
        }
    }

    // Save settings
    if (isset($_POST['rvk_marketing_submit'])) {
        check_admin_referer('rvk_marketing_settings');
        $sanitized_data = rvk_sanitize_marketing_settings($_POST['rvk_marketing_banners'] ?? []);
        update_option('rvk_marketing_banners', $sanitized_data);
        echo '<div class="notice notice-success"><p>Postavke su sačuvane.</p></div>';
    }
    
    $settings = get_option('rvk_marketing_banners', array(
        'top_banner' => array(
            'desktop_image' => 0,
            'tablet_image' => 0,
            'mobile_image' => 0,
            'link' => '',
            'alt_text' => '',
            'visibility' => array(
                'pages' => false,
                'posts' => false,
                'obavijesti' => false,
                'servisne' => false,
                'categories' => array(),
                'tags' => array(),
            )
        ),
        'small_banners' => array()
    ));
    
    // Get categories and tags
    $categories = get_categories(array('hide_empty' => false));
    $tags = get_tags(array('hide_empty' => false));
    
    ?>
    <div class="wrap">
        <h1>Marketing - Upravljanje Bannerima</h1>

        <!-- Fix Broken Images Button -->
        <div class="notice notice-info" style="margin: 20px 0; padding: 15px;">
            <p><strong>Problemi sa slikama?</strong> Ako vidite prazne slike ili slike koje se ne učitavaju, kliknite dugme ispod da automatski popravite putanje slika.</p>
            <form method="post" action="" style="display: inline;">
                <?php wp_nonce_field('rvk_marketing_settings'); ?>
                <button type="submit" name="rvk_fix_images" class="button button-secondary">Popravi Slike</button>
            </form>
        </div>

        <form method="post" action="">
            <?php wp_nonce_field('rvk_marketing_settings'); ?>
            
            <!-- TOP BANNER SECTION -->
            <div class="rvk-marketing-section">
                <h2>Top Banner (Horizontalni Banner)</h2>
                <p class="description">Banner koji se prikazuje iznad naslova stranice. Potrebno je dodati 3 verzije za različite uređaje.</p>
                
                <table class="form-table">
                    <!-- Desktop Image -->
                    <tr>
                        <th scope="row"><label>Desktop Slika</label></th>
                        <td>
                            <div class="rvk-image-upload">
                                <input type="hidden" 
                                       name="rvk_marketing_banners[top_banner][desktop_image]" 
                                       id="top_banner_desktop_image" 
                                       value="<?php echo esc_attr($settings['top_banner']['desktop_image']); ?>">
                                <button type="button" class="button rvk-upload-image" data-target="top_banner_desktop_image">
                                    Odaberi sliku
                                </button>
                                <button type="button" class="button rvk-remove-image" data-target="top_banner_desktop_image" style="<?php echo $settings['top_banner']['desktop_image'] ? '' : 'display:none;'; ?>">
                                    Ukloni sliku
                                </button>
                                <div class="rvk-image-preview" id="top_banner_desktop_image_preview">
                                    <?php if ($settings['top_banner']['desktop_image']): ?>
                                        <?php echo wp_get_attachment_image($settings['top_banner']['desktop_image'], 'medium'); ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Tablet Image -->
                    <tr>
                        <th scope="row"><label>Tablet Slika</label></th>
                        <td>
                            <div class="rvk-image-upload">
                                <input type="hidden" 
                                       name="rvk_marketing_banners[top_banner][tablet_image]" 
                                       id="top_banner_tablet_image" 
                                       value="<?php echo esc_attr($settings['top_banner']['tablet_image']); ?>">
                                <button type="button" class="button rvk-upload-image" data-target="top_banner_tablet_image">
                                    Odaberi sliku
                                </button>
                                <button type="button" class="button rvk-remove-image" data-target="top_banner_tablet_image" style="<?php echo $settings['top_banner']['tablet_image'] ? '' : 'display:none;'; ?>">
                                    Ukloni sliku
                                </button>
                                <div class="rvk-image-preview" id="top_banner_tablet_image_preview">
                                    <?php if ($settings['top_banner']['tablet_image']): ?>
                                        <?php echo wp_get_attachment_image($settings['top_banner']['tablet_image'], 'medium'); ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Mobile Image -->
                    <tr>
                        <th scope="row"><label>Mobile Slika</label></th>
                        <td>
                            <div class="rvk-image-upload">
                                <input type="hidden" 
                                       name="rvk_marketing_banners[top_banner][mobile_image]" 
                                       id="top_banner_mobile_image" 
                                       value="<?php echo esc_attr($settings['top_banner']['mobile_image']); ?>">
                                <button type="button" class="button rvk-upload-image" data-target="top_banner_mobile_image">
                                    Odaberi sliku
                                </button>
                                <button type="button" class="button rvk-remove-image" data-target="top_banner_mobile_image" style="<?php echo $settings['top_banner']['mobile_image'] ? '' : 'display:none;'; ?>">
                                    Ukloni sliku
                                </button>
                                <div class="rvk-image-preview" id="top_banner_mobile_image_preview">
                                    <?php if ($settings['top_banner']['mobile_image']): ?>
                                        <?php echo wp_get_attachment_image($settings['top_banner']['mobile_image'], 'medium'); ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Banner Link -->
                    <tr>
                        <th scope="row"><label for="top_banner_link">Link</label></th>
                        <td>
                            <input type="url" 
                                   name="rvk_marketing_banners[top_banner][link]" 
                                   id="top_banner_link" 
                                   value="<?php echo esc_attr($settings['top_banner']['link']); ?>" 
                                   class="regular-text"
                                   placeholder="https://example.com">
                        </td>
                    </tr>
                    
                    <!-- Alt Text -->
                    <tr>
                        <th scope="row"><label for="top_banner_alt_text">Alt Tekst</label></th>
                        <td>
                            <input type="text" 
                                   name="rvk_marketing_banners[top_banner][alt_text]" 
                                   id="top_banner_alt_text" 
                                   value="<?php echo esc_attr($settings['top_banner']['alt_text'] ?? ''); ?>" 
                                   class="regular-text"
                                   placeholder="Npr: Zimska rasprodaja - do 50% popusta">
                            <p class="description">Opis bannera za pristupačnost (screen readers). Ako ostavite prazno, koristit će se "Promotivni banner".</p>
                        </td>
                    </tr>
                    
                    <!-- Visibility Controls -->
                    <tr>
                        <th scope="row"><label>Prikaži na</label></th>
                        <td>
                            <fieldset>
                                <legend class="screen-reader-text"><span>Prikaži na</span></legend>
                                <label>
                                    <input type="checkbox" 
                                           name="rvk_marketing_banners[top_banner][visibility][pages]" 
                                           value="1" 
                                           <?php checked(isset($settings['top_banner']['visibility']['pages']) && $settings['top_banner']['visibility']['pages']); ?>>
                                    Stranice
                                </label><br>
                                <label>
                                    <input type="checkbox" 
                                           name="rvk_marketing_banners[top_banner][visibility][posts]" 
                                           value="1" 
                                           <?php checked(isset($settings['top_banner']['visibility']['posts']) && $settings['top_banner']['visibility']['posts']); ?>>
                                    Objave
                                </label><br>
                                <label>
                                    <input type="checkbox" 
                                           name="rvk_marketing_banners[top_banner][visibility][obavijesti]" 
                                           value="1" 
                                           <?php checked(isset($settings['top_banner']['visibility']['obavijesti']) && $settings['top_banner']['visibility']['obavijesti']); ?>>
                                    Obavijesti o smrti
                                </label><br>
                                <label>
                                    <input type="checkbox" 
                                           name="rvk_marketing_banners[top_banner][visibility][servisne]" 
                                           value="1" 
                                           <?php checked(isset($settings['top_banner']['visibility']['servisne']) && $settings['top_banner']['visibility']['servisne']); ?>>
                                    Servisne informacije
                                </label>
                                
                                <?php if (!empty($categories)): ?>
                                    <br><br><strong>Kategorije:</strong><br>
                                    <?php foreach ($categories as $category): ?>
                                        <label style="display: inline-block; margin-right: 15px;">
                                            <input type="checkbox" 
                                                   name="rvk_marketing_banners[top_banner][visibility][categories][]" 
                                                   value="<?php echo esc_attr($category->term_id); ?>" 
                                                   <?php checked(in_array($category->term_id, $settings['top_banner']['visibility']['categories'] ?? array())); ?>>
                                            <?php echo esc_html($category->name); ?>
                                        </label>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                
                                <?php if (!empty($tags)): ?>
                                    <br><br><strong>Tagovi:</strong><br>
                                    <?php foreach ($tags as $tag): ?>
                                        <label style="display: inline-block; margin-right: 15px;">
                                            <input type="checkbox" 
                                                   name="rvk_marketing_banners[top_banner][visibility][tags][]" 
                                                   value="<?php echo esc_attr($tag->term_id); ?>" 
                                                   <?php checked(in_array($tag->term_id, $settings['top_banner']['visibility']['tags'] ?? array())); ?>>
                                            <?php echo esc_html($tag->name); ?>
                                        </label>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </fieldset>
                        </td>
                    </tr>
                </table>
            </div>
            
            <hr style="margin: 40px 0;">
            
            <!-- SMALL BANNERS SECTION -->
            <div class="rvk-marketing-section">
                <h2>Mali Banneri (320px)</h2>
                <p class="description">Lista malih bannera. Možete ih preurediti povlačenjem.</p>
                
                <div id="small-banners-container">
                    <?php 
                    $small_banners = $settings['small_banners'] ?? array();
                    if (!empty($small_banners)):
                        foreach ($small_banners as $index => $banner): 
                    ?>
                        <div class="small-banner-item" draggable="true">
                            <div class="small-banner-drag-handle">☰</div>
                            <div class="small-banner-content">
                                <div class="rvk-image-upload">
                                    <input type="hidden" 
                                           name="rvk_marketing_banners[small_banners][<?php echo $index; ?>][image]" 
                                           class="small-banner-image-id" 
                                           value="<?php echo esc_attr($banner['image']); ?>">
                                    <button type="button" class="button rvk-upload-small-banner">
                                        Odaberi sliku
                                    </button>
                                    <div class="rvk-image-preview small-banner-preview">
                                        <?php if ($banner['image']): ?>
                                            <?php echo wp_get_attachment_image($banner['image'], 'thumbnail'); ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="small-banner-link-field">
                                    <label>Link:</label>
                                    <input type="url" 
                                           name="rvk_marketing_banners[small_banners][<?php echo $index; ?>][link]" 
                                           value="<?php echo esc_attr($banner['link']); ?>" 
                                           class="regular-text"
                                           placeholder="https://example.com">
                                </div>
                                <div class="small-banner-link-field">
                                    <label>Alt Tekst:</label>
                                    <input type="text" 
                                           name="rvk_marketing_banners[small_banners][<?php echo $index; ?>][alt_text]" 
                                           value="<?php echo esc_attr($banner['alt_text'] ?? ''); ?>" 
                                           class="regular-text"
                                           placeholder="Opis bannera">
                                </div>
                                <button type="button" class="button button-link-delete rvk-remove-small-banner">Ukloni</button>
                            </div>
                        </div>
                    <?php 
                        endforeach;
                    endif;
                    ?>
                </div>
                
                <button type="button" class="button button-primary" id="add-small-banner">+ Dodaj Mali Banner</button>
            </div>
            
            <hr style="margin: 40px 0;">
            
            <?php submit_button('Sačuvaj postavke', 'primary', 'rvk_marketing_submit'); ?>
        </form>
    </div>
    <?php
}
