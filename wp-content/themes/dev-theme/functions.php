<?php

// CPT TAXONOMY
include( 'configure/cpt-taxonomy.php' );

// Utilities
include( 'configure/utilities.php' );

// CONFIG
include( 'configure/configure.php' );

// JAVASCRIPT & CSS
include( 'configure/js-css.php' );

// COMPONENTS
include( 'configure/components.php' );

// BOOTSTRAP NAV WALKER
include( 'configure/class-bootstrap-nav-walker.php' );

// BLOCKS
include( 'configure/blocks.php' );

// IMAGE OPTIMIZATION
include( 'configure/images.php' );
include( 'inc/image-processor.php' );
include( 'inc/image-helper.php' );
include( 'inc/image-fallbacks.php' );

// ACF
include( 'configure/acf.php' );

// HOOKS ADMIN
if(is_admin()) {
	include( 'configure/admin.php' );
}
