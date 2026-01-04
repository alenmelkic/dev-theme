<?php
/**
 * SEO Core - Main Orchestrator
 * Manages all SEO/AEO functionality and coordinates modules
 * Tailored by Alen Melkić
 */

// Security: Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class RVK_SEO_Core {

    /**
     * SEO System Version
     */
    const VERSION = '1.0.0';

    /**
     * Singleton instance
     */
    private static $instance = null;

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
     * Constructor - Initialize all hooks and modules
     */
    private function __construct() {
        // Register post meta fields on init to ensure all CPTs are loaded
        // This must run before REST API initialization
        add_action('init', array($this, 'register_post_meta_fields'), 10);

        // Initialize modules
        add_action('init', array($this, 'init_modules'));


        // Register meta boxes
        add_action('add_meta_boxes', array($this, 'register_meta_boxes'));

        // Save post meta
        add_action('save_post', array($this, 'save_post_meta'), 10, 2);

        // Save term meta
        add_action('edited_term', array($this, 'save_term_meta'), 10, 3);
        add_action('create_term', array($this, 'save_term_meta'), 10, 3);

        // Debug REST saving (ALL types)
        add_filter('rest_pre_insert_post', array($this, 'debug_rest_save'), 10, 2);
        add_filter('rest_pre_insert_page', array($this, 'debug_rest_save'), 10, 2);
        add_filter('rest_pre_insert_servicne-informacije', array($this, 'debug_rest_save'), 10, 2);
        add_filter('rest_pre_insert_obavijesti-o-smrti', array($this, 'debug_rest_save'), 10, 2);

        // Deep Debug: Trace who is updating meta
        add_action('updated_post_meta', array($this, 'debug_meta_updates'), 1, 4);
        add_action('added_post_meta', array($this, 'debug_meta_updates'), 1, 4);

        // Add term meta fields to category/tag edit screens
        add_action('category_edit_form_fields', array($this, 'render_term_meta_fields'), 10, 2);
        add_action('post_tag_edit_form_fields', array($this, 'render_term_meta_fields'), 10, 2);

        // Enqueue admin scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));

        // Add admin menu pages
        add_action('admin_menu', array($this, 'add_admin_menus'));

        // Register AJAX handlers
        add_action('wp_ajax_rvk_seo_analyze_content', array($this, 'ajax_analyze_content'));

        // Handle title output
        add_filter('pre_get_document_title', array($this, 'filter_document_title'), 20);
        add_filter('document_title_parts', array($this, 'filter_title_parts'), 20);
    }

    /**
     * Initialize SEO modules
     */
    public function init_modules() {
        // Log initialization
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('RVK SEO/AEO v' . self::VERSION . ' initialized');
        }
    }

    public function register_post_meta_fields() {
        // Get all public post types that support custom fields
        $post_types = get_post_types(array('public' => true), 'names');
        error_log("SEO REGISTER: registering for post types: " . implode(', ', $post_types));

        $meta_fields = array(
            '_seo_title' => 'string',
            '_seo_description' => 'string',
            '_seo_keywords' => 'string',
            '_seo_canonical' => 'string',
            '_seo_noindex' => 'string',
            '_seo_nofollow' => 'string',
            '_seo_og_title' => 'string',
            '_seo_og_description' => 'string',
            '_seo_og_image' => 'integer',
            '_seo_twitter_title' => 'string',
            '_seo_twitter_description' => 'string',
            '_seo_twitter_image' => 'integer',
            '_seo_readability_score' => 'number',
            '_seo_content_score' => 'number',
            '_seo_last_optimized' => 'string'
        );

        $post_types = get_post_types(array('public' => true));
        // Explicitly ensure our CPTs are included
        if (post_type_exists('servicne-informacije') && !in_array('servicne-informacije', $post_types)) $post_types[] = 'servicne-informacije';
        if (post_type_exists('obavijesti-o-smrti') && !in_array('obavijesti-o-smrti', $post_types)) $post_types[] = 'obavijesti-o-smrti';

        $sanitization_callbacks = array(
            'string'  => function($v, $meta_key, $object_type, $object_id) { 
                $clean = sanitize_text_field($v); 
                error_log("SEO SANITIZE: Key $meta_key, Value: '$v' -> '$clean'");
                return $clean; 
            },
            'integer' => function($v) { return absint($v); },
            'number'  => function($v) { return floatval($v); }
        );

        foreach ($post_types as $post_type) {
            foreach ($meta_fields as $meta_key => $type) {
                register_post_meta($post_type, $meta_key, array(
                    'show_in_rest'      => true,
                    'single'            => true,
                    'type'              => $type,
                    'default'           => ($type === 'number' || $type === 'integer') ? 0 : '',
                    'sanitize_callback' => $sanitization_callbacks[$type] ?? 'sanitize_text_field',
                    'auth_callback'     => function($allowed, $meta_key, $post_id, $user_id, $cap, $caps) {
                        $can_edit = current_user_can('edit_post', $post_id);
                        if (!$can_edit) {
                            error_log("SEO AUTH [DENIED]: User $user_id cannot edit post $post_id for $meta_key");
                        } else {
                            // error_log("SEO AUTH [ALLOWED]: User $user_id can edit post $post_id for $meta_key");
                        }
                        return $can_edit;
                    }
                ));
            }
        }
    }


    /**
     * Register meta boxes for post editor
     */
    public function register_meta_boxes() {
        $post_types = get_post_types(array('public' => true), 'names');

        foreach ($post_types as $post_type) {
            add_meta_box(
                'rvk_seo_meta_box',
                'SEO & AEO Optimization',
                array($this, 'render_seo_meta_box'),
                $post_type,
                'normal',
                'high'
            );
        }
    }

    /**
     * Render SEO meta box in classic editor
     *
     * @param WP_Post $post Current post object
     */
    public function render_seo_meta_box($post) {
        // Nonce for security
        wp_nonce_field('rvk_seo_meta_box', 'rvk_seo_meta_box_nonce');

        // Get current values
        $seo_title = rvk_get_seo_meta($post->ID, 'naslov');
        $seo_description = rvk_get_seo_meta($post->ID, 'opis');
        $seo_keywords = rvk_get_seo_meta($post->ID, 'tagovi');
        $seo_canonical = rvk_get_seo_meta($post->ID, 'canonical');
        $seo_noindex = rvk_get_seo_meta($post->ID, 'noindex');
        $seo_nofollow = rvk_get_seo_meta($post->ID, 'nofollow');

        ?>
        <div class="rvk-seo-meta-box">
            <div class="rvk-seo-field">
                <label for="rvk_seo_title">
                    <strong>SEO Title</strong>
                    <span class="description">(Optimal: 50-60 characters)</span>
                </label>
                <input type="text"
                       id="rvk_seo_title"
                       name="rvk_seo_title"
                       value="<?php echo esc_attr($seo_title); ?>"
                       class="widefat"
                       maxlength="60">
                <span class="char-count"><span id="title-char-count">0</span>/60</span>
            </div>

            <div class="rvk-seo-field">
                <label for="rvk_seo_description">
                    <strong>Meta Description</strong>
                    <span class="description">(Optimal: 150-160 characters)</span>
                </label>
                <textarea id="rvk_seo_description"
                          name="rvk_seo_description"
                          class="widefat"
                          rows="3"
                          maxlength="160"><?php echo esc_textarea($seo_description); ?></textarea>
                <span class="char-count"><span id="desc-char-count">0</span>/160</span>
            </div>

            <div class="rvk-seo-field">
                <label for="rvk_seo_keywords">
                    <strong>Focus Keywords</strong>
                    <span class="description">(Comma-separated)</span>
                </label>
                <input type="text"
                       id="rvk_seo_keywords"
                       name="rvk_seo_keywords"
                       value="<?php echo esc_attr($seo_keywords); ?>"
                       class="widefat"
                       placeholder="e.g., WordPress, SEO, optimization">
            </div>

            <div class="rvk-seo-field">
                <label for="rvk_seo_canonical">
                    <strong>Canonical URL</strong>
                    <span class="description">(Leave empty for default)</span>
                </label>
                <input type="url"
                       id="rvk_seo_canonical"
                       name="rvk_seo_canonical"
                       value="<?php echo esc_url($seo_canonical); ?>"
                       class="widefat"
                       placeholder="https://example.com/page">
            </div>

            <div class="rvk-seo-field">
                <label>
                    <input type="checkbox"
                           id="rvk_seo_noindex"
                           name="rvk_seo_noindex"
                           value="1"
                           <?php checked($seo_noindex, '1'); ?>>
                    <strong>No Index</strong>
                    <span class="description">(Prevent search engines from indexing)</span>
                </label>
            </div>

            <div class="rvk-seo-field">
                <label>
                    <input type="checkbox"
                           id="rvk_seo_nofollow"
                           name="rvk_seo_nofollow"
                           value="1"
                           <?php checked($seo_nofollow, '1'); ?>>
                    <strong>No Follow</strong>
                    <span class="description">(Prevent search engines from following links)</span>
                </label>
            </div>

            <div class="rvk-seo-actions">
                <button type="button" class="button button-secondary rvk-seo-ai-generate">
                    🤖 Generate All with AI
                </button>
            </div>
        </div>

        <style>
            .rvk-seo-meta-box {
                padding: 10px 0;
            }
            .rvk-seo-field {
                margin-bottom: 20px;
            }
            .rvk-seo-field label {
                display: block;
                margin-bottom: 5px;
            }
            .rvk-seo-field .description {
                color: #666;
                font-size: 12px;
                font-style: italic;
            }
            .rvk-seo-field .char-count {
                display: block;
                text-align: right;
                font-size: 12px;
                color: #666;
                margin-top: 5px;
            }
            .rvk-seo-actions {
                margin-top: 15px;
                padding-top: 15px;
                border-top: 1px solid #ddd;
            }
        </style>

        <script>
            jQuery(document).ready(function($) {
                // Character counters
                function updateCharCount(input, counter) {
                    var length = $(input).val().length;
                    $('#' + counter).text(length);
                }

                $('#rvk_seo_title').on('input', function() {
                    updateCharCount(this, 'title-char-count');
                });

                $('#rvk_seo_description').on('input', function() {
                    updateCharCount(this, 'desc-char-count');
                });

                // Initialize counts
                updateCharCount('#rvk_seo_title', 'title-char-count');
                updateCharCount('#rvk_seo_description', 'desc-char-count');
            });
        </script>
        <?php
    }

    /**
     * Save SEO meta data when post is saved
     *
     * @param int $post_id Post ID
     * @param WP_Post $post Post object
     */
    public function save_post_meta($post_id, $post) {
        // Skip for autosave or revisions
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (wp_is_post_revision($post_id)) return;

        // Security check for classic meta box
        // IMPORTANT: We ONLY process this for classic editor POST requests with our nonce.
        // Gutenberg updates happen automatically via REST API and register_post_meta['show_in_rest'].
        if (!isset($_POST['rvk_seo_meta_box_nonce']) || !wp_verify_nonce($_POST['rvk_seo_meta_box_nonce'], 'rvk_seo_meta_box')) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save SEO title
        if (isset($_POST['rvk_seo_title'])) {
            $seo_title = rvk_sanitize_seo_title($_POST['rvk_seo_title']);
            rvk_update_seo_meta($post_id, 'naslov', $seo_title);
        }

        // Save meta description
        if (isset($_POST['rvk_seo_description'])) {
            $seo_description = rvk_sanitize_meta_description($_POST['rvk_seo_description']);
            rvk_update_seo_meta($post_id, 'opis', $seo_description);
        }

        // Save keywords/tags
        if (isset($_POST['rvk_seo_keywords'])) {
            $seo_keywords = sanitize_text_field($_POST['rvk_seo_keywords']);
            rvk_update_seo_meta($post_id, 'tagovi', $seo_keywords);
        }

        // Save canonical URL
        if (isset($_POST['rvk_seo_canonical'])) {
            $seo_canonical = esc_url_raw($_POST['rvk_seo_canonical']);
            rvk_update_seo_meta($post_id, 'canonical', $seo_canonical);
        }

        // Save noindex
        if (isset($_POST['rvk_seo_noindex_submitted'])) {
            $seo_noindex = isset($_POST['rvk_seo_noindex']) ? '1' : '0';
            rvk_update_seo_meta($post_id, 'noindex', $seo_noindex);
        }

        // Save nofollow
        if (isset($_POST['rvk_seo_nofollow_submitted'])) {
            $seo_nofollow = isset($_POST['rvk_seo_nofollow']) ? '1' : '0';
            rvk_update_seo_meta($post_id, 'nofollow', $seo_nofollow);
        }
    }

    /**
     * Render term meta fields on edit screen
     *
     * @param WP_Term $term Term object
     */
    public function render_term_meta_fields($term) {
        $term_title = rvk_get_term_seo_meta($term->term_id, 'title');
        $term_description = rvk_get_term_seo_meta($term->term_id, 'description');

        ?>
        <tr class="form-field">
            <th scope="row">
                <label for="rvk_seo_term_title">SEO Title</label>
            </th>
            <td>
                <input type="text"
                       id="rvk_seo_term_title"
                       name="rvk_seo_term_title"
                       value="<?php echo esc_attr($term_title); ?>"
                       class="regular-text">
                <p class="description">Custom SEO title for this term archive (leave empty for default)</p>
            </td>
        </tr>

        <tr class="form-field">
            <th scope="row">
                <label for="rvk_seo_term_description">Meta Description</label>
            </th>
            <td>
                <textarea id="rvk_seo_term_description"
                          name="rvk_seo_term_description"
                          class="large-text"
                          rows="3"><?php echo esc_textarea($term_description); ?></textarea>
                <p class="description">Custom meta description for this term archive (150-160 characters recommended)</p>
            </td>
        </tr>
        <?php
    }

    /**
     * Save term meta data
     *
     * @param int $term_id Term ID
     * @param int $tt_id Term taxonomy ID
     * @param string $taxonomy Taxonomy slug
     */
    public function save_term_meta($term_id, $tt_id, $taxonomy) {
        // Save term title
        if (isset($_POST['rvk_seo_term_title'])) {
            $term_title = rvk_sanitize_seo_title($_POST['rvk_seo_term_title']);
            rvk_update_term_seo_meta($term_id, 'title', $term_title);
        }

        // Save term description
        if (isset($_POST['rvk_seo_term_description'])) {
            $term_description = rvk_sanitize_meta_description($_POST['rvk_seo_term_description']);
            rvk_update_term_seo_meta($term_id, 'description', $term_description);
        }
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        // Only load on post edit screens and settings pages
        if (!in_array($hook, ['post.php', 'post-new.php', 'settings_page_seo-aeo-settings'])) {
            return;
        }

        // Admin CSS (inline for now, can be moved to separate file)
        wp_add_inline_style('wp-admin', '
            .rvk-seo-status-good { color: #46b450; }
            .rvk-seo-status-warning { color: #ffb900; }
            .rvk-seo-status-bad { color: #dc3232; }
        ');
    }

    /**
     * Add admin menu pages
     */
    public function add_admin_menus() {
        // SEO Dashboard (coming in later phases)
        // This will be added when we create the settings page in Phase 2
    }

    /**
     * AJAX handler for content analysis
     */
    public function ajax_analyze_content() {
        check_ajax_referer('rvk_seo_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied');
        }

        $content = isset($_POST['content']) ? wp_kses_post($_POST['content']) : '';

        if (empty($content)) {
            wp_send_json_error('No content provided');
        }

        // Basic analysis (will be enhanced with AI in Phase 2)
        $word_count = str_word_count(wp_strip_all_tags($content));
        $reading_time = rvk_calculate_reading_time($content);

        $analysis = array(
            'word_count' => $word_count,
            'reading_time' => $reading_time,
            'status' => $word_count >= 300 ? 'good' : 'warning',
            'message' => $word_count >= 300 ? 'Content length is good for SEO' : 'Consider adding more content (300+ words recommended)'
        );

        wp_send_json_success($analysis);
    }

    /**
     * Filter document title (pre_get_document_title)
     */
    public function filter_document_title($title) {
        if (!rvk_should_output_meta_tags()) {
            return $title;
        }

        $custom_title = rvk_get_seo_title();
        if (!empty($custom_title) && $custom_title !== get_the_title()) {
            return $custom_title;
        }

        return $title;
    }

    /**
     * Filter document title parts
     */
    public function filter_title_parts($parts) {
        if (!rvk_should_output_meta_tags()) {
            return $parts;
        }

        $post_id = get_queried_object_id();
        $custom_title = '';

        if (is_singular()) {
            $custom_title = rvk_get_seo_meta($post_id, 'title');
        } elseif (is_category() || is_tag() || is_tax()) {
            $custom_title = rvk_get_term_seo_meta($post_id, 'title');
        }

        if (!empty($custom_title)) {
            $parts['title'] = $custom_title;
        }

        return $parts;
    }

    /**
     * Get system status for debugging
     */
    public static function get_system_status() {
        return array(
            'version' => self::VERSION,
            'active_plugins' => rvk_detect_seo_plugins(),
            'theme_handles_seo' => rvk_should_output_meta_tags(),
            'php_version' => PHP_VERSION,
            'wp_version' => get_bloginfo('version')
        );
    }
    
    /**
     * Debug REST API save requests
     */
    public function debug_rest_save($prepared_post, $request) {
        $meta = $request->get_param('meta');
        $params = $request->get_params(); // Log EVERYTHING to see if it's outside 'meta'
        error_log("SEO REST SAVE [DEBUG]: Full params keys: " . implode(', ', array_keys($params)));
        
        if ($meta) {
            $seo_meta = array_filter($meta, function($k) { return strpos($k, '_seo_') === 0; }, ARRAY_FILTER_USE_KEY);
            if (!empty($seo_meta)) {
                error_log("SEO REST SAVE: Meta found: " . var_export($seo_meta, true));
            } else {
                error_log("SEO REST SAVE: No _seo_ meta in the 'meta' object.");
            }
        } else {
            error_log("SEO REST SAVE: NO 'meta' object found in request.");
        }
        return $prepared_post;
    }

    /**
     * Debug meta updates (for internal hooks)
     */
    public function debug_meta_updates($meta_id, $object_id, $meta_key, $meta_value) {
        if (strpos($meta_key, '_seo_') === 0) {
            error_log("SEO META UPDATE: Post ID $object_id, Key $meta_key, Value: " . var_export($meta_value, true));
            ob_start();
            debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);
            $trace = ob_get_clean();
            error_log("SEO TRACE:\n" . $trace);
        }
    }
}

// Initialize the SEO Core
RVK_SEO_Core::get_instance();
