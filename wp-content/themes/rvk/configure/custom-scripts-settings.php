<?php
/**
 * Custom Scripts Settings Page
 * Manages custom script injection in head and footer with conditional loading
 */

// Security: Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Add Custom Scripts submenu under Settings
function rvk_add_custom_scripts_menu() {
    add_options_page(
        'Custom Scripts',       // Page title
        'Custom Scripts',       // Menu title
        'manage_options',       // Capability
        'rvk-custom-scripts',   // Menu slug
        'rvk_custom_scripts_page' // Callback function
    );
}
add_action('admin_menu', 'rvk_add_custom_scripts_menu');

// Enqueue admin scripts and styles for Custom Scripts page
function rvk_custom_scripts_admin_scripts($hook) {
    if ($hook !== 'settings_page_rvk-custom-scripts') {
        return;
    }

    // Enqueue custom admin script (no dependencies - vanilla JS with Sortable.js)
    $js_file = get_template_directory() . '/dist/custom-scripts-admin.js';
    if (file_exists($js_file)) {
        wp_enqueue_script(
            'rvk-custom-scripts-admin',
            get_template_directory_uri() . '/dist/custom-scripts-admin.js',
            array(),
            filemtime($js_file),
            true
        );
    }

    // Enqueue custom admin styles
    $css_file = get_template_directory() . '/dist/css/admin/custom-scripts.css';
    if (file_exists($css_file)) {
        wp_enqueue_style(
            'rvk-custom-scripts-admin',
            get_template_directory_uri() . '/dist/css/admin/custom-scripts.css',
            array(),
            filemtime($css_file)
        );
    }
}
add_action('admin_enqueue_scripts', 'rvk_custom_scripts_admin_scripts');

// Register settings
function rvk_register_custom_scripts_settings() {
    register_setting('rvk_custom_scripts_settings', 'rvk_custom_scripts_settings', 'rvk_sanitize_custom_scripts_settings');
}
add_action('admin_init', 'rvk_register_custom_scripts_settings');

// Get default custom scripts settings
function rvk_get_default_custom_scripts_settings() {
    return array(
        'scripts' => array(),
        'global' => array(
            'consent_storage_key' => 'rvk_scripts_consent',
        ),
        'version' => '1.0'
    );
}

// Sanitize inline script
function rvk_sanitize_inline_script($script) {
    // Define allowed HTML tags for scripts
    $allowed_tags = array(
        'script' => array(
            'type' => array(),
            'id' => array(),
            'class' => array(),
            'src' => array(),
            'async' => array(),
            'defer' => array(),
        ),
        'noscript' => array(),
    );

    // Check for dangerous patterns
    $dangerous_patterns = array(
        '/document\.write\s*\(/i',
        '/eval\s*\(/i',
        '/innerHTML\s*=/i',
        '/outerHTML\s*=/i',
    );

    foreach ($dangerous_patterns as $pattern) {
        if (preg_match($pattern, $script)) {
            add_settings_error(
                'rvk_custom_scripts_settings',
                'dangerous_code',
                'Script contains potentially dangerous code (document.write, eval, innerHTML, outerHTML) and was blocked for security.',
                'error'
            );
            return '';
        }
    }

    // Use wp_kses with allowed tags
    return wp_kses($script, $allowed_tags);
}

// Validate script URL
function rvk_validate_script_url($url, $script_name = '') {
    // Check if URL starts with https://
    if (strpos($url, 'https://') !== 0) {
        add_settings_error(
            'rvk_custom_scripts_settings',
            'invalid_url_protocol',
            sprintf('Script "%s": URL must use HTTPS protocol.', $script_name),
            'error'
        );
        return false;
    }

    // Block localhost and private IPs
    if (preg_match('/localhost|127\.0\.0\.1|192\.168\.|10\.|172\.(1[6-9]|2[0-9]|3[01])\./', $url)) {
        add_settings_error(
            'rvk_custom_scripts_settings',
            'localhost_blocked',
            sprintf('Script "%s": Cannot load scripts from localhost or private IPs for security.', $script_name),
            'error'
        );
        return false;
    }

    return true;
}

