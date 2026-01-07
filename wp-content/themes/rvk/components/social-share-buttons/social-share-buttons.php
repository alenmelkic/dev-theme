<?php
/**
 * Social Share Buttons Component
 * Share buttons with excerpt and hashtags from post tags
 *
 * @param array $args {
 *     Optional. Array of arguments.
 *     @type array  $platforms        Array of platforms to display. Default: all.
 *     @type bool   $include_hashtags Whether to include hashtags. Default: true.
 *     @type string $style            Button style: 'default', 'round', 'minimal'. Default: 'default'.
 *     @type string $size             Button size: 'small', 'medium', 'large'. Default: 'medium'.
 * }
 */

// Default arguments
$defaults = array(
    'platforms' => array('facebook', 'twitter', 'linkedin', 'whatsapp', 'email'),
    'include_hashtags' => true,
    'style' => 'default',
    'size' => 'medium'
);

$args = wp_parse_args($args ?? array(), $defaults);

// Get post data
$post_id = get_the_ID();
$post_url = get_permalink($post_id);
$post_title = get_the_title($post_id);
$post_excerpt = get_the_excerpt($post_id);

// Get hashtags if enabled
$hashtags = '';
$hashtags_display = '';
if ($args['include_hashtags']) {
    $hashtags = rvk_get_post_hashtags($post_id, 5);
    $hashtags_display = !empty($hashtags) ? $hashtags : '';
}

// Prepare share texts
$facebook_url = rvk_get_share_url('facebook', $post_id);
$twitter_url = rvk_get_share_url('twitter', $post_id);
$linkedin_url = rvk_get_share_url('linkedin', $post_id);
$whatsapp_url = rvk_get_share_url('whatsapp', $post_id);
$email_url = rvk_get_share_url('email', $post_id);

// Platform icons (SVG)
$icons = array(
    'facebook' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>',

    'twitter' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',

    'linkedin' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>',

    'whatsapp' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>',

    'email' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>'
);

?>

<div class="rvk-social-share-buttons rvk-social-share-<?php echo esc_attr($args['style']); ?> rvk-social-share-<?php echo esc_attr($args['size']); ?>">
    
    <div class="share-buttons d-flex gap-2">
        <?php if (in_array('facebook', $args['platforms'])): ?>
        <a href="<?php echo esc_url($facebook_url); ?>"
           class="share-button share-facebook"
           target="_blank"
           rel="noopener noreferrer"
           aria-label="Podijeli na Facebooku"
           data-platform="facebook">
            <span class="share-icon"><?php echo $icons['facebook']; ?></span>
            <span class="share-label d-none">Facebook</span>
        </a>
        <?php endif; ?>

        <?php if (in_array('twitter', $args['platforms'])): ?>
        <a href="<?php echo esc_url($twitter_url); ?>"
           class="share-button share-twitter"
           target="_blank"
           rel="noopener noreferrer"
           aria-label="Podijeli na Twitter/X"
           data-platform="twitter">
            <span class="share-icon"><?php echo $icons['twitter']; ?></span>
            <span class="share-label d-none">Twitter/X</span>
        </a>
        <?php endif; ?>

        <?php if (in_array('linkedin', $args['platforms'])): ?>
        <a href="<?php echo esc_url($linkedin_url); ?>"
           class="share-button share-linkedin"
           target="_blank"
           rel="noopener noreferrer"
           aria-label="Podijeli na LinkedInu"
           data-platform="linkedin">
            <span class="share-icon"><?php echo $icons['linkedin']; ?></span>
            <span class="share-label d-none">LinkedIn</span>
        </a>
        <?php endif; ?>

        <?php if (in_array('whatsapp', $args['platforms'])): ?>
        <a href="<?php echo esc_url($whatsapp_url); ?>"
           class="share-button share-whatsapp"
           target="_blank"
           rel="noopener noreferrer"
           aria-label="Podijeli na WhatsAppu"
           data-platform="whatsapp">
            <span class="share-icon"><?php echo $icons['whatsapp']; ?></span>
            <span class="share-label d-none">WhatsApp</span>
        </a>
        <?php endif; ?>

        <?php if (in_array('email', $args['platforms'])): ?>
        <a href="<?php echo esc_url($email_url); ?>"
           class="share-button share-email"
           aria-label="Podijeli emailom"
           data-platform="email">
            <span class="share-icon"><?php echo $icons['email']; ?></span>
            <span class="share-label d-none">Email</span>
        </a>
        <?php endif; ?>

        <button class="share-button share-copy bg-transparent border-0"
                data-url="<?php echo esc_url($post_url); ?>"
                aria-label="Kopiraj link">
            <span class="share-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/>
                </svg>
            </span>
            <span class="share-label d-none">Kopiraj Link</span>
        </button>
    </div>

    <?php if ($args['include_hashtags']): ?>
    <?php
    // Get post tags for clickable hashtags
    $post_tags = get_the_tags();
    if (!empty($post_tags)):
    ?>
    <div class="share-hashtags">
        <span class="hashtags-label">Tagovi:</span>
        <span class="hashtags-list">
            <?php
            foreach ($post_tags as $tag) {
                echo '<a href="' . esc_url(get_tag_link($tag->term_id)) . '" class="hashtag-link">#' . esc_html($tag->name) . '</a> ';
            }
            ?>
        </span>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>
