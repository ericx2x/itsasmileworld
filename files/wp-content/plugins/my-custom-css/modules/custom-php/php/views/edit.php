<?php

/**
 * HTML code for the Add New/Edit Snippet page
 *
 * @package MyCustomPhp
 * @subpackage Views
 */

/* Bail if accessed directly */
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

$table = custom_php()->db->get_table_name();
$edit_id = isset( $_REQUEST['id'] ) && intval( $_REQUEST['id'] ) ? absint( $_REQUEST['id'] ) : 0;
$snippet = get_snippet( $edit_id );

$classes = array();

if ( $edit_id ) {
	$classes[] = ( $snippet->active ? '' : 'in' ) . 'active-snippet';
} else {
	$classes[] = 'new-snippet';
}

?>
<div class="wrap">
	<h1><?php

		if ( $edit_id ) {
			esc_html_e( 'Edit PHP code', 'my-custom-php' );
			printf( ' <a href="%1$s" class="page-title-action add-new-h2">%2$s</a>',
                custom_php()->get_menu_url( 'choice' ),
				esc_html_x( 'Add New', 'snippet', 'my-custom-php' )
			);
		} else {
			esc_html_e( 'Add new PHP code', 'my-custom-php' );
		}

		if ( custom_php()->admin->is_compact_menu() ) {

			printf( '<a href="%2$s" class="page-title-action">%1$s</a>',
				esc_html_x( 'Manage', 'snippets', 'my-custom-php' ),
				custom_php()->get_menu_url()
			);

			printf( '<a href="%2$s" class="page-title-action">%1$s</a>',
				esc_html_x( 'Import', 'snippets', 'my-custom-php' ),
				custom_php()->get_menu_url( 'import' )
			);

			if ( isset( $admin->menus['settings'] ) ) {

				printf( '<a href="%2$s" class="page-title-action">%1$s</a>',
					esc_html_x( 'Settings', 'snippets', 'my-custom-php' ),
					custom_php()->get_menu_url( 'settings' )
				);
			}
		}

		?></h1>

	<form method="post" id="snippet-form" action="" style="margin-top: 10px;" class="<?php echo implode( ' ', $classes ); ?>">
		<?php
		/* Output the hidden fields */

		if ( 0 !== $snippet->id ) {
			printf( '<input type="hidden" name="snippet_id" value="%d" />', $snippet->id );
		}

		printf( '<input type="hidden" name="snippet_active" value="%d" />', $snippet->active );
        if(isset($_GET['code_type']) and $_GET['code_type'] == 'shortcode'){
            ?>
            <input type="hidden" name="code_type" value="shortcode">
            <?
        }else{
            ?>
            <input type="hidden" name="code_type" value="<?=$snippet->code_type?>">
            <?
        }
		?>
		<div id="titlediv">
			<div id="titlewrap">
				<label for="title" style="display: none;"><?php _e( 'Name', 'my-custom-php' ); ?></label>
				<input id="title" type="text" autocomplete="off" name="snippet_name" value="<?php echo esc_attr( $snippet->name ); ?>" placeholder="<?php _e( 'Enter title here', 'my-custom-php' ); ?>" />
			</div>
		</div>

        <?php if(isset($_GET['code_type']) and $_GET['code_type'] == 'shortcode'){
            if(!isset($_GET['id'])){?>
                <p class="mycc_shortcode_placeholder">
                <?= __('Shortcode for your snippet will be displayed here after post saving', 'my-custom-php' )?>
                </p>
            <? } ?>
        <?php } ?>
        <? if($snippet->code_type == 'shortcode' and 0 !== $snippet->id ){ ?>
            <div class="mycc_shortcode_example">
                <div class="mycc_shortcode_code">[my_custom_php id="<?=(int)$_GET['id'];?>"]</div>
                <div class="mycc_shortcode_text">Insert this shortcode to post content.</div>
            </div>
        <? } ?>

		<h2>
			<label for="snippet_code">
				<?php _e( 'Code', 'my-custom-php' ); ?>
			</label>
		</h2>

		<textarea id="snippet_code" name="snippet_code" rows="200" spellcheck="false" style="font-family: monospace; width: 100%;"><?php
			echo esc_textarea( $snippet->code );
			?></textarea>

		<?php
		/* Allow plugins to add fields and content to this page */
		do_action( 'custom_php/admin/single', $snippet );

		/* Add a nonce for security */
		wp_nonce_field( 'save_snippet' );

		?>

		<p class="submit">
			<?php

			/* Make the 'Save and Activate' button the default if the setting is enabled */

			if ( 'single-use' === $snippet->scope ) {

				submit_button( null, 'primary', 'save_snippet', false );

				submit_button( __( 'Save Changes and Execute Once', 'my-custom-php' ), 'secondary', 'save_snippet_execute', false );

			} elseif ( $snippet->shared_network && is_network_admin() ) {

				submit_button( null, 'primary', 'save_snippet', false );

			} elseif ( ! $snippet->active && custom_php_get_setting( 'general', 'activate_by_default' ) ) {

				submit_button(
					__( 'Save Changes and Activate', 'my-custom-php' ),
					'primary', 'save_snippet_activate', false
				);

				submit_button( null, 'secondary', 'save_snippet', false );

			} else {

				/* Save Snippet button */
				submit_button( null, 'primary', 'save_snippet', false );

				/* Save Snippet and Activate/Deactivate button */
				if ( ! $snippet->active ) {
					submit_button(
						__( 'Save Changes and Activate', 'my-custom-php' ),
						'secondary', 'save_snippet_activate', false
					);

				} else {
					submit_button(
						__( 'Save Changes and Deactivate', 'my-custom-php' ),
						'secondary', 'save_snippet_deactivate', false
					);
				}
			}

			if ( 0 !== $snippet->id ) {

				/* Download button */

				if ( apply_filters( 'custom_php/enable_downloads', true ) ) {
					submit_button( __( 'Download', 'my-custom-php' ), 'secondary', 'download_snippet', false );
				}

				/* Export button */

				submit_button( __( 'Export', 'my-custom-php' ), 'secondary', 'export_snippet', false );

				/* Delete button */

				$confirm_delete_js = esc_js(
					sprintf(
						'return confirm("%s");',
						__( 'You are about to permanently delete this snippet.', 'my-custom-php' ) . "\n" .
						__( "'Cancel' to stop, 'OK' to delete.", 'my-custom-php' )
					)
				);

				submit_button(
					__( 'Delete', 'my-custom-php' ),
					'secondary', 'delete_snippet', false,
					sprintf( 'onclick="%s"', $confirm_delete_js )
				);
			}

			?>
		</p>
	</form>
</div>
