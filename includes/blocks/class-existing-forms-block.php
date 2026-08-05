<?php
/**
 * Class Existing Forms Block
 *
 * @since 1.6.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Existing_Forms_Block' ) ) :
	/**
	 * Gutena Forms Existing Forms Block Class.
	 *
	 * @since 1.6.0
	 */
	class Gutena_Forms_Existing_Forms_Block {
		private static $instance;

		/**
		 * Register block.
		 *
		 * @since 1.6.0
		 */
		public function register_block() {
			register_block_type(
				GUTENA_FORMS_DIR_PATH . 'build/blocks/existing-forms',
				array(
					'render_callback' => array( $this, 'render' ),
				)
			);
		}

		/**
		 * Render block callback.
		 *
		 * @since 1.6.0
		 * @param array    $attributes Block attributes.
		 * @param string   $content Block content.
		 * @param WP_Block $block Block instance.
		 *
		 * @return string
		 */
		public function render( $attributes, $content, $block ) {
			if ( ! isset( $attributes['formID'] ) || empty( $attributes['formID'] ) ) {
				return $content;
			}
			$form_id = intval( $attributes['formID'] );
			$form    = get_post( $form_id );
			if ( ! $form || 'gutena_forms' !== $form->post_type ) {
				return $content;
			}

			$html = do_blocks( wp_unslash( $form->post_content ) );

			// Nested Form > Existing Forms: inner <form> (with hide class) is dropped by the browser.
			// Emit a marker so frontend JS can apply hide-form-after-submit on the parent form.
			if ( false !== strpos( $html, 'hide-form-after-submit' ) ) {
				$html = '<span hidden data-gutena-hide-form-after-submit="1"></span>' . $html;
			}

			return $html;
		}

		/**
		 * Get the single instance of the class.
		 *
		 * @since 1.6.0
		 * @return Gutena_Forms_Existing_Forms_Block
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}
	}
endif;
