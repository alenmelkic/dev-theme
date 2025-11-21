<?php
/**
 * Debug REST API for Servicne informacije
 * 
 * Visit this file in your browser to test the REST API
 * Example: yoursite.com/wp-content/themes/dev-theme/debug-rest-api.php
 */

// Load WordPress
require_once('../../../../../wp-load.php');

header('Content-Type: application/json');

echo json_encode([
    'message' => 'Testing Servicne informacije REST API',
    'tests' => [
        'post_type_registered' => post_type_exists('servicne-informacije'),
        'taxonomy_registered' => taxonomy_exists('servicne_tag'),
        'rest_url' => rest_url('wp/v2/servicne-informacije'),
        'sample_request' => 'Try: ' . rest_url('wp/v2/servicne-informacije'),
    ],
    'instructions' => [
        '1. Check if post type is registered (should be true)',
        '2. Visit the sample_request URL to see if REST API works',
        '3. If you get 404, flush rewrite rules',
        '4. Go to Settings > Permalinks and click Save',
    ]
], JSON_PRETTY_PRINT);
