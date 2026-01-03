<?php
/**
 * Organization Schema Markup
 * Outputs JSON-LD schema for organization
 */

// Get organization data from settings
$org_name = get_option('rvk_seo_organization_name', get_bloginfo('name'));
$org_logo_id = get_option('rvk_seo_organization_logo');
$org_logo_url = $org_logo_id ? wp_get_attachment_image_url($org_logo_id, 'full') : '';

// Social profile URLs
$social_profiles = array_filter(array(
    get_option('rvk_seo_social_facebook', ''),
    get_option('rvk_seo_social_twitter', ''),
    get_option('rvk_seo_social_instagram', ''),
    get_option('rvk_seo_social_linkedin', '')
));

// Build schema
$schema = array(
    '@context' => 'https://schema.org',
    '@type' => 'Organization',
    'name' => $org_name,
    'url' => home_url('/'),
);

// Add logo if available
if (!empty($org_logo_url)) {
    $schema['logo'] = array(
        '@type' => 'ImageObject',
        'url' => $org_logo_url
    );
}

// Add social profiles if available
if (!empty($social_profiles)) {
    $schema['sameAs'] = $social_profiles;
}

// Output JSON-LD
echo '<script type="application/ld+json">';
echo wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
echo '</script>' . "\n";
