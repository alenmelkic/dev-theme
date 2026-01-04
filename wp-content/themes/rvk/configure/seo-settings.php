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
            update_option('rvk_seo_title_separator', sanitize_text_field($_POST['title_separator']));

            // Compatibility settings
            update_option('rvk_seo_override_plugins', isset($_POST['override_plugins']));

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
        $title_separator = get_option('rvk_seo_title_separator', '|');

        $override_plugins = get_option('rvk_seo_override_plugins', false);

        // Mask API keys
        $gemini_key_masked = !empty($gemini_key) ? substr($gemini_key, 0, 10) . '...' : '';
        $openai_key_masked = !empty($openai_key) ? substr($openai_key, 0, 10) . '...' : '';

        // Check compatibility
        $compat_status = rvk_get_compatibility_status();

        ?>
        <div class="wrap">
            <h1>SEO & AEO Optimization - Postavke</h1>

            <?php if ($compat_status['has_seo_plugin']): ?>
            <div class="notice notice-<?php echo $compat_status['theme_handles_seo'] ? 'warning' : 'info'; ?>">
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
                                       placeholder="Konfigurisano u AI Sadržaj postavkama">

                                <?php if (!empty($gemini_key)): ?>
                                    <p class="description" style="color: green;">
                                        ✓ Gemini API ključ konfigurisan: <?php echo esc_html($gemini_key_masked); ?>
                                    </p>
                                <?php endif; ?>
                                <p class="description">
                                    <a href="<?php echo admin_url('options-general.php?page=ai-content-settings'); ?>">
                                        Konfiguriši u AI Sadržaj postavkama
                                    </a>
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
                                <button type="button" class="button upload-image-button" data-target="organization_logo">
                                    Odaberi Logo
                                </button>
                                <?php if ($org_logo): ?>
                                    <div class="image-preview">
                                        <?php echo wp_get_attachment_image($org_logo, 'thumbnail'); ?>
                                    </div>
                                <?php endif; ?>
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
                                <button type="button" class="button upload-image-button" data-target="default_og_image">
                                    Odaberi Sliku
                                </button>
                                <?php if ($default_og_image): ?>
                                    <div class="image-preview">
                                        <?php echo wp_get_attachment_image($default_og_image, 'thumbnail'); ?>
                                    </div>
                                <?php endif; ?>
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
                                <input type="text"
                                       name="title_separator"
                                       id="title_separator"
                                       value="<?php echo esc_attr($title_separator); ?>"
                                       class="small-text">
                                <p class="description">Separator u title tagovima (npr. "Naslov | Ime sajta")</p>
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

                <?php submit_button('Sačuvaj Postavke'); ?>
            </form>

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
                margin-top: 10px;
            }
            .image-preview img {
                max-width: 150px;
                height: auto;
            }
        </style>

        <script>
            jQuery(document).ready(function($) {
                // Media uploader
                $('.upload-image-button').click(function(e) {
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

                        // Show preview
                        var preview = button.next('.image-preview');
                        if (preview.length === 0) {
                            button.after('<div class="image-preview"><img src="' + attachment.url + '"></div>');
                        } else {
                            preview.find('img').attr('src', attachment.url);
                        }
                    });

                    frame.open();
                });
            });
        </script>
        <?php
    }
}

// Initialize SEO Settings Page
new RVK_SEO_Settings_Page();
