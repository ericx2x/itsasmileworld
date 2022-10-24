<?php

/**
 * This file registers the settings
 *
 * @package MyCustomPhp
 */

/**
 * Returns 'true' if plugin settings are unified on a multisite installation
 * under the Network Admin settings menu
 *
 * This option is controlled by the "Enable administration menus" setting on the Network Settings menu
 *
 * @return bool
 */
function custom_php_unified_settings() {

	if ( ! is_multisite() ) {
		return false;
	}

	$menu_perms = get_site_option( 'menu_items', array() );

	return empty( $menu_perms['snippets_settings'] );
}

/**
 * Retrieve the setting values from the database.
 * If a setting does not exist in the database, the default value will be returned.
 *
 * @return array
 */
function custom_php_get_settings() {

	/* Check if the settings have been cached */
	if ( $settings = wp_cache_get( 'custom_php_settings' ) ) {
		return $settings;
	}

	/* Begin with the default settings */
	$settings = custom_php_get_default_settings();

	/* Retrieve saved settings from the database */
	$saved = custom_php_unified_settings() ?
		get_site_option( 'custom_php_settings', array() ) :
		get_option( 'custom_php_settings', array() );

	/* Replace the default field values with the ones saved in the database */
	if ( function_exists( 'array_replace_recursive' ) ) {

		/* Use the much more efficient array_replace_recursive() function in PHP 5.3 and later */
		$settings = array_replace_recursive( $settings, $saved );
	} else {

		/* Otherwise, do it manually */
		foreach ( $settings as $section => $fields ) {
			foreach ( $fields as $field => $value ) {

				if ( isset( $saved[ $section ][ $field ] ) ) {
					$settings[ $section ][ $field ] = $saved[ $section ][ $field ];
				}
			}
		}
	}

	wp_cache_set( 'custom_php_settings', $settings );

	return $settings;
}

/**
 * Retrieve an individual setting field value
 *
 * @param  string $section The ID of the section the setting belongs to
 * @param  string $field The ID of the setting field
 *
 * @return array
 */
function custom_php_get_setting( $section, $field ) {
	$settings = custom_php_get_settings();

	return $settings[ $section ][ $field ];
}

/**
 * Retrieve the settings sections
 * @return array
 */
function custom_php_get_settings_sections() {
	$sections = array(
		'general'            => __( 'General', 'my-custom-php' ),
		'description_editor' => __( 'Description Editor', 'my-custom-php' ),
		'editor'             => __( 'Code Editor', 'my-custom-php' ),
	);

	return apply_filters( 'custom_php_settings_sections', $sections );
}

/**
 * Register settings sections, fields, etc
 */
function custom_php_register_settings() {

	if ( custom_php_unified_settings() ) {

		if ( ! get_site_option( 'custom_php_settings', false ) ) {
			add_site_option( 'custom_php_settings', custom_php_get_default_settings() );
		}
	} else {

		if ( ! get_option( 'custom_php_settings', false ) ) {
			add_option( 'custom_php_settings', custom_php_get_default_settings() );
		}
	}

	/* Register the setting */
	register_setting( 'my-custom-php', 'custom_php_settings', 'custom_php_settings_validate' );

	/* Register settings sections */
	foreach ( custom_php_get_settings_sections() as $section_id => $section_name ) {
		add_settings_section(
			'my-custom-php-' . $section_id,
			$section_name,
			'__return_empty_string',
			'my-custom-php'
		);
	}

	/* Register settings fields */
	foreach ( custom_php_get_settings_fields() as $section_id => $fields ) {
		foreach ( $fields as $field_id => $field ) {
			$atts = $field;
			$atts['id'] = $field_id;
			$atts['section'] = $section_id;

			add_settings_field(
				'custom_php_' . $field_id,
				$field['name'],
				"custom_php_{$field['type']}_field",
				'my-custom-php',
				'my-custom-php-' . $section_id,
				$atts
			);
		}
	}

	/* Add editor preview as a field */
	add_settings_field(
		'custom_php_editor_preview',
		__( 'Editor Preview', 'my-custom-php' ),
		'custom_php_settings_editor_preview',
		'my-custom-php',
		'my-custom-php-editor'
	);
}

add_action( 'admin_init', 'custom_php_register_settings' );

/**
 * Validate the settings
 *
 * @param  array $input The sent settings
 *
 * @return array        The validated settings
 */
function custom_php_settings_validate( array $input ) {
	$settings = custom_php_get_settings();
	$settings_fields = custom_php_get_settings_fields();

	// Don't directly loop through $input as it does not include as deselected checkboxes
	foreach ( $settings_fields as $section_id => $fields ) {

		// Loop through fields
		foreach ( $fields as $field_id => $field ) {

			switch ( $field['type'] ) {

				case 'checkbox':
					$settings[ $section_id ][ $field_id ] =
						isset( $input[ $section_id ][ $field_id ] ) && 'on' === $input[ $section_id ][ $field_id ];
					break;

				case 'number':
					$settings[ $section_id ][ $field_id ] = absint( $input[ $section_id ][ $field_id ] );
					break;

				case 'codemirror_theme_select':
					$available_themes = custom_php_get_available_themes();
					$selected_theme = $input[ $section_id ][ $field_id ];

					if ( in_array( $selected_theme, $available_themes ) ) {
						$settings[ $section_id ][ $field_id ] = $selected_theme;
					}

					break;

				default:
					break;

			}
		}
	}

	/* Add an updated message */
	add_settings_error(
		'my-custom-php-settings-notices',
		'settings-saved',
		__( 'Settings saved.', 'my-custom-php' ),
		'updated'
	);

	return $settings;
}
