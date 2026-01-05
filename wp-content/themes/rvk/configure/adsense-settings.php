<?php
/**
 * AdSense Settings Page
 * Manages Google AdSense ad placements across the website
 */

// Security: Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Add AdSense submenu under Settings
function rvk_add_adsense_menu() {
    add_options_page(
        'AdSense',              // Page title
        'AdSense',              // Menu title
        'manage_options',       // Capability
        'rvk-adsense',          // Menu slug
        'rvk_adsense_page'      // Callback function
    );
}
add_action('admin_menu', 'rvk_add_adsense_menu');

// Enqueue admin scripts and styles for AdSense page
function rvk_adsense_admin_scripts($hook) {
    if ($hook !== 'settings_page_rvk-adsense') {
        return;
    }

    // Enqueue custom admin script
    $js_file = get_template_directory() . '/dist/adsense-admin.js';
    if (file_exists($js_file)) {
        wp_enqueue_script(
            'rvk-adsense-admin',
            get_template_directory_uri() . '/dist/adsense-admin.js',
            array('jquery'),
            filemtime($js_file),
            true
        );
    }

    // Enqueue custom admin styles
    $css_file = get_template_directory() . '/dist/css/admin/adsense.css';
    if (file_exists($css_file)) {
        wp_enqueue_style(
            'rvk-adsense-admin',
            get_template_directory_uri() . '/dist/css/admin/adsense.css',
            array(),
            filemtime($css_file)
        );
    }
}
add_action('admin_enqueue_scripts', 'rvk_adsense_admin_scripts');

// Register settings
function rvk_register_adsense_settings() {
    register_setting('rvk_adsense_settings', 'rvk_adsense_settings', 'rvk_sanitize_adsense_settings');
}
add_action('admin_init', 'rvk_register_adsense_settings');

// Sanitize position settings helper
function rvk_sanitize_position_settings($input) {
    if (!is_array($input)) {
        return array();
    }

    return array(
        'enabled' => isset($input['enabled']),
        'ad_slot_id' => sanitize_text_field($input['ad_slot_id'] ?? ''),
        'ad_format' => sanitize_text_field($input['ad_format'] ?? 'auto'),
        'lazy_load' => isset($input['lazy_load']),
        'full_width_responsive' => isset($input['full_width_responsive']),
        'responsive' => array(
            'desktop' => array(
                'enabled' => isset($input['responsive']['desktop']['enabled']),
                'width' => absint($input['responsive']['desktop']['width'] ?? 728),
                'height' => absint($input['responsive']['desktop']['height'] ?? 90),
            ),
            'tablet' => array(
                'enabled' => isset($input['responsive']['tablet']['enabled']),
                'width' => absint($input['responsive']['tablet']['width'] ?? 468),
                'height' => absint($input['responsive']['tablet']['height'] ?? 60),
            ),
            'mobile' => array(
                'enabled' => isset($input['responsive']['mobile']['enabled']),
                'width' => absint($input['responsive']['mobile']['width'] ?? 320),
                'height' => absint($input['responsive']['mobile']['height'] ?? 50),
            ),
        ),
        'visibility' => array(
            'pages' => isset($input['visibility']['pages']),
            'posts' => isset($input['visibility']['posts']),
            'obavijesti' => isset($input['visibility']['obavijesti']),
            'servisne' => isset($input['visibility']['servisne']),
            'categories' => array_map('absint', $input['visibility']['categories'] ?? array()),
            'tags' => array_map('absint', $input['visibility']['tags'] ?? array()),
        ),
        'ab_testing' => array(
            'enabled' => isset($input['ab_testing']['enabled']),
            'variant_a_weight' => absint($input['ab_testing']['variant_a_weight'] ?? 50),
            'variant_b_slot_id' => sanitize_text_field($input['ab_testing']['variant_b_slot_id'] ?? ''),
            'variant_b_format' => sanitize_text_field($input['ab_testing']['variant_b_format'] ?? 'auto'),
        ),
    );
}

