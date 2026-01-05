<?php
/**
 * Component: Load More
 * Renders the load more button and container for AJAX pagination
 */

if (!defined('ABSPATH')) {
    exit;
}

global $wp_query;

$max_pages = $wp_query->max_num_pages;
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$next_page = $paged + 1;

if ($max_pages <= $paged) {
    return;
}

$next_page_url = next_posts($max_pages, false);
?>

<div class="load-more-container text-center my-5">
    <button id="load-more-btn" 
            class="btn btn-primary load-more-btn" 
            data-next-page="<?php echo esc_url($next_page_url); ?>"
            data-max-pages="<?php echo $max_pages; ?>"
            data-current-page="<?php echo $paged; ?>"
            data-container="<?php echo isset($args['container']) ? esc_attr($args['container']) : '.posts-grid'; ?>">
        <span class="btn-text"><?php _e('Učitaj više', 'dev-theme'); ?></span>
        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
    </button>
</div>
