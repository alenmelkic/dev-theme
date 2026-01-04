<?php
require_once 'wp-load.php';

// Check post ID 8211 (from the screenshot)
$post_id = 8211;

echo "Checking meta for post ID: $post_id\n\n";

$meta_keys = [
    '_seo_title',
    '_seo_description',
    '_seo_keywords'
];

foreach ($meta_keys as $key) {
    $value = get_post_meta($post_id, $key, true);
    echo "$key: " . ($value ? $value : '(empty)') . "\n";
}

echo "\n--- Full post meta ---\n";
$all_meta = get_post_meta($post_id);
foreach ($all_meta as $key => $values) {
    if (strpos($key, '_seo_') === 0) {
        echo "$key: " . print_r($values, true);
    }
}
