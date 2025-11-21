<?php
/**
 * Template Name: React Full Page
 *
 * This template renders the entire page using React
 */

// Enqueue the React app instead of components
wp_dequeue_script('components');
wp_enqueue_script('react-app', get_template_directory_uri() . '/dist/js/app.js', [], '1.0.0', true);

// Localize the same data for the React app
$react_vars = array(
    'siteName' => get_bloginfo('name'),
    'tagline' => get_bloginfo('description'),
    'homeUrl' => esc_url(home_url('/')),
    'isHome' => is_front_page() || is_home(),
    'currentYear' => date('Y'),
    'menuItems' => get_nav_menu_items_array('menu-main'),
    'footerWidgets' => get_footer_widgets_data(),
    'socialLinks' => get_social_links_data(),
    'copyrightText' => get_theme_mod('copyright_text', ''),
    'pageId' => get_the_ID(),
    'pageSlug' => get_post_field('post_name', get_the_ID()),
    'restUrl' => rest_url('wp/v2/'),
    'nonce' => wp_create_nonce('wp_rest')
);

wp_localize_script('react-app', 'wpReactData', $react_vars);

// Minimal HTML structure
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>

<body <?php body_class('react-page'); ?>>
    <?php wp_body_open(); ?>

    <!-- React will render the entire page here -->
    <div id="react-app"></div>

    <!-- Fallback content for when JavaScript is disabled -->
    <noscript>
        <div class="no-js-message">
            <div class="container">
                <div class="alert alert-warning">
                    <h2>JavaScript Required</h2>
                    <p>This page requires JavaScript to display content. Please enable JavaScript in your browser or visit the <a href="<?php echo esc_url(home_url('/')); ?>">standard version</a> of our website.</p>
                </div>

                <!-- Basic content fallback -->
                <div class="basic-content">
                    <h1><?php the_title(); ?></h1>
                    <div class="content">
                        <?php
                        if (have_posts()) :
                            while (have_posts()) :
                                the_post();
                                the_content();
                            endwhile;
                        endif;
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </noscript>

    <?php wp_footer(); ?>
</body>
</html>