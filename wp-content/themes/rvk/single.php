<?php
/**
 * Template for single post
 */

get_header();

while (have_posts()) : the_post();
?>

<main id="main" class="site-main single-post-page">
    <div class="container">
        <article <?php post_class(); ?>>
            <header class="post-header col-xl-10 mx-auto">
                <?php
                // Sponsored disclaimer - top
                get_component('sponsored-disclaimer', ['position' => 'top']);

                // Article type badge
                get_component('article-type-badge', ['variant' => 'block']);
                ?>

                <?php get_component('post-title', array('tag' => 'h1', 'link' => false)); ?>

                <?php
                // AdSense Position A: Below Title, Above Featured Image
                if (function_exists('rvk_display_adsense_ad')) {
                    rvk_display_adsense_ad('position_a');
                }
                ?>

                <?php if (has_excerpt()) : ?>
                    <div class="post-excerpt">
                        <?php the_excerpt(); ?>
                    </div>
                <?php endif; ?>
                
                <div class="post-reading-time">
                    <?php echo esc_html(get_reading_time()); ?>
                </div>
                
                <?php get_component('featured-image', array('variant' => 'post', 'size' => 'large', 'loading' => 'eager')); ?>
                
                <div class="post-meta">
                    <?php get_component('author', array('size' => 'medium')); ?>
                    <?php get_component('post-date'); ?>
                    <span class="reading-time"><?php echo esc_html(get_reading_time()); ?></span>
                    
                    <?php
                    // Categories
                    $categories = get_the_category();
                    if (!empty($categories)) :
                    ?>
                        <div class="post-categories">
                            <span class="meta-label"><?php esc_html_e('Categories:', 'dev-theme'); ?></span>
                            <?php
                            foreach ($categories as $category) {
                                echo '<a href="' . esc_url(get_category_link($category->term_id)) . '" class="category-link">' . esc_html($category->name) . '</a>';
                            }
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
            </header>

            <?php
            // AdSense Position B: Above Article Content
            if (function_exists('rvk_display_adsense_ad')) {
                rvk_display_adsense_ad('position_b');
            }
            ?>

            <div class="post-content col-xl-10 mx-auto">
                <?php the_content(); ?>
                
                <?php
                // Page links for paginated posts
                wp_link_pages(array(
                    'before' => '<div class="page-links">' . esc_html__('Pages:', 'dev-theme'),
                    'after'  => '</div>',
                ));
                ?>
            </div>

            <?php
            // AdSense Position C: Below Article Content
            if (function_exists('rvk_display_adsense_ad')) {
                rvk_display_adsense_ad('position_c');
            }
            ?>

            <?php
            // Social share buttons
            get_template_part('components/social-share-buttons/social-share-buttons', null, array(
                'platforms' => array('facebook', 'twitter', 'linkedin', 'whatsapp', 'email'),
                'include_hashtags' => true,
                'style' => 'default',
                'size' => 'medium'
            ));
            ?>

            <footer class="post-footer">
                <?php
                // Tags
                $tags = get_the_tags();
                if (!empty($tags)) :
                ?>
                    <div class="post-tags">
                        <span class="tags-label"><?php esc_html_e('Tags:', 'dev-theme'); ?></span>
                        <?php
                        foreach ($tags as $tag) {
                            echo '<a href="' . esc_url(get_tag_link($tag->term_id)) . '" class="tag-link">' . esc_html($tag->name) . '</a>';
                        }
                        ?>
                    </div>
                <?php endif; ?>
                
                <?php
                // Author bio
                $author_bio = get_the_author_meta('description');
                if (!empty($author_bio)) :
                ?>
                    <div class="author-bio">
                        <h3><?php esc_html_e('About the Author', 'dev-theme'); ?></h3>
                        <div class="author-bio-content">
                            <?php echo get_avatar(get_the_author_meta('ID'), 80); ?>
                            <div class="author-bio-text">
                                <h4><?php echo esc_html(get_the_author()); ?></h4>
                                <p><?php echo wp_kses_post($author_bio); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </footer>

            <?php
            // Sponsored disclaimer - bottom
            get_component('sponsored-disclaimer', ['position' => 'bottom']);
            ?>

            <?php
            // Post navigation
            the_post_navigation(array(
                'prev_text' => '<span class="nav-subtitle">' . esc_html__('Previous:', 'dev-theme') . '</span> <span class="nav-title">%title</span>',
                'next_text' => '<span class="nav-subtitle">' . esc_html__('Next:', 'dev-theme') . '</span> <span class="nav-title">%title</span>',
            ));
            ?>
        </article>
        
        <?php
        // Display small banners in sidebar
        if (function_exists('rvk_display_small_banners')) {
            echo '<aside class="post-sidebar col-xl-10 mx-auto mt-4">';
            rvk_display_small_banners();
            echo '</aside>';
        }
        ?>
    </div>
</main>

<?php
endwhile;

get_footer();
