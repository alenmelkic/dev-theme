<?php

// CPT TAXONOMY
include( 'configure/cpt-taxonomy.php' );
include( 'configure/cpt-obavijesti-o-smrti.php' );

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

// USER PROFILE
include( 'configure/user-profile.php' );

// AI CONTENT GENERATION
include( 'configure/ai-content-generator.php' );
include( 'configure/ai-settings.php' );
include( 'configure/ai-enqueue.php' );

// HOOKS ADMIN
if(is_admin()) {
	include( 'configure/admin.php' );
}
