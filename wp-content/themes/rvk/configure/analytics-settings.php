<?php
/**
 * Analytics Settings Page
 * Manages Google Analytics 4, Google Tag Manager, and Facebook Pixel integrations
 */

// Security: Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Add Analytics submenu under Settings
function rvk_add_analytics_menu() {
    add_options_page(
        'Google Analytics',     // Page title
        'Google Analytics',     // Menu title
        'manage_options',       // Capability
        'rvk-analytics',        // Menu slug
        'rvk_analytics_page'    // Callback function
    );
}
add_action('admin_menu', 'rvk_add_analytics_menu');

// Enqueue admin scripts and styles for Analytics page
function rvk_analytics_admin_scripts($hook) {
    if ($hook !== 'settings_page_rvk-analytics') {
        return;
    }

    // Enqueue custom admin script
    $js_file = get_template_directory() . '/dist/analytics-admin.js';
    if (file_exists($js_file)) {
        wp_enqueue_script(
            'rvk-analytics-admin',
            get_template_directory_uri() . '/dist/analytics-admin.js',
            array('jquery'),
            filemtime($js_file),
            true
        );
    }

    // Enqueue custom admin styles
    $css_file = get_template_directory() . '/dist/css/admin/analytics.css';
    if (file_exists($css_file)) {
        wp_enqueue_style(
            'rvk-analytics-admin',
            get_template_directory_uri() . '/dist/css/admin/analytics.css',
            array(),
            filemtime($css_file)
        );
    }
}
add_action('admin_enqueue_scripts', 'rvk_analytics_admin_scripts');

// Register settings
function rvk_register_analytics_settings() {
    register_setting('rvk_analytics_settings', 'rvk_analytics_settings', 'rvk_sanitize_analytics_settings');
}
add_action('admin_init', 'rvk_register_analytics_settings');

// Get default analytics settings
function rvk_get_default_analytics_settings() {
    return array(
        'ga4' => array(
            'enabled' => false,
            'measurement_id' => '',
            'async_loading' => true,
            'defer_loading' => false,
            'lazy_load' => false,
            'lazy_load_delay' => 0,
            'cookie_consent_required' => false,
        ),
        'gtm' => array(
            'enabled' => false,
            'container_id' => '',
            'async_loading' => true,
            'defer_loading' => false,
            'lazy_load' => false,
            'lazy_load_delay' => 0,
            'cookie_consent_required' => false,
        ),
        'facebook_pixel' => array(
            'enabled' => false,
            'pixel_id' => '',
            'async_loading' => true,
            'defer_loading' => false,
            'lazy_load' => false,
            'lazy_load_delay' => 0,
            'cookie_consent_required' => false,
            'track_page_view' => true,
        ),
        'performance' => array(
            'consent_mode' => 'none',
            'consent_storage_key' => 'rvk_analytics_consent',
        ),
        'version' => '1.0'
    );
}

