<?php
get_header();
?>

  <div id="primary" class="content-area">
    <main id="main" class="site-main c">
      <div class="container">
      <?php
      while(have_posts()) : the_post();
      ?>

        <section>
          <?php the_title('<h1>', '</h1>'); ?>

          <?php
          the_content();
          ?>
        </section>

      <?php
      endwhile; // End of the loop.
      ?>
      </div><!-- .container -->
    </main><!-- #main -->
  </div><!-- #primary -->

<?php
get_footer();