// Sanitize settings
function rvk_sanitize_adsense_settings($input) {
    $sanitized = array();

    // Global settings
    $sanitized['publisher_id'] = sanitize_text_field($input['publisher_id'] ?? '');
    $sanitized['enable_adsense'] = isset($input['enable_adsense']);

    // Sanitize each position
    foreach (['position_a', 'position_b', 'position_c'] as $position) {
        if (isset($input[$position])) {
            $sanitized[$position] = rvk_sanitize_position_settings($input[$position]);
        }
    }

    // Analytics
    if (isset($input['analytics'])) {
        $sanitized['analytics'] = array(
            'track_impressions' => isset($input['analytics']['track_impressions']),
            'send_to_gtm' => isset($input['analytics']['send_to_gtm']),
            'custom_event_name' => sanitize_key($input['analytics']['custom_event_name'] ?? 'adsense_impression'),
        );
    }

    return $sanitized;
}

// Get default settings
function rvk_get_default_adsense_settings() {
    return array(
        'publisher_id' => '',
        'enable_adsense' => false,
        'position_a' => array(
            'enabled' => false,
            'ad_slot_id' => '',
            'ad_format' => 'auto',
            'lazy_load' => true,
            'full_width_responsive' => true,
            'responsive' => array(
                'desktop' => array('enabled' => true, 'width' => 728, 'height' => 90),
                'tablet' => array('enabled' => true, 'width' => 468, 'height' => 60),
                'mobile' => array('enabled' => true, 'width' => 320, 'height' => 50),
            ),
            'visibility' => array(
                'pages' => false,
                'posts' => true,
                'obavijesti' => false,
                'servisne' => false,
                'categories' => array(),
                'tags' => array(),
            ),
            'ab_testing' => array(
                'enabled' => false,
                'variant_a_weight' => 50,
                'variant_b_slot_id' => '',
                'variant_b_format' => 'auto',
            ),
        ),
        'position_b' => array(
            'enabled' => false,
            'ad_slot_id' => '',
            'ad_format' => 'auto',
            'lazy_load' => true,
            'full_width_responsive' => true,
            'responsive' => array(
                'desktop' => array('enabled' => true, 'width' => 970, 'height' => 250),
                'tablet' => array('enabled' => true, 'width' => 728, 'height' => 90),
                'mobile' => array('enabled' => true, 'width' => 300, 'height' => 250),
            ),
            'visibility' => array(
                'pages' => false,
                'posts' => true,
                'obavijesti' => false,
                'servisne' => false,
                'categories' => array(),
                'tags' => array(),
            ),
            'ab_testing' => array(
                'enabled' => false,
                'variant_a_weight' => 50,
                'variant_b_slot_id' => '',
                'variant_b_format' => 'auto',
            ),
        ),
        'position_c' => array(
            'enabled' => false,
            'ad_slot_id' => '',
            'ad_format' => 'auto',
            'lazy_load' => true,
            'full_width_responsive' => true,
            'responsive' => array(
                'desktop' => array('enabled' => true, 'width' => 336, 'height' => 280),
                'tablet' => array('enabled' => true, 'width' => 300, 'height' => 250),
                'mobile' => array('enabled' => true, 'width' => 300, 'height' => 250),
            ),
            'visibility' => array(
                'pages' => false,
                'posts' => true,
                'obavijesti' => false,
                'servisne' => false,
                'categories' => array(),
                'tags' => array(),
            ),
            'ab_testing' => array(
                'enabled' => false,
                'variant_a_weight' => 50,
                'variant_b_slot_id' => '',
                'variant_b_format' => 'auto',
            ),
        ),
        'analytics' => array(
            'track_impressions' => true,
            'send_to_gtm' => true,
            'custom_event_name' => 'adsense_impression',
        ),
    );
}

