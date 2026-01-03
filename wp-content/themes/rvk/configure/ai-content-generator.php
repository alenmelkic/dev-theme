<?php
/**
 * AI Content Generator - Google Gemini Integration
 * Generates and optimizes post titles and excerpts using AI
 */

// Security: Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class AI_Content_Generator {
    
    private $api_key;
    private $api_endpoint = 'https://generativelanguage.googleapis.com/v1/models/gemini-2.0-flash:generateContent';
    
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
        
        $result = $this->call_gemini_api($prompt);
        
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
        
        $result = $this->call_gemini_api($prompt);
        
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
        
        $result = $this->call_gemini_api($prompt);
        
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
        
        $result = $this->call_gemini_api($prompt);
        
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
     * Call Google Gemini API
     * Changed to protected to allow extension by child classes
     */
    protected function call_gemini_api($prompt, $max_tokens = 100) {
        if (empty($this->api_key)) {
            return new WP_Error('no_api_key', 'Gemini API key not configured', array('status' => 500));
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
        if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            $text = $data['candidates'][0]['content']['parts'][0]['text'];
            return trim($text);
        }

        // Alternative format
        if (isset($data['candidates'][0]['output'])) {
            return trim($data['candidates'][0]['output']);
        }

        // Log the full response if we can't parse it
        error_log('Could not parse Gemini response. Full response: ' . $body);
        return new WP_Error('invalid_response', 'Invalid API response. Check error log for details.', array('status' => 500));
    }

    /**
     * Call OpenAI API
     * New method to support dual AI providers
     */
    protected function call_openai_api($prompt, $max_tokens = 100) {
        $openai_key = get_option('dev_theme_openai_api_key', '');

        if (empty($openai_key)) {
            return new WP_Error('no_api_key', 'OpenAI API key not configured', array('status' => 500));
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
            return trim($data['choices'][0]['message']['content']);
        }

        // Log the full response if we can't parse it
        error_log('Could not parse OpenAI response. Full response: ' . $body);
        return new WP_Error('invalid_response', 'Invalid API response. Check error log for details.', array('status' => 500));
    }
}

// Initialize the AI Content Generator
new AI_Content_Generator();
