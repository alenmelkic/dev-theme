<?php
/**
 * Component: Structured Data (JSON-LD)
 * Handles AEO/SEO schema for archive pages
 */

if (!defined('ABSPATH')) {
    exit;
}

$items = array();
$counter = 1;

if (have_posts()) {
    while (have_posts()) {
        the_post();
        $items[] = array(
            "@type" => "ListItem",
            "position" => $counter++,
            "item" => array(
                "@type" => "BlogPosting",
                "headline" => get_the_title(),
                "url" => get_permalink(),
                "datePublished" => get_the_date('c'),
                "author" => array(
                    "@type" => "Person",
                    "name" => get_the_author()
                ),
                "image" => get_the_post_thumbnail_url(get_the_ID(), 'large'),
                "description" => wp_strip_all_tags(get_the_excerpt())
            )
        );
    }
    // Reset post data after the loop
    wp_reset_postdata();
}

$schema = array(
    "@context" => "https://schema.org",
    "@type" => "ItemList",
    "numberOfItems" => count($items),
    "itemListElement" => $items
);
?>

<script type="application/ld+json">
<?php echo json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT); ?>
</script>
