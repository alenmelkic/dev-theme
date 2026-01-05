<?php
/**
 * Analytics Helpers
 * Frontend script output for Google Analytics 4, Google Tag Manager, and Facebook Pixel
 */

// Security: Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Output analytics scripts in head
function rvk_output_analytics_head_scripts() {
    // Skip if in admin
    if (is_admin()) {
        return;
    }

    // Get settings
    $settings = get_option('rvk_analytics_settings', rvk_get_default_analytics_settings());

    // Check if any integration is enabled
    if (!$settings['ga4']['enabled'] && !$settings['gtm']['enabled'] && !$settings['facebook_pixel']['enabled']) {
        return;
    }

    // Output GA4 if enabled
    if ($settings['ga4']['enabled'] && !empty($settings['ga4']['measurement_id'])) {
        rvk_output_ga4_script($settings);
    }

    // Output GTM if enabled (head portion)
    if ($settings['gtm']['enabled'] && !empty($settings['gtm']['container_id'])) {
        rvk_output_gtm_head_script($settings);
    }

    // Output Facebook Pixel if enabled
    if ($settings['facebook_pixel']['enabled'] && !empty($settings['facebook_pixel']['pixel_id'])) {
        rvk_output_facebook_pixel_script($settings);
    }
}
add_action('wp_head', 'rvk_output_analytics_head_scripts', 1);

// Output analytics scripts in footer
function rvk_output_analytics_footer_scripts() {
    // Skip if in admin
    if (is_admin()) {
        return;
    }

    // Get settings
    $settings = get_option('rvk_analytics_settings', rvk_get_default_analytics_settings());

    // Output GTM noscript if enabled
    if ($settings['gtm']['enabled'] && !empty($settings['gtm']['container_id'])) {
        rvk_output_gtm_footer_noscript($settings);
    }

    // Output lazy loader if any integration uses lazy loading
    if (rvk_has_lazy_load_integrations($settings)) {
        rvk_output_integrations_lazy_loader();
    }
}
add_action('wp_footer', 'rvk_output_analytics_footer_scripts', 5);

// Check if user has granted consent for analytics
function rvk_check_analytics_consent($settings, $type = 'ga4') {
    $consent_mode = $settings['performance']['consent_mode'] ?? 'none';

    // No consent required
    if ($consent_mode === 'none') {
        return true;
    }

    // Manual consent mode - defer to JavaScript
    if ($consent_mode === 'manual') {
        // Check if this specific integration requires consent
        if (isset($settings[$type]['cookie_consent_required']) && $settings[$type]['cookie_consent_required']) {
            return 'defer_to_js'; // Special flag for JavaScript handling
        }
        return true; // This integration doesn't require consent
    }

    return true;
}

// Output Google Analytics 4 script
function rvk_output_ga4_script($settings) {
    $ga4 = $settings['ga4'];
    $measurement_id = $ga4['measurement_id'];

    // Check consent
    $consent_check = rvk_check_analytics_consent($settings, 'ga4');
    if ($consent_check === false) {
        return;
    }

    // Lazy load handling
    if ($ga4['lazy_load']) {
        // Output placeholder for lazy loading
        ?>
        <script data-lazy-load="true" data-lazy-delay="<?php echo esc_attr($ga4['lazy_load_delay']); ?>" data-lazy-type="ga4" data-lazy-id="<?php echo esc_attr($measurement_id); ?>" data-consent-required="<?php echo $consent_check === 'defer_to_js' ? 'true' : 'false'; ?>"></script>
        <?php
        return;
    }

    // Immediate load with consent check
    if ($consent_check === 'defer_to_js') {
        ?>
        <!-- Google Analytics 4 (Consent Required) -->
        <script data-swup-ignore-script>
        (function() {
            function loadGA4() {
                var consentKey = '<?php echo esc_js($settings['performance']['consent_storage_key'] ?? 'rvk_analytics_consent'); ?>';
                var consent = localStorage.getItem(consentKey);
                if (consent === 'granted') {
                    var script = document.createElement('script');
                    script.src = 'https://www.googletagmanager.com/gtag/js?id=<?php echo esc_js($measurement_id); ?>';
                    script.async = true;
                    script.setAttribute('data-swup-ignore-script', '');
                    document.head.appendChild(script);

                    window.dataLayer = window.dataLayer || [];
                    function gtag(){dataLayer.push(arguments);}
                    gtag('js', new Date());
                    gtag('config', '<?php echo esc_js($measurement_id); ?>');
                }
            }

            // Check on page load
            if (document.readyState === 'complete') {
                loadGA4();
            } else {
                window.addEventListener('load', loadGA4);
            }

            // Listen for consent granted event
            window.addEventListener('rvk_consent_granted', loadGA4);
        })();
        </script>
        <?php
    } else {
        // Immediate load without consent check
        $async_attr = $ga4['async_loading'] ? 'async' : '';
        $defer_attr = $ga4['defer_loading'] ? 'defer' : '';
        ?>
        <!-- Google Analytics 4 -->
        <script <?php echo $async_attr; ?> data-swup-ignore-script src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr($measurement_id); ?>"></script>
        <script data-swup-ignore-script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '<?php echo esc_js($measurement_id); ?>');
        </script>
        <?php
    }
}

