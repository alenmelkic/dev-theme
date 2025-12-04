<?php
/**
 * Template for single Servicna informacija
 */

get_header();

while (have_posts()) : the_post();
?>

<main id="main" class="site-main servicna-informacija-single-page">
    <div class="container">
        <div class="col-xl-10 mx-auto">
            <article <?php post_class(); ?>>
                <header class="post-header">
                    <?php get_component('post-title', array('tag' => 'h1', 'link' => false)); ?>
                    
                    <?php get_component('featured-image', array('variant' => 'post', 'size' => 'large', 'loading' => 'eager')); ?>
                    
                    <div class="post-meta">
                        <?php get_component('author', array('size' => 'medium')); ?>
                        <?php get_component('post-date'); ?>
                    </div>
                </header>

                <div class="post-content">
                    <?php the_content(); ?>
                </div>

                <?php get_component('post-terms', array('taxonomy' => 'servicne_tag')); ?>
            </article>
        </div>
    </div>
</main>

<?php
endwhile;

get_footer();
