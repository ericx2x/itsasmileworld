<?php

/**
 * Fetch the admin menu slug for a snippets menu
 *
 * @deprecated Use custom_php()->get_menu_slug() instead
 *
 * @param string $menu The menu to retrieve the slug for
 *
 * @return string The menu slug
 */
function custom_php_get_menu_slug( $menu = '' ) {
	return custom_php()->get_menu_slug( $menu );
}

/**
 * Fetch the URL to a snippets admin menu
 *
 * @deprecated Use custom_php()->get_menu_url() instead
 *
 * @param string $menu The menu to retrieve the URL to
 * @param string $context The URL scheme to use
 *
 * @return string The menu URL
 */
function custom_php_get_menu_url( $menu = '', $context = 'self' ) {
	return custom_php()->get_menu_url( $menu, $context );
}

/**
 * Fetch the admin menu hook for a snippets menu
 *
 * @deprecated Use custom_php()->get_menu_hook() instead
 *
 * @param string $menu The menu to retrieve the hook for
 *
 * @return string The menu hook
 */
function custom_php_get_menu_hook( $menu = '' ) {
	return custom_php()->get_menu_hook( $menu );
}

/**
 * Fetch the admin menu slug for a snippets menu
 *
 * @deprecated Use custom_php()->get_snippet_edit_url() instead
 *
 * @param int    $snippet_id The snippet
 * @param string $context The URL scheme to use
 *
 * @return string The URL to the edit snippet page for that snippet
 */
function get_snippet_edit_url( $snippet_id, $context = 'self' ) {
	return custom_php()->get_snippet_edit_url( $snippet_id, $context );
}

/**
 * Get the required capability to perform a certain action on snippets.
 * Does not check if the user has this capability or not.
 *
 * If multisite, checks if *Enable Administration Menus: Snippets* is active
 * under the *Settings > Network Settings* network admin menu
 *
 * @deprecated Use custom_php()->get_cap() instead
 * @since 2.0
 * @return string The capability required to manage snippets
 */
function get_snippets_cap() {
	return custom_php()->get_cap();
}

/**
 * Return the appropriate snippet table name
 *
 * @deprecated Use custom_php()->db->get_table_name() instead
 * @since 2.0
 *
 * @param string|bool|null $multisite Retrieve the multisite table name or the site table name?
 *
 * @return string The snippet table name
 */
function get_snippets_table_name( $multisite = null ) {
	return custom_php()->db->get_table_name( $multisite );
}
