<?php
/**
 * Template for Servicne informacije archive
 */

get_header();

// Get filter parameters from URL
$search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
$tag_id = isset($_GET['tag']) ? intval($_GET['tag']) : 0;
$paged = get_query_var('paged') ? get_query_var('paged') : 1;

// Build query args
$args = array(
    'post_type' => 'servicne-informacije',
    'posts_per_page' => 12,
    'paged' => $paged,
    'orderby' => 'date',
    'order' => 'DESC'
);

// Add search
if (!empty($search)) {
    $args['s'] = $search;
}

// Add tag filter
if ($tag_id > 0) {
    $args['tax_query'] = array(
        array(
            'taxonomy' => 'servicne_tag',
            'field' => 'term_id',
            'terms' => $tag_id
        )
    );
}

// Execute query
$query = new WP_Query($args);

// Get all tags for filter
$tags = get_terms(array(
    'taxonomy' => 'servicne_tag',
    'hide_empty' => true
));
?>

<main id="main" class="site-main servicne-informacije-archive archive-main">
    <?php get_component('structured-data'); ?>
    <div class="container">
        <div class="col-xl-10 mx-auto">
            <header class="archive-header my-5">
                <div class="row">
                    <div class="col-12 col-md-6">
                        <h1 class="archive-title">Servicne informacije</h1>
                    </div>
                    <div class="col-12 col-md-6">
                        <?php 
                        $archive_description = get_option('dev_theme_archive_description', '');
                        if (!empty($archive_description)) : 
                        ?>
                            <div class="archive-description text-right">
                                <?php echo wp_kses_post($archive_description); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </header>



        <!-- Posts Grid -->
        <?php if ($query->have_posts()) : ?>
            <div class="posts-grid row">
                <?php while ($query->have_posts()) : $query->the_post(); ?>
                    <div class="col-12 col-md-6 col-lg-4 mb-4">
                        <article <?php post_class('post-card'); ?> aria-labelledby="post-<?php the_ID(); ?>-title">
                            <?php if (has_post_thumbnail()) : ?>
                                <div class="post-card-image">
                                    <a href="<?php the_permalink(); ?>" tabindex="-1">
                                        <?php
                                        get_component('featured-image', array(
                                            'variant' => 'card',
                                            'size' => 'medium',
                                            'loading' => 'lazy',
                                        ));
                                        ?>
                                        <?php get_component('article-type-overlay'); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <div class="post-card-content d-flex flex-column justify-content-between">
                                <header class="post-card-header">
                                    <div id="post-<?php the_ID(); ?>-title">
                                        <?php get_component('post-title', array('tag' => 'h2', 'link' => true)); ?>
                                    </div>
    
                                    <?php
                                    // Sponsored badge
                                    get_component('sponsored-badge');
                                    ?>
                                
                                    <p class="post-card-excerpt">
                                        <?php echo get_trimmed_excerpt(); ?>
                                    </p>
                                </header>
                                
                                <div class="post-card-meta pt-2 d-flex justify-content-between align-items-center">
                                        <?php get_component('author', array('size' => 'small')); ?>
                                        <?php get_component('post-date'); ?>
                                </div>
                            </div>
                        </article>
                    </div>
                <?php endwhile; ?>
            </div>

            <?php
            // Pagination
            get_component('load-more', array('container' => '.posts-grid'));
            ?>
        <?php else : ?>
            <div class="no-results">
                <p>Nema pronađenih informacija.</p>
            </div>
        <?php endif; ?>

        <?php wp_reset_postdata(); ?>
    </div>
    </div>
</main>

<?php
get_footer();
