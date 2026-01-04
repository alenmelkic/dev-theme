<?php
/**
 * AEO (Answer Engine Optimization) QA Test
 * Tests FAQ, HowTo, and Key Takeaways functionality
 */

require_once 'wp-load.php';

echo "======================================\n";
echo "AEO FEATURES END-TO-END TEST\n";
echo "======================================\n\n";

// TEST 1: FAQ Auto-Extraction
echo "TEST 1: FAQ Auto-Extraction\n";
echo "------------------------------------\n";

$faq_content = '
<h3>What is SEO?</h3>
<p>SEO stands for Search Engine Optimization, the practice of optimizing websites for search engines.</p>

<h3>How does SEO work?</h3>
<p>SEO works by improving various factors like keywords, meta tags, and content quality to rank higher in search results.</p>

<h3>Why is SEO important?</h3>
<p>SEO is important because it drives organic traffic to your website and increases visibility.</p>
';

// Create test post
$test_post_id = wp_insert_post(array(
    'post_title' => 'SEO FAQ Test - QA',
    'post_content' => $faq_content,
    'post_status' => 'publish',
    'post_type' => 'post'
));

if (is_wp_error($test_post_id)) {
    echo "✗ Failed to create test post\n";
    $test1_pass = false;
} else {
    $faq_schema = new RVK_AEO_FAQ_Schema();
    $extracted_faqs = $faq_schema->auto_extract_faqs($test_post_id);

    echo "Test Post ID: $test_post_id\n";
    echo "Extracted FAQs: " . count($extracted_faqs) . "\n";

    if (count($extracted_faqs) >= 3) {
        echo "✓ Successfully extracted FAQs\n";

        foreach ($extracted_faqs as $index => $faq) {
            echo "  FAQ " . ($index + 1) . ":\n";
            echo "    Q: " . substr($faq['question'], 0, 50) . "...\n";
            echo "    A: " . substr($faq['answer'], 0, 50) . "...\n";
        }

        $test1_pass = true;
    } else {
        echo "✗ Failed to extract FAQs\n";
        $test1_pass = false;
    }

    wp_delete_post($test_post_id, true);
}

echo "\nRESULT: " . ($test1_pass ? "✓ PASS\n" : "✗ FAIL\n") . "\n";

// TEST 2: HowTo Auto-Extraction
echo "TEST 2: HowTo Auto-Extraction\n";
echo "------------------------------------\n";

$howto_content = '
<h2>How to Optimize Your Website</h2>
<ol>
    <li>Research relevant keywords for your content</li>
    <li>Optimize meta tags including title and description</li>
    <li>Create high-quality, original content</li>
    <li>Build quality backlinks from reputable sources</li>
    <li>Monitor your rankings and adjust strategy</li>
</ol>
';

$test_howto_id = wp_insert_post(array(
    'post_title' => 'HowTo Test - QA',
    'post_content' => $howto_content,
    'post_status' => 'publish',
    'post_type' => 'post'
));

if (is_wp_error($test_howto_id)) {
    echo "✗ Failed to create test post\n";
    $test2_pass = false;
} else {
    $howto_schema = new RVK_AEO_HowTo_Schema();
    $extracted_steps = $howto_schema->auto_extract_steps($test_howto_id);

    echo "Test Post ID: $test_howto_id\n";
    echo "Extracted Steps: " . count($extracted_steps) . "\n";

    if (count($extracted_steps) >= 5) {
        echo "✓ Successfully extracted HowTo steps\n";

        foreach ($extracted_steps as $index => $step) {
            echo "  Step " . ($index + 1) . ": " . substr($step['text'], 0, 60) . "...\n";
        }

        $test2_pass = true;
    } else {
        echo "✗ Failed to extract steps\n";
        $test2_pass = false;
    }

    wp_delete_post($test_howto_id, true);
}

echo "\nRESULT: " . ($test2_pass ? "✓ PASS\n" : "✗ FAIL\n") . "\n";

// TEST 3: Key Takeaways Injection
echo "TEST 3: Key Takeaways Injection\n";
echo "------------------------------------\n";

$takeaways_content = '<p>This is the first paragraph of an article.</p><p>This is the second paragraph.</p>';
$takeaways_text = "• First key point\n• Second key point\n• Third key point";

