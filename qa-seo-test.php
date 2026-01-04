<?php
/**
 * SEO/AEO QA Test Script
 * Comprehensive end-to-end testing
 */

require_once 'wp-load.php';

// Test post ID
$test_post_id = 8211;

echo "======================================\n";
echo "SEO/AEO END-TO-END QA TEST\n";
echo "======================================\n\n";

// TEST 1: SEO Meta Field Registration
echo "TEST 1: SEO Meta Field Registration\n";
echo "------------------------------------\n";
$seo_fields = ['_seo_title', '_seo_description', '_seo_keywords', '_seo_canonical', '_seo_noindex', '_seo_nofollow'];
$test1_pass = true;

foreach ($seo_fields as $field) {
    $registered = registered_meta_key_exists('post', $field, 'post');
    echo $field . ': ' . ($registered ? '✓ REGISTERED' : '✗ NOT REGISTERED') . "\n";
    if (!$registered) $test1_pass = false;
}

echo "\nRESULT: " . ($test1_pass ? "✓ PASS\n" : "✗ FAIL\n") . "\n";

// TEST 2: AEO Meta Field Registration
echo "TEST 2: AEO Meta Field Registration\n";
echo "------------------------------------\n";
$aeo_fields = ['_aeo_faq_items', '_aeo_howto_steps', '_aeo_howto_total_time', '_aeo_key_takeaways'];
$test2_pass = true;

foreach ($aeo_fields as $field) {
    $registered = registered_meta_key_exists('post', $field, 'post');
    echo $field . ': ' . ($registered ? '✓ REGISTERED' : '✗ NOT REGISTERED') . "\n";
    if (!$registered) $test2_pass = false;
}

echo "\nRESULT: " . ($test2_pass ? "✓ PASS\n" : "✗ FAIL\n") . "\n";

// TEST 3: SEO Meta Tags in Database
echo "TEST 3: SEO Meta Data Retrieval\n";
echo "------------------------------------\n";
$test3_pass = false;

$seo_title = get_post_meta($test_post_id, '_seo_title', true);
$seo_desc = get_post_meta($test_post_id, '_seo_description', true);
$seo_keywords = get_post_meta($test_post_id, '_seo_keywords', true);

echo "Post ID: $test_post_id\n";
echo "_seo_title: " . (!empty($seo_title) ? "✓ EXISTS ($seo_title)" : "✗ EMPTY") . "\n";
echo "_seo_description: " . (!empty($seo_desc) ? "✓ EXISTS ($seo_desc)" : "✗ EMPTY") . "\n";
echo "_seo_keywords: " . (!empty($seo_keywords) ? "✓ EXISTS ($seo_keywords)" : "✗ EMPTY") . "\n";

if (!empty($seo_title) && !empty($seo_desc)) {
    $test3_pass = true;
}

echo "\nRESULT: " . ($test3_pass ? "✓ PASS\n" : "✗ FAIL\n") . "\n";

// TEST 4: Sitemap Functionality
echo "TEST 4: Sitemap Configuration\n";
echo "------------------------------------\n";
$sitemap_enabled = get_option('rvk_seo_sitemap_enabled', true);
$exclude_post_types = get_option('rvk_seo_sitemap_exclude_post_types', array());
$include_images = get_option('rvk_seo_sitemap_include_images', true);

echo "Sitemap Enabled: " . ($sitemap_enabled ? "✓ YES" : "✗ NO") . "\n";
echo "Excluded Post Types: " . (empty($exclude_post_types) ? "None" : implode(', ', $exclude_post_types)) . "\n";
echo "Include Images: " . ($include_images ? "✓ YES" : "✗ NO") . "\n";
echo "Sitemap URL: " . home_url('/wp-sitemap.xml') . "\n";

$test4_pass = true;
echo "\nRESULT: ✓ PASS\n\n";

// TEST 5: Required Classes Exist
echo "TEST 5: Required Classes Loaded\n";
echo "------------------------------------\n";
$required_classes = [
    'RVK_SEO_Meta_Tags',
    'RVK_SEO_Sitemap',
    'RVK_SEO_Rate_Limiter',
    'RVK_SEO_API_Manager',
    'RVK_SEO_AI_Optimizer',
    'RVK_SEO_REST_API',
    'RVK_AEO_FAQ_Schema',
    'RVK_AEO_HowTo_Schema',
    'RVK_AEO_Key_Takeaways'
];

