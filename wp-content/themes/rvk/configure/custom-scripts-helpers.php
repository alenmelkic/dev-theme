<?php
/**
 * Custom Scripts Helpers
 * Frontend script output for custom head and footer scripts with conditional loading
 */

// Security: Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Output custom head scripts
function rvk_output_custom_head_scripts() {
    // Skip if in admin
    if (is_admin()) {
        return;
    }

    // Get settings
    $settings = get_option('rvk_custom_scripts_settings', rvk_get_default_custom_scripts_settings());

    // Filter scripts for head position
    $head_scripts = array_filter($settings['scripts'], function($script) {
        return $script['position'] === 'head';
    });

    // Output each script
    foreach ($head_scripts as $script) {
        if (rvk_should_load_script($script, $settings)) {
            rvk_output_single_script($script, $settings);
        }
    }
}
add_action('wp_head', 'rvk_output_custom_head_scripts', 10);

// Output custom footer scripts
function rvk_output_custom_footer_scripts() {
    // Skip if in admin
    if (is_admin()) {
        return;
    }

    // Get settings
    $settings = get_option('rvk_custom_scripts_settings', rvk_get_default_custom_scripts_settings());

    // Filter scripts for footer position
    $footer_scripts = array_filter($settings['scripts'], function($script) {
        return $script['position'] === 'footer';
    });

    // Output each script
    foreach ($footer_scripts as $script) {
        if (rvk_should_load_script($script, $settings)) {
            rvk_output_single_script($script, $settings);
        }
    }

    // Output lazy loader if any custom script uses lazy loading
    if (rvk_has_lazy_load_custom_scripts($settings)) {
        rvk_output_custom_scripts_lazy_loader($settings);
    }
}
add_action('wp_footer', 'rvk_output_custom_footer_scripts', 10);

// Check if script should load on current page
function rvk_should_load_script($script, $settings) {
    // Check if enabled
    if (!$script['enabled']) {
        return false;
    }

    // Check cookie consent if required
    if ($script['cookie_consent_required']) {
        $consent_check = rvk_check_script_consent($script, $settings);
        if ($consent_check === false) {
            return false;
        }
        // If defer_to_js, we'll handle consent check in JavaScript
    }

    // Check conditional loading
    if (!rvk_check_script_conditional_loading($script)) {
        return false;
    }

    return true;
}

// Check script conditional loading rules
function rvk_check_script_conditional_loading($script) {
    // If conditional loading not enabled, load everywhere
    if (!$script['conditional_loading']['enabled']) {
        return true;
    }

    // Static cache for repeated calls
    static $current_context = null;
    if ($current_context === null) {
        $current_context = array(
            'is_post' => is_singular('post'),
            'is_page' => is_page(),
            'post_type' => get_post_type(),
            'post_id' => get_the_ID(),
            'is_front' => is_front_page(),
            'is_archive' => is_archive(),
        );
    }

    $load_on = $script['conditional_loading']['load_on'];
    $exclude_from = $script['conditional_loading']['exclude_from'];

    // Check exclude rules first (higher priority)
    if (in_array('home', $exclude_from) && $current_context['is_front']) {
        return false;
    }
    if (in_array('archive', $exclude_from) && $current_context['is_archive']) {
        return false;
    }

    // Check load_on rules
    if ($load_on === 'posts' && !$current_context['is_post']) {
        return false;
    }
    if ($load_on === 'pages' && !$current_context['is_page']) {
        return false;
    }
    if ($load_on === 'post_types') {
        $allowed_types = $script['conditional_loading']['post_types'];
        if (!empty($allowed_types) && !in_array($current_context['post_type'], $allowed_types)) {
            return false;
        }
    }

    return true;
}

// Check cookie consent for custom scripts
function rvk_check_script_consent($script, $settings) {
    // If consent not required for this script, allow it
    if (!$script['cookie_consent_required']) {
        return true;
    }

    // Return defer_to_js flag for JavaScript handling
    return 'defer_to_js';
}

