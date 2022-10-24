<?php

class MyCustomPhp_Shortcode {

	function __construct() {
		add_shortcode( 'MyPHPCode', array( $this, 'render_shortcode' ) );
		add_action( 'the_posts', array( $this, 'enqueue_assets' ) );
		add_shortcode('my_custom_php', array($this, 'phpShortcodeRender'));

	}

	function enqueue_assets( $posts ) {

		if ( empty( $posts ) || custom_php_get_setting( 'general', 'disable_prism' ) ) {
			return $posts;
		}

		$found = false;

		foreach ( $posts as $post ) {

			if ( false !== stripos( $post->post_content, '[my_custom_php' ) ) {
				$found = true;
				break;
			}
		}

		if ( ! $found ) {
			return $posts;
		}

		$plugin = custom_php();

		wp_enqueue_style(
			'my-custom-php-front-end',
			plugins_url( 'css/min/front-end.css', $plugin->file ),
			array(), $plugin->version
		);

		wp_enqueue_script(
			'my-custom-php-front-end',
			plugins_url( 'js/min/front-end.js', $plugin->file ),
			array(), $plugin->version, true
		);

		return $posts;
	}

	function render_shortcode( $atts ) {

		$atts = shortcode_atts(
			array(
				'id'      => 0,
				'network' => false,
			),
			$atts, 'MyPHPCode'
		);

		if ( ! $id = intval( $atts['id'] ) ) {
			return '';
		}

		$network = $atts['network'] ? true : false;
		$snippet = get_snippet( $id, $network );

		if ( ! trim( $snippet->code ) ) {
			return '';
		}

		return '<pre><code class="language-php">' . esc_html( $snippet->code ) . '</code></pre>';
	}

    public function phpShortcodeRender($atts)
    {
        /* Bail early if safe mode is active */
        if ( defined( 'MY_CUSTOM_PHP_SAFE_MODE' ) && MY_CUSTOM_PHP_SAFE_MODE || ! apply_filters( 'custom_php/execute_snippets', true ) ) {
            return false;
        }

        $atts = shortcode_atts(
            array(
                'id'      => 0,
                'network' => false,
            ),
            $atts, 'MyPHPCode'
        );

        if ( ! $id = intval( $atts['id'] ) ) {
            return '';
        }

        $network = $atts['network'] ? true : false;
        $snippet = get_snippet( $id, $network );

        if ( ! trim( $snippet->code ) or !$snippet->active) {
            return '';
        }

        ob_start();

        eval( $snippet->code );

        $html = ob_get_clean();
        return $html;
    }
}

