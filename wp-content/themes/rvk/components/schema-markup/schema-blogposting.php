<?php
/**
 * BlogPosting Schema Markup
 * Outputs JSON-LD schema for blog posts
 */

// Only output on single posts
if (!is_singular('post')) {
    return;
}

$post_id = get_the_ID();

// Get post data
$title = get_the_title($post_id);
$description = get_the_excerpt($post_id);
if (empty($description)) {
    $content = get_post_field('post_content', $post_id);
    $description = wp_trim_words(wp_strip_all_tags($content), 30);
}

$date_published = get_the_date('c', $post_id);
$date_modified = get_the_modified_date('c', $post_id);
$author_id = get_post_field('post_author', $post_id);
$author_name = get_the_author_meta('display_name', $author_id);
$author_url = get_author_posts_url($author_id);

// Get featured image
$image_url = get_the_post_thumbnail_url($post_id, 'large');
if (!$image_url) {
    // Fallback to default OG image
    $default_og_image_id = get_option('rvk_seo_default_og_image');
    if ($default_og_image_id) {
        $image_url = wp_get_attachment_image_url($default_og_image_id, 'large');
    }
}

// Get organization logo for publisher
$org_logo_id = get_option('rvk_seo_organization_logo');
$org_logo_url = $org_logo_id ? wp_get_attachment_image_url($org_logo_id, 'full') : '';

// Build schema
$schema = array(
    '@context' => 'https://schema.org',
    '@type' => 'BlogPosting',
    'headline' => $title,
    'description' => $description,
    'datePublished' => $date_published,
    'dateModified' => $date_modified,
    'author' => array(
        '@type' => 'Person',
        'name' => $author_name,
        'url' => $author_url
    ),
    'publisher' => array(
        '@type' => 'Organization',
        'name' => get_option('rvk_seo_organization_name', get_bloginfo('name')),
    ),
    'mainEntityOfPage' => array(
        '@type' => 'WebPage',
        '@id' => get_permalink($post_id)
    )
);

// Add image if available
if (!empty($image_url)) {
    $schema['image'] = array(
        '@type' => 'ImageObject',
        'url' => $image_url
    );
}

// Add publisher logo if available
if (!empty($org_logo_url)) {
    $schema['publisher']['logo'] = array(
        '@type' => 'ImageObject',
        'url' => $org_logo_url
    );
}

// Add article section (primary category)
$categories = get_the_category($post_id);
if (!empty($categories)) {
    $schema['articleSection'] = $categories[0]->name;
}

// Add keywords from tags
$tags = get_the_tags($post_id);
if ($tags) {
    $keywords = array();
    foreach ($tags as $tag) {
        $keywords[] = $tag->name;
    }
    $schema['keywords'] = implode(', ', $keywords);
}

// Add word count
$content = get_post_field('post_content', $post_id);
$word_count = str_word_count(wp_strip_all_tags($content));
if ($word_count > 0) {
    $schema['wordCount'] = $word_count;
}

// Output JSON-LD
echo '<script type="application/ld+json">';
echo wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
echo '</script>' . "\n";
