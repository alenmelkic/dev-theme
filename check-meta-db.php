<?php
require_once('wp-load.php');
$post_id = 141214;
echo "Post ID: $post_id\n";
$meta = get_post_custom($post_id);
foreach ($meta as $key => $values) {
    if (strpos($key, '_seo_') === 0) {
        echo "$key: " . $values[0] . "\n";
    }
}
