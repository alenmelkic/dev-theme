<?php
/**
 * AI Settings Admin Page
 * Configure Google Gemini API key and AI features
 * Tailored by Alen Melkic
 */

// Security: Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class AI_Settings_Page {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    /**
     * Add settings page to WordPress admin
     */
    public function add_settings_page() {
        add_options_page(
            'AI Sadržaj Postavke',
            'AI Sadržaj',
            'manage_options',
            'ai-content-settings',
            array($this, 'render_settings_page')
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('ai_content_settings', 'dev_theme_gemini_api_key', array(
            'sanitize_callback' => 'sanitize_text_field',
        ));
        
        register_setting('ai_content_settings', 'dev_theme_ai_enabled', array(
            'sanitize_callback' => 'rest_sanitize_boolean',
            'default' => true,
        ));
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Save settings
        if (isset($_POST['submit']) && check_admin_referer('ai_content_settings')) {
            update_option('dev_theme_gemini_api_key', sanitize_text_field($_POST['gemini_api_key']));
            update_option('dev_theme_ai_enabled', isset($_POST['ai_enabled']));
            echo '<div class="notice notice-success"><p>Postavke uspješno sačuvane!</p></div>';
        }
        
        $api_key = get_option('dev_theme_gemini_api_key', '');
        $ai_enabled = get_option('dev_theme_ai_enabled', true);
        $api_key_masked = !empty($api_key) ? substr($api_key, 0, 10) . '...' : '';
        
        ?>
        <div class="wrap">
            <h1>AI Generisanje Sadržaja - Postavke</h1>
            
            <div class="card" style="max-width: 800px;">
                <h2>Google Gemini API Konfiguracija</h2>
                
                <form method="post" action="">
                    <?php wp_nonce_field('ai_content_settings'); ?>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="ai_enabled">Omogući AI Funkcije</label>
                            </th>
                            <td>
                                <input type="checkbox" 
                                       name="ai_enabled" 
                                       id="ai_enabled" 
                                       value="1" 
                                       <?php checked($ai_enabled, true); ?>>
                                <p class="description">
                                    Omogući AI generisanje naslova i izvoda u uređivaču objava
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="gemini_api_key">Gemini API Ključ</label>
                            </th>
                            <td>
                                <input type="text" 
                                       name="gemini_api_key" 
                                       id="gemini_api_key" 
                                       value="<?php echo esc_attr($api_key); ?>" 
                                       class="regular-text"
                                       placeholder="Unesite vaš Gemini API ključ">
                                
                                <?php if (!empty($api_key)): ?>
                                    <p class="description" style="color: green;">
                                        ✓ API ključ konfigurisan: <?php echo esc_html($api_key_masked); ?>
                                    </p>
                                <?php else: ?>
                                    <p class="description" style="color: orange;">
                                        ⚠ API ključ nije konfigurisan. AI funkcije neće raditi.
                                    </p>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                    
                    <?php submit_button('Sačuvaj Postavke'); ?>
                </form>
            </div>
            
            <div class="card" style="max-width: 800px; margin-top: 20px;">
                <h2>Kako Dobiti Gemini API Ključ (BESPLATNO)</h2>
                <ol>
                    <li>Idite na <a href="https://makersuite.google.com/app/apikey" target="_blank">Google AI Studio</a></li>
                    <li>Prijavite se sa vašim Google nalogom</li>
                    <li>Kliknite "Get API Key" ili "Create API Key"</li>
                    <li>Kopirajte API ključ</li>
                    <li>Zalijepite ga u polje iznad i sačuvajte</li>
                </ol>
                
                <p><strong>Besplatni Plan:</strong> 60 zahtjeva po minuti - savršeno za većinu web stranica!</p>
            </div>
            
            <div class="card" style="max-width: 800px; margin-top: 20px;">
                <h2>Funkcije</h2>
                <ul>
                    <li>✨ <strong>Generiši Naslov</strong> - AI kreira SEO optimizovan naslov iz sadržaja</li>
                    <li>✨ <strong>Generiši Sažetak</strong> - AI kreira privlačan meta opis</li>
                    <li>🔧 <strong>Optimiziraj Naslov</strong> - AI poboljšava postojeći naslov za SEO</li>
                    <li>🔧 <strong>Optimiziraj Sažetak</strong> - AI unapređuje postojeći sažetak</li>
                </ul>
                
                <p><strong>Upotreba:</strong> Potražite AI dugmad u uređivaču objava pored polja za naslov i sažetak (tri tačke ⋮ u gornjem desnom uglu).</p>
            </div>
            
            <div class="card" style="max-width: 800px; margin-top: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none;">
                <p style="margin: 0; text-align: center; font-size: 14px;">
                    <strong>⚡ Tailored by Alen Melkić</strong>
                </p>
            </div>
        </div>
        
        <style>
            .card {
                background: #fff;
                border: 1px solid #ccd0d4;
                border-radius: 4px;
                padding: 20px;
                box-shadow: 0 1px 1px rgba(0,0,0,.04);
            }
            .card h2 {
                margin-top: 0;
            }
            .card ol, .card ul {
                margin-left: 20px;
            }
        </style>
        <?php
    }
}

// Initialize AI Settings Page
new AI_Settings_Page();
