<?php
/**
 * Template for Obavijesti o Smrti archive
 */

get_header();

// Get filter parameters from URL
$search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
$tag_id = isset($_GET['tag']) ? intval($_GET['tag']) : 0;
$paged = get_query_var('paged') ? get_query_var('paged') : 1;

// Build query args
$args = array(
    'post_type' => 'obavijesti-o-smrti',
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
            'taxonomy' => 'obavijest_tag',
            'field' => 'term_id',
            'terms' => $tag_id
        )
    );
}

// Execute query
$query = new WP_Query($args);

// Get all tags for filter
$tags = get_terms(array(
    'taxonomy' => 'obavijest_tag',
    'hide_empty' => true
));
?>

<main id="main" class="site-main obavijesti-o-smrti-archive">
    <?php get_component('structured-data'); ?>
    <div class="container">
        <header class="page-header">
            <h1 class="page-title">Obavijesti o Smrti</h1>
            <?php 
            $archive_description = get_option('dev_theme_archive_description', '');
            if (!empty($archive_description)) : 
            ?>
                <p class="archive-description"><?php echo wp_kses_post($archive_description); ?></p>
            <?php endif; ?>
        </header>



        <!-- Posts Grid -->
        <?php if ($query->have_posts()) : ?>
            <div class="obavijesti-o-smrti-grid">
                <?php while ($query->have_posts()) : $query->the_post(); ?>
                    <div class="col-12 col-md-6 col-lg-4">
                        <article class="obavijest-o-smrti-card">
                            <?php get_component('featured-image', array('variant' => 'card')); ?>
                            
                            <div class="card-content">
                                <?php get_component('post-title', array('tag' => 'h2', 'link' => true)); ?>
                                
                                <div class="card-excerpt">
                                    <?php echo get_trimmed_excerpt(); ?>
                                </div>
                                
                                <div class="card-meta">
                                    <?php get_component('author', array('size' => 'small')); ?>
                                    <?php get_component('post-date'); ?>
                                </div>
                                
                                <?php get_component('post-terms', array('taxonomy' => 'obavijest_tag')); ?>
                            </div>
                        </article>
                    </div>
                <?php endwhile; ?>
            </div>

            <?php
            // Pagination
            get_component('load-more', array('container' => '.obavijesti-o-smrti-grid'));
            ?>
        <?php else : ?>
            <div class="no-results">
                <p>Nema pronađenih obavijesti.</p>
            </div>
        <?php endif; ?>

        <?php wp_reset_postdata(); ?>
    </div>
</main>

<?php
get_footer();