$test_takeaways_id = wp_insert_post(array(
    'post_title' => 'Takeaways Test - QA',
    'post_content' => $takeaways_content,
    'post_status' => 'publish',
    'post_type' => 'post'
));

if (is_wp_error($test_takeaways_id)) {
    echo "✗ Failed to create test post\n";
    $test3_pass = false;
} else {
    // Save takeaways meta
    update_post_meta($test_takeaways_id, '_aeo_key_takeaways', $takeaways_text);

    // Get the content with filter applied
    $filtered_content = apply_filters('the_content', get_post_field('post_content', $test_takeaways_id));

    echo "Test Post ID: $test_takeaways_id\n";

    // Check if takeaways box was injected
    if (strpos($filtered_content, 'aeo-key-takeaways') !== false) {
        echo "✓ Key takeaways box injected into content\n";

        if (strpos($filtered_content, 'First key point') !== false) {
            echo "✓ Takeaway content rendered correctly\n";
            $test3_pass = true;
        } else {
            echo "✗ Takeaway content not found\n";
            $test3_pass = false;
        }
    } else {
        echo "✗ Takeaways box not injected\n";
        $test3_pass = false;
    }

    wp_delete_post($test_takeaways_id, true);
}

echo "\nRESULT: " . ($test3_pass ? "✓ PASS\n" : "✗ FAIL\n") . "\n";

// TEST 4: Schema Output
echo "TEST 4: JSON-LD Schema Output\n";
echo "------------------------------------\n";

// Create post with both FAQ and HowTo
$schema_post_id = wp_insert_post(array(
    'post_title' => 'Schema Test - QA',
    'post_content' => $faq_content . $howto_content,
    'post_status' => 'publish',
    'post_type' => 'post'
));

if (is_wp_error($schema_post_id)) {
    echo "✗ Failed to create test post\n";
    $test4_pass = false;
} else {
    // Set up query
    global $wp_query, $post;
    $post = get_post($schema_post_id);
    setup_postdata($post);
    $wp_query->is_single = true;
    $wp_query->is_singular = true;
    $wp_query->queried_object = $post;
    $wp_query->queried_object_id = $schema_post_id;

    // Capture wp_head output
    ob_start();
    do_action('wp_head');
    $head_output = ob_get_clean();

    echo "Test Post ID: $schema_post_id\n";

    // Check for FAQ schema
    $has_faq_schema = strpos($head_output, '"@type":"FAQPage"') !== false ||
                       strpos($head_output, '"@type": "FAQPage"') !== false;

    // Check for HowTo schema
    $has_howto_schema = strpos($head_output, '"@type":"HowTo"') !== false ||
                         strpos($head_output, '"@type": "HowTo"') !== false;

    echo ($has_faq_schema ? "✓" : "✗") . " FAQ Schema JSON-LD output\n";
    echo ($has_howto_schema ? "✓" : "✗") . " HowTo Schema JSON-LD output\n";

    $test4_pass = $has_faq_schema && $has_howto_schema;

    wp_reset_postdata();
    wp_delete_post($schema_post_id, true);
}

echo "\nRESULT: " . ($test4_pass ? "✓ PASS\n" : "✗ FAIL\n") . "\n";

// TEST 5: Meta Field Registration
echo "TEST 5: AEO Meta Fields\n";
echo "------------------------------------\n";

$aeo_fields = array(
    '_aeo_faq_items',
    '_aeo_howto_steps',
    '_aeo_howto_total_time',
    '_aeo_key_takeaways'
);

$test5_pass = true;
foreach ($aeo_fields as $field) {
    $registered = registered_meta_key_exists('post', $field, 'post');
    echo ($registered ? "✓" : "✗") . " $field\n";
    if (!$registered) $test5_pass = false;
}

echo "\nRESULT: " . ($test5_pass ? "✓ PASS\n" : "✗ FAIL\n") . "\n";

// FINAL SUMMARY
echo "======================================\n";
echo "AEO TEST SUMMARY\n";
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

if ($pass_rate == 100) {
    echo "🎉 AEO SYSTEM FULLY FUNCTIONAL\n";
}
