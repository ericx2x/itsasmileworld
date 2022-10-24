<?php

/**
 * HTML code for the Manage Snippets page
 *
 * @package MyCustomPhp
 * @subpackage Views
 *
 * @var MyCustomPhp_Manage_Menu $this
 */

/* Bail if accessed directly */
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

?>

<div class="wrap mycc-content">
	<h1><?php
		esc_html_e( 'My Custom PHP', 'my-custom-php' );

		printf( '<a href="%2$s" class="page-title-action add-new-h2">%1$s</a>',
			esc_html_x( 'Add New', 'snippet', 'my-custom-php' ),
			custom_php()->get_menu_url( 'choice' )
		);

		printf( '<a href="%2$s" class="page-title-action">%1$s</a>',
			esc_html_x( 'Import', 'snippets', 'my-custom-php' ),
			custom_php()->get_menu_url( 'import' )
		);

		if ( custom_php()->admin->is_compact_menu() && isset( $admin->menus['settings'] ) ) {
			printf( '<a href="%2$s" class="page-title-action">%1$s</a>',
				esc_html_x( 'Settings', 'snippets', 'my-custom-php' ),
				custom_php()->get_menu_url( 'settings' )
			);
		}

		$this->list_table->search_notice();
		?></h1>

	<?php $this->list_table->views(); ?>

	<form method="get" action="">
		<?php
		$this->list_table->required_form_fields( 'search_box' );
		$this->list_table->search_box( __( 'Search custom PHP', 'my-custom-php' ), 'search_id' );
		?>
	</form>
	<form method="post" action="">
		<input type="hidden" id="custom_php_ajax_nonce" value="<?php echo esc_attr( wp_create_nonce( 'custom_php_manage' ) ); ?>">

		<?php
		$this->list_table->required_form_fields();
		$this->list_table->display();
		?>
	</form>

	<?php do_action( 'custom_php/admin/manage' ); ?>
</div>
