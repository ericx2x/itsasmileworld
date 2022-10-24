<?php

/**
 * This class handles the import admin menu
 * @since 2.4.0
 * @package MyCustomPhp
 */
class MyCustomPhp_Import_Menu extends MyCustomPhp_Admin_Menu {

	/**
	 * Class constructor
	 */
	function __construct() {
		parent::__construct( 'import',
			_x( 'Import PHP code', 'menu label', 'my-custom-php' ),
			__( 'Import PHP code', 'my-custom-php' )
		);
	}

	/**
	 * Register action and filter hooks
	 */
	public function run() {
		parent::run();
		add_action( 'admin_init', array( $this, 'register_importer' ) );
		add_action( 'load-importer-my-custom-php', array( $this, 'load' ) );
	}

	/**
	 * Executed when the menu is loaded
	 */
	public function load() {
		parent::load();

		$contextual_help = new MyCustomPhp_Contextual_Help( 'import' );
		$contextual_help->load();

		$this->process_import_files();
	}

	/**
	 * Process the uploaded import files
	 *
	 * @uses import_snippets() to process the import file
	 * @uses wp_redirect() to pass the import results to the page
	 * @uses add_query_arg() to append the results to the current URI
	 */
	private function process_import_files() {

		/* Ensure the import file exists */
		if ( ! isset( $_FILES['custom_php_import_files'] ) || ! count( $_FILES['custom_php_import_files'] ) ) {
			return;
		}

		$count = 0;
		$network = is_network_admin();
		$uploads = $_FILES['custom_php_import_files'];
		$dup_action = isset( $_POST['duplicate_action'] ) ? $_POST['duplicate_action'] : 'ignore';
		$error = false;

		/* Loop through the uploaded files and import the snippets */

		foreach ( $uploads['tmp_name'] as $i => $import_file ) {
			$ext = pathinfo( $uploads['name'][ $i ] );
			$ext = $ext['extension'];
			$mime_type = $uploads['type'][ $i ];

			if ( 'json' === $ext || 'application/json' === $mime_type ) {
				$result = import_snippets_json( $import_file, $network, $dup_action );
			} elseif ( 'xml' === $ext || 'text/xml' === $mime_type ) {
				$result = import_snippets_xml( $import_file, $network, $dup_action );
			} else {
				$result = false;
			}

			if ( false === $result || -1 === $result ) {
				$error = true;
			} else {
				$count += count( $result );
			}
		}

		/* Send the amount of imported snippets to the page */
		$url = add_query_arg( $error ? array( 'error' => true ) : array( 'imported' => $count ) );
		wp_redirect( esc_url_raw( $url ) );
		exit;
	}

	/**
	 * Add the importer to the Tools > Import menu
	 */
	function register_importer() {

		/* Only register the importer if the current user can manage snippets */
		if ( ! defined( 'WP_LOAD_IMPORTERS' ) || ! custom_php()->current_user_can() ) {
			return;
		}

		/* Register the My Custom PHP importer with WordPress */
		register_importer(
			'my-custom-php',
			__( 'My Custom PHP', 'my-custom-php' ),
			__( 'Import snippets from a code snippets export file', 'my-custom-php' ),
			array( $this, 'render' )
		);
	}

	/**
	 * Print the status and error messages
	 */
	protected function print_messages() {

		if ( isset( $_REQUEST['error'] ) && $_REQUEST['error'] ) {
			echo '<div id="message" class="error fade"><p>';
			_e( 'An error occurred when processing the import files.', 'my-custom-php' );
			echo '</p></div>';
		}

		if ( isset( $_REQUEST['imported'] ) && intval( $_REQUEST['imported'] ) >= 0 ) {
			echo '<div id="message" class="updated fade"><p>';

			$imported = intval( $_REQUEST['imported'] );

			if ( 0 === $imported ) {
				esc_html_e( 'No snippets were imported.', 'my-custom-php' );

			} else {

				printf(
					/* translators: 1: amount of snippets imported, 2: link to Snippets menu */
					_n(
						'Successfully imported <strong>%1$d</strong> snippet. <a href="%2$s">Have fun!</a>',
						'Successfully imported <strong>%1$d</strong> snippets. <a href="%2$s">Have fun!</a>',
						$imported, 'my-custom-php'
					),
					$imported,
					custom_php()->get_menu_url( 'manage' )
				);
			}

			echo '</p></div>';
		}
	}
}
