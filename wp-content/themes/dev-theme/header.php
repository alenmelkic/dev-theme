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

  <header id="masthead" class="site-header" role="banner">
    <nav class="navbar navbar-expand-lg navbar-light bg-white">
      <div class="container">
        <!-- Site Branding -->
        <div class="navbar-brand">
          <?php if ( is_front_page() || is_home() ) : ?>
            <h1 class="site-title">
              <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
                <?php bloginfo( 'name' ); ?>
              </a>
            </h1>
          <?php else : ?>
            <div class="site-title">
              <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
                <?php bloginfo( 'name' ); ?>
              </a>
            </div>
          <?php endif; ?>

          <?php
          $description = get_bloginfo( 'description', 'display' );
          if ( $description || is_customize_preview() ) : ?>
            <p class="site-description"><?php echo $description; ?></p>
          <?php endif; ?>
        </div>

        <!-- Mobile Menu Toggle -->
        <button class="navbar-toggler d-lg-none" type="button" data-toggle="mobile-menu" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Header Play Button -->
        <button id="header-play-btn" class="header-play-btn d-none d-lg-flex" aria-label="Listen Live">
            <span class="icon-play">▶</span>
            <span class="text">RVK Uživo</span>
        </button>

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
        <div class="mobile-menu collapse navbar-collapse d-lg-none" id="mobileNav">
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
    </nav>
  </header>

  <div id="content" class="site-content">
