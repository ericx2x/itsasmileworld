<?php

/**
 * This file holds all of the content for the contextual help screens
 * @package MyCustomPhp
 */
class MyCustomPhp_Contextual_Help {

	/**
	 * @var WP_Screen
	 */
	public $screen;

	/**
	 * @param string $screen_name
	 */
	function __construct( $screen_name ) {
		$this->screen_name = $screen_name;
	}

	/**
	 * Load the contextual help
	 */
	public function load() {
		$this->screen = get_current_screen();

		if ( method_exists( $this, "load_{$this->screen_name}_help" ) ) {
			call_user_func( array( $this, "load_{$this->screen_name}_help" ) );
		}

	}



	/**
	 * Register and handle the help tabs for the manage snippets admin page
	 */
	private function load_manage_help() {

		$this->screen->add_help_tab( array(
			'id'      => 'overview',
			'title'   => __( 'Overview', 'my-custom-php' ),
			'content' => '<p>' . __( 'Snippets are similar to plugins - they both extend and expand the functionality of WordPress. Snippets are more light-weight, just a few lines of code, and do not put as much load on your server. Here you can manage your existing snippets and perform tasks on them such as activating, deactivating, deleting and exporting.', 'my-custom-php' ) . '</p>',
		) );

		$this->screen->add_help_tab( array(
			'id'      => 'safe-mode',
			'title'   => __( 'Safe Mode', 'my-custom-php' ),
			'content' =>
				'<p>' . __( 'Be sure to check your snippets for errors before you activate them, as a faulty snippet could bring your whole blog down. If your site starts doing strange things, deactivate all your snippets and activate them one at a time.', 'my-custom-php' ) . '</p>' .
				'<p>' . __( "If something goes wrong with a snippet and you can't use WordPress, you can cause all snippets to stop executing by adding <code>define('MY_CUSTOM_PHP_SAFE_MODE', true);</code> to your <code>wp-config.php</code> file. After you have deactivated the offending snippet, you can turn off safe mode by removing this line or replacing <strong>true</strong> with <strong>false</strong>.", 'my-custom-php' ) . '</p>',
		) );

		$this->screen->add_help_tab( array(
			'id'      => 'uninstall',
			'title'   => __( 'Uninstall', 'my-custom-php' ),
			'content' =>
				/* translators: 1: snippets table name, 2: My Custom PHP plugin directory */
				'<p>' . sprintf( __( 'When you delete My Custom PHP through the Plugins menu in WordPress it will clear up the <code>%1$s</code> table and a few other bits of data stored in the database. If you want to keep this data (ie: you are only temporally uninstalling My Custom PHP) then remove the <code>%2$s</code> folder using FTP.', 'my-custom-php' ), custom_php()->db->get_table_name(), dirname( MY_CUSTOM_PHP_FILE ) ) .
				'<p>' . __( "Even if you're sure that you don't want to use My Custom PHP ever again on this WordPress installation, you may want to use the export feature to back up your snippets.", 'my-custom-php' ) . '</p>',
		) );
	}

	/**
	 * Register and handle the help tabs for the single snippet admin page
	 */
	private function load_edit_help() {

		$this->screen->add_help_tab( array(
			'id'      => 'overview',
			'title'   => __( 'Overview', 'my-custom-php' ),
			'content' => '<p>' . __( 'Snippets are similar to plugins - they both extend and expand the functionality of WordPress. Snippets are more light-weight, just a few lines of code, and do not put as much load on your server. Here you can add a new snippet, or edit an existing one.', 'my-custom-php' ) . '</p>',
		) );

		$snippet_host_links = array(

		);

		$snippet_host_list = '';
		foreach ( $snippet_host_links as $title => $link ) {
			$snippet_host_list .= sprintf( '<li><a href="%s">%s</a></li>', esc_url( $link ), esc_html( $title ) );
		}



		$this->screen->add_help_tab( array(
			'id'      => 'adding',
			'title'   => __( 'Adding Snippets', 'my-custom-php' ),
			'content' =>
				'<p>' . __( 'You need to fill out the name and code fields for your snippet to be added. While the description field will add more information about how your snippet works, what is does and where you found it, it is completely optional.', 'my-custom-php' ) . '</p>' .
				'<p>' . __( 'Please be sure to check that your snippet is valid PHP code and will not produce errors before adding it through this page. While doing so will not become active straight away, it will help to minimise the chance of a faulty snippet becoming active on your site.', 'my-custom-php' ) . '</p>',
		) );
	}

	/**
	 * Register and handle the help tabs for the import snippets admin page
	 */
	private function load_import_help() {
		$manage_url = custom_php()->get_menu_url( 'manage' );

		$this->screen->add_help_tab( array(
			'id'      => 'overview',
			'title'   => __( 'Overview', 'my-custom-php' ),
			'content' => '<p>' . __( 'Snippets are similar to plugins - they both extend and expand the functionality of WordPress. Snippets are more light-weight, just a few lines of code, and do not put as much load on your server. Here you can load php code from a (.xml) import file into the database with your existing snippets.', 'my-custom-php' ) . '</p>',
		) );

		$this->screen->add_help_tab( array(
			'id'      => 'import',
			'title'   => __( 'Importing', 'my-custom-php' ),
			'content' =>
				'<p>' . __( 'You can load your snippets from a code snippets (.xml) export file using this page.', 'my-custom-php' ) .
				/* translators: %s: URL to Snippets admin menu */
				sprintf( __( 'Snippets will be added to the database along with your existing snippets. Regardless of whether the snippets were active on the previous site, imported snippets are always inactive until activated using the <a href="%s">All Snippets</a> page.</p>', 'my-custom-php' ), $manage_url ) . '</p>',
		) );

		$this->screen->add_help_tab( array(
			'id'      => 'export',
			'title'   => __( 'Exporting', 'my-custom-php' ),
			/* translators: %s: URL to Manage Snippets admin menu */
			'content' => '<p>' . sprintf( __( 'You can save your snippets to a file (.xml) export file using the <a href="%s">Manage Snippets</a> page.', 'my-custom-php' ), $manage_url ) . '</p>',
		) );
	}
}
