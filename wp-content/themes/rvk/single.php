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
            <header class="post-header col-xl-9 mx-auto">
                <?php
                // Sponsored disclaimer - top
                get_component('sponsored-disclaimer', ['position' => 'top']);

                // Article type badge
                get_component('article-type-badge', ['variant' => 'block']);
                ?>

                <div class="post-content col-xl-9 mx-auto">

                <?php get_component('post-title', array('tag' => 'h1', 'link' => false)); ?>         
                
                <?php if (has_excerpt()) : ?>
                    <div class="post-excerpt">
                        <?php the_excerpt(); ?>
                    </div>
                <?php endif; ?>

                <div class="post-meta py-3 d-flex align-items-md-center justify-content-between gap-3 gap-md-4">
                    <?php get_component('author', array('size' => 'medium')); ?>

                    <div class="post-info d-flex flex-column gap-1">
                    <?php get_component('post-date'); ?>
                    <!-- <span class="reading-time"><?php echo esc_html(get_reading_time()); ?></span> -->
                    
                    <?php
                    // Categories
                    $categories = get_the_category();
                    if (!empty($categories)) :
                    ?>
                        <div class="post-categories">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9.776c.112-.017.227-.026.344-.026h15.812c.117 0 .232.009.344.026m-16.5 0a2.25 2.25 0 0 0-1.883 2.542l.857 6a2.25 2.25 0 0 0 2.227 1.932H19.05a2.25 2.25 0 0 0 2.227-1.932l.857-6a2.25 2.25 0 0 0-1.883-2.542m-16.5 0V6A2.25 2.25 0 0 1 6 3.75h3.879a1.5 1.5 0 0 1 1.06.44l2.122 2.12a1.5 1.5 0 0 0 1.06.44H18A2.25 2.25 0 0 1 20.25 9v.776" />
                            </svg>

                            <?php
                            foreach ($categories as $category) {
                                echo '<a href="' . esc_url(get_category_link($category->term_id)) . '" class="category-link">' . esc_html($category->name) . '</a>';
                            }
                            ?>
                        </div>
                    <?php endif; ?>
                    </div>
                </div>  
                </div>

                <?php
                // AdSense Position A: Below Title, Above Featured Image
                if (function_exists('rvk_display_adsense_ad')) {
                    rvk_display_adsense_ad('position_a');
                }
                ?>
                
                <?php get_component('featured-image', array('variant' => 'post', 'size' => 'large', 'loading' => 'eager', 'class' => 'rounded-4 mb-4 mb-lg-5')); ?>
                
                
            </header>

            <?php
            // AdSense Position B: Above Article Content
            if (function_exists('rvk_display_adsense_ad')) {
                rvk_display_adsense_ad('position_b');
            }
            ?>

            <div class="post-content col-xl-7 mx-auto">
                <?php the_content(); ?>
            </div>

            <?php
            // AdSense Position C: Below Article Content
            if (function_exists('rvk_display_adsense_ad')) {
                rvk_display_adsense_ad('position_c');
            }
            ?>

            <div class="col-xl-9 mx-auto mb-5">

                <?php
                // Sponsored disclaimer - bottom
                get_component('sponsored-disclaimer', ['position' => 'bottom']);
                ?>

                <?php
                // Social share buttons
                get_component('social-share-buttons', array(
                    'platforms' => array('facebook', 'twitter', 'linkedin', 'whatsapp', 'email'),
                    'include_hashtags' => true,
                    'style' => 'default',
                    'size' => 'medium'
                ));
                ?>

            </div>

            <footer class="post-footer">
                
            </footer>

            
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
