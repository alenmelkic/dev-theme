<?php
/**
 * SEO REST API Endpoints
 * Exposes SEO/AEO functionality via WordPress REST API
 * Tailored by Alen Melkić
 */

// Security: Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class RVK_SEO_REST_API {

    private $seo_optimizer;

    public function __construct() {
        // Get SEO AI Optimizer instance
        $this->seo_optimizer = new SEO_AI_Optimizer();

        // Register REST routes
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    /**
     * Register all REST API routes
     */
    public function register_routes() {
        $namespace = 'dev-theme/v1/seo';

        // Content analysis
        register_rest_route($namespace, '/analyze', array(
            'methods' => 'POST',
            'callback' => array($this, 'analyze_content'),
            'permission_callback' => array($this, 'check_permissions'),
        ));

        // Generate all SEO meta
        register_rest_route($namespace, '/generate-meta', array(
            'methods' => 'POST',
            'callback' => array($this, 'generate_meta'),
            'permission_callback' => array($this, 'check_permissions'),
        ));

        // Extract keywords
        register_rest_route($namespace, '/extract-keywords', array(
            'methods' => 'POST',
            'callback' => array($this, 'extract_keywords'),
            'permission_callback' => array($this, 'check_permissions'),
        ));

        // Calculate readability score
        register_rest_route($namespace, '/readability-score', array(
            'methods' => 'POST',
            'callback' => array($this, 'readability_score'),
            'permission_callback' => array($this, 'check_permissions'),
        ));

        // Get improvement suggestions
        register_rest_route($namespace, '/suggestions', array(
            'methods' => 'POST',
            'callback' => array($this, 'get_suggestions'),
            'permission_callback' => array($this, 'check_permissions'),
        ));

        // Generate category/tag description
        register_rest_route($namespace, '/generate-term-description', array(
            'methods' => 'POST',
            'callback' => array($this, 'generate_term_description'),
            'permission_callback' => array($this, 'check_permissions'),
        ));

        // Generate image alt text
        register_rest_route($namespace, '/generate-alt-text', array(
            'methods' => 'POST',
            'callback' => array($this, 'generate_alt_text'),
            'permission_callback' => array($this, 'check_permissions'),
        ));

        // Get term meta
        register_rest_route($namespace, '/term-meta/(?P<term_id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_term_meta'),
            'permission_callback' => array($this, 'check_permissions'),
        ));

        // Update term meta
        register_rest_route($namespace, '/term-meta/(?P<term_id>\d+)', array(
            'methods' => 'POST',
            'callback' => array($this, 'update_term_meta'),
            'permission_callback' => array($this, 'check_permissions'),
        ));

        // Save post SEO meta
        register_rest_route($namespace, '/save-meta/(?P<post_id>\d+)', array(
            'methods' => 'POST',
            'callback' => array($this, 'save_post_meta'),
            'permission_callback' => array($this, 'check_permissions'),
        ));

        // AEO Endpoints
        $aeo_namespace = 'dev-theme/v1/aeo';

        // Generate FAQ schema
        register_rest_route($aeo_namespace, '/generate-faqs', array(
            'methods' => 'POST',
            'callback' => array($this, 'generate_faqs'),
            'permission_callback' => array($this, 'check_permissions'),
        ));

        // Generate How-To steps
        register_rest_route($aeo_namespace, '/generate-howto', array(
            'methods' => 'POST',
            'callback' => array($this, 'generate_howto'),
            'permission_callback' => array($this, 'check_permissions'),
        ));

        // Generate key takeaways
        register_rest_route($aeo_namespace, '/generate-takeaways', array(
            'methods' => 'POST',
            'callback' => array($this, 'generate_takeaways'),
            'permission_callback' => array($this, 'check_permissions'),
        ));
    }

    /**
     * Check user permissions
     * Security: Require edit_published_posts (Authors+) to prevent Contributors from accessing AI features
     */
    public function check_permissions() {
        // Only Authors, Editors, and Admins can use AI features
        // This prevents Contributors from consuming AI API credits
        return current_user_can('edit_published_posts');
    }

    /**
     * Analyze content
     */
    public function analyze_content($request) {
        // Security: Sanitize and validate inputs
        $content = $request->get_param('content');
        $post_id = absint($request->get_param('post_id'));

        if (empty($content) && empty($post_id)) {
            return new WP_Error('no_content', 'Content or post ID required', array('status' => 400));
        }

        // Security: Sanitize content to prevent XSS
        if (!empty($content)) {
            $content = wp_kses_post($content);
        }

        // Security: Verify post ID belongs to user if specified
        if ($post_id) {
            $post = get_post($post_id);
            if (!$post || !current_user_can('edit_post', $post_id)) {
                return new WP_Error('unauthorized', 'You do not have permission to analyze this post', array('status' => 403));
            }
        }

        $analysis = $this->seo_optimizer->analyze_content($post_id ? $post_id : $content);

        if (is_wp_error($analysis)) {
            return $analysis;
        }

        return rest_ensure_response(array(
            'success' => true,
            'analysis' => $analysis
        ));
    }

    /**
     * Generate all SEO meta (title, description, keywords)
     */
    public function generate_meta($request) {
        // Security: Rate limiting (50 requests per hour per user)
        $user_id = get_current_user_id();
        $rate_check = RVK_SEO_Rate_Limiter::check_rate_limit($user_id, 'ai_generation', 50);

        if (is_wp_error($rate_check)) {
            return $rate_check;
        }

        // Security: Sanitize inputs
        $content = wp_kses_post($request->get_param('content'));
        $title = sanitize_text_field($request->get_param('title'));

        if (empty($content)) {
            return new WP_Error('no_content', 'Content required', array('status' => 400));
        }

        // Generate SEO title
        $content_text = wp_strip_all_tags($content);
        $content_text = substr($content_text, 0, 1000);

        $title_prompt = "Create an SEO-optimized title for this article.\n\n";
        $title_prompt .= "Requirements:\n";
        $title_prompt .= "- 50-60 characters\n";
        $title_prompt .= "- Include primary keyword\n";
        $title_prompt .= "- Compelling and clear\n";
        $title_prompt .= "- Return ONLY the title\n\n";
        $title_prompt .= "Content:\n" . $content_text;

        $seo_title = $this->seo_optimizer->call_gemini_api($title_prompt, 60);

        // Generate meta description
        $desc_prompt = "Create a compelling meta description for this article.\n\n";
        $desc_prompt .= "Requirements:\n";
        $desc_prompt .= "- 150-160 characters\n";
        $desc_prompt .= "- Include primary keyword\n";
        $desc_prompt .= "- Summarize main points\n";
        $desc_prompt .= "- Return ONLY the description\n\n";
        $desc_prompt .= "Content:\n" . $content_text;

        $seo_description = $this->seo_optimizer->call_gemini_api($desc_prompt, 100);

        // Extract keywords
        $keywords = $this->seo_optimizer->extract_keywords($content_text);

        return rest_ensure_response(array(
            'success' => true,
            'title' => is_wp_error($seo_title) ? '' : $seo_title,
            'description' => is_wp_error($seo_description) ? '' : $seo_description,
            'keywords' => is_wp_error($keywords) ? array() : $keywords
        ));
    }

    /**
     * Extract focus keywords
     */
    public function extract_keywords($request) {
        $content = $request->get_param('content');

        if (empty($content)) {
            return new WP_Error('no_content', 'Content required', array('status' => 400));
        }

        $keywords = $this->seo_optimizer->extract_keywords($content);

        if (is_wp_error($keywords)) {
            return $keywords;
        }

        return rest_ensure_response(array(
            'success' => true,
            'keywords' => $keywords
        ));
    }

    /**
     * Calculate readability score
     */
    public function readability_score($request) {
        $content = $request->get_param('content');

        if (empty($content)) {
            return new WP_Error('no_content', 'Content required', array('status' => 400));
        }

        $score = $this->seo_optimizer->calculate_readability($content);

        // Determine status
        $status = 'bad';
        $label = 'Teško';

        if ($score >= 80) {
            $status = 'good';
            $label = 'Veoma lako';
        } elseif ($score >= 60) {
            $status = 'good';
            $label = 'Lako';
        } elseif ($score >= 50) {
            $status = 'warning';
            $label = 'Umjereno';
        } elseif ($score >= 30) {
            $status = 'warning';
            $label = 'Teško';
        }

        return rest_ensure_response(array(
            'success' => true,
            'score' => $score,
            'status' => $status,
            'label' => $label
        ));
    }

    /**
     * Get improvement suggestions
     */
    public function get_suggestions($request) {
        $post_id = $request->get_param('post_id');

        if (empty($post_id)) {
            return new WP_Error('no_post_id', 'Post ID required', array('status' => 400));
        }

        $suggestions = $this->seo_optimizer->suggest_improvements($post_id);

        if (is_wp_error($suggestions)) {
            return $suggestions;
        }

        return rest_ensure_response(array(
            'success' => true,
            'suggestions' => $suggestions
        ));
    }

    /**
     * Generate category/tag description
     */
    public function generate_term_description($request) {
        $term_id = $request->get_param('term_id');
        $taxonomy = $request->get_param('taxonomy');

        if (empty($term_id)) {
            return new WP_Error('no_term_id', 'Term ID required', array('status' => 400));
        }

        if (empty($taxonomy)) {
            $taxonomy = 'category';
        }

        $description = $this->seo_optimizer->generate_category_description($term_id, $taxonomy);

        if (is_wp_error($description)) {
            return $description;
        }

        return rest_ensure_response(array(
            'success' => true,
            'description' => $description
        ));
    }

    /**
     * Generate image alt text
     */
    public function generate_alt_text($request) {
        $attachment_id = $request->get_param('attachment_id');

        if (empty($attachment_id)) {
            return new WP_Error('no_attachment_id', 'Attachment ID required', array('status' => 400));
        }

        $alt_text = $this->seo_optimizer->generate_alt_text($attachment_id);

        if (is_wp_error($alt_text)) {
            return $alt_text;
        }

        // Save alt text
        update_post_meta($attachment_id, '_wp_attachment_image_alt', $alt_text);

        return rest_ensure_response(array(
            'success' => true,
            'alt_text' => $alt_text
        ));
    }

    /**
     * Get term meta
     */
    public function get_term_meta($request) {
        $term_id = $request['term_id'];

        $meta = array(
            'title' => rvk_get_term_seo_meta($term_id, 'title'),
            'description' => rvk_get_term_seo_meta($term_id, 'description'),
            'og_image' => rvk_get_term_seo_meta($term_id, 'og_image')
        );

        return rest_ensure_response(array(
            'success' => true,
            'meta' => $meta
        ));
    }

    /**
     * Update term meta
     */
    public function update_term_meta($request) {
        $term_id = $request['term_id'];
        $title = $request->get_param('title');
        $description = $request->get_param('description');
        $og_image = $request->get_param('og_image');

        if (!empty($title)) {
            rvk_update_term_seo_meta($term_id, 'title', $title);
        }

        if (!empty($description)) {
            rvk_update_term_seo_meta($term_id, 'description', $description);
        }

        if (!empty($og_image)) {
            rvk_update_term_seo_meta($term_id, 'og_image', $og_image);
        }

        return rest_ensure_response(array(
            'success' => true,
            'message' => 'Term meta updated'
        ));
    }

    /**
     * Save post SEO meta
     */
    public function save_post_meta($request) {
        $post_id = $request['post_id'];

        // Get all SEO meta from request
        $seo_data = array(
            'title' => $request->get_param('seo_title'),
            'description' => $request->get_param('seo_description'),
            'keywords' => $request->get_param('seo_keywords'),
            'canonical' => $request->get_param('seo_canonical'),
            'noindex' => $request->get_param('seo_noindex'),
            'nofollow' => $request->get_param('seo_nofollow'),
            'og_title' => $request->get_param('seo_og_title'),
            'og_description' => $request->get_param('seo_og_description'),
            'og_image' => $request->get_param('seo_og_image'),
            'twitter_title' => $request->get_param('seo_twitter_title'),
            'twitter_description' => $request->get_param('seo_twitter_description'),
            'twitter_image' => $request->get_param('seo_twitter_image'),
            'readability_score' => $request->get_param('seo_readability_score'),
            'content_score' => $request->get_param('seo_content_score')
        );

        // Save each field
        foreach ($seo_data as $key => $value) {
            if ($value !== null) {
                rvk_update_seo_meta($post_id, $key, $value);
            }
        }

        // Update last optimized timestamp
        rvk_update_seo_meta($post_id, 'last_optimized', current_time('mysql'));

        return rest_ensure_response(array(
            'success' => true,
            'message' => 'SEO meta saved'
        ));
    }

    /**
     * Generate FAQ schema with AI
     */
    public function generate_faqs($request) {
        // Security: Validate and sanitize post ID
        $post_id = absint($request->get_param('post_id'));

        if (empty($post_id)) {
            return new WP_Error('no_post_id', 'Post ID required', array('status' => 400));
        }

        // Security: Verify user can edit this post
        if (!current_user_can('edit_post', $post_id)) {
            return new WP_Error('unauthorized', 'You do not have permission to edit this post', array('status' => 403));
        }

        $content = get_post_field('post_content', $post_id);

        if (empty($content)) {
            return new WP_Error('no_content', 'Post has no content', array('status' => 400));
        }

        $faqs = RVK_AEO_FAQ_Schema::generate_faqs_with_ai($content);

        if (empty($faqs)) {
            return new WP_Error('generation_failed', 'Could not generate FAQs', array('status' => 500));
        }

        // Save FAQs
        update_post_meta($post_id, '_aeo_faq_items', $faqs);

        return rest_ensure_response(array(
            'success' => true,
            'faqs' => $faqs
        ));
    }

    /**
     * Generate How-To steps with AI
     */
    public function generate_howto($request) {
        // Security: Validate and sanitize post ID
        $post_id = absint($request->get_param('post_id'));

        if (empty($post_id)) {
            return new WP_Error('no_post_id', 'Post ID required', array('status' => 400));
        }

        // Security: Verify user can edit this post
        if (!current_user_can('edit_post', $post_id)) {
            return new WP_Error('unauthorized', 'You do not have permission to edit this post', array('status' => 403));
        }

        $content = get_post_field('post_content', $post_id);

        if (empty($content)) {
            return new WP_Error('no_content', 'Post has no content', array('status' => 400));
        }

        $steps = RVK_AEO_HowTo_Schema::generate_howto_with_ai($content);

        if (empty($steps)) {
            return new WP_Error('generation_failed', 'Could not generate how-to steps', array('status' => 500));
        }

        // Save steps
        update_post_meta($post_id, '_aeo_howto_steps', $steps);

        return rest_ensure_response(array(
            'success' => true,
            'steps' => $steps
        ));
    }

    /**
     * Generate key takeaways with AI
     */
    public function generate_takeaways($request) {
        // Security: Validate and sanitize post ID
        $post_id = absint($request->get_param('post_id'));

        if (empty($post_id)) {
            return new WP_Error('no_post_id', 'Post ID required', array('status' => 400));
        }

        // Security: Verify user can edit this post
        if (!current_user_can('edit_post', $post_id)) {
            return new WP_Error('unauthorized', 'You do not have permission to edit this post', array('status' => 403));
        }

        $takeaways = RVK_AEO_Key_Takeaways::generate_takeaways($post_id);

        if (empty($takeaways)) {
            return new WP_Error('generation_failed', 'Could not generate takeaways', array('status' => 500));
        }

        return rest_ensure_response(array(
            'success' => true,
            'takeaways' => $takeaways
        ));
    }
}

// Initialize REST API
new RVK_SEO_REST_API();
