<?php
/**
 * AEO FAQ Schema Generator
 * Automatically extract Q&A from content and generate FAQ Schema
 * Critical for Answer Engines (ChatGPT, Perplexity, Google SGE)
 * Tailored by Alen Melkić
 */

// Security: Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class RVK_AEO_FAQ_Schema {

    public function __construct() {
        // Output FAQ schema in head
        add_action('wp_head', array($this, 'output_faq_schema'), 5);

        // Register meta fields for FAQ
        add_action('init', array($this, 'register_faq_meta'));
    }

    /**
     * Register FAQ meta fields
     */
    public function register_faq_meta() {
        register_post_meta('post', '_aeo_faq_items', array(
            'show_in_rest' => array(
                'schema' => array(
                    'type' => 'array',
                    'items' => array(
                        'type' => 'object',
                        'properties' => array(
                            'question' => array('type' => 'string'),
                            'answer' => array('type' => 'string')
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
    }

    /**
     * Output FAQ Schema in JSON-LD format
     */
    public function output_faq_schema() {
        if (!is_singular('post')) {
            return;
        }

        $post_id = get_the_ID();

        // Get FAQ items from meta
        $faq_items = get_post_meta($post_id, '_aeo_faq_items', true);

        // Try to auto-extract FAQs from content if none exist
        if (empty($faq_items) || !is_array($faq_items)) {
            $faq_items = $this->auto_extract_faqs($post_id);
        }

        if (empty($faq_items)) {
            return;
        }

        // Build FAQ Schema
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array()
        );

        foreach ($faq_items as $item) {
            if (empty($item['question']) || empty($item['answer'])) {
                continue;
            }

            $schema['mainEntity'][] = array(
                '@type' => 'Question',
                'name' => $item['question'],
                'acceptedAnswer' => array(
                    '@type' => 'Answer',
                    'text' => $item['answer']
                )
            );
        }

        // Only output if we have valid FAQs
        if (!empty($schema['mainEntity'])) {
            echo '<script type="application/ld+json">' . "\n";
            echo wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            echo "\n" . '</script>' . "\n";
        }
    }

    /**
     * Auto-extract FAQs from content using AI patterns
     */
    public function auto_extract_faqs($post_id) {
        $content = get_post_field('post_content', $post_id);

        if (empty($content)) {
            return array();
        }

        $faqs = array();

        // Pattern 1: Heading followed by content (most common)
        // Matches: <h3>Question?</h3><p>Answer</p>
        preg_match_all('/<h[2-4][^>]*>([^<]*\?[^<]*)<\/h[2-4]>\s*<p>([^<]+)<\/p>/i', $content, $matches1, PREG_SET_ORDER);

        foreach ($matches1 as $match) {
            $faqs[] = array(
                'question' => strip_tags($match[1]),
                'answer' => strip_tags($match[2])
            );
        }

        // Pattern 2: Bold question followed by answer
        // Matches: <strong>Question?</strong> Answer text
        preg_match_all('/<strong>([^<]*\?[^<]*)<\/strong>\s*([^<\.]+[\.\!])/i', $content, $matches2, PREG_SET_ORDER);

        foreach ($matches2 as $match) {
            $question = strip_tags($match[1]);
            $answer = strip_tags($match[2]);

            // Avoid duplicates
            $exists = false;
            foreach ($faqs as $existing) {
                if ($existing['question'] === $question) {
                    $exists = true;
                    break;
                }
            }

            if (!$exists) {
                $faqs[] = array(
                    'question' => $question,
                    'answer' => $answer
                );
            }
        }

        // Pattern 3: WordPress FAQ blocks (Gutenberg)
        if (has_blocks($content)) {
            $blocks = parse_blocks($content);
            foreach ($blocks as $block) {
                if ($block['blockName'] === 'core/heading' && isset($block['innerHTML'])) {
                    $heading = strip_tags($block['innerHTML']);
                    if (strpos($heading, '?') !== false) {
                        // Look for next paragraph block
                        // This is simplified - in production you'd track block order
                        $faqs[] = array(
                            'question' => $heading,
                            'answer' => '' // Would need to find following paragraph
                        );
                    }
                }
            }
        }

        return $faqs;
    }

    /**
     * AI-powered FAQ generation from content
     */
    public static function generate_faqs_with_ai($content) {
        $api_manager = RVK_SEO_API_Manager::get_instance();

        $prompt = "Extract FAQ-style questions and answers from this content. Return ONLY a JSON array of objects with 'question' and 'answer' fields. Generate 3-5 FAQs that would be most useful for readers searching for information:\n\n" . wp_strip_all_tags($content);

        try {
            $response = $api_manager->generate_content($prompt, array(
                'max_tokens' => 1000,
                'temperature' => 0.3 // Lower temperature for more factual responses
            ));

            // Check if response is an error
            if (is_wp_error($response)) {
                error_log('AEO FAQ Generation Error: ' . $response->get_error_message());
                return array();
            }

            // Try to parse JSON response
            $faqs = json_decode($response, true);

            if (is_array($faqs)) {
                return $faqs;
            }

            // Fallback: try to extract JSON from response
            preg_match('/\[.*\]/s', $response, $matches);
            if (!empty($matches[0])) {
                $faqs = json_decode($matches[0], true);
                if (is_array($faqs)) {
                    return $faqs;
                }
            }

        } catch (Exception $e) {
            error_log('AEO FAQ Generation Error: ' . $e->getMessage());
        }

        return array();
    }
}

// Initialize FAQ Schema
new RVK_AEO_FAQ_Schema();
