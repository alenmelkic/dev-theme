<?php
/**
 * Post Listings Block Render Template
 *
 * Displays filtered posts with multiple layout options
 * Supports: Categories, Tags, All Taxonomies, Article Types
 * Layouts: Cards 1, Cards 2 (Featured), List
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get block attributes
$title = $attributes['title'] ?? '';
$subtitle = $attributes['subtitle'] ?? '';
$category_ids = $attributes['categoryIds'] ?? [];
$tag_ids = $attributes['tagIds'] ?? [];
$taxonomy_filters = $attributes['taxonomyFilters'] ?? [];
$article_type = $attributes['articleType'] ?? 'all';
$layout = $attributes['layout'] ?? 'cards-1';
$posts_per_page = $attributes['postsPerPage'] ?? 6;
$orderby = $attributes['orderBy'] ?? 'date';
$order = $attributes['order'] ?? 'DESC';

// Conditionally enqueue layout-specific CSS
$base_handle = 'post-listings-base';
$layout_handle = 'post-listings-' . $layout;

// Enqueue base styles (always needed)
if (!wp_style_is($base_handle, 'enqueued')) {
    wp_enqueue_style(
        $base_handle,
        get_template_directory_uri() . '/dist/css/components/post-listings-base.min.css',
        [],
        filemtime(get_template_directory() . '/dist/css/components/post-listings-base.min.css')
    );
}

// Enqueue layout-specific styles
if (!wp_style_is($layout_handle, 'enqueued')) {
    wp_enqueue_style(
        $layout_handle,
        get_template_directory_uri() . '/dist/css/components/post-listings-' . $layout . '.min.css',
        [$base_handle],
        filemtime(get_template_directory() . '/dist/css/components/post-listings-' . $layout . '.min.css')
    );
}

// Build WP_Query arguments
$args = [
    'post_type' => 'post',
    'posts_per_page' => $posts_per_page,
    'post_status' => 'publish',
    'orderby' => $orderby,
    'order' => $order,
    'ignore_sticky_posts' => true,
];

// Build tax_query
$tax_query = ['relation' => 'AND'];

// Add category filter
if (!empty($category_ids)) {
    $tax_query[] = [
        'taxonomy' => 'category',
        'field' => 'term_id',
        'terms' => $category_ids,
        'operator' => 'IN',
    ];
}

// Add tag filter
if (!empty($tag_ids)) {
    $tax_query[] = [
        'taxonomy' => 'post_tag',
        'field' => 'term_id',
        'terms' => $tag_ids,
        'operator' => 'IN',
    ];
}

// Add article type filter (if not 'all')
if ($article_type !== 'all') {
    $tax_query[] = [
        'taxonomy' => 'article-type',
        'field' => 'slug',
        'terms' => $article_type,
    ];
}

// Add custom taxonomy filters
if (!empty($taxonomy_filters) && is_array($taxonomy_filters)) {
    foreach ($taxonomy_filters as $taxonomy_slug => $term_ids) {
        if (!empty($term_ids) && is_array($term_ids)) {
            $tax_query[] = [
                'taxonomy' => $taxonomy_slug,
                'field' => 'term_id',
                'terms' => $term_ids,
                'operator' => 'IN',
            ];
        }
    }
}

// Only add tax_query if there are actual queries
if (count($tax_query) > 1) {
    $args['tax_query'] = $tax_query;
}

// Execute query
$query = new WP_Query($args);

// Debug output
$found_posts = $query->found_posts;
$post_count = $query->post_count;

if (!$query->have_posts()) {
    // Show helpful message
    ?>
    <div class="post-listings-no-results p-4 text-center">
        <p class="mb-2">No posts found matching the selected filters.</p>
        <small class="text-muted">Total posts checked: <?php echo $found_posts; ?></small>
    </div>
    <?php
    return;
}

// Determine layout classes
$layout_classes = '';
$col_classes = '';

switch ($layout) {
    case 'cards-1':
        $layout_classes = 'post-listings-cards-1';
        $col_classes = 'col-12 col-md-6 col-lg-4';
        break;
    case 'cards-2':
        $layout_classes = 'post-listings-cards-2';
        $col_classes = 'col-12 col-md-6';
        break;
    case 'list':
        $layout_classes = 'post-listings-list';
        $col_classes = 'col-12';
        break;
}

?>

<section <?php echo get_block_wrapper_attributes(['class' => "post-listings-block {$layout_classes} my-5"]); ?>>
    <div class="container">
        <?php if (!empty($title) || !empty($subtitle)) : ?>
        <div class="post-listings-header mb-4 text-center">
            <?php if (!empty($title)) : ?>
                <h2 class="post-listings-title h3 fw-bold mb-2"><?php echo esc_html($title); ?></h2>
            <?php endif; ?>
            <?php if (!empty($subtitle)) : ?>
                <p class="post-listings-subtitle text-muted mb-0"><?php echo esc_html($subtitle); ?></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="row g-4">
            <?php
            while ($query->have_posts()) :
                $query->the_post();
                $post_id = get_the_ID();
                $article_type_slug = get_article_type_slug($post_id);

                // Determine if icon should be shown
                // Show icon for video, audio, galerija
                // Hide icon for standard OR if layout is cards-2
                $show_icon = ($layout !== 'cards-2') &&
                             in_array($article_type_slug, ['video', 'audio', 'galerija']);
            ?>

            <div class="<?php echo esc_attr($col_classes); ?>">
                <?php if ($layout === 'cards-1') : ?>
                    <!-- Cards Layout 1: 3-column grid -->
                    <article class="post-card h-100 position-relative">
                        <!-- Featured Image with Article Type Overlay -->
                        <div class="post-card-image position-relative">
                            <?php
                            get_component('featured-image', array(
                                'variant' => 'card',
                                'size' => 'medium',
                                'loading' => 'lazy',
                            ));
                            ?>

                            <?php if ($show_icon) : ?>
                                <?php get_component('article-type-overlay'); ?>
                            <?php endif; ?>
                        </div>

                        <!-- Card Content -->
                        <div class="post-card-content">
                            <div class="post-card-header">
                                <?php get_component('post-title', array('tag' => 'h3', 'link' => true)); ?>
                            </div>

                            <!-- Excerpt -->
                            <div class="post-excerpt">
                                <p><?php echo wp_trim_words(get_the_excerpt(), 15, '...'); ?></p>
                            </div>

                            <!-- Meta -->
                            <div class="post-card-meta d-flex justify-content-between align-items-center mt-3">
                                <?php get_component('author', array('size' => 'small')); ?>
                                <?php get_component('post-date'); ?>
                            </div>
                        </div>
                    </article>

                <?php elseif ($layout === 'cards-2') : ?>
                    <!-- Cards Layout 2: Featured block style (NO ICONS) -->
                    <article class="post-card-featured h-100 position-relative overflow-hidden rounded-4 shadow-sm">
                        <?php
                        $featured_img_url = get_the_post_thumbnail_url($post_id, 'large');
                        if ($featured_img_url) : ?>
                        <div class="card-img h-100 w-100" style="background-image: url('<?php echo esc_url($featured_img_url); ?>'); background-size: cover; background-position: center;">
                            <div class="h-100 w-100 overlay-subtle"></div>
                        </div>
                        <?php endif; ?>

                        <div class="card-img-overlay d-flex flex-column position-absolute bottom-0 w-100 justify-content-end p-4">
                            <div class="content-box p-4 rounded-4 shadow-sm">
                                <h3 class="h4 fw-bold mb-3">
                                    <a href="<?php the_permalink(); ?>" class="text-dark text-decoration-none stretched-link" aria-label="<?php echo esc_attr('Read more about ' . get_the_title()); ?>">
                                        <?php the_title(); ?>
                                    </a>
                                </h3>

                                <!-- Author -->
                                <div class="d-flex align-items-center mt-3">
                                    <?php get_component('author', array('size' => 'small')); ?>
                                </div>
                            </div>
                        </div>
                    </article>

                <?php else : ?>
                    <!-- List Layout: Horizontal rows -->
                    <article class="post-list-item d-flex align-items-start gap-3 p-3 rounded-3 shadow-sm bg-white position-relative">
                        <!-- Thumbnail -->
                        <?php if (has_post_thumbnail()) : ?>
                        <div class="post-list-thumbnail flex-shrink-0 position-relative">
                            <a href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr('Read more about ' . get_the_title()); ?>">
                                <?php the_post_thumbnail('medium', ['class' => 'rounded-3', 'loading' => 'lazy']); ?>
                            </a>

                            <?php if ($show_icon) : ?>
                                <?php get_component('article-type-overlay'); ?>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <!-- Content -->
                        <div class="post-list-content flex-grow-1">
                            <?php get_component('post-title', array('tag' => 'h3', 'link' => true, 'class' => 'h5 mb-2')); ?>

                            <p class="post-excerpt mb-2"><?php echo wp_trim_words(get_the_excerpt(), 20, '...'); ?></p>

                            <!-- Meta -->
                            <div class="d-flex gap-3 text-muted small">
                                <?php get_component('post-date'); ?>
                            </div>
                        </div>
                    </article>
                <?php endif; ?>
            </div>

            <?php endwhile; ?>
        </div>
    </div>
</section>

<?php wp_reset_postdata(); ?>
