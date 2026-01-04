<?php
/**
 * AI Content Generator - Dual AI Provider Integration
 * Generates and optimizes post titles and excerpts using AI
 * Supports Google Gemini 1.5 Flash and OpenAI GPT-4o Mini with auto-fallback
 */

// Security: Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class AI_Content_Generator {

    private $api_key;
    private $api_endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent';

    public function __construct() {
        $this->api_key = get_option('dev_theme_gemini_api_key', '');
        
        // Register REST API endpoints
        add_action('rest_api_init', array($this, 'register_rest_routes'));
    }
    
    /**
     * Register REST API routes
     */
    public function register_rest_routes() {
        register_rest_route('dev-theme/v1', '/ai/generate-title', array(
            'methods' => 'POST',
            'callback' => array($this, 'generate_title'),
            'permission_callback' => array($this, 'check_permissions'),
        ));
        
        register_rest_route('dev-theme/v1', '/ai/generate-excerpt', array(
            'methods' => 'POST',
            'callback' => array($this, 'generate_excerpt'),
            'permission_callback' => array($this, 'check_permissions'),
        ));
        
        register_rest_route('dev-theme/v1', '/ai/optimize-title', array(
            'methods' => 'POST',
            'callback' => array($this, 'optimize_title'),
            'permission_callback' => array($this, 'check_permissions'),
        ));
        
        register_rest_route('dev-theme/v1', '/ai/optimize-excerpt', array(
            'methods' => 'POST',
            'callback' => array($this, 'optimize_excerpt'),
            'permission_callback' => array($this, 'check_permissions'),
        ));
    }
    
    /**
     * Check user permissions
     */
    public function check_permissions() {
        return current_user_can('edit_posts');
    }

    /**
     * Call AI API with provider selection and fallback
     * Respects SEO settings for provider preference
     */
    protected function call_ai_api_with_provider($prompt, $max_tokens = 100) {
        $api_manager = RVK_SEO_API_Manager::get_instance();

        // Check cache first
        $cache_key = 'ai_' . md5($prompt . $max_tokens);
        $cached = $api_manager->get_cache($cache_key);

        if ($cached !== false) {
            return $cached;
        }

        // Get provider preference from settings
        $provider = get_option('rvk_seo_ai_provider', 'auto');
        $gemini_key = get_option('dev_theme_gemini_api_key', '');
        $openai_key = get_option('dev_theme_openai_api_key', '');

        // Determine provider(s) to try
        $providers_to_try = array();

        if ($provider === 'auto') {
            // Auto mode: Try both providers (Gemini first, OpenAI as fallback)
            if (!empty($gemini_key)) {
                $providers_to_try[] = 'gemini';
            }
            if (!empty($openai_key)) {
                $providers_to_try[] = 'openai';
            }

            if (empty($providers_to_try)) {
                return new WP_Error('no_api_key', 'No AI provider configured');
            }
        } elseif ($provider === 'gemini') {
            if (!empty($gemini_key)) {
                $providers_to_try[] = 'gemini';
            } else {
                return new WP_Error('no_api_key', 'Gemini API key not configured');
            }
        } elseif ($provider === 'openai') {
            if (!empty($openai_key)) {
                $providers_to_try[] = 'openai';
            } else {
                return new WP_Error('no_api_key', 'OpenAI API key not configured');
            }
        } else {
            return new WP_Error('invalid_provider', 'Invalid AI provider');
        }

        // Try each provider
        $last_error = null;
        foreach ($providers_to_try as $current_provider) {
            // Check rate limit
            $rate_check = $api_manager->check_rate_limit($current_provider);
            if (is_wp_error($rate_check)) {
                $last_error = $rate_check;
                continue; // Try next provider
            }

            // Call provider
            $result = null;
            if ($current_provider === 'gemini') {
                $result = $this->call_gemini_api($prompt, $max_tokens);
            } elseif ($current_provider === 'openai') {
                $result = $this->call_openai_api($prompt, $max_tokens);
            }

            // If successful, cache and return
            if (!is_wp_error($result)) {
                $api_manager->increment_usage($current_provider, $max_tokens);
                $api_manager->set_cache($cache_key, $result);
                return $result;
            }

            // Store error and try next provider
            $last_error = $result;
        }

        // All providers failed, return last error
        return $last_error ? $last_error : new WP_Error('api_failed', 'All AI providers failed');
    }

    /**
     * Generate title from content
     */
    public function generate_title($request) {
        $content = $request->get_param('content');
        
        if (empty($content)) {
            return new WP_Error('no_content', 'Content is required', array('status' => 400));
        }
        
        // Limit content to first 1000 characters to avoid token overflow
        $content = wp_strip_all_tags($content);
        $content = substr($content, 0, 1000);
        
        $prompt = "Analyze this blog post content and generate an SEO-optimized, engaging title.\n\n";
        $prompt .= "Requirements:\n";
        $prompt .= "- 50-60 characters maximum\n";
        $prompt .= "- Include primary keyword from content\n";
        $prompt .= "- Compelling and click-worthy\n";
        $prompt .= "- Clear and descriptive\n";
        $prompt .= "- Return ONLY the title, no explanations\n\n";
        $prompt .= "Content:\n" . $content;

        $result = $this->call_ai_api_with_provider($prompt);

        if (is_wp_error($result)) {
            return $result;
        }

        return rest_ensure_response(array(
            'success' => true,
            'title' => $result,
            'length' => strlen($result)
        ));
    }
    
    /**
     * Generate excerpt from content
     */
    public function generate_excerpt($request) {
        $content = $request->get_param('content');
        
        if (empty($content)) {
            return new WP_Error('no_content', 'Content is required', array('status' => 400));
        }
        
        // Limit content to first 1000 characters to avoid token overflow
        $content = wp_strip_all_tags($content);
        $content = substr($content, 0, 1000);
        
        $prompt = "Create a compelling meta description for this blog post.\n\n";
        $prompt .= "Requirements:\n";
        $prompt .= "- 150-160 characters maximum\n";
        $prompt .= "- Include primary keyword from content\n";
        $prompt .= "- Summarize main points\n";
        $prompt .= "- Include call-to-action if appropriate\n";
        $prompt .= "- Return ONLY the excerpt, no explanations\n\n";
        $prompt .= "Content:\n" . $content;

        $result = $this->call_ai_api_with_provider($prompt);

        if (is_wp_error($result)) {
            return $result;
        }

        return rest_ensure_response(array(
            'success' => true,
            'excerpt' => $result,
            'length' => strlen($result)
        ));
    }
    
    /**
     * Optimize existing title
     */
    public function optimize_title($request) {
        $title = $request->get_param('title');
        $content = $request->get_param('content');
        
        if (empty($title) || empty($content)) {
            return new WP_Error('missing_data', 'Title and content are required', array('status' => 400));
        }
        
        $prompt = "Improve this title for SEO and engagement.\n\n";
        $prompt .= "Current title: " . $title . "\n\n";
        $prompt .= "Requirements:\n";
        $prompt .= "- 50-60 characters maximum\n";
        $prompt .= "- Better keyword optimization\n";
        $prompt .= "- More engaging and click-worthy\n";
        $prompt .= "- Return ONLY the improved title, no explanations\n\n";
        $prompt .= "Content:\n" . wp_strip_all_tags($content);

        $result = $this->call_ai_api_with_provider($prompt);

        if (is_wp_error($result)) {
            return $result;
        }

        return rest_ensure_response(array(
            'success' => true,
            'title' => $result,
            'original' => $title,
            'length' => strlen($result)
        ));
    }
    
    /**
     * Optimize existing excerpt
     */
    public function optimize_excerpt($request) {
        $excerpt = $request->get_param('excerpt');
        $content = $request->get_param('content');
        
        if (empty($excerpt) || empty($content)) {
            return new WP_Error('missing_data', 'Excerpt and content are required', array('status' => 400));
        }
        
        $prompt = "Optimize this meta description for SEO.\n\n";
        $prompt .= "Current excerpt: " . $excerpt . "\n\n";
        $prompt .= "Requirements:\n";
        $prompt .= "- 150-160 characters maximum\n";
        $prompt .= "- Better keyword optimization\n";
        $prompt .= "- More compelling\n";
        $prompt .= "- Return ONLY the improved excerpt, no explanations\n\n";
        $prompt .= "Content:\n" . wp_strip_all_tags($content);

        $result = $this->call_ai_api_with_provider($prompt);

        if (is_wp_error($result)) {
            return $result;
        }

        return rest_ensure_response(array(
            'success' => true,
            'excerpt' => $result,
            'original' => $excerpt,
            'length' => strlen($result)
        ));
    }
    
    /**
     * Call Google Gemini API with caching and rate limiting
     * Changed to protected to allow extension by child classes
     */
    protected function call_gemini_api($prompt, $max_tokens = 100) {
        if (empty($this->api_key)) {
            return new WP_Error('no_api_key', 'Gemini API key not configured', array('status' => 500));
        }

        // Use API manager for caching and rate limiting
        $api_manager = RVK_SEO_API_Manager::get_instance();

        // Check cache first
        $cache_key = 'gemini_' . md5($prompt . $max_tokens);
        $cached = $api_manager->get_cache($cache_key);

        if ($cached !== false) {
            return $cached;
        }

        // Check rate limit
        $rate_check = $api_manager->check_rate_limit('gemini');
        if (is_wp_error($rate_check)) {
            return $rate_check;
        }

        $url = $this->api_endpoint . '?key=' . $this->api_key;

        $body = array(
            'contents' => array(
                array(
                    'parts' => array(
                        array('text' => $prompt)
                    )
                )
            ),
            'generationConfig' => array(
                'temperature' => 0.7,
                'maxOutputTokens' => $max_tokens
            )
        );

        $response = wp_remote_post($url, array(
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode($body),
            'timeout' => 30,
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        // Log the response for debugging
        error_log('Gemini API Response: ' . print_r($data, true));

        if ($status_code !== 200) {
            $error_message = isset($data['error']['message']) ? $data['error']['message'] : 'API request failed';
            return new WP_Error('api_error', $error_message, array('status' => $status_code));
        }

        // Try different response formats
        $result = null;
        if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            $text = $data['candidates'][0]['content']['parts'][0]['text'];
            $result = trim($text);
        } elseif (isset($data['candidates'][0]['output'])) {
            $result = trim($data['candidates'][0]['output']);
        }

        if ($result) {
            // Cache result and increment usage
            $api_manager->set_cache($cache_key, $result);
            $api_manager->increment_usage('gemini', $max_tokens);
            return $result;
        }

        // Log the full response if we can't parse it
        error_log('Could not parse Gemini response. Full response: ' . $body);
        return new WP_Error('invalid_response', 'Invalid API response. Check error log for details.', array('status' => 500));
    }

    /**
     * Call OpenAI API with caching and rate limiting
     * New method to support dual AI providers
     */
    protected function call_openai_api($prompt, $max_tokens = 100) {
        $openai_key = get_option('dev_theme_openai_api_key', '');

        if (empty($openai_key)) {
            return new WP_Error('no_api_key', 'OpenAI API key not configured', array('status' => 500));
        }

        // Use API manager for caching and rate limiting
        $api_manager = RVK_SEO_API_Manager::get_instance();

        // Check cache first
        $cache_key = 'openai_' . md5($prompt . $max_tokens);
        $cached = $api_manager->get_cache($cache_key);

        if ($cached !== false) {
            return $cached;
        }

        // Check rate limit
        $rate_check = $api_manager->check_rate_limit('openai');
        if (is_wp_error($rate_check)) {
            return $rate_check;
        }

        $url = 'https://api.openai.com/v1/chat/completions';

        $body = array(
            'model' => 'gpt-4o-mini',
            'messages' => array(
                array(
                    'role' => 'user',
                    'content' => $prompt
                )
            ),
            'max_tokens' => $max_tokens,
            'temperature' => 0.7
        );

        $response = wp_remote_post($url, array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $openai_key
            ),
            'body' => json_encode($body),
            'timeout' => 30,
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        // Log the response for debugging
        error_log('OpenAI API Response: ' . print_r($data, true));

        if ($status_code !== 200) {
            $error_message = isset($data['error']['message']) ? $data['error']['message'] : 'API request failed';
            return new WP_Error('api_error', $error_message, array('status' => $status_code));
        }

        // Parse OpenAI response
        if (isset($data['choices'][0]['message']['content'])) {
            $result = trim($data['choices'][0]['message']['content']);

            // Cache result and increment usage
            $api_manager->set_cache($cache_key, $result);
            $api_manager->increment_usage('openai', $max_tokens);

            return $result;
        }

        // Log the full response if we can't parse it
        error_log('Could not parse OpenAI response. Full response: ' . $body);
        return new WP_Error('invalid_response', 'Invalid API response. Check error log for details.', array('status' => 500));
    }
}

// Initialize the AI Content Generator
new AI_Content_Generator();
