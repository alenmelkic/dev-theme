<?php
/**
 * Auto-detect article type from content and show admin notice
 */

function rvk_article_type_auto_detect_notice() {
    global $post;

    if (!$post || $post->post_type !== 'post') {
        return;
    }

    // Check if article type is already set
    $current_type = get_article_type($post->ID);
    if ($current_type) {
        return; // Already has a type
    }

    // Detect from content
    $detected_type = rvk_detect_article_type_from_content($post->ID);

    if (!$detected_type) {
        return; // No media blocks detected
    }

    $type_labels = [
        'video' => 'Video',
        'audio' => 'Audio',
        'galerija' => 'Galerija',
    ];

    $label = $type_labels[$detected_type] ?? '';

    ?>
    <div class="notice notice-info is-dismissible">
        <p>
            <strong>Article Type Suggestion:</strong>
            This post contains <?php echo esc_html(strtolower($label)); ?> content.
            Consider setting the Article Type to "<?php echo esc_html($label); ?>" in the sidebar.
        </p>
    </div>
    <?php
}
add_action('admin_notices', 'rvk_article_type_auto_detect_notice');
