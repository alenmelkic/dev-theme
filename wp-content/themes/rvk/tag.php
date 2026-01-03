<?php
/**
 * Template for tag archive
 */

get_header();
?>

<main id="main" class="site-main tag-archive archive-main">
    <?php get_component('structured-data'); ?>
    <div class="container">
        <div class="col-xl-10 mx-auto">
            <header class="archive-header my-5">
                <div class="row">
                    <div class="col-12 col-md-6">
                        <h1 class="archive-title">
                            <?php
                            printf(
                                esc_html__('Tag: %s', 'dev-theme'),
                                '<span>' . single_tag_title('', false) . '</span>'
                            );
                            ?>
                        </h1>
                    </div>
                    <div class="col-12 col-md-6">
                        <?php
                        // Tag description
                        $tag_description = tag_description();
                        if (!empty($tag_description)) :
                        ?>
                            <div class="archive-description text-right">
                                <?php echo wp_kses_post($tag_description); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </header>

        <?php if (have_posts()) : ?>
            <div class="posts-grid row">
                <?php
                while (have_posts()) : the_post();
                ?>
                <div class="col-12 col-md-6 col-lg-4 mb-4">
                    <article <?php post_class('post-card'); ?> aria-labelledby="post-<?php the_ID(); ?>-title">
                        <?php if (has_post_thumbnail()) : ?>
                            <div class="post-card-image">
                                <a href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
                                    <?php
                                    get_component('featured-image', array(
                                        'variant' => 'card',
                                        'size' => 'medium',
                                        'loading' => 'lazy'
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
 
                                <div class="post-card-excerpt">
                                    <?php echo get_trimmed_excerpt(); ?>
                                </div>
                            </header>
                            
                            <div class="post-card-meta mt-3 d-flex justify-content-between align-items-center">                                    
                                    <?php get_component('author', array('size' => 'small')); ?>
                                    <?php get_component('post-date'); ?>
                            </div>
                        </div>
                    </article>
                </div>
                <?php
                endwhile;
                ?>
            </div>

            <?php
            // Pagination
            get_component('load-more');
            ?>

        <?php else : ?>
            <div class="no-posts">
                <p><?php esc_html_e('No posts found with this tag.', 'dev-theme'); ?></p>
            </div>
        <?php endif; ?>
    </div>
    </div>
</main>

<?php
get_footer();
