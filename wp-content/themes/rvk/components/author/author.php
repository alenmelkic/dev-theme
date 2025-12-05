<?php
/**
 * Author Component
 * 
 * @param int $author_id Author ID (default: post author)
 * @param int $post_id Post ID (default: current post)
 * @param string $size Avatar size (default: 'small')
 * @param bool $link Whether to link to author archive (default: true)
 * @param bool $show_avatar Whether to show avatar (default: true)
 */

$post_id = $post_id ?? get_the_ID();
$author_id = $author_id ?? get_post_field('post_author', $post_id);
$size = $size ?? 'small';
$link = $link ?? true;
$show_avatar = $show_avatar ?? true;

// Avatar sizes
$avatar_sizes = array(
    'small' => 32,
    'medium' => 48,
    'large' => 64
);
$avatar_size = $avatar_sizes[$size] ?? 32;

// Get first and last name
$first_name = get_the_author_meta('first_name', $author_id);
$last_name = get_the_author_meta('last_name', $author_id);

// Build author name: "First Last" or fallback to display_name
if ($first_name && $last_name) {
    $author_name = trim($first_name . ' ' . $last_name);
} elseif ($first_name) {
    $author_name = $first_name;
} elseif ($last_name) {
    $author_name = $last_name;
} else {
    $author_name = get_the_author_meta('display_name', $author_id);
}
$author_url = get_author_posts_url($author_id);
$avatar = get_avatar($author_id, $avatar_size);
?>

<div class="post-author author-<?php echo esc_attr($size); ?>">
    <?php if ($show_avatar) : ?>
        <div class="author-avatar">
            <?php echo $avatar; ?>
        </div>
    <?php endif; ?>
    
    <div class="author-info">
        <span class="author-name">
            <?php echo esc_html($author_name); ?>
        </span>
    </div>
</div>