// Sanitize conditional loading settings
function rvk_sanitize_conditional_loading($input) {
    if (!is_array($input)) {
        return array(
            'enabled' => false,
            'load_on' => 'all',
            'post_types' => array(),
            'exclude_from' => array(),
            'exclude_ids' => array(),
        );
    }

    return array(
        'enabled' => isset($input['enabled']),
        'load_on' => in_array($input['load_on'] ?? '', array('all', 'posts', 'pages', 'post_types'))
            ? $input['load_on']
            : 'all',
        'post_types' => isset($input['post_types']) && is_array($input['post_types'])
            ? array_map('sanitize_key', $input['post_types'])
            : array(),
        'exclude_from' => isset($input['exclude_from']) && is_array($input['exclude_from'])
            ? array_map('sanitize_key', $input['exclude_from'])
            : array(),
        'exclude_ids' => isset($input['exclude_ids']) && is_array($input['exclude_ids'])
            ? array_map('absint', $input['exclude_ids'])
            : array(),
    );
}

// Sanitize custom scripts settings
function rvk_sanitize_custom_scripts_settings($input) {
    $sanitized = array('scripts' => array());

    if (isset($input['scripts']) && is_array($input['scripts'])) {
        foreach ($input['scripts'] as $script) {
            // Skip if content is empty
            if (empty($script['content'])) {
                continue;
            }

            $clean_script = array(
                'id' => sanitize_key($script['id'] ?? 'script_' . time()),
                'enabled' => isset($script['enabled']),
                'name' => sanitize_text_field($script['name'] ?? 'Untitled Script'),
                'position' => in_array($script['position'] ?? '', array('head', 'footer'))
                    ? $script['position']
                    : 'footer',
                'type' => in_array($script['type'] ?? '', array('inline', 'external'))
                    ? $script['type']
                    : 'external',
                'order' => absint($script['order'] ?? 0),
            );

            // Sanitize content based on type
            if ($clean_script['type'] === 'external') {
                $url = esc_url_raw($script['content'] ?? '');

                // Validate URL
                if (!rvk_validate_script_url($url, $clean_script['name'])) {
                    continue; // Skip this script if URL is invalid
                }

                $clean_script['content'] = $url;
                $clean_script['async'] = isset($script['async']);
                $clean_script['defer'] = isset($script['defer']);
            } else {
                // Inline script
                $clean_script['content'] = rvk_sanitize_inline_script($script['content'] ?? '');

                // Skip if sanitization removed all content
                if (empty($clean_script['content'])) {
                    continue;
                }

                $clean_script['async'] = false;
                $clean_script['defer'] = false;
            }

            // Sanitize performance options
            $clean_script['lazy_load'] = isset($script['lazy_load']);
            $clean_script['lazy_load_delay'] = absint($script['lazy_load_delay'] ?? 0);
            $clean_script['cookie_consent_required'] = isset($script['cookie_consent_required']);

            // Sanitize conditional loading
            $clean_script['conditional_loading'] = rvk_sanitize_conditional_loading($script['conditional_loading'] ?? array());

            $sanitized['scripts'][] = $clean_script;
        }
    }

    // Sort scripts by order
    usort($sanitized['scripts'], function($a, $b) {
        return $a['order'] - $b['order'];
    });

    // Global settings
    $sanitized['global'] = array(
        'consent_storage_key' => sanitize_key($input['global']['consent_storage_key'] ?? 'rvk_scripts_consent'),
    );

    $sanitized['version'] = '1.0';

    return $sanitized;
}