// Sanitize analytics settings
function rvk_sanitize_analytics_settings($input) {
    $sanitized = array();

    // Sanitize GA4 settings
    $sanitized['ga4'] = array(
        'enabled' => isset($input['ga4']['enabled']),
        'measurement_id' => sanitize_text_field($input['ga4']['measurement_id'] ?? ''),
        'async_loading' => isset($input['ga4']['async_loading']),
        'defer_loading' => isset($input['ga4']['defer_loading']),
        'lazy_load' => isset($input['ga4']['lazy_load']),
        'lazy_load_delay' => absint($input['ga4']['lazy_load_delay'] ?? 0),
        'cookie_consent_required' => isset($input['ga4']['cookie_consent_required']),
    );

    // Validate GA4 measurement ID format
    if (!empty($sanitized['ga4']['measurement_id'])) {
        if (!preg_match('/^G-[A-Z0-9]+$/', $sanitized['ga4']['measurement_id'])) {
            add_settings_error(
                'rvk_analytics_settings',
                'invalid_measurement_id',
                'Invalid GA4 Measurement ID format. Must start with G- followed by alphanumeric characters (e.g., G-ABC123XYZ).',
                'error'
            );
            $sanitized['ga4']['measurement_id'] = '';
        }
    }

    // Sanitize GTM settings
    $sanitized['gtm'] = array(
        'enabled' => isset($input['gtm']['enabled']),
        'container_id' => sanitize_text_field($input['gtm']['container_id'] ?? ''),
        'async_loading' => isset($input['gtm']['async_loading']),
        'defer_loading' => isset($input['gtm']['defer_loading']),
        'lazy_load' => isset($input['gtm']['lazy_load']),
        'lazy_load_delay' => absint($input['gtm']['lazy_load_delay'] ?? 0),
        'cookie_consent_required' => isset($input['gtm']['cookie_consent_required']),
    );

    // Validate GTM container ID format
    if (!empty($sanitized['gtm']['container_id'])) {
        if (!preg_match('/^GTM-[A-Z0-9]+$/', $sanitized['gtm']['container_id'])) {
            add_settings_error(
                'rvk_analytics_settings',
                'invalid_container_id',
                'Invalid GTM Container ID format. Must start with GTM- followed by alphanumeric characters (e.g., GTM-ABC123).',
                'error'
            );
            $sanitized['gtm']['container_id'] = '';
        }
    }

    // Sanitize Facebook Pixel settings
    $sanitized['facebook_pixel'] = array(
        'enabled' => isset($input['facebook_pixel']['enabled']),
        'pixel_id' => sanitize_text_field($input['facebook_pixel']['pixel_id'] ?? ''),
        'async_loading' => isset($input['facebook_pixel']['async_loading']),
        'defer_loading' => isset($input['facebook_pixel']['defer_loading']),
        'lazy_load' => isset($input['facebook_pixel']['lazy_load']),
        'lazy_load_delay' => absint($input['facebook_pixel']['lazy_load_delay'] ?? 0),
        'cookie_consent_required' => isset($input['facebook_pixel']['cookie_consent_required']),
        'track_page_view' => isset($input['facebook_pixel']['track_page_view']),
    );

    // Validate Facebook Pixel ID (numeric only)
    if (!empty($sanitized['facebook_pixel']['pixel_id'])) {
        if (!preg_match('/^[0-9]+$/', $sanitized['facebook_pixel']['pixel_id'])) {
            add_settings_error(
                'rvk_analytics_settings',
                'invalid_pixel_id',
                'Invalid Facebook Pixel ID format. Must be numeric only (e.g., 123456789012345).',
                'error'
            );
            $sanitized['facebook_pixel']['pixel_id'] = '';
        }
    }

    // Mutual exclusivity: GA4 and GTM cannot both be enabled
    if ($sanitized['ga4']['enabled'] && $sanitized['gtm']['enabled']) {
        $sanitized['gtm']['enabled'] = false;
        add_settings_error(
            'rvk_analytics_settings',
            'mutual_exclusivity',
            'GA4 and GTM cannot both be enabled simultaneously. GTM has been automatically disabled. Please choose one.',
            'warning'
        );
    }

    // Sanitize performance settings
    $sanitized['performance'] = array(
        'consent_mode' => in_array($input['performance']['consent_mode'] ?? '', array('none', 'manual'))
            ? $input['performance']['consent_mode']
            : 'none',
        'consent_storage_key' => sanitize_key($input['performance']['consent_storage_key'] ?? 'rvk_analytics_consent'),
    );

    // Preserve version
    $sanitized['version'] = '1.0';

    return $sanitized;
}

