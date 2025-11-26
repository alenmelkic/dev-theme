<?php
/**
 * Template for category archive
 */

get_header();
?>

<main id="main" class="site-main category-archive">
    <div class="container">
        <header class="archive-header">
            <h1 class="archive-title">
                <?php
                printf(
                    esc_html__('Category: %s', 'dev-theme'),
                    '<span>' . single_cat_title('', false) . '</span>'
                );
                ?>
            </h1>
            
            <?php
            // Category description
            $category_description = category_description();
            if (!empty($category_description)) :
            ?>
                <div class="archive-description">
                    <?php echo wp_kses_post($category_description); ?>
                </div>
            <?php endif; ?>
        </header>

        <?php if (have_posts()) : ?>
            <div class="posts-grid">
                <?php
                while (have_posts()) : the_post();
                ?>
                    <article <?php post_class('post-card'); ?>>
                        <?php if (has_post_thumbnail()) : ?>
                            <div class="post-card-image">
                                <a href="<?php the_permalink(); ?>">
                                    <?php 
                                    get_component('featured-image', array(
                                        'variant' => 'card',
                                        'size' => 'medium',
                                        'loading' => 'lazy'
                                    )); 
                                    ?>
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <div class="post-card-content">
                            <header class="post-card-header">
                                <?php get_component('post-title', array('tag' => 'h2', 'link' => true)); ?>
                                
                                <div class="post-card-meta">
                                    <?php get_component('post-date'); ?>
                                    <?php get_component('author', array('size' => 'small')); ?>
                                </div>
                            </header>
                            
                            <div class="post-card-excerpt">
                                <?php the_excerpt(); ?>
                            </div>
                            
                            <a href="<?php the_permalink(); ?>" class="read-more">
                                <?php esc_html_e('Read More', 'dev-theme'); ?>
                                <span aria-hidden="true">→</span>
                            </a>
                        </div>
                    </article>
                <?php
                endwhile;
                ?>
            </div>

            <?php
            // Pagination
            the_posts_pagination(array(
                'mid_size'  => 2,
                'prev_text' => __('← Previous', 'dev-theme'),
                'next_text' => __('Next →', 'dev-theme'),
            ));
            ?>

        <?php else : ?>
            <div class="no-posts">
                <p><?php esc_html_e('No posts found in this category.', 'dev-theme'); ?></p>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php
get_footer();
