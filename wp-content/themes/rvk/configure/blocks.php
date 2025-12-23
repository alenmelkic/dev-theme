<?php
/**
 * Block Registration
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

function dev_theme_register_blocks() {
    // Register Block Server-Side (for rendering)
    register_block_type( get_template_directory() . '/blocks/soundcloud/block.json' );
    register_block_type( get_template_directory() . '/blocks/facebook-video/block.json' );
    register_block_type( get_template_directory() . '/blocks/youtube-video/block.json' );
    register_block_type( get_template_directory() . '/blocks/category-articles/block.json' );

    // Image Gallery Block with View Script
    $view_script_handle = 'dev-theme-image-gallery-view';
    $view_asset_file = get_template_directory() . '/dist/blocks/image-gallery/view.asset.php';
    
    if ( file_exists( $view_asset_file ) ) {
        $asset = require( $view_asset_file );
        wp_register_script(
            $view_script_handle,
            get_template_directory_uri() . '/dist/blocks/image-gallery/view.js',
            $asset['dependencies'],
            $asset['version'],
            true
        );
    }

    register_block_type( get_template_directory() . '/blocks/image-gallery/block.json', [
        'editor_script' => 'dev-theme-image-gallery-block-editor',
        'view_script'   => $view_script_handle
    ] );
}
add_action( 'init', 'dev_theme_register_blocks' );

function dev_theme_enqueue_block_editor_assets() {
    // SoundCloud Block
    $sc_script_handle = 'dev-theme-soundcloud-block-editor';
    $sc_asset_file = get_template_directory() . '/dist/blocks/soundcloud/index.jsx.asset.php';
    
    if ( file_exists( $sc_asset_file ) ) {
        $asset = require( $sc_asset_file );
        
        wp_enqueue_script(
            $sc_script_handle,
            get_template_directory_uri() . '/dist/blocks/soundcloud/index.jsx.js',
            $asset['dependencies'],
            $asset['version'],
            true
        );
    }

    // Facebook Video Block
    $fb_script_handle = 'dev-theme-facebook-video-block-editor';
    $fb_asset_file = get_template_directory() . '/dist/blocks/facebook-video/index.jsx.asset.php';
    
    if ( file_exists( $fb_asset_file ) ) {
        $asset = require( $fb_asset_file );
        
        wp_enqueue_script(
            $fb_script_handle,
            get_template_directory_uri() . '/dist/blocks/facebook-video/index.jsx.js',
            $asset['dependencies'],
            $asset['version'],
            true
        );
    }

    // YouTube Video Block
    $yt_script_handle = 'dev-theme-youtube-video-block-editor';
    $yt_asset_file = get_template_directory() . '/dist/blocks/youtube-video/index.jsx.asset.php';
    
    if ( file_exists( $yt_asset_file ) ) {
        $asset = require( $yt_asset_file );
        
        wp_enqueue_script(
            $yt_script_handle,
            get_template_directory_uri() . '/dist/blocks/youtube-video/index.jsx.js',
            $asset['dependencies'],
            $asset['version'],
            true
        );
    }

    // Category Articles Block
    $ca_script_handle = 'dev-theme-category-articles-block-editor';
    $ca_asset_file = get_template_directory() . '/dist/blocks/category-articles/index.jsx.asset.php';
    
    if ( file_exists( $ca_asset_file ) ) {
        $asset = require( $ca_asset_file );
        
        wp_enqueue_script(
            $ca_script_handle,
            get_template_directory_uri() . '/dist/blocks/category-articles/index.jsx.js',
            $asset['dependencies'],
            $asset['version'],
            true
        );
    }

    // Image Gallery Block
    $ig_script_handle = 'dev-theme-image-gallery-block-editor';
    $ig_asset_file = get_template_directory() . '/dist/blocks/image-gallery/index.jsx.asset.php';
    
    if ( file_exists( $ig_asset_file ) ) {
        $asset = require( $ig_asset_file );
        
        wp_enqueue_script(
            $ig_script_handle,
            get_template_directory_uri() . '/dist/blocks/image-gallery/index.jsx.js',
            $asset['dependencies'],
            $asset['version'],
            true
        );
    }

    // Enqueue styles for the editor
    wp_enqueue_style(
        'soundcloud-custom-player-editor',
        get_template_directory_uri() . '/dist/css/components/soundcloud-custom-player.css',
        [],
        null
    );
    
    wp_enqueue_style(
        'facebook-video-player-editor',
        get_template_directory_uri() . '/dist/css/components/facebook-video-player.css',
        [],
        null
    );
    
    wp_enqueue_style(
        'youtube-video-player-editor',
        get_template_directory_uri() . '/dist/css/components/youtube-video-player.css',
        [],
        null
    );
}
add_action( 'enqueue_block_editor_assets', 'dev_theme_enqueue_block_editor_assets' );

/**
 * REST API for SoundCloud Tracks
 */
add_action( 'rest_api_init', function () {
    register_rest_route( 'dev-theme/v1', '/soundcloud-tracks', array(
        'methods' => 'GET',
        'callback' => 'dev_theme_get_soundcloud_tracks',
        'permission_callback' => '__return_true',
    ) );
} );

function dev_theme_get_soundcloud_tracks() {
    // Radio Velika Kladuša User ID: 61105252
    $rss_url = 'https://feeds.soundcloud.com/users/soundcloud:users:61105252/sounds.rss';
    
    // Customize cache duration (hook)
    add_filter( 'wp_feed_cache_transient_lifetime', function() { return 3600; } );
    
    $rss = fetch_feed( $rss_url );
    
    // Remove hook
    add_filter( 'wp_feed_cache_transient_lifetime', function() { return 43200; } );

    if ( is_wp_error( $rss ) ) {
        return new WP_Error( 'rss_error', $rss->get_error_message(), array( 'status' => 500 ) );
    }

    $maxitems = $rss->get_item_quantity( 20 ); 
    $rss_items = $rss->get_items( 0, $maxitems );
    $tracks = [];

    foreach ( $rss_items as $item ) {
        $tracks[] = [
            'title' => $item->get_title(),
            'url' => $item->get_permalink(),
            'date' => $item->get_date('d.m.Y'),
        ];
    }

    return $tracks;
}