// Render Custom Scripts settings page
function rvk_custom_scripts_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    // Get current settings
    $settings = get_option('rvk_custom_scripts_settings', rvk_get_default_custom_scripts_settings());

    // Get all post types for conditional loading
    $post_types = get_post_types(array('public' => true), 'objects');

    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

        <?php settings_errors('rvk_custom_scripts_settings'); ?>

        <form method="post" action="options.php" id="custom-scripts-form">
            <?php
            settings_fields('rvk_custom_scripts_settings');
            wp_nonce_field('rvk_custom_scripts_settings', 'rvk_custom_scripts_nonce');
            ?>

            <!-- Scripts List -->
            <div class="rvk-custom-scripts-section">
                <h2>Custom Scripts</h2>
                <p class="description">Add custom scripts to load in the &lt;head&gt; or before &lt;/body&gt;. Drag to reorder execution.</p>

                <div id="scripts-container">
                    <?php
                    if (!empty($settings['scripts'])) {
                        foreach ($settings['scripts'] as $index => $script) {
                            rvk_render_script_item($script, $index, $post_types);
                        }
                    }
                    ?>
                </div>

                <p>
                    <button type="button" id="add-script-btn" class="button button-primary">+ Add New Script</button>
                </p>
            </div>

            <!-- Global Settings -->
            <div class="rvk-custom-scripts-section">
                <h2>Global Settings</h2>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="consent_storage_key">Cookie Consent Storage Key</label>
                        </th>
                        <td>
                            <input type="text" name="rvk_custom_scripts_settings[global][consent_storage_key]" id="consent_storage_key" value="<?php echo esc_attr($settings['global']['consent_storage_key']); ?>" class="regular-text">
                            <p class="description">LocalStorage key for cookie consent (default: rvk_scripts_consent)</p>
                        </td>
                    </tr>
                </table>
            </div>

            <?php submit_button('Save Settings'); ?>
        </form>

        <!-- Help Section -->
        <div class="rvk-custom-scripts-section help-section">
            <h2>Help & Tips</h2>

            <h3>Tips</h3>
            <ul>
                <li>Drag the ☰ handle to reorder scripts (execution order)</li>
                <li>External scripts must use HTTPS protocol</li>
                <li>Use <strong>defer</strong> for better performance (scripts execute after HTML parsing)</li>
                <li>Test inline scripts in browser console before adding</li>
                <li><strong>Lazy load</strong> delays script loading until page is fully interactive</li>
            </ul>

            <h3>Security</h3>
            <ul>
                <li>Only add scripts from trusted sources</li>
                <li>Inline scripts are sanitized to prevent XSS attacks</li>
                <li>Dangerous patterns (eval, document.write, innerHTML) are blocked</li>
                <li>Localhost and private IP URLs are blocked for security</li>
            </ul>

            <h3>Examples</h3>
            <ul>
                <li><strong>Hotjar:</strong> https://static.hotjar.com/c/hotjar-{YOUR_ID}.js (External, Async)</li>
                <li><strong>Google Fonts:</strong> https://fonts.googleapis.com/css2?family=Roboto (External, Head)</li>
                <li><strong>Custom Tracking:</strong> Inline code with console.log or analytics calls</li>
            </ul>
        </div>

        <!-- Hidden template for new scripts -->
        <script type="text/template" id="script-item-template">
            <?php
            $template_script = array(
                'id' => '{{SCRIPT_ID}}',
                'enabled' => true,
                'name' => '',
                'position' => 'footer',
                'type' => 'external',
                'content' => '',
                'async' => false,
                'defer' => true,
                'lazy_load' => false,
                'lazy_load_delay' => 0,
                'cookie_consent_required' => false,
                'conditional_loading' => array(
                    'enabled' => false,
                    'load_on' => 'all',
                    'post_types' => array(),
                    'exclude_from' => array(),
                    'exclude_ids' => array(),
                ),
                'order' => 999,
            );
            rvk_render_script_item($template_script, '{{INDEX}}', $post_types);
            ?>
        </script>
    </div>
    <?php
}

