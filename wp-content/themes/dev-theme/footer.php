
  </div><!-- #content -->

  <!-- React Footer Container -->
  <div id="react-footer"></div>

  <!-- Fallback PHP Footer (for when JS is disabled) -->
  <noscript>
    <footer id="colophon" class="site-footer">
      <div class="container">
        <div class="site-info">
          <p>&copy; <?php echo date('Y'); ?> <?php bloginfo( 'name' ); ?>. All rights reserved.</p>
        </div>
      </div>
    </footer><!-- #colophon -->
  </noscript>
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
