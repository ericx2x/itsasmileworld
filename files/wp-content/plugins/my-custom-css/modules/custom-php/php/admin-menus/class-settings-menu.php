<?php

/**
 * This class handles the settings admin menu
 * @since 2.4.0
 * @package MyCustomPhp
 */
class MyCustomPhp_Settings_Menu extends MyCustomPhp_Admin_Menu {

	/**
	 * Constructor
	 */
	public function __construct() {

		parent::__construct( 'settings',
			_x( 'Settings', 'menu label', 'my-custom-php' ),
			__( 'Snippets Settings', 'my-custom-php' )
		);
	}

	/**
	 * Executed when the admin page is loaded
	 */
	public function load() {
		parent::load();

		if ( isset( $_GET['reset_settings'] ) && $_GET['reset_settings'] ) {

			if ( custom_php_unified_settings() ) {
				delete_site_option( 'custom_php_settings' );
			} else {
				delete_option( 'custom_php_settings' );
			}

			add_settings_error( 'my-custom-php-settings-notices', 'settings_reset', __( 'All settings have been reset to their defaults.' ), 'updated' );
			set_transient( 'settings_errors', get_settings_errors(), 30 );

			wp_redirect( esc_url_raw( add_query_arg( 'settings-updated', true, remove_query_arg( 'reset_settings' ) ) ) );
			exit;
		}

		if ( is_network_admin() ) {

			if ( custom_php_unified_settings() ) {
				$this->update_network_options();
			} else {
				wp_redirect( custom_php()->get_menu_url( 'settings', 'admin' ) );
				exit;
			}
		}
	}

	/**
	 * Enqueue the stylesheet for the settings menu
	 */
	public function enqueue_assets() {
		$plugin = custom_php();

		wp_enqueue_style(
			'my-custom-php-edit',
			plugins_url( 'css/min/settings.css', $plugin->file ),
			array(), $plugin->version
		);
	}

	/**
	 * Render the admin screen
	 */
	public function render() {
		$update_url = is_network_admin() ? add_query_arg( 'update_site_option', true ) : admin_url( 'options.php' );

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Settings', 'my-custom-php' ); ?></h1>

			<?php settings_errors( 'my-custom-php-settings-notices' ); ?>

			<form action="<?php echo esc_url( $update_url ); ?>" method="post">
				<?php

				settings_fields( 'my-custom-php' );
				do_settings_sections( 'my-custom-php' );

				?>
				<p class="submit" style="max-width: 1020px;">
					<?php submit_button( null, 'primary', 'submit', false ); ?>

					<a class="button button-secondary" style="float: right;"
					   href="<?php echo esc_url( add_query_arg( 'reset_settings', true ) ); ?>">
						<?php esc_html_e( 'Reset to Default', 'my-custom-php' ); ?>
					</a>
				</p>
			</form>
		</div>
		<?php
	}

	/**
	 * Fill in for the Settings API in the Network Admin
	 */
	function update_network_options() {

		/* Ensure the settings have been saved */
		if ( ! isset( $_GET['update_site_option'], $_POST['custom_php_settings'] ) || ! $_GET['update_site_option'] ) {
			return;
		}

		check_admin_referer( 'my-custom-php-options' );

		/* Retrieve the saved options and save them to the database */
		$value = wp_unslash( $_POST['custom_php_settings'] );
		update_site_option( 'custom_php_settings', $value );

		/* Add an updated notice */
		if ( ! count( get_settings_errors() ) ) {
			add_settings_error( 'general', 'settings_updated', __( 'Settings saved.' ), 'updated' );
		}
		set_transient( 'settings_errors', get_settings_errors(), 30 );

		/* Redirect back to the settings menu */
		$goback = add_query_arg( 'settings-updated', 'true', remove_query_arg( 'update_site_option', wp_get_referer() ) );
		wp_redirect( $goback );
		exit;
	}
}