// Output Google Tag Manager head script
function rvk_output_gtm_head_script($settings) {
    $gtm = $settings['gtm'];
    $container_id = $gtm['container_id'];

    // Check consent
    $consent_check = rvk_check_analytics_consent($settings, 'gtm');
    if ($consent_check === false) {
        return;
    }

    // Lazy load handling
    if ($gtm['lazy_load']) {
        ?>
        <script data-lazy-load="true" data-lazy-delay="<?php echo esc_attr($gtm['lazy_load_delay']); ?>" data-lazy-type="gtm" data-lazy-id="<?php echo esc_attr($container_id); ?>" data-consent-required="<?php echo $consent_check === 'defer_to_js' ? 'true' : 'false'; ?>"></script>
        <?php
        return;
    }

    // Immediate load with consent check
    if ($consent_check === 'defer_to_js') {
        ?>
        <!-- Google Tag Manager (Consent Required) -->
        <script data-swup-ignore-script>
        (function() {
            function loadGTM() {
                var consentKey = '<?php echo esc_js($settings['performance']['consent_storage_key'] ?? 'rvk_analytics_consent'); ?>';
                var consent = localStorage.getItem(consentKey);
                if (consent === 'granted') {
                    (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
                    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
                    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
                    'https://www.googletagmanager.com/gtm.js?id='+i+dl;j.setAttribute('data-swup-ignore-script', '');f.parentNode.insertBefore(j,f);
                    })(window,document,'script','dataLayer','<?php echo esc_js($container_id); ?>');
                }
            }

            // Check on page load
            if (document.readyState === 'complete') {
                loadGTM();
            } else {
                window.addEventListener('load', loadGTM);
            }

            // Listen for consent granted event
            window.addEventListener('rvk_consent_granted', loadGTM);
        })();
        </script>
        <?php
    } else {
        // Immediate load without consent check
        ?>
        <!-- Google Tag Manager -->
        <script data-swup-ignore-script>
        (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id='+i+dl;j.setAttribute('data-swup-ignore-script', '');f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','<?php echo esc_js($container_id); ?>');
        </script>
        <?php
    }
}

// Output Google Tag Manager noscript (footer)
function rvk_output_gtm_footer_noscript($settings) {
    $container_id = $settings['gtm']['container_id'];
    ?>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo esc_attr($container_id); ?>" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    <?php
}

// Output Facebook Pixel script
function rvk_output_facebook_pixel_script($settings) {
    $pixel = $settings['facebook_pixel'];
    $pixel_id = $pixel['pixel_id'];

    // Check consent
    $consent_check = rvk_check_analytics_consent($settings, 'facebook_pixel');
    if ($consent_check === false) {
        return;
    }

    // Lazy load handling
    if ($pixel['lazy_load']) {
        ?>
        <script data-lazy-load="true" data-lazy-delay="<?php echo esc_attr($pixel['lazy_load_delay']); ?>" data-lazy-type="facebook_pixel" data-lazy-id="<?php echo esc_attr($pixel_id); ?>" data-consent-required="<?php echo $consent_check === 'defer_to_js' ? 'true' : 'false'; ?>" data-track-pageview="<?php echo $pixel['track_page_view'] ? 'true' : 'false'; ?>"></script>
        <?php
        return;
    }

    // Immediate load with consent check
    if ($consent_check === 'defer_to_js') {
        ?>
        <!-- Facebook Pixel (Consent Required) -->
        <script data-swup-ignore-script>
        (function() {
            function loadFBPixel() {
                var consentKey = '<?php echo esc_js($settings['performance']['consent_storage_key'] ?? 'rvk_analytics_consent'); ?>';
                var consent = localStorage.getItem(consentKey);
                if (consent === 'granted') {
                    !function(f,b,e,v,n,t,s)
                    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
                    n.callMethod.apply(n,arguments):n.queue.push(arguments)};
                    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
                    n.queue=[];t=b.createElement(e);t.async=!0;
                    t.src=v;s=b.getElementsByTagName(e)[0];
                    s.parentNode.insertBefore(t,s)}(window, document,'script',
                    'https://connect.facebook.net/en_US/fbevents.js');
                    fbq('init', '<?php echo esc_js($pixel_id); ?>');
                    <?php if ($pixel['track_page_view']): ?>
                    fbq('track', 'PageView');
                    <?php endif; ?>
                }
            }

            // Check on page load
            if (document.readyState === 'complete') {
                loadFBPixel();
            } else {
                window.addEventListener('load', loadFBPixel);
            }

            // Listen for consent granted event
            window.addEventListener('rvk_consent_granted', loadFBPixel);
        })();
        </script>
        <?php
    } else {
        // Immediate load without consent check
        $async_attr = $pixel['async_loading'] ? 'async' : '';
        ?>
        <!-- Facebook Pixel Code -->
        <script data-swup-ignore-script>
        !function(f,b,e,v,n,t,s)
        {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};
        if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
        n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t,s)}(window, document,'script',
        'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '<?php echo esc_js($pixel_id); ?>');
        <?php if ($pixel['track_page_view']): ?>
        fbq('track', 'PageView');
        <?php endif; ?>
        </script>
        <noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=<?php echo esc_attr($pixel_id); ?>&ev=PageView&noscript=1"/></noscript>
        <!-- End Facebook Pixel Code -->
        <?php
    }
}