// Render Analytics settings page
function rvk_analytics_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    // Get current settings
    $settings = get_option('rvk_analytics_settings', rvk_get_default_analytics_settings());

    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

        <?php settings_errors('rvk_analytics_settings'); ?>

        <form method="post" action="options.php">
            <?php
            settings_fields('rvk_analytics_settings');
            wp_nonce_field('rvk_analytics_settings', 'rvk_analytics_nonce');
            ?>

            <!-- Integration Type Selection -->
            <div class="rvk-analytics-section">
                <h2>Choose Your Analytics Platform</h2>
                <p class="description">Select either Google Analytics 4 or Google Tag Manager (both cannot be enabled simultaneously).</p>

                <table class="form-table">
                    <tr>
                        <th scope="row">Primary Integration</th>
                        <td class="integration-type-selector">
                            <label>
                                <input type="radio" name="integration_type" value="ga4" <?php checked($settings['ga4']['enabled'] && !$settings['gtm']['enabled']); ?>>
                                <strong>Google Analytics 4 (GA4)</strong> - Recommended for standard analytics tracking
                            </label>
                            <br><br>
                            <label>
                                <input type="radio" name="integration_type" value="gtm" <?php checked($settings['gtm']['enabled']); ?>>
                                <strong>Google Tag Manager (GTM)</strong> - Advanced tag management for multiple tools
                            </label>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- GA4 Configuration -->
            <div class="rvk-analytics-section integration-config <?php echo $settings['ga4']['enabled'] ? 'active' : ''; ?>" id="ga4-section">
                <h2>Google Analytics 4 Configuration</h2>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="ga4_enabled">Enable GA4</label>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" name="rvk_analytics_settings[ga4][enabled]" id="ga4_enabled" value="1" <?php checked($settings['ga4']['enabled']); ?>>
                                Enable Google Analytics 4 tracking
                            </label>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="ga4_measurement_id">Measurement ID</label>
                        </th>
                        <td>
                            <input type="text" name="rvk_analytics_settings[ga4][measurement_id]" id="ga4_measurement_id" value="<?php echo esc_attr($settings['ga4']['measurement_id']); ?>" class="regular-text" placeholder="G-XXXXXXXXXX">
                            <p class="description">Find this in GA4 Admin → Data Streams → Web Stream Details</p>
                        </td>
                    </tr>

                    <?php rvk_render_performance_options('ga4', $settings['ga4']); ?>
                </table>
            </div>

            <!-- GTM Configuration -->
            <div class="rvk-analytics-section integration-config <?php echo $settings['gtm']['enabled'] ? 'active' : ''; ?>" id="gtm-section">
                <h2>Google Tag Manager Configuration</h2>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="gtm_enabled">Enable GTM</label>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" name="rvk_analytics_settings[gtm][enabled]" id="gtm_enabled" value="1" <?php checked($settings['gtm']['enabled']); ?>>
                                Enable Google Tag Manager
                            </label>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="gtm_container_id">Container ID</label>
                        </th>
                        <td>
                            <input type="text" name="rvk_analytics_settings[gtm][container_id]" id="gtm_container_id" value="<?php echo esc_attr($settings['gtm']['container_id']); ?>" class="regular-text" placeholder="GTM-XXXXXX">
                            <p class="description">Find this in GTM Admin → Container Settings</p>
                        </td>
                    </tr>

                    <?php rvk_render_performance_options('gtm', $settings['gtm']); ?>
                </table>
            </div>

            <!-- Facebook Pixel Configuration -->
            <div class="rvk-analytics-section">
                <h2>Facebook Pixel Configuration</h2>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="facebook_pixel_enabled">Enable Facebook Pixel</label>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" name="rvk_analytics_settings[facebook_pixel][enabled]" id="facebook_pixel_enabled" value="1" <?php checked($settings['facebook_pixel']['enabled']); ?>>
                                Enable Facebook Pixel tracking
                            </label>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="facebook_pixel_id">Pixel ID</label>
                        </th>
                        <td>
                            <input type="text" name="rvk_analytics_settings[facebook_pixel][pixel_id]" id="facebook_pixel_id" value="<?php echo esc_attr($settings['facebook_pixel']['pixel_id']); ?>" class="regular-text" placeholder="123456789012345">
                            <p class="description">Find this in Meta Events Manager → Data Sources</p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="facebook_track_page_view">Automatic Page Views</label>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" name="rvk_analytics_settings[facebook_pixel][track_page_view]" id="facebook_track_page_view" value="1" <?php checked($settings['facebook_pixel']['track_page_view']); ?>>
                                Automatically track page views
                            </label>
                        </td>
                    </tr>

                    <?php rvk_render_performance_options('facebook_pixel', $settings['facebook_pixel']); ?>
                </table>
            </div>

            <!-- Cookie Consent Settings -->
            <div class="rvk-analytics-section">
                <h2>Cookie Consent Settings</h2>

                <table class="form-table">
                    <tr>
                        <th scope="row">Consent Mode</th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="radio" name="rvk_analytics_settings[performance][consent_mode]" value="none" <?php checked($settings['performance']['consent_mode'], 'none'); ?>>
                                    <strong>None</strong> - No consent required, scripts load immediately
                                </label>
                                <br><br>
                                <label>
                                    <input type="radio" name="rvk_analytics_settings[performance][consent_mode]" value="manual" <?php checked($settings['performance']['consent_mode'], 'manual'); ?>>
                                    <strong>Manual</strong> - I'm using a consent plugin (scripts wait for consent)
                                </label>
                            </fieldset>
                        </td>
                    </tr>

                    <tr class="consent-manual-options" style="<?php echo $settings['performance']['consent_mode'] === 'manual' ? '' : 'display:none;'; ?>">
                        <th scope="row">
                            <label for="consent_storage_key">LocalStorage Key</label>
                        </th>
                        <td>
                            <input type="text" name="rvk_analytics_settings[performance][consent_storage_key]" id="consent_storage_key" value="<?php echo esc_attr($settings['performance']['consent_storage_key']); ?>" class="regular-text">
                            <p class="description">Your consent plugin should set this localStorage key to 'granted' when user accepts cookies.</p>
                        </td>
                    </tr>
                </table>
            </div>

            <?php submit_button('Save Settings'); ?>
        </form>

        <!-- Help Section -->
        <div class="rvk-analytics-section">
            <h2>Help & Documentation</h2>

            <h3>Quick Start Guide</h3>
            <ul>
                <li><strong>GA4 Measurement ID:</strong> Log into Google Analytics → Admin (gear icon) → Data Streams → Select your web stream → Copy the Measurement ID (starts with G-)</li>
                <li><strong>GTM Container ID:</strong> Log into Google Tag Manager → Select your container → Click Container ID in the top bar (starts with GTM-)</li>
                <li><strong>Facebook Pixel ID:</strong> Go to Meta Events Manager → Data Sources → Select your pixel → Copy the Pixel ID (15-digit number)</li>
            </ul>

            <h3>Performance Options Explained</h3>
            <ul>
                <li><strong>Async Loading:</strong> Scripts download in parallel without blocking page rendering (recommended)</li>
                <li><strong>Defer Loading:</strong> Scripts execute after HTML parsing is complete</li>
                <li><strong>Lazy Load:</strong> Scripts load only after the page is fully interactive (best for performance)</li>
                <li><strong>Cookie Consent:</strong> Scripts wait for user consent before loading (GDPR compliance)</li>
            </ul>

            <h3>Cookie Consent Plugin Integration</h3>
            <p>If using <strong>Manual</strong> consent mode, your consent plugin must:</p>
            <ol>
                <li>Set the localStorage key (default: <code>rvk_analytics_consent</code>) to <code>'granted'</code> when user accepts</li>
                <li>Dispatch a custom event: <code>window.dispatchEvent(new Event('rvk_consent_granted'))</code></li>
            </ol>
            <p>Compatible plugins: CookieYes, Complianz GDPR, Cookie Notice & Compliance</p>
        </div>
    </div>
    <?php
}

