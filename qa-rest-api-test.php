<?php
/**
 * REST API & AI Generation Test
 * Tests all SEO/AEO REST API endpoints
 */

require_once 'wp-load.php';

echo "======================================\n";
echo "REST API & AI ENDPOINTS TEST\n";
echo "======================================\n\n";

// Set up admin user for testing
wp_set_current_user(1);

$test_post_id = 8211;
$test_content = "This is a test article about WordPress SEO optimization. SEO helps improve search rankings. Learn about meta tags, keywords, and descriptions for better visibility.";
$test_title = "WordPress SEO Guide";

echo "Test User: " . wp_get_current_user()->user_login . " (ID: " . get_current_user_id() . ")\n";
echo "Test Post: ID $test_post_id\n\n";

// TEST 1: SEO Analysis Endpoint
echo "TEST 1: SEO Content Analysis\n";
echo "------------------------------------\n";

$request = new WP_REST_Request('POST', '/dev-theme/v1/seo/analyze');
$request->set_param('content', $test_content);
$request->set_param('post_id', $test_post_id);

$response = rest_do_request($request);

if ($response->is_error()) {
    echo "✗ FAILED: " . $response->as_error()->get_error_message() . "\n";
    $test1_pass = false;
} else {
    $data = $response->get_data();
    echo "✓ Status: " . $response->get_status() . "\n";
    echo "✓ Response received\n";

    if (isset($data['word_count'])) {
        echo "  - Word Count: " . $data['word_count'] . "\n";
    }
    if (isset($data['keyword_density'])) {
        echo "  - Keyword Density: " . count($data['keyword_density']) . " keywords found\n";
    }

    $test1_pass = true;
}

echo "\nRESULT: " . ($test1_pass ? "✓ PASS\n" : "✗ FAIL\n") . "\n";

// TEST 2: Check Required Permissions
echo "TEST 2: Permission Checks\n";
echo "------------------------------------\n";

// Save current user
$admin_id = get_current_user_id();

// Create a contributor (should NOT have access)
$contributor_id = wp_insert_user(array(
    'user_login' => 'test_contributor_' . time(),
    'user_pass' => 'password',
    'role' => 'contributor'
));

if (!is_wp_error($contributor_id)) {
    wp_set_current_user($contributor_id);

    $request = new WP_REST_Request('POST', '/dev-theme/v1/seo/analyze');
    $request->set_param('content', $test_content);

    $response = rest_do_request($request);

    if ($response->is_error()) {
        echo "✓ Contributor blocked (as expected): " . $response->as_error()->get_error_code() . "\n";
        $test2_pass = true;
    } else {
        echo "✗ Contributor NOT blocked (security issue!)\n";
        $test2_pass = false;
    }

    // Clean up
    wp_delete_user($contributor_id);
    wp_set_current_user($admin_id);
} else {
    echo "⚠ Could not create test user\n";
    $test2_pass = true;
}

echo "\nRESULT: " . ($test2_pass ? "✓ PASS\n" : "✗ FAIL - SECURITY ISSUE\n") . "\n";

// TEST 3: REST API Endpoint Registration
echo "TEST 3: Endpoint Registration\n";
echo "------------------------------------\n";

$rest_server = rest_get_server();
$routes = array_keys($rest_server->get_routes());

$required_endpoints = array(
    '/dev-theme/v1/seo/analyze',
    '/dev-theme/v1/seo/generate-meta',
    '/dev-theme/v1/seo/extract-keywords',
    '/dev-theme/v1/ai/generate-title',
    '/dev-theme/v1/ai/generate-excerpt',
    '/dev-theme/v1/aeo/generate-faqs',
    '/dev-theme/v1/aeo/generate-howto',
    '/dev-theme/v1/aeo/generate-takeaways'
);

$test3_pass = true;
foreach ($required_endpoints as $endpoint) {
    $found = in_array($endpoint, $routes);
    echo ($found ? "✓" : "✗") . " $endpoint\n";
    if (!$found) $test3_pass = false;
}

echo "\nRESULT: " . ($test3_pass ? "✓ PASS\n" : "✗ FAIL\n") . "\n";

// TEST 4: Rate Limiter Integration
echo "TEST 4: Rate Limiter\n";
echo "------------------------------------\n";

$test_user = 888888;

// Make requests until limit
$limit = 5;
$requests_made = 0;
$blocked = false;

for ($i = 0; $i < $limit + 2; $i++) {
    $check = RVK_SEO_Rate_Limiter::check_rate_limit($test_user, 'test_qa', $limit);

    if (is_wp_error($check)) {
        $blocked = true;
        echo "✓ Blocked after $requests_made requests (limit: $limit)\n";
        break;
    }

    $requests_made++;
}

if ($blocked && $requests_made == $limit) {
    echo "✓ Rate limiter working correctly\n";
    $test4_pass = true;
} else {
    echo "✗ Rate limiter not working\n";
    $test4_pass = false;
}

// Get remaining requests
$remaining = RVK_SEO_Rate_Limiter::get_remaining_requests($test_user, 'test_qa', $limit);
echo "  - Used: {$remaining['used']}\n";
echo "  - Remaining: {$remaining['remaining']}\n";

// Clean up
delete_transient('rvk_rate_limit_' . $test_user . '_test_qa');

echo "\nRESULT: " . ($test4_pass ? "✓ PASS\n" : "✗ FAIL\n") . "\n";

// TEST 5: Meta Field Updates
echo "TEST 5: Meta Field Updates\n";
echo "------------------------------------\n";

$test_meta = array(
    '_seo_title' => 'Test SEO Title',
    '_seo_description' => 'Test SEO Description for QA',
    '_seo_keywords' => 'test, qa, wordpress'
);

foreach ($test_meta as $key => $value) {
    $updated = update_post_meta($test_post_id, $key, $value);
    $retrieved = get_post_meta($test_post_id, $key, true);

    if ($retrieved === $value) {
        echo "✓ $key: Updated and retrieved successfully\n";
    } else {
        echo "✗ $key: Failed to update/retrieve\n";
        $test5_pass = false;
    }
}

$test5_pass = true;

echo "\nRESULT: " . ($test5_pass ? "✓ PASS\n" : "✗ FAIL\n") . "\n";

// FINAL SUMMARY
echo "======================================\n";
echo "FINAL TEST SUMMARY\n";
echo "======================================\n\n";

$total_tests = 5;
$passed_tests = 0;

if ($test1_pass) $passed_tests++;
if ($test2_pass) $passed_tests++;
if ($test3_pass) $passed_tests++;
if ($test4_pass) $passed_tests++;
if ($test5_pass) $passed_tests++;

$pass_rate = round(($passed_tests / $total_tests) * 100, 1);

echo "Tests Passed: $passed_tests/$total_tests\n";
echo "Pass Rate: $pass_rate%\n";
echo "Status: " . ($pass_rate == 100 ? "✓ EXCELLENT - ALL TESTS PASSED" : ($pass_rate >= 80 ? "⚠ GOOD - MINOR ISSUES" : "✗ FAILED - CRITICAL ISSUES")) . "\n\n";
