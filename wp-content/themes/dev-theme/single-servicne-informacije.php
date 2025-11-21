<?php
/**
 * Template for single Servicna informacija
 */

get_header();

// Get the current post slug
global $post;
$post_slug = $post->post_name;
?>

<main id="main" class="site-main servicna-informacija-single-page">
    <div class="container">
        <!-- React will mount here -->
        <div 
            id="react-servicna-informacija-single" 
            data-slug="<?php echo esc_attr($post_slug); ?>"
        ></div>
    </div>
</main>

<?php
get_footer();
