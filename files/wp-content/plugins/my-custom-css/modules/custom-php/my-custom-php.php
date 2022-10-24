<?php

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MY_CUSTOM_PHP_VERSION', '3.0.0' );

define( 'MY_CUSTOM_PHP_FILE', __FILE__ );


function custom_php_autoload( $class_name ) {

	/* Only autoload classes from this plugin */
	if ( 'MyPHPCode' !== $class_name && strpos($class_name, 'MyCustomPhp') !== 0 ) {
		return;
	}

	/* Remove namespace from class name */
	$class_file = str_replace( 'MyCustomPhp_', '', $class_name );

	if ( 'MyPHPCode' === $class_name ) {
		$class_file = 'Snippet';
	}

	/* Convert class name format to file name format */
	$class_file = strtolower( $class_file );
	$class_file = str_replace( '_', '-', $class_file );

	$class_path = dirname( __FILE__ ) . '/php/';

	if ( 'Menu' === substr( $class_name, -4, 4 ) ) {
		$class_path .= 'admin-menus/';
	}

	/* Load the class */
	require_once $class_path . "class-{$class_file}.php";
}

spl_autoload_register( 'custom_php_autoload' );


function custom_php() {
	static $plugin;

	if ( is_null( $plugin ) ) {
		$plugin = new MyCustomPhp( MY_CUSTOM_PHP_VERSION, __FILE__ );
	}

	return $plugin;
}

custom_php()->load_plugin();

add_action( 'plugins_loaded', 'execute_active_snippets', 100 );

