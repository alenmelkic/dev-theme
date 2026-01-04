<?php
require_once('wp-load.php');

$post_id = 141214;
$post = get_post($post_id);

echo "POST ID: " . $post_id . "\n";
echo "POST TYPE: " . ($post ? $post->post_type : "NOT FOUND") . "\n";

$meta_keys = array('_seo_naslov', '_seo_opis', '_seo_tagovi', '_seo_title', '_seo_description');

foreach ($meta_keys as $key) {
    $exists = metadata_exists('post', $post_id, $key);
    $value = get_post_meta($post_id, $key, true);
    echo "META [$key]: " . ($exists ? "EXISTS" : "MISSING") . " | VALUE: '$value'\n";
    
    // Check if registered
    $registered = get_registered_meta_keys('post');
    if (isset($registered[$key])) {
        echo "  REGISTERED: YES\n";
        echo "  SHOW IN REST: " . ($registered[$key]['show_in_rest'] ? "YES" : "NO") . "\n";
        echo "  SINGLE: " . ($registered[$key]['single'] ? "YES" : "NO") . "\n";
    } else {
        echo "  REGISTERED: NO\n";
    }
}

// Check specific post type registration
if ($post) {
    $registered_pt = get_registered_meta_keys('post', $post->post_type);
    echo "\nPOST TYPE SPECIFIC REGISTRATION (" . $post->post_type . "):\n";
    foreach ($meta_keys as $key) {
        if (isset($registered_pt[$key])) {
            echo "  $key: REGISTERED\n";
        } else {
            echo "  $key: NOT REGISTERED\n";
        }
    }
}
