<?php
get_header();
?>

  <div id="primary" class="content-area">
    <main id="main" class="site-main c">
      
      <?php
      while(have_posts()) : the_post();
      ?>

        <section>
          <?php 
          $hide_title = get_post_meta(get_the_ID(), '_hide_page_title', true);
          $title_class = $hide_title ? ' class="visually-hidden"' : '';
          the_title('<h1' . $title_class . '>', '</h1>'); 
          ?>

          <?php
          the_content();
          ?>
        </section>

      <?php
      endwhile; // End of the loop.
      ?>
      
    </main><!-- #main -->
  </div><!-- #primary -->

<?php
get_footer();
