<?php

/**
 * Functions specific to the administration interface
 *
 * @package MyCustomPhp
 */
class MyCustomPhp_Admin {

	public $menus = array();

	function __construct() {

		if ( is_admin() ) {
			$this->run();
		}
	}

	public function load_classes() {
		$this->menus['manage'] = new MyCustomPhp_Manage_Menu();
        $this->menus['choice'] = new MyCustomPhp_Choice_Menu();
		$this->menus['edit'] = new MyCustomPhp_Edit_Menu();
		$this->menus['import'] = new MyCustomPhp_Import_Menu();


		if ( is_network_admin() === custom_php_unified_settings() ) {
			$this->menus['settings'] = new MyCustomPhp_Settings_Menu();
		}

		foreach ( $this->menus as $menu ) {
			$menu->run();
		}
	}

	public function run() {
		add_action( 'init', array( $this, 'load_classes' ), 11 );

		add_filter( 'mu_menu_items', array( $this, 'mu_menu_items' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( MY_CUSTOM_PHP_FILE ), array( $this, 'plugin_settings_link' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'load_admin_menu_icon' ) );

		if ( isset( $_POST['save_snippet'] ) && $_POST['save_snippet'] ) {
			add_action( 'custom_php/allow_execute_snippet', array( $this, 'prevent_exec_on_save' ), 10, 3 );
		}
	}

	/**
	 * @return bool
	 */
	public function is_compact_menu() {
		return ! is_network_admin() && apply_filters( 'custom_php_compact_menu', false );
	}

	/**
	 * Allow super admins to control site admin access to
	 * snippet admin menus
	 *
	 * Adds a checkbox to the *Settings > Network Settings*
	 * network admin menu
	 *
	 * @since 1.7.1
	 *
	 * @param  array $menu_items The current mu menu items
	 *
	 * @return array             The modified mu menu items
	 */
	function mu_menu_items( $menu_items ) {
		$menu_items['snippets'] = __( 'Snippets', 'my-custom-php' );
		$menu_items['snippets_settings'] = __( 'Snippets &raquo; Settings', 'my-custom-php' );

		return $menu_items;
	}

	/**
	 * Load the stylesheet for the admin menu icon
	 */
	function load_admin_menu_icon() {


	}

	/**
	 * Prevent the snippet currently being saved from being executed
	 * so it is not run twice (once normally, once
	 *
	 * @param bool   $exec Whether the snippet will be executed
	 * @param int    $exec_id The ID of the snippet being executed
	 * @param string $table_name
	 *
	 * @return bool Whether the snippet will be executed
	 */
	function prevent_exec_on_save( $exec, $exec_id, $table_name ) {

		if ( ! isset( $_POST['save_snippet'], $_POST['snippet_id'] ) ) {
			return $exec;
		}

		if ( custom_php()->db->get_table_name() !== $table_name ) {
			return $exec;
		}

		$id = intval( $_POST['snippet_id'] );

		if ( $id === $exec_id ) {
			return false;
		}

		return $exec;
	}

	/**
	 * Adds a link pointing to the Manage Snippets page
	 *
	 * @since 2.0
	 *
	 * @param  array $links The existing plugin action links
	 *
	 * @return array        The modified plugin action links
	 */
	function plugin_settings_link( $links ) {
		array_unshift( $links, sprintf(
			'<a href="%1$s" title="%2$s">%3$s</a>',
			custom_php()->get_menu_url(),
			__( 'Manage your existing snippets', 'my-custom-php' ),
			__( 'Snippets', 'my-custom-php' )
		) );

		return $links;
	}



}
