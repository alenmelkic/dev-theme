<?php
/**
 * Category Articles Block Render Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// RESTRICTION: Show only on Pages
if ( ! is_page() && ! is_admin() ) {
    return;
}

$category_id = $attributes['categoryId'] ?? 0;
$block_title = $attributes['blockTitle'] ?? 'Kategorija';

if ( empty( $category_id ) ) {
    return;
}

// Fetch 4 latest posts from the category
$args = [
    'posts_per_page' => 4,
    'cat'            => $category_id,
    'post_status'    => 'publish',
    'orderby'        => 'date',
    'order'          => 'DESC',
];

$query = new WP_Query( $args );

if ( ! $query->have_posts() ) {
    return;
}

$posts = $query->posts;
$featured_post = $posts[0];
$list_posts = array_slice($posts, 1);
$category_obj = get_category($category_id);
$category_name = $category_obj ? $category_obj->name : '';
$category_link = get_category_link($category_id);

?>

<section <?php echo get_block_wrapper_attributes(['class' => 'category-articles-block my-5']); ?>>
    <div class="container">
        <div class="col-xl-10 mx-auto">
        <div class="row">
        
            <!-- Featured Post (Left Column) -->
            <div class="col-lg-8 mb-4 mb-lg-0">
                <article class="card border-0 text-white h-100 position-relative overflow-hidden rounded-4 shadow-sm featured-article">
                    <?php 
                    $featured_img_url = get_the_post_thumbnail_url($featured_post->ID, 'large'); 
                    if ($featured_img_url) : ?>
                    <div class="card-img p-0 h-100 w-100" style="background-image: url('<?php echo esc_url($featured_img_url); ?>'); background-size: cover; background-position: center;">
                        <div class="h-100 w-100 overlay-subtle"></div>
                    </div>
                <?php endif; ?>
                
                <div class="card-img-overlay d-flex flex-column position-absolute bottom-0 w-100 justify-content-between p-4 p-md-4">
                    
                    
                    <div class="content-box p-4 p-md-4 rounded-4 shadow-sm">
                        <h1 class="card-title h1 fw-bold mb-0">
                            <a href="<?php echo get_permalink($featured_post->ID); ?>" class="text-dark text-decoration-none stretched-link focus-ring focus-ring-dark">
                                <?php echo esc_html(get_the_title($featured_post->ID)); ?>
                            </a>
                        </h1>
                        
                    </div>
                </div>
                </article>
            </div>

            <!-- List Posts (Right Column) -->
            <div class="col-lg-4">
                <div class="d-flex flex-column h-100 justify-content-between">
                    
                    <?php foreach ($list_posts as $post) : 
                        $img_url = get_the_post_thumbnail_url($post->ID, 'medium');
                    ?>
                    <article class="card border-0 position-relative list-article">
                        <div class="card-body shadow-sm rounded-4 overflow-hidden p-3">
                            <div class="d-flex align-items-center">
                                
                                <!-- Thumbnail -->
                                <?php if ($img_url): ?>
                                <div class="flex-shrink-0 me-3 thumbnail-wrap">
                                    <img src="<?php echo esc_url($img_url); ?>" alt="<?php echo esc_attr(get_the_title($post->ID)); ?>" class="rounded-3 shadow-sm thumbnail">
                                </div>
                                <?php endif; ?>

                                <!-- Content -->
                                <div class="d-flex flex-column">
                                   
                                    <h3 class="article-title mb-2">
                                        <a href="<?php echo get_permalink($post->ID); ?>" class="text-dark text-decoration-none stretched-link focus-ring focus-ring-dark"><?php echo esc_html(wp_trim_words(get_the_title($post->ID), 10)); ?></a>
                                    </h3>
                                    
                                    <!-- Author (Name + Image) Small -->
                                    <div class="d-flex align-items-center mt-2 z-index-top author-info">
                                        <div class="me-2 avatar-img">
                                            <?php echo get_avatar($post->post_author, 24); ?>
                                        </div>
                                        <div>
                                            <span class="fw-bold text-dark d-block author-name">
                                                <?php 
                                                    $fname = get_the_author_meta('first_name', $post->post_author);
                                                    $lname = get_the_author_meta('last_name', $post->post_author);
                                                    echo esc_html($fname . ' ' . $lname); 
                                                ?>
                                            </span>
                                            <span class="d-block text-muted author-role">
                                                Novinar
                                            </span>
                                        </div>
                                    </div>

                                </div>

                            </div>
                        </div>
                    </article>
                    <?php endforeach; ?>

                </div>
            </div>

        </div>
        </div>
    </div>

</section>

<?php wp_reset_postdata(); ?>
