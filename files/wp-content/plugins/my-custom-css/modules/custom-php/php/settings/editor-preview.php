<?php

/**
 * This file handles the editor preview setting
 *
 * @since 2.0
 * @package MyCustomPhp
 */

/**
 * Load the CSS and JavaScript for the editor preview field
 *
 * @param string $hook The current page hook
 */
function custom_php_editor_settings_preview_assets( $hook ) {
	$plugin = custom_php();

	// Only load on the settings page
	if ( $plugin->get_menu_hook( 'settings' ) !== $hook ) {
		return;
	}

	// Enqueue scripts for the editor preview
	custom_php_enqueue_editor();

	// Enqueue all editor themes
	$themes = custom_php_get_available_themes();

	foreach ( $themes as $theme ) {

		wp_enqueue_style(
			'my-custom-php-editor-theme-' . $theme,
			plugins_url( "css/min/editor-themes/$theme.css", $plugin->file ),
			array( 'my-custom-php-editor' ), $plugin->version
		);
	}

	// Enqueue the menu scripts
	wp_enqueue_script(
		'my-custom-php-settings-menu',
		plugins_url( 'js/min/settings.js', $plugin->file ),
		array( 'my-custom-php-editor' ), $plugin->version, true
	);

	// Extract the CodeMirror-specific editor settings
	$setting_fields = custom_php_get_settings_fields();
	$editor_fields = array();

	foreach ( $setting_fields['editor'] as $name => $field ) {
		if ( empty( $field['codemirror'] ) ) {
			continue;
		}

		$editor_fields[] = array(
			'name' => $name,
			'type' => $field['type'],
			'codemirror' => addslashes( $field['codemirror'] ),
		);
	}

	// Pass the saved options to the external JavaScript file
	$inline_script = 'var custom_php_editor_atts = ' . custom_php_get_editor_atts( array(), true ) . ';';
	$inline_script .= "\n" . 'var custom_php_editor_settings = ' . wp_json_encode( $editor_fields ) . ';';

	wp_add_inline_script( 'my-custom-php-settings-menu', $inline_script, 'before' );
}

add_action( 'admin_enqueue_scripts', 'custom_php_editor_settings_preview_assets' );

/**
 * Render a theme select field
 *
 * @param array $atts
 */
function custom_php_codemirror_theme_select_field( $atts ) {

	$saved_value = custom_php_get_setting( $atts['section'], $atts['id'] );

	echo '<select name="custom_php_settings[editor][theme]">';
	echo '<option value="default"' . selected( 'default', $saved_value, false ) . '>Default</option>';

	// print a dropdown entry for each theme
	foreach ( custom_php_get_available_themes() as $theme ) {

		// skip mobile themes
		if ( 'ambiance-mobile' === $theme ) {
			continue;
		}

		printf(
			'<option value="%s"%s>%s</option>',
			$theme,
			selected( $theme, $saved_value, false ),
			ucwords( str_replace( '-', ' ', $theme ) )
		);
	}

	echo '</select>';
}

/**
 * Render the editor preview setting
 */
function custom_php_settings_editor_preview() {
	echo '<div id="custom_php_editor_preview"></div>';
}
