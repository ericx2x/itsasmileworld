<?php

/**
 * This class handles the manage snippets menu
 * @since 2.4.0
 * @package MyCustomPhp
 */
class MyCustomPhp_Manage_Menu extends MyCustomPhp_Admin_Menu {

	/**
	 * Holds the list table class
	 * @var MyCustomPhp_List_Table
	 */
	public $list_table;

	/**
	 * Class constructor
	 */
	public function __construct() {

		parent::__construct( 'manage',
			_x( 'My Custom PHP', 'menu label', 'my-custom-php' ),
			__( 'My Custom PHP', 'my-custom-php' )
		);
	}

	/**
	 * Register action and filter hooks
	 */
	public function run() {
		parent::run();

		if ( custom_php()->admin->is_compact_menu() ) {
			add_action( 'admin_menu', array( $this, 'register_compact_menu' ), 2 );
			add_action( 'network_admin_menu', array( $this, 'register_compact_menu' ), 2 );
		}

		add_filter( 'set-screen-option', array( $this, 'save_screen_option' ), 10, 3 );
		add_action( 'wp_ajax_update_my_php_code', array( $this, 'ajax_callback' ) );
	}

	/**
	 * Register the top-level 'Snippets' menu and associated 'Manage' subpage
	 *
	 * @uses add_menu_page() to register a top-level menu
	 * @uses add_submenu_page() to register a sub-menu
	 */
	function register() {

		/* Register the top-level menu */
		/*add_menu_page(
			__( 'Snippets', 'my-custom-php' ),
			_x( 'Snippets', 'top-level menu label', 'my-custom-php' ),
			custom_php()->get_cap(),
			custom_php()->get_menu_slug(),
			array( $this, 'render' ),
			'div', // icon is added through CSS
			is_network_admin() ? 21 : 67
		);*/

		/* Register the sub-menu */
		parent::register();
	}

	public function register_compact_menu() {

		if ( ! custom_php()->admin->is_compact_menu() ) {
			return;
		}

		$sub = custom_php()->get_menu_slug( isset( $_GET['sub'] ) ? $_GET['sub'] : 'snippets' );

		$classmap = array(
			'snippets' => 'manage',
			'add-snippet' => 'edit',
			'edit-snippet' => 'edit',
			'import-snippets' => 'import',
			'snippets-settings' => 'settings',
		);

		if ( isset( $classmap[ $sub ], custom_php()->admin->menus[ $classmap[ $sub ] ] ) ) {
			/** @var MyCustomPhp_Admin_Menu $class */
			$class = custom_php()->admin->menus[ $classmap[ $sub ] ];
		} else {
			$class = $this;
		}

		/* Add a submenu to the Tools menu */
		$hook = add_submenu_page(
			'tools.php',
			__( 'Snippets', 'my-custom-php' ),
			_x( 'Snippets', 'tools submenu label', 'my-custom-php' ),
			custom_php()->get_cap(),
			custom_php()->get_menu_slug(),
			array( $class, 'render' )
		);

		add_action( 'load-' . $hook, array( $class, 'load' ) );

	}

	/**
	 * Executed when the admin page is loaded
	 */
	function load() {
		parent::load();

		/* Load the contextual help tabs */
		$contextual_help = new MyCustomPhp_Contextual_Help( 'manage' );
		$contextual_help->load();

		/* Initialize the list table class */
		$this->list_table = new MyCustomPhp_List_Table();
		$this->list_table->prepare_items();
	}

	/**
	 * Enqueue scripts and stylesheets for the admin page
	 */
	public function enqueue_assets() {
		$plugin = custom_php();
		$rtl = is_rtl() ? '-rtl' : '';

		wp_enqueue_style(
			'my-custom-php-manage',
			plugins_url( "css/min/manage{$rtl}.css", $plugin->file ),
			array(), $plugin->version
		);

		wp_enqueue_script(
			'my-custom-php-manage-js',
			plugins_url( 'js/min/manage.js', $plugin->file ),
			array(), $plugin->version, true
		);
	}

