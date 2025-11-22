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
            <p class="archive-description">
                Važne informacije koje su dostupne 15 dana od objave. Nakon toga se automatski uklanjaju.
            </p>
        </header>

        <!-- Filters -->
        <form method="get" class="servicne-informacije-filters" id="filter-form">
            <div class="filter-group">
                <input 
                    type="search" 
                    name="s" 
                    value="<?php echo esc_attr($search); ?>" 
                    placeholder="Pretraži informacije..."
                    class="search-input"
                >
            </div>

            <?php if (!empty($tags)) : ?>
                <div class="filter-group">
                    <label for="tag-filter">Oznaka:</label>
                    <select name="tag" id="tag-filter">
                        <option value="">Sve oznake</option>
                        <?php foreach ($tags as $tag) : ?>
                            <option value="<?php echo esc_attr($tag->term_id); ?>" <?php selected($tag_id, $tag->term_id); ?>>
                                <?php echo esc_html($tag->name); ?> (<?php echo $tag->count; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <button type="submit" class="filter-submit">Filtriraj</button>
        </form>

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
