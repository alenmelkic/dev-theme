<?php
/**
 * AEO Key Takeaways Generator
 * Extract and structure key points for AI Answer Engines
 * Helps AI engines provide accurate, concise answers
 * Tailored by Alen Melkić
 */

// Security: Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class RVK_AEO_Key_Takeaways {

    public function __construct() {
        // Don't register classic meta box - we use Gutenberg sidebar panel instead
        // add_action('add_meta_boxes', array($this, 'add_meta_box'));

        // Register meta fields
        add_action('init', array($this, 'register_meta'));

        // Output key takeaways in content
        add_filter('the_content', array($this, 'inject_takeaways_into_content'));
    }

    /**
     * Register meta fields
     */
    public function register_meta() {
        register_post_meta('post', '_aeo_key_takeaways', array(
            'show_in_rest' => true,
            'single' => true,
            'type' => 'string',
            'default' => '',
            'auth_callback' => function() {
                return current_user_can('edit_posts');
            }
        ));
    }

    /**
     * Add meta box
     */
    public function add_meta_box() {
        add_meta_box(
            'aeo_key_takeaways',
            '🎯 Key Takeaways (AEO)',
            array($this, 'render_meta_box'),
            'post',
            'side',
            'high'
        );
    }

    /**
     * Render meta box
     */
    public function render_meta_box($post) {
        $takeaways = get_post_meta($post->ID, '_aeo_key_takeaways', true);
        wp_nonce_field('aeo_takeaways_nonce', 'aeo_takeaways_nonce');
        ?>
        <p style="margin-top: 0; color: #666; font-size: 12px;">
            AI engines prioritize content with clear takeaways. Add 3-5 key points.
        </p>
        <textarea name="aeo_key_takeaways" rows="8" style="width: 100%;" placeholder="• First key point&#10;• Second key point&#10;• Third key point"><?php echo esc_textarea($takeaways); ?></textarea>
        <p style="margin-bottom: 0;">
            <button type="button" class="button" onclick="aeoGenerateTakeaways(<?php echo $post->ID; ?>)">
                🤖 Generate with AI
            </button>
        </p>

        <script>
        function aeoGenerateTakeaways(postId) {
            const button = event.target;
            const originalText = button.textContent;
            button.textContent = 'Generating...';
            button.disabled = true;

            wp.apiFetch({
                path: '/dev-theme/v1/aeo/generate-takeaways',
                method: 'POST',
                data: {
                    post_id: postId
                }
            }).then(response => {
                if (response.success && response.takeaways) {
                    document.querySelector('textarea[name="aeo_key_takeaways"]').value = response.takeaways;
                }
            }).catch(error => {
                alert('Error: ' + error.message);
            }).finally(() => {
                button.textContent = originalText;
                button.disabled = false;
            });
        }
        </script>
        <?php
    }

    /**
     * Inject takeaways into content
     * Placed at the beginning for maximum AI visibility
     */
    public function inject_takeaways_into_content($content) {
        if (!is_singular('post') || !in_the_loop() || !is_main_query()) {
            return $content;
        }

        $post_id = get_the_ID();
        $takeaways = get_post_meta($post_id, '_aeo_key_takeaways', true);

        if (empty($takeaways)) {
            return $content;
        }

        // Convert to array of points
        $points = array_filter(array_map('trim', explode("\n", $takeaways)));

        if (empty($points)) {
            return $content;
        }

        // Build hidden takeaways for AEO - using d-none Bootstrap class
        // AI crawlers read the HTML source regardless of display property
        // Using Schema.org ItemList for structured data
        $html = '<div class="aeo-key-takeaways d-none" itemscope itemtype="https://schema.org/ItemList">';
        $html .= '<h3 itemprop="name">Ključni Zaključci</h3>';
        $html .= '<meta itemprop="description" content="Key Takeaways - Main points summary">';
        $html .= '<ul>';

        $position = 1;
        foreach ($points as $point) {
            // Remove bullet points if they exist
            $point = preg_replace('/^[\•\-\*]\s*/', '', $point);
            $html .= '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
            $html .= '<meta itemprop="position" content="' . $position . '">';
            $html .= '<span itemprop="name">' . esc_html($point) . '</span>';
            $html .= '</li>';
            $position++;
        }

        $html .= '</ul>';
        $html .= '</div>';

        // Inject at the bottom of content
        return $content . $html;
    }

    /**
     * AI-powered key takeaways generation
     */
    public static function generate_takeaways($post_id) {
        $content = get_post_field('post_content', $post_id);
        $title = get_the_title($post_id);

        if (empty($content)) {
            return '';
        }

        $api_manager = RVK_SEO_API_Manager::get_instance();

        $prompt = "Ekstraktuj 3-5 ključnih zaključaka iz ovog članka. Formatiraj kao bullet points. Budi koncizan i faktičan. VAŽNO: Odgovori SAMO na bosanskom jeziku.\n\nNaslov: $title\n\nSadržaj: " . wp_strip_all_tags($content);

        try {
            $response = $api_manager->generate_content($prompt, array(
                'max_tokens' => 300,
                'temperature' => 0.3
            ));

            // Check if response is an error
            if (is_wp_error($response)) {
                error_log('AEO Takeaways Generation Error: ' . $response->get_error_message());
                return '';
            }

            // Clean up response
            $response = trim($response);

            // Ensure bullet points
            if (strpos($response, '•') === false && strpos($response, '-') === false) {
                $lines = explode("\n", $response);
                $response = '';
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (!empty($line) && !preg_match('/^[\•\-\*]/', $line)) {
                        $response .= '• ' . $line . "\n";
                    } else {
                        $response .= $line . "\n";
                    }
                }
            }

            return trim($response);

        } catch (Exception $e) {
            error_log('AEO Takeaways Generation Error: ' . $e->getMessage());
            return '';
        }
    }
}

// Initialize Key Takeaways
new RVK_AEO_Key_Takeaways();