// Check if any integration uses lazy loading
function rvk_has_lazy_load_integrations($settings) {
    return ($settings['ga4']['lazy_load'] ?? false) ||
           ($settings['gtm']['lazy_load'] ?? false) ||
           ($settings['facebook_pixel']['lazy_load'] ?? false);
}

// Output shared lazy loader script
function rvk_output_integrations_lazy_loader() {
    static $output_once = false;
    if ($output_once) {
        return;
    }
    $output_once = true;

    $settings = get_option('rvk_analytics_settings', rvk_get_default_analytics_settings());
    $consent_key = $settings['performance']['consent_storage_key'] ?? 'rvk_analytics_consent';
    ?>
    <!-- Analytics Lazy Loader -->
    <script data-swup-ignore-script>
    (function() {
        'use strict';

        function loadLazyIntegrations() {
            var lazyScripts = document.querySelectorAll('[data-lazy-load="true"]');

            lazyScripts.forEach(function(placeholder) {
                var delay = parseInt(placeholder.getAttribute('data-lazy-delay')) || 0;
                var type = placeholder.getAttribute('data-lazy-type');
                var id = placeholder.getAttribute('data-lazy-id');
                var consentRequired = placeholder.getAttribute('data-consent-required') === 'true';
                var trackPageview = placeholder.getAttribute('data-track-pageview') === 'true';

                // Check consent if required
                if (consentRequired) {
                    var consent = localStorage.getItem('<?php echo esc_js($consent_key); ?>');
                    if (consent !== 'granted') {
                        return; // Skip loading if consent not granted
                    }
                }

                setTimeout(function() {
                    if (type === 'ga4') {
                        // Load GA4
                        var script1 = document.createElement('script');
                        script1.src = 'https://www.googletagmanager.com/gtag/js?id=' + id;
                        script1.async = true;
                        script1.setAttribute('data-swup-ignore-script', '');
                        document.head.appendChild(script1);

                        var script2 = document.createElement('script');
                        script2.setAttribute('data-swup-ignore-script', '');
                        script2.textContent = 'window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag("js",new Date());gtag("config","' + id + '");';
                        document.head.appendChild(script2);
                    } else if (type === 'gtm') {
                        // Load GTM
                        var script = document.createElement('script');
                        script.setAttribute('data-swup-ignore-script', '');
                        script.textContent = '(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({"gtm.start":new Date().getTime(),event:"gtm.js"});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!="dataLayer"?"&l="+l:"";j.async=true;j.src="https://www.googletagmanager.com/gtm.js?id="+i+dl;j.setAttribute("data-swup-ignore-script","");f.parentNode.insertBefore(j,f);})(window,document,"script","dataLayer","' + id + '");';
                        document.head.appendChild(script);
                    } else if (type === 'facebook_pixel') {
                        // Load Facebook Pixel
                        var script = document.createElement('script');
                        script.setAttribute('data-swup-ignore-script', '');
                        script.textContent = '!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version="2.0";n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,"script","https://connect.facebook.net/en_US/fbevents.js");fbq("init","' + id + '");' + (trackPageview ? 'fbq("track","PageView");' : '');
                        document.head.appendChild(script);
                    }

                    // Remove placeholder
                    placeholder.remove();
                }, delay);
            });
        }

        // Trigger after page fully loaded
        if (document.readyState === 'complete') {
            loadLazyIntegrations();
        } else {
            window.addEventListener('load', loadLazyIntegrations);
        }

        // Also listen for consent granted event
        window.addEventListener('rvk_consent_granted', function() {
            loadLazyIntegrations();
        });
    })();
    </script>
    <?php
}