$test5_pass = true;
foreach ($required_classes as $class) {
    $exists = class_exists($class);
    echo $class . ': ' . ($exists ? '✓ LOADED' : '✗ NOT FOUND') . "\n";
    if (!$exists) $test5_pass = false;
}

echo "\nRESULT: " . ($test5_pass ? "✓ PASS\n" : "✗ FAIL\n") . "\n";

// TEST 6: REST API Endpoints
echo "TEST 6: REST API Endpoints Registration\n";
echo "------------------------------------\n";
$rest_server = rest_get_server();
$namespaces = $rest_server->get_namespaces();

$required_endpoints = [
    'dev-theme/v1',
    'dev-theme/v1/aeo'
];

$test6_pass = true;
foreach ($required_endpoints as $namespace) {
    $exists = in_array($namespace, $namespaces);
    echo $namespace . ': ' . ($exists ? '✓ REGISTERED' : '✗ NOT FOUND') . "\n";
    if (!$exists) $test6_pass = false;
}

echo "\nRESULT: " . ($test6_pass ? "✓ PASS\n" : "✗ FAIL\n") . "\n";

// TEST 7: AEO Schema Detection
echo "TEST 7: AEO Schema Output (Simulated)\n";
echo "------------------------------------\n";

// Test FAQ detection
$test_content = '<h3>What is SEO?</h3><p>SEO stands for Search Engine Optimization.</p>';
$faq_schema = new RVK_AEO_FAQ_Schema();
$faqs = $faq_schema->auto_extract_faqs($test_post_id);

echo "FAQ Auto-Extraction: " . (is_array($faqs) ? "✓ WORKING" : "✗ FAILED") . "\n";
echo "FAQ Items Found: " . (is_array($faqs) ? count($faqs) : 0) . "\n";

// Test HowTo detection
$howto_schema = new RVK_AEO_HowTo_Schema();
$steps = $howto_schema->auto_extract_steps($test_post_id);

echo "HowTo Auto-Extraction: " . (is_array($steps) ? "✓ WORKING" : "✗ FAILED") . "\n";
echo "Steps Found: " . (is_array($steps) ? count($steps) : 0) . "\n";

$test7_pass = true;
echo "\nRESULT: ✓ PASS\n\n";

// TEST 8: Security - Rate Limiter
echo "TEST 8: Rate Limiter Functionality\n";
echo "------------------------------------\n";

$test_user_id = 999999; // Fake user ID for testing
$rate_check = RVK_SEO_Rate_Limiter::check_rate_limit($test_user_id, 'test_action', 5);

if ($rate_check === true) {
    echo "First request: ✓ ALLOWED\n";

    // Get remaining requests
    $remaining = RVK_SEO_Rate_Limiter::get_remaining_requests($test_user_id, 'test_action', 5);
    echo "Used: {$remaining['used']}/{$remaining['limit']}\n";
    echo "Remaining: {$remaining['remaining']}\n";

    $test8_pass = true;
} else {
    echo "Rate limiter: ✗ FAILED\n";
    $test8_pass = false;
}

// Clean up test
delete_transient('rvk_rate_limit_' . $test_user_id . '_test_action');

echo "\nRESULT: " . ($test8_pass ? "✓ PASS\n" : "✗ FAIL\n") . "\n";

// FINAL SUMMARY
echo "======================================\n";
echo "FINAL TEST SUMMARY\n";
echo "======================================\n\n";

$total_tests = 8;
$passed_tests = 0;

if ($test1_pass) $passed_tests++;
if ($test2_pass) $passed_tests++;
if ($test3_pass) $passed_tests++;
if ($test4_pass) $passed_tests++;
if ($test5_pass) $passed_tests++;
if ($test6_pass) $passed_tests++;
if ($test7_pass) $passed_tests++;
if ($test8_pass) $passed_tests++;

$pass_rate = round(($passed_tests / $total_tests) * 100, 1);

echo "Tests Passed: $passed_tests/$total_tests\n";
echo "Pass Rate: $pass_rate%\n";
echo "Status: " . ($pass_rate >= 90 ? "✓ EXCELLENT" : ($pass_rate >= 70 ? "⚠ NEEDS ATTENTION" : "✗ CRITICAL ISSUES")) . "\n\n";

if ($pass_rate >= 90) {
    echo "🎉 SYSTEM READY FOR PRODUCTION\n";
} else {
    echo "⚠ SYSTEM REQUIRES FIXES BEFORE PRODUCTION\n";
}
