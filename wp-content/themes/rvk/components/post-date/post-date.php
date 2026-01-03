<?php
/**
 * Post Date Component
 * 
 * @param int $post_id Post ID (default: current post)
 * @param string $format Date format (default: get_option('date_format'))
 * @param string $icon Whether to show icon (default: true)
 */

$post_id = $post_id ?? get_the_ID();
$format = $format ?? get_option('date_format');
$icon = $icon ?? true;

$date = get_the_date($format, $post_id);
$datetime = get_the_date('c', $post_id);
?>

<div class="post-date">
    <?php if ($icon) : ?>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
  <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
</svg>

    <?php endif; ?>
    <time datetime="<?php echo esc_attr($datetime); ?>">
        <?php echo esc_html($date); ?>
    </time>
</div>
