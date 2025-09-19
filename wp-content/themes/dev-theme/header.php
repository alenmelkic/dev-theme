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
<div id="page" class="site">

  <header id="masthead" class="site-header" role="banner">
    <div class="site-branding">
      <?php
      if ( is_front_page() || is_home() ) : ?>
        <h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
      <?php else : ?>
        <p class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></p>
      <?php
      endif;

      $description = get_bloginfo( 'description', 'display' );
      if ( $description || is_customize_preview() ) : ?>
        <p class="site-description"><?php echo $description; ?></p>
      <?php endif; ?>

    </div><!-- .site-branding -->

    <nav id="site-navigation" class="main-navigation" role="navigation" aria-label="Primary menu">
      <button class="menu-toggle" aria-controls="menu-main" aria-expanded="false">Menu</button>
      <?php wp_nav_menu( array(
        'theme_location' => 'menu-main',
        'menu_id' => 'menu-main',
        'fallback_cb' => false
      ) ); ?>
    </nav><!-- #site-navigation -->
  </header><!-- #masthead -->

  <div id="content" class="site-content">
