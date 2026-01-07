<?php
/**
 * SEO & AEO Settings Admin Page
 * Configure AI providers, organization schema, and default SEO settings
 * Tailored by Alen Melkić
 */

// Security: Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class RVK_SEO_Settings_Page {

    public function __construct() {
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_media_uploader'));
    }

    /**
     * Enqueue media uploader scripts
     */
    public function enqueue_media_uploader($hook) {
        // Only load on our settings page
        if ($hook !== 'settings_page_seo-aeo-settings') {
            return;
        }

        // Enqueue WordPress media uploader
        wp_enqueue_media();
    }

    /**
     * Add settings page to WordPress admin
     */
    public function add_settings_page() {
        add_options_page(
            'SEO & AEO Postavke',
            'SEO & AEO',
            'manage_options',
            'seo-aeo-settings',
            array($this, 'render_settings_page')
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        // AI Provider Settings
        register_setting('rvk_seo_settings', 'rvk_seo_ai_provider', array(
            'sanitize_callback' => 'sanitize_text_field',
            'default' => 'auto'
        ));

        register_setting('rvk_seo_settings', 'dev_theme_openai_api_key', array(
            'sanitize_callback' => 'sanitize_text_field',
        ));

        register_setting('rvk_seo_settings', 'dev_theme_gemini_api_key', array(
            'sanitize_callback' => 'sanitize_text_field',
        ));

        register_setting('rvk_seo_settings', 'rvk_seo_ai_enabled', array(
            'sanitize_callback' => 'rest_sanitize_boolean',
            'default' => true,
        ));

        register_setting('rvk_seo_settings', 'rvk_seo_auto_analysis_enabled', array(
            'sanitize_callback' => 'rest_sanitize_boolean',
            'default' => true,
        ));

        // Organization Schema Settings
        register_setting('rvk_seo_settings', 'rvk_seo_organization_name', array(
            'sanitize_callback' => 'sanitize_text_field',
        ));

        register_setting('rvk_seo_settings', 'rvk_seo_organization_logo', array(
            'sanitize_callback' => 'absint',
        ));

        register_setting('rvk_seo_settings', 'rvk_seo_social_facebook', array(
            'sanitize_callback' => 'esc_url_raw',
        ));

        register_setting('rvk_seo_settings', 'rvk_seo_social_twitter', array(
            'sanitize_callback' => 'esc_url_raw',
        ));

        register_setting('rvk_seo_settings', 'rvk_seo_social_instagram', array(
            'sanitize_callback' => 'esc_url_raw',
        ));

        register_setting('rvk_seo_settings', 'rvk_seo_social_linkedin', array(
            'sanitize_callback' => 'esc_url_raw',
        ));

        // Default SEO Settings
        register_setting('rvk_seo_settings', 'rvk_seo_default_og_image', array(
            'sanitize_callback' => 'absint',
        ));

        register_setting('rvk_seo_settings', 'rvk_seo_twitter_handle', array(
            'sanitize_callback' => 'sanitize_text_field',
        ));

        register_setting('rvk_seo_settings', 'rvk_seo_homepage_description', array(
            'sanitize_callback' => 'sanitize_textarea_field',
        ));

        // Template Settings
        register_setting('rvk_seo_settings', 'rvk_seo_title_separator', array(
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '|'
        ));

        // Compatibility Settings
        register_setting('rvk_seo_settings', 'rvk_seo_override_plugins', array(
            'sanitize_callback' => 'rest_sanitize_boolean',
            'default' => false,
        ));

        // Sitemap Settings
        register_setting('rvk_seo_settings', 'rvk_seo_sitemap_enabled', array(
            'sanitize_callback' => 'rest_sanitize_boolean',
            'default' => true,
        ));

        register_setting('rvk_seo_settings', 'rvk_seo_sitemap_exclude_post_types', array(
            'sanitize_callback' => array($this, 'sanitize_array'),
            'default' => array(),
        ));

        register_setting('rvk_seo_settings', 'rvk_seo_sitemap_exclude_taxonomies', array(
            'sanitize_callback' => array($this, 'sanitize_array'),
            'default' => array(),
        ));

        register_setting('rvk_seo_settings', 'rvk_seo_sitemap_include_images', array(
            'sanitize_callback' => 'rest_sanitize_boolean',
            'default' => true,
        ));

        register_setting('rvk_seo_settings', 'rvk_seo_sitemap_entries_per_page', array(
            'sanitize_callback' => 'absint',
            'default' => 2000,
        ));
    }

    /**
     * Sanitize array for settings
     */
    public function sanitize_array($input) {
        if (!is_array($input)) {
            return array();
        }
        return array_map('sanitize_text_field', $input);
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Save settings
        if (isset($_POST['submit']) && check_admin_referer('rvk_seo_settings')) {
            // AI Provider settings
            update_option('rvk_seo_ai_provider', sanitize_text_field($_POST['ai_provider']));
            update_option('dev_theme_openai_api_key', sanitize_text_field($_POST['openai_api_key']));
            update_option('dev_theme_gemini_api_key', sanitize_text_field($_POST['gemini_api_key']));
            update_option('rvk_seo_ai_enabled', isset($_POST['ai_enabled']));
            update_option('rvk_seo_auto_analysis_enabled', isset($_POST['auto_analysis_enabled']));

            // Organization settings
            update_option('rvk_seo_organization_name', sanitize_text_field($_POST['organization_name']));
            update_option('rvk_seo_organization_logo', absint($_POST['organization_logo']));
            update_option('rvk_seo_social_facebook', esc_url_raw($_POST['social_facebook']));
            update_option('rvk_seo_social_twitter', esc_url_raw($_POST['social_twitter']));
            update_option('rvk_seo_social_instagram', esc_url_raw($_POST['social_instagram']));
            update_option('rvk_seo_social_linkedin', esc_url_raw($_POST['social_linkedin']));

            // Default SEO settings
            update_option('rvk_seo_default_og_image', absint($_POST['default_og_image']));
            update_option('rvk_seo_twitter_handle', sanitize_text_field($_POST['twitter_handle']));
            update_option('rvk_seo_homepage_description', sanitize_textarea_field($_POST['homepage_description']));

            // Title separator - handle custom value
            $separator = sanitize_text_field($_POST['title_separator']);
            if ($separator === 'custom' && !empty($_POST['title_separator_custom'])) {
                $separator = sanitize_text_field($_POST['title_separator_custom']);
            }
            update_option('rvk_seo_title_separator', $separator);

            // Compatibility settings
            update_option('rvk_seo_override_plugins', isset($_POST['override_plugins']));

            // Sitemap settings
            update_option('rvk_seo_sitemap_enabled', isset($_POST['sitemap_enabled']));
            update_option('rvk_seo_sitemap_exclude_post_types', isset($_POST['sitemap_exclude_post_types']) ? $_POST['sitemap_exclude_post_types'] : array());
            update_option('rvk_seo_sitemap_exclude_taxonomies', isset($_POST['sitemap_exclude_taxonomies']) ? $_POST['sitemap_exclude_taxonomies'] : array());
            update_option('rvk_seo_sitemap_include_images', isset($_POST['sitemap_include_images']));
            update_option('rvk_seo_sitemap_entries_per_page', absint($_POST['sitemap_entries_per_page']));

            // Flush rewrite rules to update sitemap
            flush_rewrite_rules();

            echo '<div class="notice notice-success"><p>Postavke uspješno sačuvane!</p></div>';
        }

        // Get current values
        $ai_provider = get_option('rvk_seo_ai_provider', 'auto');
        $gemini_key = get_option('dev_theme_gemini_api_key', '');
        $openai_key = get_option('dev_theme_openai_api_key', '');
        $ai_enabled = get_option('rvk_seo_ai_enabled', true);

        $org_name = get_option('rvk_seo_organization_name', get_bloginfo('name'));
        $org_logo = get_option('rvk_seo_organization_logo');
        $social_facebook = get_option('rvk_seo_social_facebook', '');
        $social_twitter = get_option('rvk_seo_social_twitter', '');
        $social_instagram = get_option('rvk_seo_social_instagram', '');
        $social_linkedin = get_option('rvk_seo_social_linkedin', '');

        $default_og_image = get_option('rvk_seo_default_og_image');
        $twitter_handle = get_option('rvk_seo_twitter_handle', '');
        $homepage_description = get_option('rvk_seo_homepage_description', get_bloginfo('description'));
        $title_separator = get_option('rvk_seo_title_separator', '-');

        $override_plugins = get_option('rvk_seo_override_plugins', false);

        // Sitemap settings
        $sitemap_enabled = get_option('rvk_seo_sitemap_enabled', true);
        $sitemap_exclude_post_types = get_option('rvk_seo_sitemap_exclude_post_types', array());
        $sitemap_exclude_taxonomies = get_option('rvk_seo_sitemap_exclude_taxonomies', array());
        $sitemap_include_images = get_option('rvk_seo_sitemap_include_images', true);
        $sitemap_entries_per_page = get_option('rvk_seo_sitemap_entries_per_page', 2000);

        // Mask API keys
        $gemini_key_masked = !empty($gemini_key) ? substr($gemini_key, 0, 10) . '...' : '';
        $openai_key_masked = !empty($openai_key) ? substr($openai_key, 0, 10) . '...' : '';

        // Check compatibility
        $compat_status = rvk_get_compatibility_status();

        ?>
        <div class="wrap">
            <h1>SEO & AEO Optimization - Postavke</h1>

            <?php if ($compat_status['has_seo_plugin']): ?>
            <div class="notice notice-<?php echo esc_attr($compat_status['theme_handles_seo'] ? 'warning' : 'info'); ?>">
                <p><?php echo esc_html($compat_status['message']); ?></p>
            </div>
            <?php endif; ?>

            <form method="post" action="">
                <?php wp_nonce_field('rvk_seo_settings'); ?>

                <div class="card" style="max-width: 900px;">
                    <h2>AI Provider Konfiguracija</h2>

                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label>Omogući AI Funkcije</label>
                            </th>
                            <td>
                                <input type="checkbox"
                                       name="ai_enabled"
                                       value="1"
                                       <?php checked($ai_enabled, true); ?>>
                                <p class="description">Omogući AI optimizaciju za SEO/AEO</p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label>Automatska SEO Analiza</label>
                            </th>
                            <td>
                                <input type="checkbox"
                                       name="auto_analysis_enabled"
                                       value="1"
                                       <?php checked(get_option('rvk_seo_auto_analysis_enabled', true), true); ?>>
                                <p class="description">
                                    Automatski analiziraj sadržaj dok pišeš (bez AI poziva - samo lokalna analiza).<br>
                                    <strong>Napomena:</strong> Ovo NE koristi API - samo računa riječi, čitljivost i SEO score.
                                </p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label>AI Provider</label>
                            </th>
                            <td>
                                <label>
                                    <input type="radio"
                                           name="ai_provider"
                                           value="auto"
                                           <?php checked($ai_provider, 'auto'); ?>>
                                    <strong>Auto (Prioritet: Gemini → OpenAI)</strong>
                                </label><br>

                                <label>
                                    <input type="radio"
                                           name="ai_provider"
                                           value="gemini"
                                           <?php checked($ai_provider, 'gemini'); ?>>
                                    Google Gemini (besplatno 60 req/min)
                                </label><br>

                                <label>
                                    <input type="radio"
                                           name="ai_provider"
                                           value="openai"
                                           <?php checked($ai_provider, 'openai'); ?>>
                                    OpenAI GPT-4o Mini
                                </label>

                                <p class="description">Auto će koristiti prvi konfigurisani provider</p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="gemini_api_key">Gemini API Key</label>
                            </th>
                            <td>
                                <input type="text"
                                       name="gemini_api_key"
                                       id="gemini_api_key"
                                       value="<?php echo esc_attr($gemini_key); ?>"
                                       class="regular-text"
                                       placeholder="Unesite Gemini API ključ">

                                <?php if (!empty($gemini_key)): ?>
                                    <p class="description" style="color: green;">
                                        ✓ Gemini API ključ konfigurisan: <?php echo esc_html($gemini_key_masked); ?>
                                    </p>
                                <?php else: ?>
                                    <p class="description">
                                        Nabavite API ključ: <a href="https://makersuite.google.com/app/apikey" target="_blank">Google AI Studio</a>
                                    </p>
                                <?php endif; ?>
                                <p class="description">
                                    Besplatno: 60 zahtjeva po minuti
                                </p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="openai_api_key">OpenAI API Key</label>
                            </th>
                            <td>
                                <input type="text"
                                       name="openai_api_key"
                                       id="openai_api_key"
                                       value="<?php echo esc_attr($openai_key); ?>"
                                       class="regular-text"
                                       placeholder="Unesite OpenAI API ključ">

                                <p class="description">
                                    <a href="<?php echo admin_url('options-general.php?page=seo-api-usage'); ?>" target="_blank">
                                        📊 Pogledaj API Usage Statistics
                                    </a>
                                </p>

                                <?php if (!empty($openai_key)): ?>
                                    <p class="description" style="color: green;">
                                        ✓ OpenAI API ključ konfigurisan: <?php echo esc_html($openai_key_masked); ?>
                                    </p>
                                <?php else: ?>
                                    <p class="description">
                                        Nabavite API ključ: <a href="https://platform.openai.com/api-keys" target="_blank">OpenAI Platform</a>
                                    </p>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="card" style="max-width: 900px; margin-top: 20px;">
                    <h2>Organization Schema Podaci</h2>

                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="organization_name">Naziv Organizacije</label>
                            </th>
                            <td>
                                <input type="text"
                                       name="organization_name"
                                       id="organization_name"
                                       value="<?php echo esc_attr($org_name); ?>"
                                       class="regular-text">
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label>Logo Organizacije</label>
                            </th>
                            <td>
                                <input type="hidden" name="organization_logo" id="organization_logo" value="<?php echo esc_attr($org_logo); ?>">
                                <div style="display: flex; align-items: flex-start; gap: 10px;">
                                    <div>
                                        <button type="button" class="button upload-image-button" data-target="organization_logo">
                                            Odaberi Logo
                                        </button>
                                        <button type="button" class="button remove-image-button" data-target="organization_logo" <?php echo !$org_logo ? 'style="display:none;"' : ''; ?>>
                                            Ukloni
                                        </button>
                                    </div>
                                    <div class="image-preview" id="preview_organization_logo" <?php echo !$org_logo ? 'style="display:none;"' : ''; ?>>
                                        <?php if ($org_logo):
                                            $image_url = wp_get_attachment_image_url($org_logo, 'thumbnail');
                                            if (!$image_url) {
                                                $image_url = wp_get_attachment_url($org_logo);
                                            }
                                            // Check if it's an SVG
                                            $is_svg = (strpos($image_url, '.svg') !== false);
                                            $style = $is_svg ? 'style="width: 150px; height: auto;"' : '';
                                        ?>
                                            <img src="<?php echo esc_url($image_url); ?>" alt="Organization Logo Preview" <?php echo $style; ?>>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="social_facebook">Facebook URL</label>
                            </th>
                            <td>
                                <input type="url"
                                       name="social_facebook"
                                       id="social_facebook"
                                       value="<?php echo esc_url($social_facebook); ?>"
                                       class="regular-text"
                                       placeholder="https://facebook.com/vaša-stranica">
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="social_twitter">Twitter/X URL</label>
                            </th>
                            <td>
                                <input type="url"
                                       name="social_twitter"
                                       id="social_twitter"
                                       value="<?php echo esc_url($social_twitter); ?>"
                                       class="regular-text"
                                       placeholder="https://twitter.com/vaš-nalog">
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="social_instagram">Instagram URL</label>
                            </th>
                            <td>
                                <input type="url"
                                       name="social_instagram"
                                       id="social_instagram"
                                       value="<?php echo esc_url($social_instagram); ?>"
                                       class="regular-text"
                                       placeholder="https://instagram.com/vaš-nalog">
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="social_linkedin">LinkedIn URL</label>
                            </th>
                            <td>
                                <input type="url"
                                       name="social_linkedin"
                                       id="social_linkedin"
                                       value="<?php echo esc_url($social_linkedin); ?>"
                                       class="regular-text"
                                       placeholder="https://linkedin.com/company/vaša-kompanija">
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="card" style="max-width: 900px; margin-top: 20px;">
                    <h2>Defaultne SEO Postavke</h2>

                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label>Default OG Image</label>
                            </th>
                            <td>
                                <input type="hidden" name="default_og_image" id="default_og_image" value="<?php echo esc_attr($default_og_image); ?>">
                                <div style="display: flex; align-items: flex-start; gap: 10px; margin-bottom: 10px;">
                                    <div>
                                        <button type="button" class="button upload-image-button" data-target="default_og_image">
                                            Odaberi Sliku
                                        </button>
                                        <button type="button" class="button remove-image-button" data-target="default_og_image" <?php echo !$default_og_image ? 'style="display:none;"' : ''; ?>>
                                            Ukloni
                                        </button>
                                    </div>
                                    <div class="image-preview" id="preview_default_og_image" <?php echo !$default_og_image ? 'style="display:none;"' : ''; ?>>
                                        <?php if ($default_og_image):
                                            $og_image_url = wp_get_attachment_image_url($default_og_image, 'thumbnail');
                                            if (!$og_image_url) {
                                                $og_image_url = wp_get_attachment_url($default_og_image);
                                            }
                                            // Check if it's an SVG
                                            $is_svg_og = (strpos($og_image_url, '.svg') !== false);
                                            $style_og = $is_svg_og ? 'style="width: 150px; height: auto;"' : '';
                                        ?>
                                            <img src="<?php echo esc_url($og_image_url); ?>" alt="Default OG Image Preview" <?php echo $style_og; ?>>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <p class="description">Fallback slika za social sharing (1200x630 preporučeno)</p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="twitter_handle">Twitter Handle</label>
                            </th>
                            <td>
                                <input type="text"
                                       name="twitter_handle"
                                       id="twitter_handle"
                                       value="<?php echo esc_attr($twitter_handle); ?>"
                                       class="regular-text"
                                       placeholder="@vašnalog">
                                <p class="description">Twitter korisničko ime (sa @)</p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="homepage_description">Homepage Meta Description</label>
                            </th>
                            <td>
                                <textarea name="homepage_description"
                                          id="homepage_description"
                                          class="large-text"
                                          rows="3"><?php echo esc_textarea($homepage_description); ?></textarea>
                                <p class="description">Meta opis za početnu stranicu</p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="title_separator">Title Separator</label>
                            </th>
                            <td>
                                <fieldset>
                                    <legend class="screen-reader-text"><span>Title Separator</span></legend>
                                    <?php
                                    $separators = array(
                                        '-' => '-',
                                        '–' => '–', // En dash
                                        '—' => '—', // Em dash
                                        '|' => '|',
                                        '/' => '/',
                                        '::' => '::',
                                        '>' => '>',
                                        '~' => '~',
                                        '•' => '•'
                                    );

                                    // Check if current separator is in the predefined list
                                    $is_custom = !array_key_exists($title_separator, $separators);
                                    $custom_value = $is_custom ? $title_separator : '';

                                    foreach ($separators as $value => $label) {
                                        $checked = (!$is_custom && $title_separator === $value) ? 'checked="checked"' : '';
                                        echo '<label style="display: inline-block; margin-right: 15px;">';
                                        echo '<input type="radio" name="title_separator" value="' . esc_attr($value) . '" ' . $checked . '> ';
                                        echo '<span style="font-size: 18px; font-weight: bold;">' . esc_html($label) . '</span>';
                                        echo '</label> ';
                                    }
                                    ?>
                                    <br><br>
                                    <label>
                                        <input type="radio" name="title_separator" value="custom" <?php checked($is_custom, true); ?>>
                                        Custom: <input type="text" name="title_separator_custom" value="<?php echo esc_attr($custom_value); ?>" class="small-text" placeholder="Enter custom separator">
                                    </label>
                                    <p class="description">Odaberite separator koji će se koristiti u title tagovima (npr. "Naslov - Ime sajta")</p>
                                </fieldset>
                            </td>
                        </tr>
                    </table>
                </div>

                <?php if ($compat_status['has_seo_plugin']): ?>
                <div class="card" style="max-width: 900px; margin-top: 20px;">
                    <h2>Kompatibilnost</h2>

                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label>Override SEO Plugins</label>
                            </th>
                            <td>
                                <input type="checkbox"
                                       name="override_plugins"
                                       value="1"
                                       <?php checked($override_plugins, true); ?>>
                                <p class="description">Omogući theme SEO i kada je drugi SEO plugin aktivan (može izazvati konflikte)</p>
                            </td>
                        </tr>
                    </table>
                </div>
                <?php endif; ?>

                <!-- Sitemap Settings -->
                <div class="card" style="max-width: 900px; margin-top: 20px;">
                    <h2>🗺️ XML Sitemap Postavke</h2>
                    <p style="color: #666; margin-bottom: 20px;">
                        WordPress sitemap URL: <a href="<?php echo home_url('/wp-sitemap.xml'); ?>" target="_blank"><?php echo home_url('/wp-sitemap.xml'); ?></a>
                    </p>

                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label>Omogući Sitemap</label>
                            </th>
                            <td>
                                <input type="checkbox"
                                       name="sitemap_enabled"
                                       id="sitemap_enabled"
                                       value="1"
                                       <?php checked($sitemap_enabled, true); ?>>
                                <label for="sitemap_enabled">Omogući WordPress XML sitemap</label>
                                <p class="description">WordPress automatski generiše sitemap koji prati vaše SEO postavke</p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label>Isključi Post Tipove</label>
                            </th>
                            <td>
                                <?php
                                $post_types = get_post_types(array('public' => true), 'objects');
                                foreach ($post_types as $post_type) {
                                    if ($post_type->name === 'attachment') continue;
                                    $checked = in_array($post_type->name, $sitemap_exclude_post_types);
                                    ?>
                                    <label style="display: block; margin-bottom: 8px;">
                                        <input type="checkbox"
                                               name="sitemap_exclude_post_types[]"
                                               value="<?php echo esc_attr($post_type->name); ?>"
                                               <?php checked($checked, true); ?>>
                                        <?php echo esc_html($post_type->label); ?> (<?php echo esc_html($post_type->name); ?>)
                                    </label>
                                    <?php
                                }
                                ?>
                                <p class="description">Post tipovi koji NEĆE biti uključeni u sitemap</p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label>Isključi Taksonomije</label>
                            </th>
                            <td>
                                <?php
                                $taxonomies = get_taxonomies(array('public' => true), 'objects');
                                foreach ($taxonomies as $taxonomy) {
                                    if ($taxonomy->name === 'post_format') continue;
                                    $checked = in_array($taxonomy->name, $sitemap_exclude_taxonomies);
                                    ?>
                                    <label style="display: block; margin-bottom: 8px;">
                                        <input type="checkbox"
                                               name="sitemap_exclude_taxonomies[]"
                                               value="<?php echo esc_attr($taxonomy->name); ?>"
                                               <?php checked($checked, true); ?>>
                                        <?php echo esc_html($taxonomy->label); ?> (<?php echo esc_html($taxonomy->name); ?>)
                                    </label>
                                    <?php
                                }
                                ?>
                                <p class="description">Taksonomije (kategorije, tagovi) koje NEĆE biti uključene u sitemap</p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label>Slike u Sitemap-u</label>
                            </th>
                            <td>
                                <input type="checkbox"
                                       name="sitemap_include_images"
                                       id="sitemap_include_images"
                                       value="1"
                                       <?php checked($sitemap_include_images, true); ?>>
                                <label for="sitemap_include_images">Uključi slike u sitemap (Google Image Search)</label>
                                <p class="description">Dodaje sve slike iz postova u sitemap za bolju vidljivost u Google Image pretrazi</p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="sitemap_entries_per_page">Max Unosa po Stranici</label>
                            </th>
                            <td>
                                <input type="number"
                                       name="sitemap_entries_per_page"
                                       id="sitemap_entries_per_page"
                                       value="<?php echo esc_attr($sitemap_entries_per_page); ?>"
                                       min="1"
                                       max="50000"
                                       class="small-text">
                                <p class="description">Preporučeno: 2000 (Google limit je 50,000)</p>
                            </td>
                        </tr>
                    </table>
                </div>

                <?php submit_button('Sačuvaj Postavke'); ?>
            </form>

            <!-- Sitemap Statistics -->
            <?php if ($sitemap_enabled): ?>
            <div class="card" style="max-width: 900px; margin-top: 20px;">
                <h2>📊 Sitemap Statistika</h2>
                <?php
                $sitemap_stats = RVK_SEO_Sitemap::get_sitemap_stats();
                ?>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 15px;">
                    <div>
                        <h3 style="margin-top: 0; color: #666; font-size: 14px; text-transform: uppercase;">Post Tipovi u Sitemap-u</h3>
                        <?php if (!empty($sitemap_stats['post_types'])): ?>
                            <ul style="margin: 0; padding-left: 20px;">
                                <?php foreach ($sitemap_stats['post_types'] as $post_type => $count): ?>
                                    <li><strong><?php echo esc_html($post_type); ?>:</strong> <?php echo esc_html($count); ?> unosa</li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p style="color: #999;">Nema post tipova u sitemap-u</p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h3 style="margin-top: 0; color: #666; font-size: 14px; text-transform: uppercase;">Taksonomije u Sitemap-u</h3>
                        <?php if (!empty($sitemap_stats['taxonomies'])): ?>
                            <ul style="margin: 0; padding-left: 20px;">
                                <?php foreach ($sitemap_stats['taxonomies'] as $taxonomy => $count): ?>
                                    <li><strong><?php echo esc_html($taxonomy); ?>:</strong> <?php echo esc_html($count); ?> termova</li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p style="color: #999;">Nema taksonomija u sitemap-u</p>
                        <?php endif; ?>
                    </div>
                </div>
                <div style="margin-top: 20px; padding: 15px; background: #f0f6fc; border-left: 4px solid #0073aa; border-radius: 4px;">
                    <p style="margin: 0;"><strong>💡 Savjet:</strong> Sitemap se automatski ažurira kada kreirate, ažurirate ili obrišete sadržaj. Ne morate ručno regenerisati.</p>
                </div>
            </div>
            <?php endif; ?>

            <div class="card" style="max-width: 900px; margin-top: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none;">
                <p style="margin: 0; text-align: center; font-size: 14px;">
                    <strong>⚡ Tailored by Alen Melkić - RVK SEO/AEO v1.0</strong>
                </p>
            </div>
        </div>

        <style>
            .card {
                background: #fff;
                border: 1px solid #ccd0d4;
                border-radius: 4px;
                padding: 20px;
                box-shadow: 0 1px 1px rgba(0,0,0,.04);
            }
            .card h2 {
                margin-top: 0;
            }
            .image-preview {
                display: inline-block;
                border: 2px solid #ddd;
                padding: 10px;
                background: #fff;
                border-radius: 4px;
                min-height: 60px;
                min-width: 100px;
            }
            .image-preview img {
                max-width: 150px !important;
                min-width: 50px !important;
                width: auto !important;
                height: auto !important;
                min-height: 50px !important;
                display: block !important;
                margin: 0 auto;
            }
            .image-preview img[src$=".svg"] {
                width: 150px !important;
                height: auto !important;
            }
            .button.upload-image-button,
            .button.remove-image-button {
                margin-right: 5px;
                margin-bottom: 5px;
            }
        </style>

        <script>
            jQuery(document).ready(function($) {
                // Media uploader
                $('.upload-image-button').on('click', function(e) {
                    e.preventDefault();

                    var button = $(this);
                    var target = button.data('target');

                    var frame = wp.media({
                        title: 'Odaberi Sliku',
                        button: { text: 'Koristi ovu sliku' },
                        multiple: false
                    });

                    frame.on('select', function() {
                        var attachment = frame.state().get('selection').first().toJSON();
                        $('#' + target).val(attachment.id);

                        // Get the best image URL (prefer thumbnail, fallback to full)
                        var imageUrl = attachment.url;
                        if (attachment.sizes && attachment.sizes.thumbnail) {
                            imageUrl = attachment.sizes.thumbnail.url;
                        } else if (attachment.sizes && attachment.sizes.medium) {
                            imageUrl = attachment.sizes.medium.url;
                        }

                        // Show preview
                        var imgHtml = '<img src="' + imageUrl + '" alt="Preview">';
                        var previewDiv = $('#preview_' + target);
                        if (previewDiv.length) {
                            previewDiv.html(imgHtml).show();
                        } else {
                            button.parent().find('.image-preview').html(imgHtml).show();
                        }

                        // Show remove button
                        button.siblings('.remove-image-button').show();
                    });

                    frame.open();
                });

                // Remove image
                $('.remove-image-button').on('click', function(e) {
                    e.preventDefault();

                    var button = $(this);
                    var target = button.data('target');

                    // Clear the hidden input
                    $('#' + target).val('');

                    // Hide preview
                    var previewDiv = $('#preview_' + target);
                    if (previewDiv.length) {
                        previewDiv.hide().html('');
                    } else {
                        button.parent().find('.image-preview').hide().html('');
                    }

                    // Hide remove button
                    button.hide();
                });
            });
        </script>
        <?php
    }
}

// Initialize SEO Settings Page
new RVK_SEO_Settings_Page();
