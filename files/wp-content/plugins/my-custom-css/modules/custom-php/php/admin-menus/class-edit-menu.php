<?php

/**
 * This class handles the add/edit menu
 */
class MyCustomPhp_Edit_Menu extends MyCustomPhp_Admin_Menu {

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct( 'edit',
			__( 'Edit PHP', 'my-custom-php' ),
			__( 'Edit PHP code', 'my-custom-php' )
		);
	}

	/**
	 * Register action and filter hooks
	 */
	public function run() {
		parent::run();
		$this->remove_debug_bar_codemirror();
	}

	/**
	 * Register the admin menu
	 */
	public function register() {

		/* Add edit menu if we are currently editing a snippet */
		if ( isset( $_REQUEST['page'] ) && custom_php()->get_menu_slug( 'edit' ) === $_REQUEST['page'] ) {
			parent::register();

		}
		if( isset( $_REQUEST['page'] ) && $_REQUEST['page'] == 'add-snippet'){
            /* Add New Snippet menu */
            $this->add_menu(
                custom_php()->get_menu_slug( 'add' ),
                _x( 'Edit PHP', 'menu label', 'my-custom-php' ),
                __( 'Edit PHP code', 'my-custom-php' )
            );
        }


	}

	/**
	 * Executed when the menu is loaded
	 */
	public function load() {
		parent::load();

		/* Don't allow visiting the edit snippet page without a valid ID */
		if ( custom_php()->get_menu_slug( 'edit' ) === $_REQUEST['page'] ) {
			if ( ! isset( $_REQUEST['id'] ) || 0 == $_REQUEST['id'] ) {
				wp_redirect( custom_php()->get_menu_url( 'add' ) );
				exit;
			}
		}

		/* Load the contextual help tabs */
		$contextual_help = new MyCustomPhp_Contextual_Help( 'edit' );
		$contextual_help->load();

		/* Register action hooks */
		if ( custom_php_get_setting( 'general', 'enable_description' ) ) {
			add_action( 'custom_php/admin/single', array( $this, 'render_description_editor' ), 9 );
		}

		if ( custom_php_get_setting( 'general', 'enable_tags' ) ) {
			add_action( 'custom_php/admin/single', array( $this, 'render_tags_editor' ) );
		}

		add_action( 'custom_php/admin/single', array( $this, 'render_priority_setting' ), 0 );

		if ( custom_php_get_setting( 'general', 'snippet_scope_enabled' ) ) {
			add_action( 'custom_php/admin/single', array( $this, 'render_scope_setting' ), 1 );
		}

		if ( is_network_admin() ) {
			add_action( 'custom_php/admin/single', array( $this, 'render_multisite_sharing_setting' ), 1 );
		}

		$this->process_actions();
	}

	/**
	 * Process data sent from the edit page
	 */
	private function process_actions() {

		/* Check for a valid nonce */
		if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'save_snippet' ) ) {
			return;
		}

		if ( isset( $_POST['save_snippet'] ) || isset( $_POST['save_snippet_execute'] ) ||
			isset( $_POST['save_snippet_activate'] ) || isset( $_POST['save_snippet_deactivate'] ) ) {
			$this->save_posted_snippet();
		}

		if ( isset( $_POST['snippet_id'] ) ) {

			/* Delete the snippet if the button was clicked */
			if ( isset( $_POST['delete_snippet'] ) ) {
				delete_snippet( $_POST['snippet_id'] );
				wp_redirect( add_query_arg( 'result', 'delete', custom_php()->get_menu_url( 'manage' ) ) );
				exit;
			}

			/* Export the snippet if the button was clicked */
			if ( isset( $_POST['export_snippet'] ) ) {
				export_snippets( array( $_POST['snippet_id'] ) );
			}

			/* Download the snippet if the button was clicked */
			if ( isset( $_POST['download_snippet'] ) ) {
				download_snippets( array( $_POST['snippet_id'] ) );
			}
		}
	}

	/**
	 * Remove the sharing status from a network snippet
	 *
	 * @param int $snippet_id
	 */
	private function unshare_network_snippet( $snippet_id ) {
		$shared_snippets = get_site_option( 'shared_network_snippets', array() );

		if ( ! in_array( $snippet_id, $shared_snippets ) ) {
			return;
		}

		/* Remove the snippet ID from the array */
		$shared_snippets = array_diff( $shared_snippets, array( $snippet_id ) );
		update_site_option( 'shared_network_snippets', array_values( $shared_snippets ) );

		/* Deactivate on all sites */
		global $wpdb;
		if ( $sites = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" ) ) {

			foreach ( $sites as $site ) {
				switch_to_blog( $site );
				$active_shared_snippets = get_option( 'active_shared_network_snippets' );

				if ( is_array( $active_shared_snippets ) ) {
					$active_shared_snippets = array_diff( $active_shared_snippets, array( $snippet_id ) );
					update_option( 'active_shared_network_snippets', $active_shared_snippets );
				}
			}

			restore_current_blog();
		}
	}

	private function code_error_callback( $out ) {
		$error = error_get_last();

		if ( is_null( $error ) ) {
			return $out;
		}

		$m = '<h3>' . __( "Don't Panic", 'my-custom-php' ) . '</h3>';
		/* translators: %d: line where error was produced */
		$m .= '<p>' . sprintf( __( 'The code snippet you are trying to save produced a fatal error on line %d:', 'my-custom-php' ), $error['line'] ) . '</p>';
		$m .= '<strong>' . $error['message'] . '</strong>';
		$m .= '<p>' . __( 'The previous version of the snippet is unchanged, and the rest of this site should be functioning normally as before.', 'my-custom-php' ) . '</p>';
		$m .= '<p>' . __( 'Please use the back button in your browser to return to the previous page and try to fix the code error.', 'my-custom-php' );
		$m .= ' ' . __( 'If you prefer, you can close this page and discard the changes you just made. No changes will be made to this site.', 'my-custom-php' ) . '</p>';

		return $m;
	}

	/**
	 * Validate the snippet code before saving to database
	 *
	 * @param MyPHPCode $snippet
	 *
	 * @return bool true if code produces errors
	 */
	private function validate_code(MyPHPCode $snippet ) {

		if ( empty( $snippet->code ) ) {
			return false;
		}

		ob_start( array( $this, 'code_error_callback' ) );

		$result = eval( $snippet->code );

		ob_end_clean();

		do_action( 'custom_php/after_execute_snippet', $snippet->id, $snippet->code, $result );

		return false === $result;
	}

	/**
	 * Save the posted snippet data to the database and redirect
	 */
	private function save_posted_snippet() {

		/* Build snippet object from fields with 'snippet_' prefix */
		$snippet = new MyPHPCode();

		foreach ( $_POST as $field => $value ) {
			if ( 'snippet_' === substr( $field, 0, 8 ) ) {

				/* Remove the 'snippet_' prefix from field name and set it on the object */
				$snippet->set_field( substr( $field, 8 ), stripslashes( $value ) );
			}
		}

		if ( isset( $_POST['save_snippet_execute'] ) && 'single-use' !== $snippet->scope ) {
			unset( $_POST['save_snippet_execute'] );
			$_POST['save_snippet'] = 'yes';
		}

		/* Activate or deactivate the snippet before saving if we clicked the button */

		if ( isset( $_POST['save_snippet_execute'] ) ) {
			$snippet->active = 1;
		} elseif ( isset( $_POST['snippet_sharing'] ) && 'on' === $_POST['snippet_sharing'] ) {
			// Shared network snippets cannot be network activated
			$snippet->active = 0;
			unset( $_POST['save_snippet_activate'], $_POST['save_snippet_deactivate'] );
		} elseif ( isset( $_POST['save_snippet_activate'] ) ) {
			$snippet->active = 1;
		} elseif ( isset( $_POST['save_snippet_deactivate'] ) ) {
			$snippet->active = 0;
		}
		if(isset($_POST['code_type']) and in_array($_POST['code_type'], array('default', 'shortcode'))){
		    $snippet->code_type = $_POST['code_type'];
        }

		/* Deactivate snippet if code contains errors */
		if ( $snippet->active && 'single-use' !== $snippet->scope ) {
			if ( $code_error = $this->validate_code( $snippet ) ) {
				$snippet->active = 0;
			}
		}

		/* Save the snippet to the database */
		$snippet_id = save_snippet( $snippet );

		/* Update the shared network snippets if necessary */
		if ( $snippet_id && is_network_admin() ) {

			if ( isset( $_POST['snippet_sharing'] ) && 'on' === $_POST['snippet_sharing'] ) {
				$shared_snippets = get_site_option( 'shared_network_snippets', array() );

				/* Add the snippet ID to the array if it isn't already */
				if ( ! in_array( $snippet_id, $shared_snippets ) ) {
					$shared_snippets[] = $snippet_id;
					update_site_option( 'shared_network_snippets', array_values( $shared_snippets ) );
				}
			} else {
				$this->unshare_network_snippet( $snippet_id );
			}
		}

		/* If the saved snippet ID is invalid, display an error message */
		if ( ! $snippet_id || $snippet_id < 1 ) {
			/* An error occurred */
			wp_redirect( add_query_arg( 'result', 'save-error', custom_php()->get_menu_url( 'add' ) ) );
			exit;
		}

		/* Display message if a parse error occurred */
		if ( isset( $code_error ) && $code_error ) {
			wp_redirect( add_query_arg(
				array( 'id' => $snippet_id, 'result' => 'code-error' ),
				custom_php()->get_menu_url( 'edit' )
			) );
			exit;
		}

		/* Set the result depending on if the snippet was just added */
		$result = isset( $_POST['snippet_id'] ) ? 'updated' : 'added';

		/* Append a suffix if the snippet was activated or deactivated */
		if ( isset( $_POST['save_snippet_activate'] ) ) {
			$result .= '-and-activated';
		} elseif ( isset( $_POST['save_snippet_deactivate'] ) ) {
			$result .= '-and-deactivated';
		} elseif ( isset( $_POST['save_snippet_execute'] ) ) {
			$result .= '-and-executed';
		}

		/* Redirect to edit snippet page */
		$redirect_uri = add_query_arg(
			array( 'id' => $snippet_id, 'result' => $result ),
			custom_php()->get_menu_url( 'edit' )
		);

		if ( isset( $_POST['snippet_editor_cursor_line'], $_POST['snippet_editor_cursor_ch'] ) &&
			is_numeric( $_POST['snippet_editor_cursor_line'] ) && is_numeric( $_POST['snippet_editor_cursor_ch'] ) ) {
			$redirect_uri = add_query_arg( 'cursor_line', intval( $_POST['snippet_editor_cursor_line'] ), $redirect_uri );
			$redirect_uri = add_query_arg( 'cursor_ch', intval( $_POST['snippet_editor_cursor_ch'] ), $redirect_uri );
		}

		wp_redirect( esc_url_raw( $redirect_uri ) );
		exit;
	}

	/**
	 * Add a description editor to the single snippet page
	 *
	 * @param MyPHPCode $snippet The snippet being used for this page
	 */
	function render_description_editor(MyPHPCode $snippet ) {
		$settings = custom_php_get_settings();
		$settings = $settings['description_editor'];
		$heading = __( 'Description', 'my-custom-php' );

		/* Hack to remove space between heading and editor tabs */
		if ( ! $settings['media_buttons'] && 'false' !== get_user_option( 'rich_editing' ) ) {
			$heading = "<div>$heading</div>";
		}

		echo '<h2><label for="snippet_description">', $heading, '</label></h2>';

		remove_editor_styles(); // stop custom theme styling interfering with the editor

		wp_editor(
			$snippet->desc,
			'description',
			apply_filters( 'custom_php/admin/description_editor_settings', array(
				'textarea_name' => 'snippet_description',
				'textarea_rows' => $settings['rows'],
				'teeny'         => ! $settings['use_full_mce'],
				'media_buttons' => $settings['media_buttons'],
			) )
		);
	}

	/**
	 * Render the interface for editing snippet tags
	 *
	 * @param MyPHPCode $snippet the snippet currently being edited
	 */
	function render_tags_editor(MyPHPCode $snippet ) {

		?>
		<h2 style="margin: 25px 0 10px;">
			<label for="snippet_tags" style="cursor: auto;">
				<?php esc_html_e( 'Tags', 'my-custom-php' ); ?>
			</label>
		</h2>

		<input type="text" id="snippet_tags" name="snippet_tags" style="width: 100%;"
		       placeholder="<?php esc_html_e( 'Enter a list of tags; separated by commas', 'my-custom-php' ); ?>"
		       value="<?php echo esc_attr( $snippet->tags_list ); ?>" />
		<?php
	}

	/**
	 * Render the snippet priority setting
	 *
	 * @param MyPHPCode $snippet the snippet currently being edited
	 */
	public function render_priority_setting(MyPHPCode $snippet ) {
	    if($snippet->code_type == 'shortcode'){ ?>
            <input name="snippet_priority" type="hidden"  value="<?php echo intval( $snippet->priority ); ?>">
        <?php
	    }else{
		?>
		<p class="snippet-priority"
		   title="<?php esc_attr_e( 'Snippets with a lower priority number will run before those with a higher number.', 'my-custom-php' ); ?>">
			<label for="snippet_priority"><?php esc_html_e( 'Priority', 'my-custom-php' ); ?></label>

			<input name="snippet_priority" type="number" id="snippet_priority" value="<?php echo intval( $snippet->priority ); ?>">
		</p>
		<?php
	    }
	}

	/**
	 * Render the snippet scope setting
	 *
	 * @param MyPHPCode $snippet the snippet currently being edited
	 */
	function render_scope_setting(MyPHPCode $snippet ) {

		$icons = MyPHPCode::get_scope_icons();

		$labels = array(
			'global'     => __( 'Run snippet everywhere', 'my-custom-php' ),
			'admin'      => __( 'Only run in administration area', 'my-custom-php' ),
			'front-end'  => __( 'Only run on site front-end', 'my-custom-php' ),
			'single-use' => __( 'Only run once', 'my-custom-php' ),
            'shortcode' => __('Run where there is a shortcode', 'my-custom-php')
		);

		if( (isset($_GET['code_type']) and $_GET['code_type'] == 'shortcode')
            or $snippet->code_type == 'shortcode'
        ){
            echo '<input type="hidden" name="snippet_scope" value="shortcode">';
        }else{
            echo '<h2 class="screen-reader-text">' . esc_html__( 'Scope', 'my-custom-php' ) . '</h2><p class="snippet-scope">';

            foreach (MyPHPCode::get_all_scopes() as $scope ) {
                if($scope == 'shortcode') continue;
                printf( '<label><input type="radio" name="snippet_scope" value="%s"', $scope );
                checked( $scope, $snippet->scope );
                printf( '> %s</label>',  esc_html( $labels[ $scope ] ) );
            }

            echo '</p>';
        }

	}

	/**
	 * Render the setting for shared network snippets
	 *
	 * @param object $snippet The snippet currently being edited
	 */
	function render_multisite_sharing_setting( $snippet ) {
		$shared_snippets = get_site_option( 'shared_network_snippets', array() );
		?>

		<div class="snippet-sharing-setting">
			<h2 class="screen-reader-text"><?php _e( 'Sharing Settings', 'my-custom-php' ); ?></h2>
			<label for="snippet_sharing">
				<input type="checkbox" name="snippet_sharing"
					<?php checked( in_array( $snippet->id, $shared_snippets ) ); ?>>
				<?php esc_html_e( 'Allow this snippet to be activated on individual sites on the network', 'my-custom-php' ); ?>
			</label>
		</div>

		<?php
	}

	/**
	 * Retrieve the first error in a snippet's code
	 *
	 * @param $snippet_id
	 *
	 * @return array|bool
	 */
	private function get_snippet_error( $snippet_id ) {

		if ( ! intval( $snippet_id ) ) {
			return false;
		}

		$snippet = get_snippet( intval( $snippet_id ) );

		if ( '' === $snippet->code ) {
			return false;
		}

		ob_start();
		$result = eval( $snippet->code );
		ob_end_clean();

		if ( false !== $result ) {
			return false;
		}

		$error = error_get_last();

		if ( is_null( $error ) ) {
			return false;
		}

		return $error;
	}

	/**
	 * Print the status and error messages
	 */
	protected function print_messages() {

		if ( ! isset( $_REQUEST['result'] ) ) {
			return;
		}

		$result = $_REQUEST['result'];

		if ( 'code-error' === $result ) {

			if ( isset( $_REQUEST['id'] ) && $error = $this->get_snippet_error( $_REQUEST['id'] ) ) {

				printf(
					'<div id="message" class="error fade"><p>%s</p><p><strong>%s</strong></p></div>',
					/* translators: %d: line of file where error originated */
					sprintf( __( 'The snippet has been deactivated due to an error on line %d:', 'my-custom-php' ), $error['line'] ),
					$error['message']
				);

			} else {
				echo '<div id="message" class="error fade"><p>', __( 'The snippet has been deactivated due to an error in the code.', 'my-custom-php' ), '</p></div>';
			}

			return;
		}

		if ( 'save-error' === $result ) {
			echo '<div id="message" class="error fade"><p>', __( 'An error occurred when saving the snippet.', 'my-custom-php' ), '</p></div>';

			return;
		}

		$messages = array(
			'added'                   => __( 'Snippet <strong>added</strong>.', 'my-custom-php' ),
			'updated'                 => __( 'Snippet <strong>updated</strong>.', 'my-custom-php' ),
			'added-and-activated'     => __( 'Snippet <strong>added</strong> and <strong>activated</strong>.', 'my-custom-php' ),
			'updated-and-executed'    => __( 'Snippet <strong>added</strong> and <strong>executed</strong>.', 'my-custom-php' ),
			'updated-and-activated'   => __( 'Snippet <strong>updated</strong> and <strong>activated</strong>.', 'my-custom-php' ),
			'updated-and-deactivated' => __( 'Snippet <strong>updated</strong> and <strong>deactivated</strong>.', 'my-custom-php' ),
		);

		if ( isset( $messages[ $result ] ) ) {
			echo '<div id="message" class="updated fade"><p>', $messages[ $result ], '</p></div>';
		}
	}

	/**
	 * Enqueue assets for the edit menu
	 */
	public function enqueue_assets() {
		$plugin = custom_php();
		$rtl = is_rtl() ? '-rtl' : '';

		custom_php_enqueue_editor();

		wp_enqueue_style(
			'my-custom-php-edit',
			plugins_url( "css/min/edit{$rtl}.css", $plugin->file ),
			array(), $plugin->version
		);

		$tags_enabled = custom_php_get_setting( 'general', 'enable_tags' );

		/* the tag-it library has a number of jQuery dependencies */
		$tagit_deps = array(
			'jquery', 'jquery-ui-core',
			'jquery-ui-widget', 'jquery-ui-position', 'jquery-ui-autocomplete',
			'jquery-effects-blind', 'jquery-effects-highlight',
		);

		wp_enqueue_script(
			'my-custom-php-edit-menu',
			plugins_url( 'js/min/edit.js', $plugin->file ),
			$tags_enabled ? $tagit_deps : array(),
			$plugin->version, true
		);

		$atts = custom_php_get_editor_atts( array(), true );
		$inline_script = 'var custom_php_editor_atts = ' . $atts . ';';

		if ( $tags_enabled ) {
			$snippet_tags = wp_json_encode( get_all_snippet_tags() );
			$inline_script .= "\n" . 'var custom_php_all_tags = ' . $snippet_tags . ';';
		}

		wp_add_inline_script( 'my-custom-php-edit-menu', $inline_script, 'before' );
	}

	/**
	 * Remove the old CodeMirror version used by the Debug Bar Console plugin
	 * that is messing up the snippet editor
	 */
	function remove_debug_bar_codemirror() {

		/* Try to discern if we are on the single snippet page as best as we can at this early time */
		if ( ! is_admin() || 'admin.php' !== $GLOBALS['pagenow'] ) {
			return;
		}

		if ( ! isset( $_GET['page'] ) || custom_php()->get_menu_slug( 'edit' ) !== $_GET['page'] && custom_php()->get_menu_slug( 'settings' ) !== $_GET['page'] ) {
			return;
		}

		remove_action( 'debug_bar_enqueue_scripts', 'debug_bar_console_scripts' );
	}
}
