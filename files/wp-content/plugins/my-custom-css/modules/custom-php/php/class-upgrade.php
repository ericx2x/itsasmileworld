<?php

/**
 * Manages upgrade tasks such as deleting and updating options
 */
class MyCustomPhp_Upgrade {

	/**
	 * Instance of database class
	 * @var MyCustomPhp_DB
	 */
	private $db;

	/**
	 * Class constructor
	 *
	 * @param MyCustomPhp_DB $db Instance of database class
	 */
	public function __construct( MyCustomPhp_DB $db ) {
		$this->db = $db;
	}

	/**
	 * Run the upgrade functions
	 */
	public function run() {

		/* Always run multisite upgrades, even if not on the main site, as subsites depend on the network snippet table */
		if ( is_multisite() ) {
			$this->do_multisite_upgrades();
		}

		$this->do_site_upgrades();
	}

	/**
	 * Perform upgrades for the current site
	 */
	private function do_site_upgrades() {
		$table_name = $this->db->table;
		$prev_version = get_option( 'custom_php_version' );

		/* Do nothing if the plugin has not been updated or installed */
		if ( ! version_compare( $prev_version, MY_CUSTOM_PHP_VERSION, '<' ) ) {
			return;
		}

		$this->db->create_table( $table_name );

		if ( false !== $prev_version ) {
			$this->db->create_missing_columns( $table_name );
		}

		/* Update the plugin version stored in the database */
		update_option( 'custom_php_version', MY_CUSTOM_PHP_VERSION );

		/* Update the scope column of the database */
		if ( version_compare( $prev_version, '2.10.0', '<' ) ) {
			$this->migrate_scope_data( $table_name );
		}

		/* Custom capabilities were removed after version 2.9.5 */
		if ( version_compare( $prev_version, '2.9.5', '<=' ) ) {
			$role = get_role( apply_filters( 'custom_php_role', 'administrator' ) );
			$role->remove_cap( apply_filters( 'custom_php_cap', 'manage_snippets' ) );
		}

		if ( false === $prev_version ) {
			$this->create_sample_content();
		}
	}

	/**
	 * Perform multisite-only upgrades
	 */
	private function do_multisite_upgrades() {
		$table_name = $this->db->ms_table;
		$prev_version = get_site_option( 'custom_php_version' );

		/* Do nothing if the plugin has not been updated or installed */
		if ( ! version_compare( $prev_version, MY_CUSTOM_PHP_VERSION, '<' ) ) {
			return;
		}

		/* Always attempt to create or upgrade the database tables */
		$this->db->create_table( $table_name );

		/* If the plugin has been upgraded, also attempt to create the new columns */
		if ( false !== $prev_version ) {
			$this->db->create_missing_columns( $table_name );
		}

		/* Update the plugin version stored in the database */
		update_site_option( 'custom_php_version', MY_CUSTOM_PHP_VERSION );

		/* Update the scope column of the database */
		if ( version_compare( $prev_version, '2.10.0', '<' ) ) {
			$this->migrate_scope_data( $table_name );
		}

		/* Custom capabilities were removed after version 2.9.5 */
		if ( version_compare( $prev_version, '2.9.5', '<=' ) ) {
			$network_cap = apply_filters( 'custom_php_network_cap', 'manage_network_snippets' );

			foreach ( get_super_admins() as $admin ) {
				$user = new WP_User( 0, $admin );
				$user->remove_cap( $network_cap );
			}
		}
	}

	/**
	 * Migrate data from the old integer method of storing scopes to the new string method
	 *
	 * @param string $table_name
	 */
	private function migrate_scope_data( $table_name ) {
		global $wpdb;

		$scopes = array(
			0 => 'global',
			1 => 'admin',
			2 => 'front-end',
		);

		foreach ( $scopes as $scope_number => $scope_name ) {
			$wpdb->query( sprintf(
				"UPDATE %s SET scope = '%s' WHERE scope = %d",
				$table_name, $scope_name, $scope_number
			) );
		}
	}

	/**
	 * Add sample snippet content to the database
	 */
	public function create_sample_content() {

		if ( ! apply_filters( 'custom_php/create_sample_content', true ) ) {
			return;
		}

		$snippets = array(

			array(
				'name' => __( 'Example HTML shortcode', 'my-custom-php' ),
				'code' => sprintf(
					"\nadd_shortcode( 'shortcode_name', function () { ?>\n\n\t<p>%s</p>\n\n<?php } );",
					strip_tags( __( 'write your HTML shortcode content here', 'my-custom-php' ) )
				),
				'desc' => __( 'This is an example snippet for demonstrating how to add an HTML shortcode.', 'my-custom-php' ),
				'tags' => array( 'shortcode' ),
			),

			array(
				'name'  => __( 'Example CSS snippet', 'my-custom-php' ),
				'code'  => sprintf(
					"\nadd_action( 'wp_head', function () { ?>\n\t<style>\n\n\t\t/* %s */\n\n\t</style>\n<?php } );\n",
					strip_tags( __( 'write your CSS code here', 'my-custom-php' ) )
				),
				'desc'  => __( 'This is an example snippet for demonstrating how to add custom CSS code to your website.', 'my-custom-php' ),
				'tags'  => array( 'css' ),
				'scope' => 'front-end',
			),

			array(
				'name'  => __( 'Example JavaScript snippet', 'my-custom-php' ),
				'code'  => sprintf(
					"\nadd_action( 'wp_head', function () { ?>\n\t<script>\n\n\t\t/* %s */\n\n\t</script>\n<?php } );\n",
					strip_tags( __( 'write your JavaScript code here', 'my-custom-php' ) )
				),
				'desc'  => __( 'This is an example snippet for demonstrating how to add custom JavaScript code to your website.', 'my-custom-php' ),
				'tags'  => array( 'javascript' ),
				'scope' => 'front-end',
			),
		);

		foreach ( $snippets as $snippet ) {
			$snippet = new MyPHPCode( $snippet );
			$snippet->desc .= ' ' . __( 'You can remove it, or edit it to add your own content.', 'my-custom-php' );
			save_snippet( $snippet );
		}
	}
}