// Helper function to render performance options for each integration
function rvk_render_performance_options($type, $config) {
    ?>
    <tr>
        <th scope="row">Performance Options</th>
        <td class="performance-options async-defer-toggle">
            <fieldset>
                <label>
                    <input type="checkbox" name="rvk_analytics_settings[<?php echo esc_attr($type); ?>][async_loading]" value="1" class="async-checkbox" <?php checked($config['async_loading']); ?>>
                    Async Loading (recommended)
                </label>
                <br>
                <label>
                    <input type="checkbox" name="rvk_analytics_settings[<?php echo esc_attr($type); ?>][defer_loading]" value="1" class="defer-checkbox" <?php checked($config['defer_loading']); ?>>
                    Defer Loading
                </label>
                <br>
                <label>
                    <input type="checkbox" name="rvk_analytics_settings[<?php echo esc_attr($type); ?>][lazy_load]" value="1" class="lazy-load-checkbox" <?php checked($config['lazy_load']); ?>>
                    Lazy Load (load after page interactive)
                </label>
            </fieldset>
        </td>
    </tr>

    <tr class="lazy-delay-field" style="<?php echo $config['lazy_load'] ? '' : 'display:none;'; ?>">
        <th scope="row">
            <label for="<?php echo esc_attr($type); ?>_lazy_delay">Lazy Load Delay</label>
        </th>
        <td>
            <input type="number" name="rvk_analytics_settings[<?php echo esc_attr($type); ?>][lazy_load_delay]" id="<?php echo esc_attr($type); ?>_lazy_delay" value="<?php echo esc_attr($config['lazy_load_delay']); ?>" min="0" step="100" class="small-text"> milliseconds
            <p class="description">Additional delay after page load (0 = load immediately after page interactive)</p>
        </td>
    </tr>

    <tr>
        <th scope="row">Cookie Consent</th>
        <td>
            <label>
                <input type="checkbox" name="rvk_analytics_settings[<?php echo esc_attr($type); ?>][cookie_consent_required]" value="1" <?php checked($config['cookie_consent_required']); ?>>
                Require cookie consent before loading this integration
            </label>
        </td>
    </tr>
    <?php
}
