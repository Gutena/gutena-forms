<?php
/**
 * Class Field Label Block
 *
 * @since 1.6.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Field_Label_Block' ) ) :
	/**
	 * Gutena Forms Field Label Block Class.
	 *
	 * @since 1.6.0
	 */
	class Gutena_Forms_Field_Label_Block {
		/**
		 * The single instance of the class.
		 *
		 * @since 1.6.0
		 * @var Gutena_Forms_Field_Label_Block $instance The single instance of the class.
		 */
		private static $instance;

		/**
		 * Register block.
		 *
		 * @since 1.6.0
		 */
		public function register_block() {
			register_block_type(
				GUTENA_FORMS_DIR_PATH . 'build/field-label',
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
			ob_start();

			echo '<label for="' . esc_attr( $attributes['htmlFor'] ) . '">' . esc_attr( $attributes['content'] ) . '</label>';

			return ob_get_clean();
		}

		/**
		 * Get the single instance of the class.
		 *
		 * @since 1.6.0
		 * @return Gutena_Forms_Field_Label_Block
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}
	}
endif;