// Render position section
function rvk_render_position_section($position, $label, $description, $settings, $categories, $tags) {
    $pos_settings = $settings[$position];
    ?>
    <div class="rvk-adsense-section">
        <h2><?php echo esc_html($label); ?></h2>
        <p class="description"><?php echo esc_html($description); ?></p>

        <table class="form-table">
            <!-- Enable Position -->
            <tr>
                <th scope="row"><label>Omogući</label></th>
                <td>
                    <label>
                        <input type="checkbox"
                               name="rvk_adsense_settings[<?php echo $position; ?>][enabled]"
                               value="1"
                               <?php checked($pos_settings['enabled']); ?>>
                        Omogući ovu poziciju reklame
                    </label>
                </td>
            </tr>

            <!-- Ad Slot ID -->
            <tr>
                <th scope="row"><label>ID Reklamnog Slota</label></th>
                <td>
                    <input type="text"
                           name="rvk_adsense_settings[<?php echo $position; ?>][ad_slot_id]"
                           value="<?php echo esc_attr($pos_settings['ad_slot_id']); ?>"
                           class="regular-text"
                           placeholder="1234567890">
                    <p class="description">Vaš AdSense ID reklamne jedinice (pronađite u AdSense kontrolnoj tabli)</p>
                </td>
            </tr>

            <!-- Ad Format -->
            <tr>
                <th scope="row"><label>Format Reklame</label></th>
                <td>
                    <select name="rvk_adsense_settings[<?php echo $position; ?>][ad_format]">
                        <option value="auto" <?php selected($pos_settings['ad_format'], 'auto'); ?>>Auto (Preporučeno)</option>
                        <option value="rectangle" <?php selected($pos_settings['ad_format'], 'rectangle'); ?>>Pravougaonik</option>
                        <option value="horizontal" <?php selected($pos_settings['ad_format'], 'horizontal'); ?>>Horizontalno</option>
                        <option value="vertical" <?php selected($pos_settings['ad_format'], 'vertical'); ?>>Vertikalno</option>
                    </select>
                </td>
            </tr>

            <!-- Lazy Load -->
            <tr>
                <th scope="row"><label>Lenjivo Učitavanje</label></th>
                <td>
                    <label>
                        <input type="checkbox"
                               name="rvk_adsense_settings[<?php echo $position; ?>][lazy_load]"
                               value="1"
                               <?php checked($pos_settings['lazy_load']); ?>>
                        Omogući lenjivo učitavanje (učitava reklamu kada je vidljiva - poboljšava brzinu stranice)
                    </label>
                </td>
            </tr>

            <!-- Full Width Responsive -->
            <tr>
                <th scope="row"><label>Responzivnost</label></th>
                <td>
                    <label>
                        <input type="checkbox"
                               name="rvk_adsense_settings[<?php echo $position; ?>][full_width_responsive]"
                               value="1"
                               <?php checked($pos_settings['full_width_responsive']); ?>>
                        Puna širina responzivna (preporučeno)
                    </label>
                </td>
            </tr>

            <!-- Responsive Units -->
            <tr>
                <th scope="row"><label>Responzivne Veličine</label></th>
                <td>
                    <fieldset>
                        <legend class="screen-reader-text"><span>Responzivne Veličine</span></legend>

                        <!-- Desktop -->
                        <label>
                            <input type="checkbox"
                                   name="rvk_adsense_settings[<?php echo $position; ?>][responsive][desktop][enabled]"
                                   value="1"
                                   <?php checked($pos_settings['responsive']['desktop']['enabled']); ?>>
                            Desktop (≥1024px):
                        </label>
                        <input type="number"
                               name="rvk_adsense_settings[<?php echo $position; ?>][responsive][desktop][width]"
                               value="<?php echo esc_attr($pos_settings['responsive']['desktop']['width']); ?>"
                               style="width: 80px;"
                               min="1"> ×
                        <input type="number"
                               name="rvk_adsense_settings[<?php echo $position; ?>][responsive][desktop][height]"
                               value="<?php echo esc_attr($pos_settings['responsive']['desktop']['height']); ?>"
                               style="width: 80px;"
                               min="1"> px
                        <br><br>

                        <!-- Tablet -->
                        <label>
                            <input type="checkbox"
                                   name="rvk_adsense_settings[<?php echo $position; ?>][responsive][tablet][enabled]"
                                   value="1"
                                   <?php checked($pos_settings['responsive']['tablet']['enabled']); ?>>
                            Tablet (768-1023px):
                        </label>
                        <input type="number"
                               name="rvk_adsense_settings[<?php echo $position; ?>][responsive][tablet][width]"
                               value="<?php echo esc_attr($pos_settings['responsive']['tablet']['width']); ?>"
                               style="width: 80px;"
                               min="1"> ×
                        <input type="number"
                               name="rvk_adsense_settings[<?php echo $position; ?>][responsive][tablet][height]"
                               value="<?php echo esc_attr($pos_settings['responsive']['tablet']['height']); ?>"
                               style="width: 80px;"
                               min="1"> px
                        <br><br>

                        <!-- Mobile -->
                        <label>
                            <input type="checkbox"
                                   name="rvk_adsense_settings[<?php echo $position; ?>][responsive][mobile][enabled]"
                                   value="1"
                                   <?php checked($pos_settings['responsive']['mobile']['enabled']); ?>>
                            Mobilni (<768px):
                        </label>
                        <input type="number"
                               name="rvk_adsense_settings[<?php echo $position; ?>][responsive][mobile][width]"
                               value="<?php echo esc_attr($pos_settings['responsive']['mobile']['width']); ?>"
                               style="width: 80px;"
                               min="1"> ×
                        <input type="number"
                               name="rvk_adsense_settings[<?php echo $position; ?>][responsive][mobile][height]"
                               value="<?php echo esc_attr($pos_settings['responsive']['mobile']['height']); ?>"
                               style="width: 80px;"
                               min="1"> px
                    </fieldset>
                </td>
            </tr>

            <!-- Visibility Controls -->
            <tr>
                <th scope="row"><label>Prikaži Na</label></th>
                <td>
                    <fieldset>
                        <legend class="screen-reader-text"><span>Prikaži Na</span></legend>
                        <label>
                            <input type="checkbox"
                                   name="rvk_adsense_settings[<?php echo $position; ?>][visibility][pages]"
                                   value="1"
                                   <?php checked($pos_settings['visibility']['pages']); ?>>
                            Stranice
                        </label><br>
                        <label>
                            <input type="checkbox"
                                   name="rvk_adsense_settings[<?php echo $position; ?>][visibility][posts]"
                                   value="1"
                                   <?php checked($pos_settings['visibility']['posts']); ?>>
                            Postovi
                        </label><br>
                        <label>
                            <input type="checkbox"
                                   name="rvk_adsense_settings[<?php echo $position; ?>][visibility][obavijesti]"
                                   value="1"
                                   <?php checked($pos_settings['visibility']['obavijesti']); ?>>
                            Obavijesti o smrti
                        </label><br>
                        <label>
                            <input type="checkbox"
                                   name="rvk_adsense_settings[<?php echo $position; ?>][visibility][servisne]"
                                   value="1"
                                   <?php checked($pos_settings['visibility']['servisne']); ?>>
                            Servisne informacije
                        </label>

                        <?php if (!empty($categories)): ?>
                            <br><br><strong>Kategorije:</strong><br>
                            <?php foreach ($categories as $category): ?>
                                <label style="display: inline-block; margin-right: 15px;">
                                    <input type="checkbox"
                                           name="rvk_adsense_settings[<?php echo $position; ?>][visibility][categories][]"
                                           value="<?php echo esc_attr($category->term_id); ?>"
                                           <?php checked(in_array($category->term_id, $pos_settings['visibility']['categories'] ?? array())); ?>>
                                    <?php echo esc_html($category->name); ?>
                                </label>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <?php if (!empty($tags)): ?>
                            <br><br><strong>Oznake:</strong><br>
                            <?php foreach ($tags as $tag): ?>
                                <label style="display: inline-block; margin-right: 15px;">
                                    <input type="checkbox"
                                           name="rvk_adsense_settings[<?php echo $position; ?>][visibility][tags][]"
                                           value="<?php echo esc_attr($tag->term_id); ?>"
                                           <?php checked(in_array($tag->term_id, $pos_settings['visibility']['tags'] ?? array())); ?>>
                                    <?php echo esc_html($tag->name); ?>
                                </label>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </fieldset>
                </td>
            </tr>

            <!-- A/B Testing -->
            <tr>
                <th scope="row"><label>A/B Testiranje</label></th>
                <td>
                    <fieldset>
                        <legend class="screen-reader-text"><span>A/B Testiranje</span></legend>
                        <label>
                            <input type="checkbox"
                                   name="rvk_adsense_settings[<?php echo $position; ?>][ab_testing][enabled]"
                                   value="1"
                                   <?php checked($pos_settings['ab_testing']['enabled']); ?>>
                            Omogući A/B testiranje
                        </label>
                        <br><br>

                        <label>Težina Varijante A (%):</label>
                        <input type="number"
                               name="rvk_adsense_settings[<?php echo $position; ?>][ab_testing][variant_a_weight]"
                               value="<?php echo esc_attr($pos_settings['ab_testing']['variant_a_weight']); ?>"
                               min="0"
                               max="100"
                               style="width: 80px;">
                        <p class="description">50 = 50/50 podjela, 75 = 75% varijanta A, 25% varijanta B</p>

                        <label>ID Slota Varijante B:</label>
                        <input type="text"
                               name="rvk_adsense_settings[<?php echo $position; ?>][ab_testing][variant_b_slot_id]"
                               value="<?php echo esc_attr($pos_settings['ab_testing']['variant_b_slot_id']); ?>"
                               class="regular-text"
                               placeholder="9876543210">
                        <br><br>

                        <label>Format Varijante B:</label>
                        <select name="rvk_adsense_settings[<?php echo $position; ?>][ab_testing][variant_b_format]">
                            <option value="auto" <?php selected($pos_settings['ab_testing']['variant_b_format'], 'auto'); ?>>Auto</option>
                            <option value="rectangle" <?php selected($pos_settings['ab_testing']['variant_b_format'], 'rectangle'); ?>>Pravougaonik</option>
                            <option value="horizontal" <?php selected($pos_settings['ab_testing']['variant_b_format'], 'horizontal'); ?>>Horizontalno</option>
                            <option value="vertical" <?php selected($pos_settings['ab_testing']['variant_b_format'], 'vertical'); ?>>Vertikalno</option>
                        </select>
                    </fieldset>
                </td>
            </tr>
        </table>
    </div>
    <?php
}

