<?php
/**
 * Layout field blocks registration.
 *
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Layout_Blocks' ) ) :
	/**
	 * Registers layout-field and layout-column blocks.
	 */
	class Gutena_Forms_Layout_Blocks {
		/**
		 * Singleton instance.
		 *
		 * @var Gutena_Forms_Layout_Blocks|null
		 */
		private static $instance = null;

		/**
		 * Get instance.
		 *
		 * @return Gutena_Forms_Layout_Blocks
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Register blocks.
		 */
		public function register_blocks() {
			if ( ! function_exists( 'register_block_type' ) ) {
				return;
			}

			$blocks = array( 'layout-field', 'layout-column' );

			foreach ( $blocks as $block_dir ) {
				$path = GUTENA_FORMS_DIR_PATH . 'build/blocks/' . $block_dir;
				if ( file_exists( $path . '/block.json' ) ) {
					register_block_type( $path );
				}
			}
		}
	}
endif;
