<!DOCTYPE HTML>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo( 'charset' ); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="profile" href="https://gmpg.org/xfn/11">
  <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
  <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- Persistent Radio Player -->
<?php get_template_part('components/radio-player/radio-player'); ?>

<div id="swup" class="transition-fade">
<div id="page" class="site">

  <header id="masthead" role="banner">
    <nav class="navbar navbar-expand-lg navbar-light bg-white">
      <div class="container floating-header">
        <!-- Site Branding -->
        <div class="navbar-brand">
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
    </nav>
  </header>

  <div id="content" class="site-content">
