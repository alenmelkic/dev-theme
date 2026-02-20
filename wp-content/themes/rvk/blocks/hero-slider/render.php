<?php
/**
 * Hero Slider Block – Server-Side Render (Dual Swiper Sync)
 *
 * @package Dev_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$category_id    = $attributes['categoryId']    ?? 0;
$posts_count    = $attributes['postsCount']    ?? 6;
$autoplay       = $attributes['autoplay']      ?? true;
$autoplay_delay = $attributes['autoplayDelay'] ?? 5000;

if ( empty( $category_id ) ) {
    return;
}

// Fetch posts
$args = [
    'posts_per_page' => (int) $posts_count,
    'cat'            => (int) $category_id,
    'post_status'    => 'publish',
    'orderby'        => 'date',
    'order'          => 'DESC',
];

$query = new WP_Query( $args );
$posts = $query->posts;

if ( empty( $posts ) ) {
    if ( is_admin() ) {
        echo '<div class="hero-slider-placeholder">Nema članaka u odabranoj kategoriji.</div>';
    }
    return;
}

$block_id = 'hero-slider-' . wp_generate_password( 6, false );
$align_class = ! empty( $attributes['align'] ) ? 'align' . $attributes['align'] : 'alignfull';
?>

<section 
    class="hero-slider-block <?php echo esc_attr( $align_class ); ?>" 
    id="<?php echo esc_attr( $block_id ); ?>"
    data-autoplay="<?php echo $autoplay ? 'true' : 'false'; ?>"
    data-autoplay-delay="<?php echo esc_attr( $autoplay_delay ); ?>"
>
    <!-- MAIN HERO SWIPER -->
    <div class="hero-swiper swiper">
        <div class="swiper-wrapper">
            <?php foreach ( $posts as $post ) : 
                $bg_url = get_the_post_thumbnail_url( $post->ID, 'full' );
                $cats = get_the_category( $post->ID );
                $cat = $cats ? $cats[0] : null;
                ?>
                <div class="swiper-slide hero-slide">
                    <div class="hero-slide__bg" <?php echo $bg_url ? 'style="background-image: url(' . esc_url( $bg_url ) . ');"' : 'class="hero-slide__bg--placeholder"'; ?>></div>
                    <div class="hero-slide__overlay"></div>

                    <div class="container hero-slide__container">
                        <div class="hero-slide__content">
                            <?php if ( $cat ) : ?>
                                <a href="<?php echo esc_url( get_category_link( $cat->term_id ) ); ?>" class="hero-slide__category">
                                    <?php echo esc_html( $cat->name ); ?>
                                </a>
                            <?php endif; ?>

                            <h2 class="hero-slide__title">
                                <a href="<?php echo esc_url( get_permalink( $post->ID ) ); ?>" class="hero-slide__title-link">
                                    <?php echo esc_html( get_the_title( $post->ID ) ); ?>
                                </a>
                            </h2>

                            <div class="hero-slide__excerpt">
                                <?php echo wp_trim_words( get_the_excerpt( $post->ID ), 25 ); ?>
                            </div>

                            <a href="<?php echo esc_url( get_permalink( $post->ID ) ); ?>" class="hero-slide__cta">
                                Čitaj više
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- OVERLAY CONTROLS (BOTTOM RIGHT) -->
    <div class="hero-slider-controls">
        <div class="container hero-slider-controls__container">
            <div class="hero-slider-controls__inner">
                
                <!-- CARDS SWIPER -->
                <div class="cards-swiper swiper">
                    <div class="swiper-wrapper">
                        <?php 
                        // Shift posts for cards by 1 (Move first post to end)
                        $card_posts = $posts;
                        if ( count($card_posts) > 1 ) {
                            $first = array_shift($card_posts);
                            $card_posts[] = $first;
                        }

                        foreach ( $card_posts as $post ) : 
                            $img_url = get_the_post_thumbnail_url( $post->ID, 'medium_large' ) ?: '';
                            $cats = get_the_category( $post->ID );
                            $cat_name = $cats ? $cats[0]->name : '';
                            ?>
                            <div class="swiper-slide hero-card-slide">
                                <article class="hero-card">
                                    <a href="<?php echo esc_url( get_permalink( $post->ID ) ); ?>" class="hero-card__inner">
                                        <div class="hero-card__thumb">
                                            <?php if ( $img_url ) : ?>
                                                <img src="<?php echo esc_url( $img_url ); ?>" alt="" class="hero-card__img" loading="lazy">
                                            <?php else : ?>
                                                <div class="hero-card__img-placeholder"></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="hero-card__body">
                                            <?php if ( $cat_name ) : ?>
                                                <span class="hero-card__category"><?php echo esc_html( $cat_name ); ?></span>
                                            <?php endif; ?>
                                            <h4 class="hero-card__title"><?php echo esc_html( get_the_title( $post->ID ) ); ?></h4>
                                        </div>
                                    </a>
                                </article>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- NAVIGATION -->
                <div class="hero-slider-nav">
                    <button class="hero-slider-nav__btn hero-slider-nav__btn--prev" aria-label="Prethodni">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                    </button>
                    <button class="hero-slider-nav__btn hero-slider-nav__btn--next" aria-label="Sljedeći">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- PROGRESS LINE -->
    <div class="hero-slider-progress">
        <div class="hero-slider-progress__bar"></div>
    </div>
</section>

<?php wp_reset_postdata(); ?>
