<?php
/**
 * SEO HTML Output Test
 * Tests actual meta tag output in HTML
 */

require_once 'wp-load.php';

$test_post_id = 8211;

echo "======================================\n";
echo "SEO META TAGS HTML OUTPUT TEST\n";
echo "======================================\n\n";

// Set up WordPress query for single post
global $wp_query, $post;
$post = get_post($test_post_id);
setup_postdata($post);
$wp_query->is_single = true;
$wp_query->is_singular = true;
$wp_query->queried_object = $post;
$wp_query->queried_object_id = $test_post_id;

echo "Testing post: " . get_the_title($test_post_id) . " (ID: $test_post_id)\n\n";

// Capture wp_head output
ob_start();
do_action('wp_head');
$head_output = ob_get_clean();

echo "META TAGS FOUND:\n";
echo "------------------------------------\n";

// Check for SEO title
if (preg_match('/<title>([^<]+)<\/title>/', $head_output, $title_match)) {
    echo "✓ Title Tag: " . trim($title_match[1]) . "\n";
} else {
    echo "✗ Title Tag: NOT FOUND\n";
}

// Check for meta description
if (preg_match('/<meta name="description" content="([^"]+)"/', $head_output, $desc_match)) {
    echo "✓ Meta Description: " . substr($desc_match[1], 0, 80) . "...\n";
} else {
    echo "✗ Meta Description: NOT FOUND\n";
}

// Check for meta keywords
if (preg_match('/<meta name="keywords" content="([^"]+)"/', $head_output, $keywords_match)) {
    echo "✓ Meta Keywords: " . $keywords_match[1] . "\n";
} else {
    echo "✗ Meta Keywords: NOT FOUND\n";
}

// Check for canonical
if (preg_match('/<link rel="canonical" href="([^"]+)"/', $head_output, $canonical_match)) {
    echo "✓ Canonical URL: " . $canonical_match[1] . "\n";
} else {
    echo "✗ Canonical URL: NOT FOUND\n";
}

// Check for robots meta
if (preg_match('/<meta name="robots" content="([^"]+)"/', $head_output, $robots_match)) {
    echo "✓ Robots Meta: " . $robots_match[1] . "\n";
} else {
    echo "✓ Robots Meta: Not present (default: index, follow)\n";
}

// Check for Open Graph tags
$og_count = preg_match_all('/<meta property="og:([^"]+)" content="([^"]+)"/', $head_output, $og_matches);
echo "✓ Open Graph Tags: $og_count found\n";

if ($og_count > 0) {
    foreach ($og_matches[1] as $index => $og_property) {
        echo "  - og:$og_property: " . substr($og_matches[2][$index], 0, 50) . "...\n";
    }
}

// Check for Twitter Card tags
$twitter_count = preg_match_all('/<meta name="twitter:([^"]+)" content="([^"]+)"/', $head_output, $twitter_matches);
echo "✓ Twitter Card Tags: $twitter_count found\n";

if ($twitter_count > 0) {
    foreach ($twitter_matches[1] as $index => $twitter_property) {
        echo "  - twitter:$twitter_property: " . substr($twitter_matches[2][$index], 0, 50) . "...\n";
    }
}

// Check for JSON-LD schema
$schema_count = preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/s', $head_output, $schema_matches);
echo "\n✓ JSON-LD Schema Blocks: $schema_count found\n";

if ($schema_count > 0) {
    foreach ($schema_matches[1] as $index => $schema_json) {
        $schema_data = json_decode(trim($schema_json), true);
        if ($schema_data && isset($schema_data['@type'])) {
            echo "  - Schema Type: " . $schema_data['@type'] . "\n";
        }
    }
}

// Check for duplicate tags
echo "\nDUPLICATE CHECK:\n";
echo "------------------------------------\n";

$title_count = substr_count($head_output, '<title>');
$desc_count = preg_match_all('/<meta name="description"/', $head_output);
$canonical_count = preg_match_all('/<link rel="canonical"/', $head_output);
$robots_count = preg_match_all('/<meta name="robots"/', $head_output);

echo "Title tags: $title_count " . ($title_count == 1 ? "✓" : "✗ DUPLICATE!") . "\n";
echo "Description tags: $desc_count " . ($desc_count <= 1 ? "✓" : "✗ DUPLICATE!") . "\n";
echo "Canonical tags: $canonical_count " . ($canonical_count <= 1 ? "✓" : "✗ DUPLICATE!") . "\n";
echo "Robots tags: $robots_count " . ($robots_count <= 1 ? "✓" : "✗ DUPLICATE!") . "\n";

// Overall result
$has_duplicates = ($title_count != 1 || $desc_count > 1 || $canonical_count > 1 || $robots_count > 1);

echo "\n======================================\n";
echo "RESULT: " . (!$has_duplicates ? "✓ PASS - No duplicates" : "✗ FAIL - Duplicates detected") . "\n";
echo "======================================\n";

wp_reset_postdata();