// Render AdSense settings page
function rvk_adsense_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    // Save settings
    if (isset($_POST['rvk_adsense_submit'])) {
        check_admin_referer('rvk_adsense_settings');
        $sanitized_data = rvk_sanitize_adsense_settings($_POST['rvk_adsense_settings'] ?? []);
        update_option('rvk_adsense_settings', $sanitized_data);
        echo '<div class="notice notice-success"><p>Postavke su uspješno sačuvane.</p></div>';
    }

    $settings = get_option('rvk_adsense_settings', rvk_get_default_adsense_settings());

    // Get categories and tags
    $categories = get_categories(array('hide_empty' => false));
    $tags = get_tags(array('hide_empty' => false));

    ?>
    <div class="wrap">
        <h1>AdSense Postavke</h1>
        <p class="description">Konfigurišite Google AdSense pozicije reklama za vašu web stranicu. Reklame će se prikazivati u člancima na osnovu postavki ispod.</p>

        <form method="post" action="">
            <?php wp_nonce_field('rvk_adsense_settings'); ?>

            <!-- GLOBAL SETTINGS SECTION -->
            <div class="rvk-adsense-section">
                <h2 class="rvk-section-toggle">Globalne Postavke</h2>

                <table class="form-table rvk-section-content">
                    <!-- Enable AdSense -->
                    <tr>
                        <th scope="row"><label>Omogući AdSense</label></th>
                        <td>
                            <label>
                                <input type="checkbox"
                                       name="rvk_adsense_settings[enable_adsense]"
                                       value="1"
                                       <?php checked($settings['enable_adsense']); ?>>
                                Omogući AdSense globalno (glavni prekidač)
                            </label>
                        </td>
                    </tr>

                    <!-- Publisher ID -->
                    <tr>
                        <th scope="row"><label>Publisher ID</label></th>
                        <td>
                            <input type="text"
                                   name="rvk_adsense_settings[publisher_id]"
                                   value="<?php echo esc_attr($settings['publisher_id']); ?>"
                                   class="regular-text"
                                   placeholder="ca-pub-XXXXXXXXXXXXXXXX">
                            <p class="description">Vaš AdSense Publisher ID (mora počinjati sa "ca-pub-")</p>
                        </td>
                    </tr>
                </table>
            </div>

            <hr style="margin: 40px 0;">

            <!-- POSITION A -->
            <?php
            rvk_render_position_section(
                'position_a',
                'Pozicija A: Ispod Naslova, Iznad Istaknute Slike',
                'Reklama prikazana ispod naslova članka i iznad istaknute slike. Preporučeno: 728×90 (desktop), 320×50 (mobitel)',
                $settings,
                $categories,
                $tags
            );
            ?>

            <hr style="margin: 40px 0;">

            <!-- POSITION B -->
            <?php
            rvk_render_position_section(
                'position_b',
                'Pozicija B: Iznad Sadržaja Članka',
                'Reklama prikazana nakon meta podataka i istaknute slike, prije glavnog sadržaja. Preporučeno: 970×250 (desktop), 300×250 (mobitel)',
                $settings,
                $categories,
                $tags
            );
            ?>

            <hr style="margin: 40px 0;">

            <!-- POSITION C -->
            <?php
            rvk_render_position_section(
                'position_c',
                'Pozicija C: Ispod Sadržaja Članka',
                'Reklama prikazana nakon sadržaja članka, prije dugmadi za dijeljenje. Preporučeno: 336×280 (desktop), 300×250 (mobitel)',
                $settings,
                $categories,
                $tags
            );
            ?>

            <hr style="margin: 40px 0;">

            <!-- ANALYTICS SECTION -->
            <div class="rvk-adsense-section">
                <h2 class="rvk-section-toggle">Analitika i Praćenje</h2>

                <table class="form-table rvk-section-content">
                    <!-- Track Impressions -->
                    <tr>
                        <th scope="row"><label>Prati Prikaze</label></th>
                        <td>
                            <label>
                                <input type="checkbox"
                                       name="rvk_adsense_settings[analytics][track_impressions]"
                                       value="1"
                                       <?php checked($settings['analytics']['track_impressions']); ?>>
                                Šalji događaje prikaza u analitiku
                            </label>
                        </td>
                    </tr>

                    <!-- Send to GTM -->
                    <tr>
                        <th scope="row"><label>Google Tag Manager</label></th>
                        <td>
                            <label>
                                <input type="checkbox"
                                       name="rvk_adsense_settings[analytics][send_to_gtm]"
                                       value="1"
                                       <?php checked($settings['analytics']['send_to_gtm']); ?>>
                                Šalji događaje u Google Tag Manager
                            </label>
                        </td>
                    </tr>

                    <!-- Custom Event Name -->
                    <tr>
                        <th scope="row"><label>Prilagođeno Ime Događaja</label></th>
                        <td>
                            <input type="text"
                                   name="rvk_adsense_settings[analytics][custom_event_name]"
                                   value="<?php echo esc_attr($settings['analytics']['custom_event_name']); ?>"
                                   class="regular-text"
                                   placeholder="adsense_impression">
                            <p class="description">Ime događaja za Google Analytics / GTM praćenje</p>
                        </td>
                    </tr>
                </table>
            </div>

            <hr style="margin: 40px 0;">

            <?php submit_button('Sačuvaj Postavke', 'primary', 'rvk_adsense_submit'); ?>
        </form>

        <!-- Help Section -->
        <div class="rvk-adsense-help" style="margin-top: 40px; padding: 20px; background: #f9f9f9; border-left: 4px solid #2271b1;">
            <h3>Potrebna Pomoć?</h3>
            <ul>
                <li><strong>Publisher ID:</strong> Pronađite ovo u vašem AdSense računu pod "Račun" → "Informacije o Računu"</li>
                <li><strong>ID Reklamnog Slota:</strong> Kreirajte reklamne jedinice u AdSense kontrolnoj tabli i kopirajte ID slota iz koda reklame</li>
                <li><strong>Lenjivo Učitavanje:</strong> Poboljšava brzinu stranice učitavanjem reklama samo kada su vidljive. Preporučeno za bolje Core Web Vitals rezultate.</li>
                <li><strong>A/B Testiranje:</strong> Testirajte različite formate reklama kako biste pronašli najprofitabilniju konfiguraciju. Pokrenite testove minimalno 2 sedmice.</li>
                <li><strong>Performanse:</strong> Ova integracija je optimizovana za Core Web Vitals (CLS < 0.1, LCP < 2.5s)</li>
            </ul>
            <p><strong>Važno:</strong> Uvjerite se da je vaša stranica odobrena za AdSense prije omogućavanja reklama. Kršenje AdSense politika može dovesti do suspenzije računa.</p>
        </div>
    </div>
    <?php
}