// Render a single script item
function rvk_render_script_item($script, $index, $post_types) {
    ?>
    <div class="script-item" data-script-id="<?php echo esc_attr($script['id']); ?>">
        <div class="script-drag-handle" title="Drag to reorder">☰</div>

        <div class="script-content">
            <input type="hidden" name="rvk_custom_scripts_settings[scripts][<?php echo esc_attr($index); ?>][id]" value="<?php echo esc_attr($script['id']); ?>">
            <input type="hidden" name="rvk_custom_scripts_settings[scripts][<?php echo esc_attr($index); ?>][order]" value="<?php echo esc_attr($script['order']); ?>" class="script-order">

            <div class="script-header">
                <label class="script-enabled-toggle">
                    <input type="checkbox" name="rvk_custom_scripts_settings[scripts][<?php echo esc_attr($index); ?>][enabled]" value="1" <?php checked($script['enabled']); ?>>
                    <strong>Enabled</strong>
                </label>

                <button type="button" class="button button-link-delete remove-script-btn">Remove Script</button>
            </div>

            <div class="script-fields">
                <div class="script-row">
                    <label>
                        <strong>Script Name:</strong><br>
                        <input type="text" name="rvk_custom_scripts_settings[scripts][<?php echo esc_attr($index); ?>][name]" value="<?php echo esc_attr($script['name']); ?>" class="regular-text" placeholder="e.g., Hotjar Tracking">
                    </label>
                </div>

                <div class="script-row">
                    <label>
                        <strong>Position:</strong>
                        <select name="rvk_custom_scripts_settings[scripts][<?php echo esc_attr($index); ?>][position]">
                            <option value="head" <?php selected($script['position'], 'head'); ?>>Head (&lt;head&gt;)</option>
                            <option value="footer" <?php selected($script['position'], 'footer'); ?>>Footer (before &lt;/body&gt;)</option>
                        </select>
                    </label>

                    <label>
                        <strong>Type:</strong>
                        <select name="rvk_custom_scripts_settings[scripts][<?php echo esc_attr($index); ?>][type]" class="script-type-select">
                            <option value="external" <?php selected($script['type'], 'external'); ?>>External URL</option>
                            <option value="inline" <?php selected($script['type'], 'inline'); ?>>Inline Code</option>
                        </select>
                    </label>
                </div>

                <!-- External URL -->
                <div class="script-row external-url-row" style="<?php echo $script['type'] === 'external' ? '' : 'display:none;'; ?>">
                    <label>
                        <strong>Script URL:</strong><br>
                        <input type="url" name="rvk_custom_scripts_settings[scripts][<?php echo esc_attr($index); ?>][content]" value="<?php echo $script['type'] === 'external' ? esc_attr($script['content']) : ''; ?>" class="large-text external-url-input" placeholder="https://example.com/script.js">
                    </label>
                </div>

                <!-- Inline Code -->
                <div class="script-row inline-code-row" style="<?php echo $script['type'] === 'inline' ? '' : 'display:none;'; ?>">
                    <label>
                        <strong>Inline Code:</strong><br>
                        <textarea name="rvk_custom_scripts_settings[scripts][<?php echo esc_attr($index); ?>][content]" rows="8" class="large-text code inline-code-textarea"><?php echo $script['type'] === 'inline' ? esc_textarea($script['content']) : ''; ?></textarea>
                        <span class="description">Include &lt;script&gt; tags if needed</span>
                    </label>
                </div>

                <!-- Async/Defer (External only) -->
                <div class="script-row async-defer-row" style="<?php echo $script['type'] === 'external' ? '' : 'display:none;'; ?>">
                    <fieldset>
                        <legend><strong>Loading Options:</strong></legend>
                        <label>
                            <input type="checkbox" name="rvk_custom_scripts_settings[scripts][<?php echo esc_attr($index); ?>][async]" value="1" <?php checked($script['async']); ?>>
                            Async
                        </label>
                        <label>
                            <input type="checkbox" name="rvk_custom_scripts_settings[scripts][<?php echo esc_attr($index); ?>][defer]" value="1" <?php checked($script['defer']); ?>>
                            Defer (recommended)
                        </label>
                    </fieldset>
                </div>

                <!-- Performance Options -->
                <div class="script-row">
                    <fieldset>
                        <legend><strong>Performance:</strong></legend>
                        <label>
                            <input type="checkbox" name="rvk_custom_scripts_settings[scripts][<?php echo esc_attr($index); ?>][lazy_load]" value="1" class="lazy-load-checkbox" <?php checked($script['lazy_load']); ?>>
                            Lazy Load (load after page interactive)
                        </label>
                        <br>
                        <label class="lazy-delay-label" style="<?php echo $script['lazy_load'] ? '' : 'display:none;'; ?>">
                            Delay:
                            <input type="number" name="rvk_custom_scripts_settings[scripts][<?php echo esc_attr($index); ?>][lazy_load_delay]" value="<?php echo esc_attr($script['lazy_load_delay']); ?>" min="0" step="100" class="small-text"> ms
                        </label>
                        <br>
                        <label>
                            <input type="checkbox" name="rvk_custom_scripts_settings[scripts][<?php echo esc_attr($index); ?>][cookie_consent_required]" value="1" <?php checked($script['cookie_consent_required']); ?>>
                            Require Cookie Consent
                        </label>
                    </fieldset>
                </div>

                <!-- Conditional Loading -->
                <div class="script-row">
                    <details class="conditional-loading-details" <?php echo $script['conditional_loading']['enabled'] ? 'open' : ''; ?>>
                        <summary>
                            <label>
                                <input type="checkbox" name="rvk_custom_scripts_settings[scripts][<?php echo esc_attr($index); ?>][conditional_loading][enabled]" value="1" class="conditional-enabled-checkbox" <?php checked($script['conditional_loading']['enabled']); ?>>
                                <strong>Conditional Loading</strong>
                            </label>
                        </summary>

                        <div class="conditional-options">
                            <label>
                                <strong>Load on:</strong>
                                <select name="rvk_custom_scripts_settings[scripts][<?php echo esc_attr($index); ?>][conditional_loading][load_on]" class="load-on-select">
                                    <option value="all" <?php selected($script['conditional_loading']['load_on'], 'all'); ?>>All Pages</option>
                                    <option value="posts" <?php selected($script['conditional_loading']['load_on'], 'posts'); ?>>Posts Only</option>
                                    <option value="pages" <?php selected($script['conditional_loading']['load_on'], 'pages'); ?>>Pages Only</option>
                                    <option value="post_types" <?php selected($script['conditional_loading']['load_on'], 'post_types'); ?>>Specific Post Types</option>
                                </select>
                            </label>

                            <div class="post-types-checkboxes" style="<?php echo $script['conditional_loading']['load_on'] === 'post_types' ? '' : 'display:none;'; ?>">
                                <?php foreach ($post_types as $post_type): ?>
                                    <label>
                                        <input type="checkbox" name="rvk_custom_scripts_settings[scripts][<?php echo esc_attr($index); ?>][conditional_loading][post_types][]" value="<?php echo esc_attr($post_type->name); ?>" <?php checked(in_array($post_type->name, $script['conditional_loading']['post_types'])); ?>>
                                        <?php echo esc_html($post_type->label); ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>

                            <br>

                            <label>
                                <strong>Exclude from:</strong>
                            </label>
                            <label>
                                <input type="checkbox" name="rvk_custom_scripts_settings[scripts][<?php echo esc_attr($index); ?>][conditional_loading][exclude_from][]" value="home" <?php checked(in_array('home', $script['conditional_loading']['exclude_from'])); ?>>
                                Homepage
                            </label>
                            <label>
                                <input type="checkbox" name="rvk_custom_scripts_settings[scripts][<?php echo esc_attr($index); ?>][conditional_loading][exclude_from][]" value="archive" <?php checked(in_array('archive', $script['conditional_loading']['exclude_from'])); ?>>
                                Archive Pages
                            </label>
                        </div>
                    </details>
                </div>
            </div>
        </div>
    </div>
    <?php
}
