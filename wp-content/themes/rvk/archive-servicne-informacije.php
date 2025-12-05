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

<main id="main" class="site-main servicne-informacije-archive">
    <div class="container">
        <header class="page-header">
            <h1 class="page-title">Servicne informacije</h1>
            <?php 
            $archive_description = get_option('dev_theme_archive_description', '');
            if (!empty($archive_description)) : 
            ?>
                <p class="archive-description"><?php echo wp_kses_post($archive_description); ?></p>
            <?php endif; ?>
        </header>



        <!-- Posts Grid -->
        <?php if ($query->have_posts()) : ?>
            <div class="servicne-informacije-grid">
                <?php while ($query->have_posts()) : $query->the_post(); ?>
                    <article class="servicna-informacija-card">
                        <?php get_component('featured-image', array('variant' => 'card')); ?>
                        
                        <div class="card-content">
                            <?php get_component('post-title', array('tag' => 'h2', 'link' => true)); ?>
                            
                            <?php if (has_excerpt()) : ?>
                                <div class="card-excerpt">
                                    <?php the_excerpt(); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="card-meta">
                                <?php get_component('author', array('size' => 'small')); ?>
                                <?php get_component('post-date'); ?>
                            </div>
                            
                            <?php get_component('post-terms', array('taxonomy' => 'servicne_tag')); ?>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>

            <!-- Pagination -->
            <div class="pagination">
                <?php
                echo paginate_links(array(
                    'total' => $query->max_num_pages,
                    'current' => $paged,
                    'prev_text' => '&laquo; Prethodna',
                    'next_text' => 'Sljedeća &raquo;',
                    'add_args' => array(
                        's' => $search,
                        'tag' => $tag_id
                    )
                ));
                ?>
            </div>
        <?php else : ?>
            <div class="no-results">
                <p>Nema pronađenih informacija.</p>
            </div>
        <?php endif; ?>

        <?php wp_reset_postdata(); ?>
    </div>
</main>

<?php
get_footer();
