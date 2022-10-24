<?php

/**
 * HTML code for the Import Snippets page
 *
 * @package MyCustomPhp
 * @subpackage Views
 */

/* Bail if accessed directly */
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

$max_size_bytes = apply_filters( 'import_upload_size_limit', wp_max_upload_size() );

?>
<div class="wrap">
	<h1><?php _e( 'Import PHP code', 'my-custom-php' );

		$admin = custom_php()->admin;

		if ( $admin->is_compact_menu() ) {

			printf( '<a href="%2$s" class="page-title-action">%1$s</a>',
				esc_html_x( 'Manage', 'snippets', 'my-custom-php' ),
				custom_php()->get_menu_url()
			);

			printf( '<a href="%2$s" class="page-title-action">%1$s</a>',
				esc_html_x( 'Add New', 'snippet', 'my-custom-php' ),
				custom_php()->get_menu_url( 'add' )
			);

			if ( isset( $admin->menus['settings'] ) ) {
				printf( '<a href="%2$s" class="page-title-action">%1$s</a>',
					esc_html_x( 'Settings', 'snippets', 'my-custom-php' ),
					custom_php()->get_menu_url( 'settings' )
				);
			}
		}

	?></h1>

	<div class="narrow">

		<p><?php _e( 'Upload one or more PHP Code export files and the snippets will be imported.', 'my-custom-php' ); ?></p>

		<p><?php
			printf(
				/* translators: %s: link to snippets admin menu */
				__( 'Afterwards, you will need to visit the <a href="%s">All Snippets</a> page to activate the imported snippets.', 'my-custom-php' ),
				custom_php()->get_menu_url( 'manage' )
			); ?></p>


		<form enctype="multipart/form-data" id="import-upload-form" method="post" class="wp-upload-form" name="custom_php_import">

			<h2><?php _e( 'Duplicate Snippets', 'my-custom-php' ); ?></h2>

			<p class="description">
				<?php esc_html_e( 'What should happen if an existing snippet is found with an identical name to an imported snippet?', 'my-custom-php' ); ?>
			</p>

			<fieldset>
				<p>
					<label>
						<input type="radio" name="duplicate_action" value="ignore" checked="checked">
						<?php esc_html_e( 'Ignore any duplicate snippets: import all snippets from the file regardless and leave all existing snippets unchanged.', 'my-custom-php' ); ?>
					</label>
				</p>

				<p>
					<label>
						<input type="radio" name="duplicate_action" value="replace">
						<?php esc_html_e( 'Replace any existing snippets with a newly imported snippet of the same name.', 'my-custom-php' ); ?>
					</label>
				</p>

				<p>
					<label>
						<input type="radio" name="duplicate_action" value="skip">
						<?php esc_html_e( 'Do not import any duplicate snippets; leave all existing snippets unchanged.', 'my-custom-php' ); ?>
					</label>
				</p>
			</fieldset>

			<h2><?php _e( 'Upload Files', 'my-custom-php' ); ?></h2>

			<p class="description">
				<?php _e( 'Choose one or more PHP Code (.xml or .json) files to upload, then click "Upload files and import".', 'my-custom-php' ); ?>
			</p>

			<fieldset>
				<p>
					<label for="upload"><?php esc_html_e( 'Choose files from your computer:', 'my-custom-php' ); ?></label>
					<?php printf(
						/* translators: %s: size in bytes */
						esc_html__( '(Maximum size: %s)', 'my-custom-php' ),
						size_format( $max_size_bytes )
					); ?>
					<input type="file" id="upload" name="custom_php_import_files[]" size="25" accept="application/json,.json,text/xml" multiple="multiple">
					<input type="hidden" name="action" value="save">
					<input type="hidden" name="max_file_size" value="<?php echo esc_attr( $max_size_bytes ); ?>">
				</p>
			</fieldset>

			<?php
			do_action( 'custom_php/admin/import_form' );
			submit_button( __( 'Upload files and import', 'my-custom-php' ) );
			?>
		</form>
	</div>
</div>
