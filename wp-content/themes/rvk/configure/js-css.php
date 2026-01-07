<?php

// Define dist directory, base uri, and path
define( 'DIST_DIR', 'dist' );
define( 'DIST_URI', get_template_directory_uri() . '/' . DIST_DIR );
define( 'DIST_PATH', get_template_directory() . '/' . DIST_DIR );

// default server address, port, and entry point can be customized in vite.config.js
define( 'VITE_SERVER', 'http://localhost:5173' );
define( 'VITE_BUILD', file_exists( DIST_PATH . '/.vite/manifest.json' ) );

// add assets bundled by vite
function add_vite_assets() {
	// add your custom js files here
	$js_files = [
		'main' => 'assets/src/js/main.js',
		'navigation' => 'assets/src/js/navigation.js',

		'bootstrap-components' => 'assets/src/js/bootstrap-components.js',
		'social-share' => 'assets/src/js/social-share.js'
	];

	// add your custom scss files here
	$scss_files = [
		'main' => 'assets/src/scss/main.scss',
		'radio-player' => 'components/radio-player/radio-player.scss'
	];

	if ( VITE_BUILD ) {
		$manifest = json_decode( file_get_contents( DIST_PATH . '/.vite/manifest.json' ), true );
	}

	foreach ( $js_files as $handle => $file ) {
		$js_uri = VITE_SERVER . '/' . $file;
		if ( VITE_BUILD ) {
			$js_uri = DIST_URI . '/' . $manifest[ $file ]['file'];
		}

		wp_register_script( $handle, $js_uri, null, null, true );

		// Localize general site variables
		$vars = array(
			// 'ajaxUrl' => admin_url( 'admin-ajax.php' ), // uncomment to use
		);
		wp_localize_script( $handle, 'siteVars', $vars );

		wp_enqueue_script( $handle );
	}

	foreach ( $scss_files as $handle => $file ) {
		// In development, main.scss is imported by main.js to enable HMR
		// So we skip enqueuing it separately to avoid double loading and HMR issues
		if ( ! VITE_BUILD && $handle === 'main' ) {
			continue;
		}

		$css_uri = VITE_SERVER . '/' . $file;
		if ( VITE_BUILD ) {
			$css_uri = DIST_URI . '/' . $manifest[ $file ]['file'];
		}

		wp_enqueue_style( $handle, $css_uri, null, null );
	}
}
add_action( 'wp_enqueue_scripts', 'add_vite_assets', 100 );

// Add type="module" to our scripts to prevent redeclaration errors
function add_module_type_attribute( $tag, $handle, $src ) {
	// List of scripts that should be loaded as modules
	$module_scripts = [ 'main', 'navigation', 'bootstrap-components', 'social-share', 'seo-content-panel', 'ai-content-helper', 'adsense-manager' ];
	
	if ( in_array( $handle, $module_scripts, true ) ) {
		if ( $handle === 'main' ) {
			$tag = '<script type="module" data-swup-ignore-script src="' . esc_url( $src ) . '"></script>';
		} else {
			$tag = '<script type="module" src="' . esc_url( $src ) . '"></script>';
		}
	}
	
	return $tag;
}
add_filter( 'script_loader_tag', 'add_module_type_attribute', 10, 3 );

// Bootstrap components are now bundled via Vite in bootstrap-components.js
// No separate enqueue needed - it's included in the $js_files array above

function vite_client_head_hook() {
	if ( ! VITE_BUILD ) {
		echo '<script type="module" crossorigin src="' . VITE_SERVER . '/@vite/client"></script>';
	}
}

add_action( 'wp_head', 'vite_client_head_hook' );



function cleaning_wordpress() {
    // force all scripts to load in footer
    remove_action('wp_head', 'wp_print_scripts');
    remove_action('wp_head', 'wp_print_head_scripts', 9);
    remove_action('wp_head', 'wp_enqueue_scripts', 1);

	// removing all WP css files enqueued by default
	wp_dequeue_style('wp-block-library');
	wp_dequeue_style('wp-block-library-theme');
	wp_dequeue_style('wc-block-style');
	wp_dequeue_style('wp-block-library');
	wp_dequeue_style('global-styles');
	wp_dequeue_style('classic-theme-styles');
}
add_action('wp_enqueue_scripts', 'cleaning_wordpress', 100);