	/**
	 * Print the status and error messages
	 */
	protected function print_messages() {

		/* Output a warning if safe mode is active */
		if ( defined( 'MY_CUSTOM_PHP_SAFE_MODE' ) && MY_CUSTOM_PHP_SAFE_MODE ) {
			echo '<div id="message" class="error fade"><p>';
			_e( '<strong>Warning:</strong> Safe mode is active and snippets will not execute! Remove the <code>MY_CUSTOM_PHP_SAFE_MODE</code> constant from <code>wp-config.php</code> to turn off safe mode. ', 'my-custom-php' );
			echo '</p></div>';
		}

		echo $this->get_result_message(
			array(
				'executed'          => __( 'Snippet <strong>executed</strong>.', 'my-custom-php' ),
				'activated'         => __( 'Snippet <strong>activated</strong>.', 'my-custom-php' ),
				'activated-multi'   => __( 'Selected snippets <strong>activated</strong>.', 'my-custom-php' ),
				'deactivated'       => __( 'Snippet <strong>deactivated</strong>.', 'my-custom-php' ),
				'deactivated-multi' => __( 'Selected snippets <strong>deactivated</strong>.', 'my-custom-php' ),
				'deleted'           => __( 'Snippet <strong>deleted</strong>.', 'my-custom-php' ),
				'deleted-multi'     => __( 'Selected snippets <strong>deleted</strong>.', 'my-custom-php' ),
				'cloned'            => __( 'Snippet <strong>cloned</strong>.', 'my-custom-php' ),
				'cloned-multi'      => __( 'Selected snippets <strong>cloned</strong>.', 'my-custom-php' ),
			)
		);
	}

	/**
	 * Handles saving the user's snippets per page preference
	 *
	 * @param  mixed  $status
	 * @param  string $option The screen option name
	 * @param  mixed  $value
	 *
	 * @return mixed
	 */
	function save_screen_option( $status, $option, $value ) {
		if ( 'snippets_per_page' === $option ) {
			return $value;
		}

		return $status;
	}

	/**
	 * Handle AJAX requests
	 */
	public function ajax_callback() {
		check_ajax_referer( 'custom_php_manage' );

		if ( ! isset( $_POST['field'], $_POST['snippet'] ) ) {
			wp_die( 'Snippet data not provided' );
		}

		$snippet_data = json_decode( stripslashes( $_POST['snippet'] ), true );

		$snippet = new MyPHPCode( $snippet_data );
		$field = $_POST['field'];

		if ( 'priority' === $field ) {

			if ( ! isset( $snippet_data['priority'] ) || ! is_numeric( $snippet_data['priority'] ) ) {
				wp_die( 'missing snippet priority data' );
			}

			global $wpdb;

			$wpdb->update(
				custom_php()->db->get_table_name( $snippet->network ),
				array( 'priority' => $snippet->priority ),
				array( 'id' => $snippet->id ),
				array( '%d' ),
				array( '%d' )
			);

		} elseif ( 'active' === $field ) {

			if ( ! isset( $snippet_data['active'] ) ) {
				wp_die( 'missing snippet active data' );
			}

			if ( $snippet->shared_network ) {
				$active_shared_snippets = get_option( 'active_shared_network_snippets', array() );

				if ( in_array( $snippet->id, $active_shared_snippets ) !== $snippet->active ) {

					$active_shared_snippets = $snippet->active ?
						array_merge( $active_shared_snippets, array( $snippet->id ) ) :
						array_diff( $active_shared_snippets, array( $snippet->id ) );

					update_option( 'active_shared_network_snippets', $active_shared_snippets );
				}
			} else {

				if ( $snippet->active ) {
					activate_snippet( $snippet->id, $snippet->network );
				} else {
					deactivate_snippet( $snippet->id, $snippet->network );
				}
			}
		}

		wp_die();
	}
}
