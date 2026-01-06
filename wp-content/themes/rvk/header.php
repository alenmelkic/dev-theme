<!DOCTYPE HTML>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo( 'charset' ); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Font Preloading for Performance -->
  <link rel="preload" href="<?php echo get_template_directory_uri(); ?>/dist/fonts/BeVietnamPro-Regular.woff2" as="font" type="font/woff2" crossorigin>
  <link rel="preload" href="<?php echo get_template_directory_uri(); ?>/dist/fonts/BeVietnamPro-Bold.woff2" as="font" type="font/woff2" crossorigin>

  <?php wp_head(); ?>
  <?php
  // Organization and Website schema
  get_template_part('components/schema-markup/schema-organization');
  get_template_part('components/schema-markup/schema-website');
  ?>
</head>


<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- Swup Overlay for Page Transitions -->
<div class="swup-overlay"></div>

<!-- Persistent Radio Player -->
<?php get_template_part('components/radio-player/radio-player'); ?>

<div id="swup" class="transition-fade">
<div id="page" class="site">

  <header id="masthead" role="banner">
    <nav class="navbar navbar-expand-lg">
      <div class="container">
        <div class="floating-header d-flex justify-content-between align-items-center w-100">
          <!-- Site Branding -->
          <div class="navbar-brand">
            <?php
            $logo_id = get_option('dev_theme_logo');
            $logo_url = $logo_id ? wp_get_attachment_url($logo_id) : '';

            if ( $logo_url ) : ?>
              <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
                <img src="<?php echo esc_url($logo_url); ?>" alt="<?php bloginfo( 'name' ); ?>" class="site-logo">
              </a>
            <?php else : ?>
              <?php if ( is_front_page() || is_home() ) : ?>
                <h1 class="site-title mb-0">
                  <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" class="text-decoration-none">
                    <?php bloginfo( 'name' ); ?>
                  </a>
                </h1>
              <?php else : ?>
                <div class="site-title mb-0">
                  <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" class="text-decoration-none">
                    <?php bloginfo( 'name' ); ?>
                  </a>
                </div>
              <?php endif; ?>

              <?php
              $description = get_bloginfo( 'description', 'display' );
              if ( $description || is_customize_preview() ) : ?>
                <p class="site-description mb-0 text-muted small"><?php echo $description; ?></p>
              <?php endif; ?>
            <?php endif; ?>
          </div>

          

          <!-- Desktop Navigation (hidden on mobile) -->
          <div class="desktop-menu d-none d-lg-block">
            <?php
            wp_nav_menu( array(
              'theme_location' => 'menu-desktop',
              'menu_id'        => 'desktop-menu',
              'menu_class'     => 'navbar-nav ms-auto',
              'container'      => false,
              'fallback_cb'    => false,
              'walker'         => new Bootstrap_Nav_Walker()
            ) );
            ?>
          </div>

          <!-- Mobile Navigation (hidden on desktop) -->
          <div class="d-lg-none">
            <div class="menu-nav-mobile" id="mobileNav">
              <?php
              wp_nav_menu( array(
                'theme_location' => 'menu-mobile',
                'menu_id'        => 'mobile-menu',
                'menu_class'     => 'navbar-nav',
                'container'      => false,
                'fallback_cb'    => false,
                'walker'         => new Bootstrap_Nav_Walker()
              ) );
              ?>
            </div>
          </div>

          <div class="button-container d-flex align-items-center">

              <!-- Header Play Button -->
              <button id="header-play-btn" class="header-play-btn mx-0 d-lg-flex align-items-center" aria-label="Listen Live">
                  <span class="icon-play me-2">▶</span>
                  <span class="text">RVK Uživo</span>
              </button>

              <!-- Mobile Menu Toggle -->
              <button class="navbar-toggler hamburger" type="button" data-bs-toggle="collapse"
                  data-bs-target="#navbarMobile" aria-controls="navbarMobile" aria-expanded="false"
                  aria-label="Toggle navigation">

                  <span class="bar"></span>
                  <span class="bar"></span>
                  <span class="bar"></span>

              </button>

          </div>
        </div>
      </div>
    </nav>
  </header>

  <div id="content" class="site-content">
    <?php
    // Display top banner if conditions are met
    if (function_exists('rvk_display_top_banner')) {
        rvk_display_top_banner();
    }
    ?>