// Output a single script
function rvk_output_single_script($script, $settings) {
    $script_id = $script['id'];

    // Lazy load handling
    if ($script['lazy_load']) {
        $consent_required = $script['cookie_consent_required'] ? 'true' : 'false';
        ?>
        <script data-lazy-load="true" data-lazy-delay="<?php echo esc_attr($script['lazy_load_delay']); ?>" data-lazy-type="custom" data-lazy-script-id="<?php echo esc_attr($script_id); ?>" data-consent-required="<?php echo esc_attr($consent_required); ?>" data-script-type="<?php echo esc_attr($script['type']); ?>" data-script-content="<?php echo esc_attr($script['type'] === 'external' ? $script['content'] : base64_encode($script['content'])); ?>" data-script-async="<?php echo $script['async'] ? 'true' : 'false'; ?>" data-script-defer="<?php echo $script['defer'] ? 'true' : 'false'; ?>"></script>
        <?php
        return;
    }

    // Consent check (immediate load)
    $consent_check = rvk_check_script_consent($script, $settings);
    if ($consent_check === 'defer_to_js') {
        // Wrap in consent checker
        $consent_key = $settings['global']['consent_storage_key'] ?? 'rvk_scripts_consent';
        ?>
        <!-- Custom Script: <?php echo esc_html($script['name']); ?> (Consent Required) -->
        <script data-swup-ignore-script>
        (function() {
            function loadScript_<?php echo esc_js($script_id); ?>() {
                var consent = localStorage.getItem('<?php echo esc_js($consent_key); ?>');
                if (consent === 'granted') {
                    <?php if ($script['type'] === 'external'): ?>
                        var script = document.createElement('script');
                        script.src = '<?php echo esc_js($script['content']); ?>';
                        <?php if ($script['async']): ?>script.async = true;<?php endif; ?>
                        <?php if ($script['defer']): ?>script.defer = true;<?php endif; ?>
                        script.setAttribute('data-swup-ignore-script', '');
                        document.<?php echo $script['position'] === 'head' ? 'head' : 'body'; ?>.appendChild(script);
                    <?php else: ?>
                        <?php echo $script['content']; ?>
                    <?php endif; ?>
                }
            }

            // Check on page load
            if (document.readyState === 'complete') {
                loadScript_<?php echo esc_js($script_id); ?>();
            } else {
                window.addEventListener('load', loadScript_<?php echo esc_js($script_id); ?>);
            }

            // Listen for consent granted event
            window.addEventListener('rvk_consent_granted', loadScript_<?php echo esc_js($script_id); ?>);
        })();
        </script>
        <?php
    } else {
        // Immediate load without consent check
        if ($script['type'] === 'external') {
            // External script
            $attributes = '';
            if ($script['async']) {
                $attributes .= ' async';
            }
            if ($script['defer']) {
                $attributes .= ' defer';
            }
            ?>
            <!-- Custom Script: <?php echo esc_html($script['name']); ?> -->
            <script<?php echo $attributes; ?> data-swup-ignore-script src="<?php echo esc_url($script['content']); ?>"></script>
            <?php
        } else {
            // Inline script
            ?>
            <!-- Custom Script: <?php echo esc_html($script['name']); ?> -->
            <?php echo $script['content']; ?>
            <?php
        }
    }
}

// Check if any custom script uses lazy loading
function rvk_has_lazy_load_custom_scripts($settings) {
    foreach ($settings['scripts'] as $script) {
        if ($script['lazy_load']) {
            return true;
        }
    }
    return false;
}

// Output lazy loader for custom scripts
function rvk_output_custom_scripts_lazy_loader($settings) {
    static $output_once = false;
    if ($output_once) {
        return;
    }
    $output_once = true;

    $consent_key = $settings['global']['consent_storage_key'] ?? 'rvk_scripts_consent';
    ?>
    <!-- Custom Scripts Lazy Loader -->
    <script data-swup-ignore-script>
    (function() {
        'use strict';

        function loadLazyCustomScripts() {
            var lazyScripts = document.querySelectorAll('[data-lazy-type="custom"]');

            lazyScripts.forEach(function(placeholder) {
                var delay = parseInt(placeholder.getAttribute('data-lazy-delay')) || 0;
                var scriptId = placeholder.getAttribute('data-lazy-script-id');
                var consentRequired = placeholder.getAttribute('data-consent-required') === 'true';
                var scriptType = placeholder.getAttribute('data-script-type');
                var scriptContent = placeholder.getAttribute('data-script-content');
                var scriptAsync = placeholder.getAttribute('data-script-async') === 'true';
                var scriptDefer = placeholder.getAttribute('data-script-defer') === 'true';

                // Check consent if required
                if (consentRequired) {
                    var consent = localStorage.getItem('<?php echo esc_js($consent_key); ?>');
                    if (consent !== 'granted') {
                        return; // Skip loading if consent not granted
                    }
                }

                setTimeout(function() {
                    if (scriptType === 'external') {
                        // Load external script
                        var script = document.createElement('script');
                        script.src = scriptContent;
                        if (scriptAsync) script.async = true;
                        if (scriptDefer) script.defer = true;
                        script.setAttribute('data-swup-ignore-script', '');
                        document.body.appendChild(script);
                    } else {
                        // Inline script (base64 encoded)
                        var decodedContent = atob(scriptContent);
                        var script = document.createElement('div');
                        script.innerHTML = decodedContent;
                        document.body.appendChild(script);
                    }

                    // Remove placeholder
                    placeholder.remove();
                }, delay);
            });
        }

        // Trigger after page fully loaded
        if (document.readyState === 'complete') {
            loadLazyCustomScripts();
        } else {
            window.addEventListener('load', loadLazyCustomScripts);
        }

        // Also listen for consent granted event
        window.addEventListener('rvk_consent_granted', function() {
            loadLazyCustomScripts();
        });
    })();
    </script>
    <?php
}
