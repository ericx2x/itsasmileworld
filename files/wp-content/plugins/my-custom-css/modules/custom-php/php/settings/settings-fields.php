<?php

/**
 * Retrieve the default setting values
 * @return array
 */
function custom_php_get_default_settings() {
	static $defaults;

	if ( isset( $defaults ) ) {
		return $defaults;
	}

	$defaults = array();

	foreach ( custom_php_get_settings_fields() as $section_id => $fields ) {
		$defaults[ $section_id ] = array();

		foreach ( $fields as $field_id => $field_atts ) {
			$defaults[ $section_id ][ $field_id ] = $field_atts['default'];
		}
	}

	return $defaults;
}

/**
 * Retrieve the settings fields
 * @return array
 */
function custom_php_get_settings_fields() {
	static $fields;

	if ( isset( $fields ) ) {
		return $fields;
	}

	$fields = array();

	$fields['general'] = array(
		'activate_by_default' => array(
			'name'    => __( 'Activate by Default', 'my-custom-php' ),
			'type'    => 'checkbox',
			'label'   => __( "Make the 'Save and Activate' button the default action when saving a snippet.", 'my-custom-php' ),
			'default' => true,
		),

		'snippet_scope_enabled' => array(
			'name'    => __( 'Enable Scope Selector', 'my-custom-php' ),
			'type'    => 'checkbox',
			'label'   => __( 'Enable the scope selector when editing a snippet', 'my-custom-php' ),
			'default' => true,
		),

		'enable_tags' => array(
			'name'    => __( 'Enable Snippet Tags', 'my-custom-php' ),
			'type'    => 'checkbox',
			'label'   => __( 'Show snippet tags on admin pages', 'my-custom-php' ),
			'default' => true,
		),

		'enable_description' => array(
			'name'    => __( 'Enable Snippet Descriptions', 'my-custom-php' ),
			'type'    => 'checkbox',
			'label'   => __( 'Show snippet descriptions on admin pages', 'my-custom-php' ),
			'default' => true,
		),

		'disable_prism' => array(
			'name'    => __( 'Disable Shortcode Syntax Highlighter', 'my-custom-php' ),
			'type'    => 'checkbox',
			'label'   => __( 'Disable the syntax highlighting for the [my_custom_php] shortcode on the front-end', 'my-custom-php' ),
			'default' => false,
		),

		'complete_uninstall' => array(
			'name'    => __( 'Complete Uninstall', 'my-custom-php' ),
			'type'    => 'checkbox',
			'label'   => sprintf(
				/* translators: %s: URL for Plugins admin menu */
				__( 'When the plugin is deleted from the <a href="%s">Plugins</a> menu, also delete all snippets and plugin settings.', 'my-custom-php' ),
				self_admin_url( 'plugins.php' )
			),
			'default' => false,
		),
	);

	if ( is_multisite() && ! is_main_site() ) {
		unset( $fields['general']['complete_uninstall'] );
	}

	/* Description Editor settings section */
	$fields['description_editor'] = array(

		'rows' => array(
			'name'    => __( 'Row Height', 'my-custom-php' ),
			'type'    => 'number',
			'label'   => __( 'rows', 'my-custom-php' ),
			'default' => 5,
			'min'     => 0,
		),

		'use_full_mce' => array(
			'name'    => __( 'Use Full Editor', 'my-custom-php' ),
			'type'    => 'checkbox',
			'label'   => __( 'Enable all features of the visual editor', 'my-custom-php' ),
			'default' => false,
		),

		'media_buttons' => array(
			'name'    => __( 'Media Buttons', 'my-custom-php' ),
			'type'    => 'checkbox',
			'label'   => __( 'Enable the add media buttons', 'my-custom-php' ),
			'default' => false,
		),
	);

	/* Code Editor settings section */

	$fields['editor'] = array(
		'theme' => array(
			'name'       => __( 'Theme', 'my-custom-php' ),
			'type'       => 'codemirror_theme_select',
			'default'    => 'default',
			'codemirror' => 'theme',
		),

		'indent_with_tabs' => array(
			'name'       => __( 'Indent With Tabs', 'my-custom-php' ),
			'type'       => 'checkbox',
			'label'      => __( 'Use hard tabs (not spaces) for indentation.', 'my-custom-php' ),
			'default'    => true,
			'codemirror' => 'indentWithTabs',
		),

		'tab_size' => array(
			'name'       => __( 'Tab Size', 'my-custom-php' ),
			'type'       => 'number',
			'desc'       => __( 'The width of a tab character.', 'my-custom-php' ),
			'default'    => 4,
			'codemirror' => 'tabSize',
			'min'        => 0,
		),

		'indent_unit' => array(
			'name'       => __( 'Indent Unit', 'my-custom-php' ),
			'type'       => 'number',
			'desc'       => __( 'How many spaces a block should be indented.', 'my-custom-php' ),
			'default'    => 4,
			'codemirror' => 'indentUnit',
			'min'        => 0,
		),

		'wrap_lines' => array(
			'name'       => __( 'Wrap Lines', 'my-custom-php' ),
			'type'       => 'checkbox',
			'label'      => __( 'Whether the editor should scroll or wrap for long lines.', 'my-custom-php' ),
			'default'    => true,
			'codemirror' => 'lineWrapping',
		),

		'line_numbers' => array(
			'name'       => __( 'Line Numbers', 'my-custom-php' ),
			'type'       => 'checkbox',
			'label'      => __( 'Show line numbers to the left of the editor.', 'my-custom-php' ),
			'default'    => true,
			'codemirror' => 'lineNumbers',
		),

		'auto_close_brackets' => array(
			'name'       => __( 'Auto Close Brackets', 'my-custom-php' ),
			'type'       => 'checkbox',
			'label'      => __( 'Auto-close brackets and quotes when typed.', 'my-custom-php' ),
			'default'    => true,
			'codemirror' => 'autoCloseBrackets',
		),

		'highlight_selection_matches' => array(
			'name'       => __( 'Highlight Selection Matches', 'my-custom-php' ),
			'label'      => __( 'Highlight all instances of a currently selected word.', 'my-custom-php' ),
			'type'       => 'checkbox',
			'default'    => true,
			'codemirror' => 'highlightSelectionMatches',
		),
	);

	$fields = apply_filters( 'custom_php_settings_fields', $fields );

	return $fields;
}
