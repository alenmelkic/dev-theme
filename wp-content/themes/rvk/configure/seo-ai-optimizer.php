<?php
/**
 * SEO AI Optimizer
 * AI-powered SEO optimization using Google Gemini and OpenAI
 * Extends AI_Content_Generator for SEO-specific features
 * Tailored by Alen Melkić
 */

// Security: Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class SEO_AI_Optimizer extends AI_Content_Generator {

    private $provider;

    public function __construct() {
        parent::__construct();

        // Get AI provider preference
        $this->provider = get_option('rvk_seo_ai_provider', 'auto');
    }

    /**
     * Call AI API with provider selection
     *
     * @param string $prompt AI prompt
     * @param int $max_tokens Maximum tokens for response
     * @return string|WP_Error AI response or error
     */
    protected function call_ai_api($prompt, $max_tokens = 100) {
        $provider = $this->provider;

        // Auto-select provider
        if ($provider === 'auto') {
            $gemini_key = get_option('dev_theme_gemini_api_key', '');
            $openai_key = get_option('dev_theme_openai_api_key', '');

            if (!empty($gemini_key)) {
                $provider = 'gemini';
            } elseif (!empty($openai_key)) {
                $provider = 'openai';
            } else {
                return new WP_Error('no_api_key', 'No AI provider configured');
            }
        }

        // Call appropriate provider
        if ($provider === 'gemini') {
            return $this->call_gemini_api($prompt, $max_tokens);
        } elseif ($provider === 'openai') {
            return $this->call_openai_api($prompt, $max_tokens);
        }

        return new WP_Error('invalid_provider', 'Invalid AI provider');
    }

    /**
     * Extract focus keywords from content using AI
     *
     * @param string $content Post content
     * @return array|WP_Error Array of keywords or error
     */
    public function extract_keywords($content) {
        $content = wp_strip_all_tags($content);
        $content = substr($content, 0, 2000); // Limit to prevent token overflow

        $prompt = "Analyze this article and extract 3-5 SEO focus keywords.\n\n";
        $prompt .= "Requirements:\n";
        $prompt .= "- Each keyword should be 1-3 words\n";
        $prompt .= "- Keywords must appear naturally in the content\n";
        $prompt .= "- Optimize for search intent\n";
        $prompt .= "- Return ONLY comma-separated keywords, no explanations\n\n";
        $prompt .= "Content:\n" . $content;

        $result = $this->call_ai_api($prompt, 50);

        if (is_wp_error($result)) {
            return $result;
        }

        // Parse keywords
        $keywords = array_map('trim', explode(',', $result));
        $keywords = array_filter($keywords);

        return array_slice($keywords, 0, 5);
    }

    /**
     * Calculate readability score using Flesch-Kincaid algorithm
     *
     * @param string $content Post content
     * @return int Readability score (0-100)
     */
    public function calculate_readability($content) {
        $content = wp_strip_all_tags($content);

        // Count words
        $word_count = str_word_count($content);

        if ($word_count === 0) {
            return 0;
        }

        // Count sentences
        $sentence_count = preg_match_all('/[.!?]+/', $content);
        if ($sentence_count === 0) {
            $sentence_count = 1;
        }

        // Count syllables (approximation)
        $syllable_count = $this->count_syllables($content);

        // Flesch Reading Ease formula
        // Score = 206.835 - 1.015(words/sentences) - 84.6(syllables/words)
        $score = 206.835 - (1.015 * ($word_count / $sentence_count)) - (84.6 * ($syllable_count / $word_count));

        // Normalize to 0-100
        $score = max(0, min(100, round($score)));

        return $score;
    }

    /**
     * Count syllables in text (approximation)
     *
     * @param string $text Text to analyze
     * @return int Syllable count
     */
    private function count_syllables($text) {
        $text = strtolower($text);
        $words = str_word_count($text, 1);

        $syllables = 0;

        foreach ($words as $word) {
            $syllables += $this->count_syllables_in_word($word);
        }

        return max(1, $syllables);
    }

    /**
     * Count syllables in a single word
     *
     * @param string $word Word to analyze
     * @return int Syllable count
     */
    private function count_syllables_in_word($word) {
        $word = strtolower(trim($word));
        $vowels = array('a', 'e', 'i', 'o', 'u', 'y');

        $syllable_count = 0;
        $previous_was_vowel = false;

        for ($i = 0; $i < strlen($word); $i++) {
            $is_vowel = in_array($word[$i], $vowels);

            if ($is_vowel && !$previous_was_vowel) {
                $syllable_count++;
            }

            $previous_was_vowel = $is_vowel;
        }

        // Adjust for silent 'e'
        if (substr($word, -1) === 'e') {
            $syllable_count--;
        }

        return max(1, $syllable_count);
    }

    /**
     * Analyze content and provide SEO recommendations
     *
     * @param int|string $content Post ID or content string
     * @return array Analysis results
     */
    public function analyze_content($content) {
        // Get content if post ID provided
        if (is_numeric($content)) {
            $post = get_post($content);
            if (!$post) {
                return new WP_Error('invalid_post', 'Post not found');
            }
            $content = $post->post_content;
            $post_id = $post->ID;
        } else {
            $post_id = null;
        }

        $content_text = wp_strip_all_tags($content);

        // Basic metrics
        $word_count = str_word_count($content_text);
        $char_count = strlen($content_text);
        $reading_time = rvk_calculate_reading_time($content);
        $readability = $this->calculate_readability($content_text);

        // Keyword analysis (if post ID provided)
        $keyword_density = array();
        if ($post_id) {
            $focus_keywords = rvk_get_focus_keywords($post_id);
            foreach ($focus_keywords as $keyword) {
                $keyword_count = substr_count(strtolower($content_text), strtolower($keyword));
                $density = $word_count > 0 ? ($keyword_count / $word_count) * 100 : 0;
                $keyword_density[$keyword] = round($density, 2);
            }
        }

        // Link analysis
        preg_match_all('/<a\s+(?:[^>]*?\s+)?href=(["\'])(.*?)\1/', $content, $links);
        $internal_links = 0;
        $external_links = 0;

        foreach ($links[2] as $link) {
            if (strpos($link, home_url()) !== false) {
                $internal_links++;
            } else {
                $external_links++;
            }
        }

        // Image analysis
        preg_match_all('/<img[^>]+>/i', $content, $images);
        $image_count = count($images[0]);
        $images_without_alt = 0;

        foreach ($images[0] as $img) {
            if (strpos($img, 'alt=') === false || strpos($img, 'alt=""') !== false) {
                $images_without_alt++;
            }
        }

        // Calculate overall SEO score
        $seo_score = $this->calculate_seo_score(array(
            'word_count' => $word_count,
            'readability' => $readability,
            'keyword_density' => $keyword_density,
            'internal_links' => $internal_links,
            'external_links' => $external_links,
            'image_count' => $image_count,
            'images_without_alt' => $images_without_alt,
            'has_title' => $post_id ? !empty(rvk_get_seo_meta($post_id, 'title')) : false,
            'has_description' => $post_id ? !empty(rvk_get_seo_meta($post_id, 'description')) : false
        ));

        return array(
            'word_count' => $word_count,
            'char_count' => $char_count,
            'reading_time' => $reading_time,
            'readability_score' => $readability,
            'keyword_density' => $keyword_density,
            'internal_links' => $internal_links,
            'external_links' => $external_links,
            'image_count' => $image_count,
            'images_without_alt' => $images_without_alt,
            'seo_score' => $seo_score,
            'status' => $seo_score >= 70 ? 'good' : ($seo_score >= 50 ? 'warning' : 'bad')
        );
    }

    /**
     * Calculate overall SEO score
     *
     * @param array $metrics Content metrics
     * @return int SEO score (0-100)
     */
    private function calculate_seo_score($metrics) {
        $score = 0;

        // Word count (0-20 points)
        if ($metrics['word_count'] >= 1500) {
            $score += 20;
        } elseif ($metrics['word_count'] >= 1000) {
            $score += 15;
        } elseif ($metrics['word_count'] >= 500) {
            $score += 10;
        } elseif ($metrics['word_count'] >= 300) {
            $score += 5;
        }

        // Readability (0-20 points)
        if ($metrics['readability'] >= 60) {
            $score += 20;
        } elseif ($metrics['readability'] >= 50) {
            $score += 15;
        } elseif ($metrics['readability'] >= 30) {
            $score += 10;
        }

        // Keyword density (0-15 points)
        if (!empty($metrics['keyword_density'])) {
            $avg_density = array_sum($metrics['keyword_density']) / count($metrics['keyword_density']);
            if ($avg_density >= 1 && $avg_density <= 3) {
                $score += 15; // Ideal range
            } elseif ($avg_density > 0 && $avg_density < 5) {
                $score += 10;
            }
        }

        // Internal links (0-10 points)
        if ($metrics['internal_links'] >= 3) {
            $score += 10;
        } elseif ($metrics['internal_links'] >= 1) {
            $score += 5;
        }

        // External links (0-10 points)
        if ($metrics['external_links'] >= 2) {
            $score += 10;
        } elseif ($metrics['external_links'] >= 1) {
            $score += 5;
        }

        // Images (0-10 points)
        if ($metrics['image_count'] > 0) {
            $score += 5;
            if ($metrics['images_without_alt'] === 0) {
                $score += 5; // Bonus for all images having alt text
            }
        }

        // SEO meta (0-15 points)
        if ($metrics['has_title']) {
            $score += 7;
        }
        if ($metrics['has_description']) {
            $score += 8;
        }

        return min(100, $score);
    }

    /**
     * Generate AI-powered improvement suggestions
     *
     * @param int $post_id Post ID
     * @return array|WP_Error Suggestions or error
     */
    public function suggest_improvements($post_id) {
        $post = get_post($post_id);
        if (!$post) {
            return new WP_Error('invalid_post', 'Post not found');
        }

        $analysis = $this->analyze_content($post_id);
        $suggestions = array();

        // Word count suggestions
        if ($analysis['word_count'] < 300) {
            $suggestions[] = array(
                'issue' => 'Content too short',
                'fix' => 'Add more content (minimum 300 words recommended)',
                'severity' => 'high'
            );
        }

        // Readability suggestions
        if ($analysis['readability_score'] < 60) {
            $suggestions[] = array(
                'issue' => 'Readability could be improved',
                'fix' => 'Use shorter sentences and simpler words',
                'severity' => 'medium'
            );
        }

        // Link suggestions
        if ($analysis['internal_links'] < 1) {
            $suggestions[] = array(
                'issue' => 'No internal links',
                'fix' => 'Add 2-3 internal links to related content',
                'severity' => 'medium'
            );
        }

        // Image suggestions
        if ($analysis['images_without_alt'] > 0) {
            $suggestions[] = array(
                'issue' => sprintf('%d images without alt text', $analysis['images_without_alt']),
                'fix' => 'Add descriptive alt text to all images',
                'severity' => 'medium'
            );
        }

        // SEO meta suggestions
        $seo_title = rvk_get_seo_meta($post_id, 'title');
        if (empty($seo_title)) {
            $suggestions[] = array(
                'issue' => 'No custom SEO title',
                'fix' => 'Add a custom SEO title (50-60 characters)',
                'severity' => 'high'
            );
        } elseif (strlen($seo_title) > 60) {
            $suggestions[] = array(
                'issue' => 'SEO title too long (' . strlen($seo_title) . ' characters)',
                'fix' => 'Shorten title to 50-60 characters',
                'severity' => 'medium'
            );
        }

        $seo_description = rvk_get_seo_meta($post_id, 'description');
        if (empty($seo_description)) {
            $suggestions[] = array(
                'issue' => 'No meta description',
                'fix' => 'Add a meta description (150-160 characters)',
                'severity' => 'high'
            );
        } elseif (strlen($seo_description) > 160) {
            $suggestions[] = array(
                'issue' => 'Meta description too long (' . strlen($seo_description) . ' characters)',
                'fix' => 'Shorten description to 150-160 characters',
                'severity' => 'medium'
            );
        }

        return $suggestions;
    }

    /**
     * Generate category/tag description using AI
     *
     * @param int $term_id Term ID
     * @param string $taxonomy Taxonomy name
     * @return string|WP_Error Generated description or error
     */
    public function generate_category_description($term_id, $taxonomy = 'category') {
        $term = get_term($term_id, $taxonomy);

        if (!$term || is_wp_error($term)) {
            return new WP_Error('invalid_term', 'Term not found');
        }

        // Get recent posts in this term
        $posts = get_posts(array(
            'tax_query' => array(
                array(
                    'taxonomy' => $taxonomy,
                    'field' => 'term_id',
                    'terms' => $term_id
                )
            ),
            'posts_per_page' => 5,
            'post_status' => 'publish'
        ));

        $post_titles = array();
        foreach ($posts as $post) {
            $post_titles[] = $post->post_title;
        }

        $prompt = "Generate a compelling meta description for this {$taxonomy} archive page.\n\n";
        $prompt .= "Category/Tag Name: {$term->name}\n";
        if (!empty($post_titles)) {
            $prompt .= "Recent posts: " . implode(', ', $post_titles) . "\n";
        }
        $prompt .= "\nRequirements:\n";
        $prompt .= "- 150-160 characters\n";
        $prompt .= "- Describe what users will find in this {$taxonomy}\n";
        $prompt .= "- Include the {$taxonomy} name naturally\n";
        $prompt .= "- Return ONLY the description, no explanations\n";

        $result = $this->call_ai_api($prompt, 100);

        return $result;
    }

    /**
     * Generate image alt text using AI
     *
     * @param int $attachment_id Attachment ID
     * @return string|WP_Error Generated alt text or error
     */
    public function generate_alt_text($attachment_id) {
        $attachment = get_post($attachment_id);

        if (!$attachment || $attachment->post_type !== 'attachment') {
            return new WP_Error('invalid_attachment', 'Attachment not found');
        }

        // Get image title and caption
        $title = $attachment->post_title;
        $caption = $attachment->post_excerpt;
        $description = $attachment->post_content;

        // Get parent post context
        $parent_id = $attachment->post_parent;
        $parent_context = '';

        if ($parent_id) {
            $parent = get_post($parent_id);
            if ($parent) {
                $parent_context = $parent->post_title;
            }
        }

        $prompt = "Generate descriptive alt text for this image.\n\n";
        if (!empty($title)) {
            $prompt .= "Image title: {$title}\n";
        }
        if (!empty($caption)) {
            $prompt .= "Caption: {$caption}\n";
        }
        if (!empty($parent_context)) {
            $prompt .= "Article context: {$parent_context}\n";
        }
        $prompt .= "\nRequirements:\n";
        $prompt .= "- Descriptive and concise (125 characters max)\n";
        $prompt .= "- Accessible for screen readers\n";
        $prompt .= "- Include relevant keywords naturally\n";
        $prompt .= "- Return ONLY the alt text, no explanations\n";

        $result = $this->call_ai_api($prompt, 50);

        return $result;
    }
}

// Initialize SEO AI Optimizer
new SEO_AI_Optimizer();
