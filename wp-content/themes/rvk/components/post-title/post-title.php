<?php
/**
 * Post Title Component
 * 
 * @param int $post_id Post ID (default: current post)
 * @param string $tag HTML tag (default: 'h2')
 * @param bool $link Whether to link to post (default: true)
 * @param string $class Additional CSS classes
 */

$post_id = $post_id ?? get_the_ID();
$tag = $tag ?? 'h2';
$link = $link ?? true;
$class = $class ?? '';

$title = get_the_title($post_id);
$permalink = get_permalink($post_id);
?>

<<?php echo esc_attr($tag); ?> class="post-title <?php echo esc_attr($class); ?>">
    <?php if ($link) : ?>
        <a href="<?php echo esc_url($permalink); ?>">
            <?php echo esc_html($title); ?>
        </a>
    <?php else : ?>
        <?php echo esc_html($title); ?>
    <?php endif; ?>
</<?php echo esc_attr($tag); ?>>
