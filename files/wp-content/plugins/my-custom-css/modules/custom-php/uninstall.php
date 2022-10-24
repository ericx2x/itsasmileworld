<?php

/**
 * Cleans up data created by this plugin
 * @package MyCustomPhp
 * @since 2.0
 */

/* Ensure this plugin is actually being uninstalled */
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

/* Fetch the Complete Uninstall option from the database settings */
$unified = false;
if ( is_multisite() ) {
	$menu_perms = get_site_option( 'menu_items', array() );
	$unified = empty( $menu_perms['snippets_settings'] );
}

$settings = $unified ? get_site_option( 'custom_php_settings' ) : get_option( 'custom_php_settings' );

/* Short circuit the uninstall cleanup process if the option is not enabled */
if ( ! isset( $settings['general']['complete_uninstall'] ) || ! $settings['general']['complete_uninstall'] ) {
	return;
}

/**
 * Clean up data created by this plugin for a single site
 * @since 2.0
 */
function custom_php_uninstall_site() {
	global $wpdb;

	/* Remove snippets database table */
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}snippets" );

	/* Remove saved options */
	delete_option( 'custom_php_version' );
	delete_option( 'recently_activated_snippets' );
	delete_option( 'custom_php_settings' );
}


global $wpdb;

/* Multisite uninstall */

if ( is_multisite() ) {

	/* Loop through sites */
	$blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );

	if ( $blog_ids ) {

		foreach ( $blog_ids as $blog_id ) {
			switch_to_blog( $blog_id );
			custom_php_uninstall_site();
		}

		restore_current_blog();
	}

	/* Remove multisite snippets database table */
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}ms_my_custom_php" );

	/* Remove saved options */
	delete_site_option( 'custom_php_version' );
	delete_site_option( 'recently_activated_snippets' );
} else {
	custom_php_uninstall_site();
}
