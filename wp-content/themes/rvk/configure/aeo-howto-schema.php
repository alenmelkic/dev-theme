<?php
/**
 * AEO How-To Schema Generator
 * Extract step-by-step instructions and generate HowTo Schema
 * Perfect for AI Answer Engines
 * Tailored by Alen Melkić
 */

// Security: Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class RVK_AEO_HowTo_Schema {

    public function __construct() {
        // Output HowTo schema in head
        add_action('wp_head', array($this, 'output_howto_schema'), 5);

        // Register meta fields
        add_action('init', array($this, 'register_howto_meta'));
    }

    /**
     * Register HowTo meta fields
     */
    public function register_howto_meta() {
        register_post_meta('post', '_aeo_howto_steps', array(
            'show_in_rest' => array(
                'schema' => array(
                    'type' => 'array',
                    'items' => array(
                        'type' => 'object',
                        'properties' => array(
                            'name' => array('type' => 'string'),
                            'text' => array('type' => 'string')
                        )
                    )
                )
            ),
            'single' => true,
            'type' => 'array',
            'default' => array(),
            'auth_callback' => function() {
                return current_user_can('edit_posts');
            }
        ));

        register_post_meta('post', '_aeo_howto_total_time', array(
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
     * Output HowTo Schema
     */
    public function output_howto_schema() {
        if (!is_singular('post')) {
            return;
        }

        $post_id = get_the_ID();

        // Get HowTo steps from meta
        $steps = get_post_meta($post_id, '_aeo_howto_steps', true);

        // Auto-extract if none exist
        if (empty($steps) || !is_array($steps)) {
            $steps = $this->auto_extract_steps($post_id);
        }

        if (empty($steps)) {
            return;
        }

        $post = get_post($post_id);
        $total_time = get_post_meta($post_id, '_aeo_howto_total_time', true);

        // Build HowTo Schema
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'HowTo',
            'name' => get_the_title($post_id),
            'description' => rvk_get_meta_description($post_id),
            'step' => array()
        );

        // Add total time if available
        if (!empty($total_time)) {
            $schema['totalTime'] = $total_time; // ISO 8601 format (PT30M = 30 minutes)
        }

        // Add featured image
        $thumbnail_id = get_post_thumbnail_id($post_id);
        if ($thumbnail_id) {
            $image_url = wp_get_attachment_image_url($thumbnail_id, 'full');
            if ($image_url) {
                $schema['image'] = $image_url;
            }
        }

        foreach ($steps as $index => $step) {
            if (empty($step['name']) || empty($step['text'])) {
                continue;
            }

            $step_schema = array(
                '@type' => 'HowToStep',
                'position' => $index + 1,
                'name' => $step['name'],
                'text' => $step['text']
            );

            // Add image if available
            if (!empty($step['image'])) {
                $step_schema['image'] = $step['image'];
            }

            $schema['step'][] = $step_schema;
        }

        // Only output if we have valid steps
        if (!empty($schema['step'])) {
            echo '<script type="application/ld+json">' . "\n";
            echo wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            echo "\n" . '</script>' . "\n";
        }
    }

    /**
     * Auto-extract steps from content
     */
    public function auto_extract_steps($post_id) {
        $content = get_post_field('post_content', $post_id);

        if (empty($content)) {
            return array();
        }

        $steps = array();

        // Pattern 1: Ordered lists <ol><li>
        preg_match_all('/<ol[^>]*>(.*?)<\/ol>/is', $content, $ol_matches);

        foreach ($ol_matches[1] as $ol_content) {
            preg_match_all('/<li[^>]*>(.*?)<\/li>/is', $ol_content, $li_matches);

            foreach ($li_matches[1] as $index => $li_content) {
                $text = wp_strip_all_tags($li_content);
                if (!empty($text)) {
                    $steps[] = array(
                        'name' => 'Step ' . (count($steps) + 1),
                        'text' => $text
                    );
                }
            }
        }

        // Pattern 2: Headings with numbers (Step 1:, 1., etc.)
        preg_match_all('/<h[2-4][^>]*>((?:Step\s*)?[0-9]+[\.\:\s]+)([^<]+)<\/h[2-4]>\s*<p>([^<]+)<\/p>/i', $content, $heading_matches, PREG_SET_ORDER);

        if (empty($steps)) {
            foreach ($heading_matches as $match) {
                $steps[] = array(
                    'name' => strip_tags($match[2]),
                    'text' => strip_tags($match[3])
                );
            }
        }

        return $steps;
    }

    /**
     * AI-powered How-To generation
     */
    public static function generate_howto_with_ai($content) {
        $api_manager = RVK_SEO_API_Manager::get_instance();

        $prompt = "Extract step-by-step instructions from this content. Return ONLY a JSON array of objects with 'name' and 'text' fields. Each step should have a clear action name and detailed instructions:\n\n" . wp_strip_all_tags($content);

        try {
            $response = $api_manager->generate_content($prompt, array(
                'max_tokens' => 1500,
                'temperature' => 0.3
            ));

            // Check if response is an error
            if (is_wp_error($response)) {
                error_log('AEO HowTo Generation Error: ' . $response->get_error_message());
                return array();
            }

            // Try to parse JSON response
            $steps = json_decode($response, true);

            if (is_array($steps)) {
                return $steps;
            }

            // Fallback: try to extract JSON from response
            preg_match('/\[.*\]/s', $response, $matches);
            if (!empty($matches[0])) {
                $steps = json_decode($matches[0], true);
                if (is_array($steps)) {
                    return $steps;
                }
            }

        } catch (Exception $e) {
            error_log('AEO HowTo Generation Error: ' . $e->getMessage());
        }

        return array();
    }
}

// Initialize HowTo Schema
new RVK_AEO_HowTo_Schema();
