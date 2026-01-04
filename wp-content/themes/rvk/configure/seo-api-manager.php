<?php
/**
 * SEO API Manager
 * Manages API calls, rate limiting, caching, and quota tracking
 */

// Security: Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class RVK_SEO_API_Manager {

    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * Cache duration (24 hours)
     */
    const CACHE_DURATION = DAY_IN_SECONDS;

    /**
     * Rate limits per provider
     */
    const RATE_LIMITS = array(
        'gemini' => array(
            'per_minute' => 10,  // Conservative limit (Gemini 1.5 Flash free tier: 15/min)
            'per_day' => 1000    // Conservative limit (Gemini 1.5 Flash free tier: 1,500/day)
        ),
        'openai' => array(
            'per_minute' => 20,
            'per_day' => 5000
        )
    );

    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        // Add admin hooks
        add_action('admin_menu', array($this, 'add_usage_page'));
        add_action('wp_ajax_rvk_reset_api_stats', array($this, 'ajax_reset_stats'));
    }

    /**
     * Check if API call is allowed (rate limiting)
     *
     * @param string $provider Provider name (gemini/openai)
     * @return bool|WP_Error True if allowed, WP_Error if rate limited
     */
    public function check_rate_limit($provider = 'gemini') {
        // Get current counts
        $minute_count = (int) get_transient("rvk_api_count_{$provider}_minute");
        $day_count = (int) get_option("rvk_api_count_{$provider}_day", 0);

        // Check limits
        $limits = self::RATE_LIMITS[$provider] ?? self::RATE_LIMITS['gemini'];

        if ($minute_count >= $limits['per_minute']) {
            return new WP_Error(
                'rate_limit_minute',
                sprintf(
                    __('API rate limit reached: %d requests per minute. Please wait before trying again.', 'dev-theme'),
                    $limits['per_minute']
                )
            );
        }

        if ($day_count >= $limits['per_day']) {
            return new WP_Error(
                'rate_limit_day',
                sprintf(
                    __('Daily API quota reached: %d requests per day. Limit will reset tomorrow.', 'dev-theme'),
                    $limits['per_day']
                )
            );
        }

        return true;
    }

    /**
     * Increment API usage counters
     *
     * @param string $provider Provider name
     * @param int $tokens Token count (optional)
     */
    public function increment_usage($provider = 'gemini', $tokens = 0) {
        // Increment per-minute counter
        $minute_count = (int) get_transient("rvk_api_count_{$provider}_minute");
        set_transient("rvk_api_count_{$provider}_minute", $minute_count + 1, MINUTE_IN_SECONDS);

        // Increment daily counter
        $day_count = (int) get_option("rvk_api_count_{$provider}_day", 0);
        update_option("rvk_api_count_{$provider}_day", $day_count + 1, false);

        // Track tokens if provided
        if ($tokens > 0) {
            $day_tokens = (int) get_option("rvk_api_tokens_{$provider}_day", 0);
            update_option("rvk_api_tokens_{$provider}_day", $day_tokens + $tokens, false);
        }

        // Update last reset date if needed
        $last_reset = get_option("rvk_api_last_reset_{$provider}");
        if (!$last_reset || date('Y-m-d', strtotime($last_reset)) !== date('Y-m-d')) {
            // New day - reset daily counter
            update_option("rvk_api_count_{$provider}_day", 1, false);
            update_option("rvk_api_tokens_{$provider}_day", $tokens, false);
            update_option("rvk_api_last_reset_{$provider}", current_time('mysql'), false);
        }

        // Log to history (keep last 100 calls)
        $history = get_option("rvk_api_history_{$provider}", array());
        $history[] = array(
            'timestamp' => current_time('mysql'),
            'tokens' => $tokens
        );

        // Keep only last 100 entries
        if (count($history) > 100) {
            $history = array_slice($history, -100);
        }

        update_option("rvk_api_history_{$provider}", $history, false);
    }

    /**
     * Get cached result
     *
     * @param string $cache_key Cache key
     * @return mixed|false Cached value or false
     */
    public function get_cache($cache_key) {
        return get_transient('rvk_seo_cache_' . md5($cache_key));
    }

    /**
     * Set cached result
     *
     * @param string $cache_key Cache key
     * @param mixed $value Value to cache
     * @param int $duration Cache duration (default: 24 hours)
     */
    public function set_cache($cache_key, $value, $duration = null) {
        if ($duration === null) {
            $duration = self::CACHE_DURATION;
        }

        set_transient('rvk_seo_cache_' . md5($cache_key), $value, $duration);
    }

    /**
     * Clear all AI cache
     */
    public function clear_cache() {
        global $wpdb;

        $wpdb->query(
            "DELETE FROM {$wpdb->options}
             WHERE option_name LIKE '_transient_rvk_seo_cache_%'
             OR option_name LIKE '_transient_timeout_rvk_seo_cache_%'"
        );
    }

    /**
     * Get usage statistics
     *
     * @param string $provider Provider name
     * @return array Usage stats
     */
    public function get_usage_stats($provider = 'gemini') {
        return array(
            'minute_count' => (int) get_transient("rvk_api_count_{$provider}_minute"),
            'day_count' => (int) get_option("rvk_api_count_{$provider}_day", 0),
            'day_tokens' => (int) get_option("rvk_api_tokens_{$provider}_day", 0),
            'last_reset' => get_option("rvk_api_last_reset_{$provider}"),
            'history' => get_option("rvk_api_history_{$provider}", array()),
            'limits' => self::RATE_LIMITS[$provider] ?? self::RATE_LIMITS['gemini']
        );
    }

    /**
     * Reset usage statistics
     *
     * @param string $provider Provider name
     */
    public function reset_stats($provider = 'gemini') {
        delete_transient("rvk_api_count_{$provider}_minute");
        delete_option("rvk_api_count_{$provider}_day");
        delete_option("rvk_api_tokens_{$provider}_day");
        delete_option("rvk_api_last_reset_{$provider}");
        delete_option("rvk_api_history_{$provider}");
    }

    /**
     * Add usage statistics page
     */
    public function add_usage_page() {
        add_submenu_page(
            'options-general.php',
            __('SEO API Usage', 'dev-theme'),
            __('SEO API Usage', 'dev-theme'),
            'manage_options',
            'seo-api-usage',
            array($this, 'render_usage_page')
        );
    }

    /**
     * Render usage statistics page
     */
    public function render_usage_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $gemini_stats = $this->get_usage_stats('gemini');
        $openai_stats = $this->get_usage_stats('openai');

        ?>
        <div class="wrap">
            <h1><?php _e('SEO API Usage Statistics', 'dev-theme'); ?></h1>

            <div class="notice notice-info">
                <p>
                    <strong><?php _e('Rate Limiting:', 'dev-theme'); ?></strong>
                    <?php _e('To prevent quota exhaustion, API calls are automatically rate-limited. Daily counters reset at midnight.', 'dev-theme'); ?>
                </p>
            </div>

            <!-- Google Gemini Stats -->
            <div class="card" style="max-width: 800px; margin: 20px 0;">
                <h2>Google Gemini API</h2>

                <table class="widefat">
                    <tbody>
                        <tr>
                            <th style="width: 200px;"><?php _e('Current Minute:', 'dev-theme'); ?></th>
                            <td>
                                <strong><?php echo esc_html($gemini_stats['minute_count']); ?></strong> / <?php echo esc_html($gemini_stats['limits']['per_minute']); ?>
                                <span style="color: <?php echo $gemini_stats['minute_count'] > ($gemini_stats['limits']['per_minute'] * 0.8) ? '#dc3232' : '#46b450'; ?>;">
                                    (<?php echo round(($gemini_stats['minute_count'] / $gemini_stats['limits']['per_minute']) * 100); ?>%)
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e('Today:', 'dev-theme'); ?></th>
                            <td>
                                <strong><?php echo esc_html($gemini_stats['day_count']); ?></strong> / <?php echo esc_html($gemini_stats['limits']['per_day']); ?>
                                <span style="color: <?php echo $gemini_stats['day_count'] > ($gemini_stats['limits']['per_day'] * 0.8) ? '#dc3232' : '#46b450'; ?>;">
                                    (<?php echo round(($gemini_stats['day_count'] / $gemini_stats['limits']['per_day']) * 100); ?>%)
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e('Tokens Today:', 'dev-theme'); ?></th>
                            <td><?php echo number_format($gemini_stats['day_tokens']); ?> / 1,000,000</td>
                        </tr>
                        <tr>
                            <th><?php _e('Last Reset:', 'dev-theme'); ?></th>
                            <td><?php echo $gemini_stats['last_reset'] ? esc_html($gemini_stats['last_reset']) : __('Never', 'dev-theme'); ?></td>
                        </tr>
                    </tbody>
                </table>

                <?php if (!empty($gemini_stats['history'])): ?>
                <h3><?php _e('Recent API Calls', 'dev-theme'); ?></h3>
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th><?php _e('Timestamp', 'dev-theme'); ?></th>
                            <th><?php _e('Tokens', 'dev-theme'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice(array_reverse($gemini_stats['history']), 0, 10) as $call): ?>
                        <tr>
                            <td><?php echo esc_html($call['timestamp']); ?></td>
                            <td><?php echo number_format($call['tokens']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>

                <p style="margin-top: 20px;">
                    <button type="button" class="button" onclick="resetApiStats('gemini')">
                        <?php _e('Reset Gemini Statistics', 'dev-theme'); ?>
                    </button>
                </p>
            </div>

            <!-- OpenAI Stats -->
            <div class="card" style="max-width: 800px; margin: 20px 0;">
                <h2>OpenAI API</h2>

                <table class="widefat">
                    <tbody>
                        <tr>
                            <th style="width: 200px;"><?php _e('Current Minute:', 'dev-theme'); ?></th>
                            <td>
                                <strong><?php echo esc_html($openai_stats['minute_count']); ?></strong> / <?php echo esc_html($openai_stats['limits']['per_minute']); ?>
                                <span style="color: <?php echo $openai_stats['minute_count'] > ($openai_stats['limits']['per_minute'] * 0.8) ? '#dc3232' : '#46b450'; ?>;">
                                    (<?php echo round(($openai_stats['minute_count'] / $openai_stats['limits']['per_minute']) * 100); ?>%)
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e('Today:', 'dev-theme'); ?></th>
                            <td>
                                <strong><?php echo esc_html($openai_stats['day_count']); ?></strong> / <?php echo esc_html($openai_stats['limits']['per_day']); ?>
                                <span style="color: <?php echo $openai_stats['day_count'] > ($openai_stats['limits']['per_day'] * 0.8) ? '#dc3232' : '#46b450'; ?>;">
                                    (<?php echo round(($openai_stats['day_count'] / $openai_stats['limits']['per_day']) * 100); ?>%)
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e('Tokens Today:', 'dev-theme'); ?></th>
                            <td><?php echo number_format($openai_stats['day_tokens']); ?></td>
                        </tr>
                        <tr>
                            <th><?php _e('Last Reset:', 'dev-theme'); ?></th>
                            <td><?php echo $openai_stats['last_reset'] ? esc_html($openai_stats['last_reset']) : __('Never', 'dev-theme'); ?></td>
                        </tr>
                    </tbody>
                </table>

                <p style="margin-top: 20px;">
                    <button type="button" class="button" onclick="resetApiStats('openai')">
                        <?php _e('Reset OpenAI Statistics', 'dev-theme'); ?>
                    </button>
                </p>
            </div>

            <!-- Cache Management -->
            <div class="card" style="max-width: 800px; margin: 20px 0;">
                <h2><?php _e('Cache Management', 'dev-theme'); ?></h2>
                <p><?php _e('AI responses are cached for 24 hours to reduce API calls.', 'dev-theme'); ?></p>
                <button type="button" class="button" onclick="clearApiCache()">
                    <?php _e('Clear All Cache', 'dev-theme'); ?>
                </button>
            </div>
        </div>

        <script>
        function resetApiStats(provider) {
            if (!confirm('<?php _e('Are you sure you want to reset statistics?', 'dev-theme'); ?>')) {
                return;
            }

            jQuery.post(ajaxurl, {
                action: 'rvk_reset_api_stats',
                provider: provider,
                nonce: '<?php echo wp_create_nonce('rvk_reset_stats'); ?>'
            }, function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data);
                }
            });
        }

        function clearApiCache() {
            if (!confirm('<?php _e('Are you sure you want to clear the cache?', 'dev-theme'); ?>')) {
                return;
            }

            jQuery.post(ajaxurl, {
                action: 'rvk_reset_api_stats',
                provider: 'cache',
                nonce: '<?php echo wp_create_nonce('rvk_reset_stats'); ?>'
            }, function(response) {
                if (response.success) {
                    alert('<?php _e('Cache cleared successfully!', 'dev-theme'); ?>');
                } else {
                    alert(response.data);
                }
            });
        }
        </script>
        <?php
    }

    /**
     * AJAX: Reset statistics
     */
    public function ajax_reset_stats() {
        check_ajax_referer('rvk_reset_stats', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Unauthorized', 'dev-theme'));
        }

        $provider = sanitize_text_field($_POST['provider'] ?? '');

        if ($provider === 'cache') {
            $this->clear_cache();
            wp_send_json_success(__('Cache cleared!', 'dev-theme'));
        } elseif (in_array($provider, array('gemini', 'openai'))) {
            $this->reset_stats($provider);
            wp_send_json_success(__('Statistics reset!', 'dev-theme'));
        } else {
            wp_send_json_error(__('Invalid provider', 'dev-theme'));
        }
    }
}

// Initialize
RVK_SEO_API_Manager::get_instance();
