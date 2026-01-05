<?php
/**
 * WebSite Schema Markup with SearchAction
 * Outputs JSON-LD schema for website with search functionality
 */

$schema = array(
    '@context' => 'https://schema.org',
    '@type' => 'WebSite',
    'name' => get_bloginfo('name'),
    'description' => get_bloginfo('description'),
    'url' => home_url('/'),
    'potentialAction' => array(
        '@type' => 'SearchAction',
        'target' => array(
            '@type' => 'EntryPoint',
            'urlTemplate' => home_url('/?s={search_term_string}')
        ),
        'query-input' => 'required name=search_term_string'
    )
);

// Output JSON-LD
echo '<script type="application/ld+json">';
echo wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
echo '</script>' . "\n";
