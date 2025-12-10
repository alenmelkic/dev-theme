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

?>

<div <?php echo get_block_wrapper_attributes(['class' => 'category-articles-block container my-5']); ?>>
    <div class="row">
        
        <!-- Featured Post (Left Column) -->
        <div class="col-lg-8 mb-4 mb-lg-0">
            <div class="card border-0 text-white h-100 position-relative overflow-hidden rounded-4">
                <?php 
                $featured_img_url = get_the_post_thumbnail_url($featured_post->ID, 'large'); 
                if ($featured_img_url) : ?>
                    <div class="card-img-overlay p-0" style="background-image: url('<?php echo esc_url($featured_img_url); ?>'); background-size: cover; background-position: center;">
                        <div class="h-100 w-100" style="background: rgba(0,0,0,0.4);"></div> <!-- Dark Overlay -->
                    </div>
                <?php endif; ?>
                
                <div class="card-img-overlay d-flex flex-column justify-content-between p-4 p-md-5">
                    <div class="align-self-start">
                        <span class="badge bg-light text-dark rounded-pill px-3 py-2 text-uppercase fw-bold"><?php echo esc_html($category_name); ?></span>
                    </div>
                    <div>
                        <h2 class="card-title display-6 fw-bold mb-3">
                            <a href="<?php echo get_permalink($featured_post->ID); ?>" class="text-white text-decoration-none stretched-link">
                                <?php echo get_the_title($featured_post->ID); ?>
                            </a>
                        </h2>
                        
                        <!-- Author (Name + Image) -->
                        <div class="d-flex align-items-center mt-3">
                            <div class="me-2 user-select-none">
                                <?php echo get_avatar($featured_post->post_author, 40, '', '', ['class' => 'rounded-circle']); ?>
                            </div>
                            <div>
                                <span class="fw-bold d-block text-white" style="font-size: 0.9rem;">
                                    <?php 
                                        $fname = get_the_author_meta('first_name', $featured_post->post_author);
                                        $lname = get_the_author_meta('last_name', $featured_post->post_author);
                                        echo esc_html($fname . ' ' . $lname); 
                                    ?>
                                </span>
                                <span class="d-block text-white-50" style="font-size: 0.8rem;">
                                    Novinar
                                </span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- List Posts (Right Column) -->
        <div class="col-lg-4">
            <div class="d-flex flex-column gap-3">
                
                <?php foreach ($list_posts as $post) : 
                    $img_url = get_the_post_thumbnail_url($post->ID, 'medium');
                ?>
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            
                            <!-- Thumbnail -->
                            <?php if ($img_url): ?>
                            <div class="flex-shrink-0 me-3">
                                <img src="<?php echo esc_url($img_url); ?>" alt="<?php echo esc_attr(get_the_title($post->ID)); ?>" class="rounded-3" style="width: 100px; height: 100px; object-fit: cover;">
                            </div>
                            <?php endif; ?>

                            <!-- Content -->
                            <div class="flex-grow-1">
                                <span class="badge bg-warning text-dark bg-opacity-25 text-warning-emphasis rounded-pill px-2 py-1 mb-2" style="font-size: 0.7rem; font-weight: 700; letter-spacing: 0.5px;">
                                    <?php echo esc_html(strtoupper($category_name)); ?>
                                </span>
                                <h6 class="card-title mb-2" style="line-height: 1.4;">
                                    <a href="<?php echo get_permalink($post->ID); ?>" class="text-dark text-decoration-none hover-primary">
                                        <?php echo wp_trim_words(get_the_title($post->ID), 10); ?>
                                    </a>
                                </h6>
                                
                                <!-- Author (Name + Image) Small -->
                                <div class="d-flex align-items-center mt-2">
                                     <div class="me-2">
                                        <?php echo get_avatar($post->post_author, 24, '', '', ['class' => 'rounded-circle']); ?>
                                    </div>
                                    <div>
                                        <span class="fw-bold text-dark d-block" style="font-size: 0.75rem;">
                                            <?php 
                                                $fname = get_the_author_meta('first_name', $post->post_author);
                                                $lname = get_the_author_meta('last_name', $post->post_author);
                                                echo esc_html($fname . ' ' . $lname); 
                                            ?>
                                        </span>
                                         <span class="d-block text-muted" style="font-size: 0.65rem; line-height: 1;">
                                            Novinar
                                        </span>
                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

            </div>
        </div>

    </div>
</div>
<?php wp_reset_postdata(); ?>
