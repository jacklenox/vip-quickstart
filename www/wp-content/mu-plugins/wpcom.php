<?php

if ( defined( 'SUBDOMAIN_INSTALL' ) && SUBDOMAIN_INSTALL || 1 === get_current_blog_id() ) {
    if ( ! defined( 'QUICKSTART_DISABLE_CONCAT' ) || ! QUICKSTART_DISABLE_CONCAT ) {
	require __DIR__ . '/http-concat/cssconcat.php';
	require __DIR__ . '/http-concat/jsconcat.php';
    }
}

// Add X-hacker header
add_action( 'send_headers', function() {
	header( "X-hacker: If you're reading this, you should visit automattic.com/jobs and apply to join the fun, mention this header." );
});

add_action( 'admin_menu', function() {
	// Hide Plugins menu
	remove_menu_page( 'plugins.php' );

	// Hide Permalinks menu
	remove_submenu_page( 'options-general.php', 'options-permalink.php' );
});

// Turn on global terms
add_filter( 'global_terms_enabled', '__return_true' );

// Disable automatic creation of intermediate images
add_filter( 'intermediate_image_sizes', function( $sizes ) {
    if ( ! defined( 'JETPACK_DEV_DEBUG' ) || ! JETPACK_DEV_DEBUG )
	return array();

    return $sizes;
});

// Check alloptions on every pageload
add_action( 'init', function() {
    $alloptions = wp_cache_get( 'alloptions', 'options' );
    $alloptions = apply_filters( 'alloptions', $alloptions );
});

// Load wpcom global.css
add_action( 'wp_head', 'global_css', 5 );

function global_css() {
	// wp_head action + echo are used instead of wp_enqueue_style, because these stylesheets must be loaded before the others
	wp_enqueue_style( 'h4-global', 'http://s0.wp.com/wp-content/themes/h4/global.css', array() );

	if ( is_rtl() )
		wp_enqueue_style( 'h4-global-rtl', 'http://s0.wp.com/wp-content/themes/h4/global-rtl.css', array() );
}

function wpcom_force_ssl_home_urls_in_content_when_secure( $content ) {
	if ( is_admin() ) {
		return $content;
	}

	if ( is_ssl() ) {
		return str_replace( home_url( '/', 'http' ), home_url( '/', 'https' ), $content );
	}

	return str_replace( home_url( '/', 'https' ), home_url( '/', 'http' ), $content );
}
add_filter( 'the_content', 'wpcom_force_ssl_home_urls_in_content_when_secure' );
add_filter( 'comment_text', 'wpcom_force_ssl_home_urls_in_content_when_secure' );
add_filter( 'widget_text', 'wpcom_force_ssl_home_urls_in_content_when_secure' );

wp_oembed_add_provider(
	'#https?://[^.]+\.(wistia\.com|wi\.st)/(medias|embed)/.*#',
	'https://fast.wistia.com/oembed',
	true
);
