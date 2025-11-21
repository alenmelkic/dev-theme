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
		'components' => 'assets/src/react/components/index.tsx',
		'blocks' => 'assets/src/react/blocks/index.tsx',
		'app' => 'assets/src/react/app/index.tsx'
	];

	// add your custom scss files here
	$scss_files = [
		'main' => 'assets/src/scss/main.scss',
		'react-components' => 'assets/src/scss/react-components.scss'
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

		// Only enqueue 'app' script for specific templates
		if ( $handle === 'app' ) {
			// Don't auto-enqueue the full page app - let templates decide
			continue;
		}

		if ( $handle === 'components' ) {
			// Get menu items and debug
			$menu_items = get_nav_menu_items_array( 'menu-main' );
			error_log( 'Menu items for React: ' . print_r( $menu_items, true ) );

			// Localize data for React components
			$react_vars = array(
				'siteName' => get_bloginfo( 'name' ),
				'tagline' => get_bloginfo( 'description' ),
				'homeUrl' => esc_url( home_url( '/' ) ),
				'isHome' => is_front_page() || is_home(),
				'currentYear' => date( 'Y' ),
				'menuItems' => $menu_items,
				'footerWidgets' => get_footer_widgets_data(),
				'socialLinks' => get_social_links_data(),
				'restUrl' => esc_url_raw( rest_url() ),
				'nonce' => wp_create_nonce( 'wp_rest' )
			);
			wp_localize_script( $handle, 'wpReactData', $react_vars );
		} else {
			$vars = array(
//				'ajaxUrl' => admin_url( 'admin-ajax.php' ), // uncomment to use - in your js : siteVars.ajaxUrl
			);
			wp_localize_script( $handle, 'siteVars', $vars );
		}

		wp_enqueue_script( $handle );
	}

	foreach ( $scss_files as $handle => $file ) {
		$css_uri = VITE_SERVER . '/' . $file;
		if ( VITE_BUILD ) {
			$css_uri = DIST_URI . '/' . $manifest[ $file ]['file'];
		}

		wp_enqueue_style( $handle, $css_uri, null, null );
	}

	// Enqueue servicne-informacije CSS separately (not processed by Vite)
	wp_enqueue_style( 
		'servicne-informacije', 
		get_template_directory_uri() . '/assets/src/css/servicne-informacije.css', 
		null, 
		filemtime( get_template_directory() . '/assets/src/css/servicne-informacije.css' )
	);
}

add_action( 'wp_enqueue_scripts', 'add_vite_assets', 100 );

function vite_client_head_hook() {
	if ( ! VITE_BUILD ) {
		echo '<script type="module" crossorigin src="' . VITE_SERVER . '/@vite/client"></script>';
	}
}

add_action( 'wp_head', 'vite_client_head_hook' );

function add_module_type_attribute( $tag, $handle, $src ) {
	// Add type="module" to React component and block scripts
	if ( in_array( $handle, array( 'components', 'blocks' ) ) ) {
		return '<script type="module" src="' . esc_url( $src ) . '" id="' . esc_attr( $handle ) . '-js"></script>';
	}

	// The handles of the enqueued scripts we want to modify for dev mode
	if ( 'main' === $handle && ! VITE_BUILD ) {
		return '<script type="module" src="' . esc_url( $src ) . '" crossorigin></script>';
	}

	return $tag;
}

add_filter( 'script_loader_tag', 'add_module_type_attribute', 10, 3 );
add_filter( 'style_loader_tag', 'add_module_type_attribute', 10, 3 );

// Helper function to get navigation menu items
function get_nav_menu_items_array( $menu_location ) {
	$menu_items = array();
	$locations = get_nav_menu_locations();

	if ( isset( $locations[ $menu_location ] ) ) {
		$menu = wp_get_nav_menu_object( $locations[ $menu_location ] );
		if ( $menu ) {
			$menu_items_wp = wp_get_nav_menu_items( $menu->term_id );
			if ( $menu_items_wp ) {
				// Organize menu items into hierarchy
				$menu_items_organized = array();
				$child_items = array();

				// First pass: collect all items and separate parents from children
				foreach ( $menu_items_wp as $item ) {
					$menu_item = array(
						'id' => $item->ID,
						'title' => $item->title,
						'url' => $item->url,
						'current' => in_array( 'current-menu-item', $item->classes ),
						'parent' => intval( $item->menu_item_parent ),
						'target' => $item->target,
						'description' => $item->description,
						'children' => array()
					);

					if ( $menu_item['parent'] == 0 ) {
						$menu_items_organized[ $item->ID ] = $menu_item;
					} else {
						$child_items[] = $menu_item;
					}
				}

				// Second pass: attach children to their parents
				foreach ( $child_items as $child ) {
					if ( isset( $menu_items_organized[ $child['parent'] ] ) ) {
						$menu_items_organized[ $child['parent'] ]['children'][] = $child;
					}
				}

				$menu_items = array_values( $menu_items_organized );
			}
		}
	}

	// If no menu is assigned, create a fallback menu
	if ( empty( $menu_items ) ) {
		$menu_items = array(
			array(
				'id' => 1,
				'title' => 'Home',
				'url' => home_url( '/' ),
				'current' => is_front_page(),
				'parent' => 0,
				'target' => '',
				'description' => '',
				'children' => array()
			),
			array(
				'id' => 2,
				'title' => 'Sample Page',
				'url' => home_url( '/sample-page/' ),
				'current' => false,
				'parent' => 0,
				'target' => '',
				'description' => '',
				'children' => array()
			)
		);
	}

	return $menu_items;
}

// Helper function to get footer widgets data
function get_footer_widgets_data() {
	// This is a placeholder - customize based on your widget areas
	return array();
}

// Helper function to get social links data
function get_social_links_data() {
	// This is a placeholder - customize based on your theme options
	return array();
}

function cleaning_wordpress() {
    // force all scripts to load in footer
    remove_action('wp_head', 'wp_print_scripts');
    remove_action('wp_head', 'wp_print_head_scripts', 9);
    remove_action('wp_head', 'wp_enqueue_scripts', 1);

	// removing all WP css files enqueued by default
	wp_dequeue_style('wp-block-library');
	wp_dequeue_style('wp-block-library-theme');
	wp_dequeue_style('wc-block-style');
	wp_dequeue_style('global-styles');
	wp_dequeue_style('classic-theme-styles');
}
add_action('wp_enqueue_scripts', 'cleaning_wordpress', 100);
