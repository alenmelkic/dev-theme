<?php
/**
 * Post Terms Component
 * 
 * @param int $post_id Post ID (default: current post)
 * @param string $taxonomy Taxonomy name (default: 'post_tag')
 * @param string $separator Separator between terms (default: ', ')
 * @param bool $link Whether to link terms (default: true)
 */

$post_id = $post_id ?? get_the_ID();
$taxonomy = $taxonomy ?? 'post_tag';
$separator = $separator ?? ', ';
$link = $link ?? true;

$terms = get_the_terms($post_id, $taxonomy);

if (!$terms || is_wp_error($terms)) {
    return;
}
?>

<div class="post-terms post-terms-<?php echo esc_attr($taxonomy); ?>">
    <?php foreach ($terms as $index => $term) : ?>
        <?php if ($index > 0) : ?>
            <span class="term-separator"><?php echo esc_html($separator); ?></span>
        <?php endif; ?>
        
        <?php if ($link) : ?>
            <a href="<?php echo esc_url(get_term_link($term)); ?>" class="term-link">
                <?php echo esc_html($term->name); ?>
            </a>
        <?php else : ?>
            <span class="term-name">
                <?php echo esc_html($term->name); ?>
            </span>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
