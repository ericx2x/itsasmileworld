<?php
/**
 * Astra Addon Gutenberg Compatibility class
 *
 * @package Astra Addon
 * @since 2.5.1
 */

/**
 * Astra Addon Gutenberg Builder Compatibility class
 *
 * @since 2.5.1
 */
class Astra_Addon_Gutenberg_Compatibility extends Astra_Addon_Page_Builder_Compatibility {

	/**
	 * Render Blocks content for post.
	 *
	 * @param int $post_id Post id.
	 *
	 * @since 2.5.1
	 */
	public function render_content( $post_id ) {

		$output       = '';
		$current_post = get_post( $post_id, OBJECT );

		if ( has_blocks( $current_post ) ) {
			$blocks = parse_blocks( $current_post->post_content );
			foreach ( $blocks as $block ) {
				$output .= render_block( $block );
			}
		} else {
			$output = $current_post->post_content;
		}

		ob_start();
		echo do_shortcode( $output );
		echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Load Gutenberg Blocks styles & scripts.
	 *
	 * @param int $post_id Post id.
	 *
	 * @since 2.5.1
	 */
	public function enqueue_blocks_assets( $post_id ) {

		wp_enqueue_style( 'wp-block-library' );

		/**
		 * Load UAG styles and scripts assets.
		 *
		 * @since 2.5.1
		 */
		if ( defined( 'UAGB_VER' ) && version_compare( UAGB_VER, '1.14.11', '>=' ) ) {
			$uag_active = get_post_meta( $post_id, 'uag_style_timestamp-css', true );
		} else {
			$uag_active = get_post_meta( $post_id, 'uagb_style_timestamp-css', true );
		}

		if ( class_exists( 'UAGB_Helper' ) && $uag_active ) {

			$current_post = get_post( $post_id, OBJECT );

			$uagb_helper_instance          = UAGB_Helper::get_instance();
			$uag_generated_stylesheet_func = array( $uagb_helper_instance, 'get_generated_stylesheet' );

			wp_enqueue_style(
				'uagb-block-css', // UAG-Handle.
				UAGB_URL . 'dist/blocks.style.css', // Block style CSS.
				array(),
				UAGB_VER
			);

			if ( is_callable( $uag_generated_stylesheet_func ) ) {
				$uagb_helper_instance->get_generated_stylesheet( $current_post );
			}
		}
	}
}
