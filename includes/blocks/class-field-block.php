<?php
/**
 * Class Field BLock.
 *
 * @since 1.6.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Field_Block' ) ) :
	/**
	 * Field Block Class.
	 *
	 * @since 1.6.0
	 */
	class Gutena_Forms_Field_Block {
		/**
		 * The single instance of the class.
		 *
		 * @since 1.6.0
		 * @var Gutena_Forms_Field_Block $instance The single instance of the class.
		 */
		private static $instance;

		/**
		 * Register field blocks.
		 *
		 * @since 1.5.0
		 */
		public function register_block() {
			$fields    = apply_filters( 'gutena_forms__register_fields', array() );
			$group_dir = GUTENA_FORMS_DIR_PATH . 'build/form-fields/';

			usort(
				$fields,
				function ( $a, $b ) {
					return strcmp( $a['title'], $b['title'] );
				}
			);

			foreach ( $fields as $field => $field_args ) {
				if ( file_exists( $group_dir . $field_args['dir'] . '/block.json' ) ) {
					register_block_type( $group_dir . $field_args['dir'] );
				} elseif ( file_exists( $field_args['dir'] . '/block.json' ) ) {
					register_block_type( $field_args['dir'] );
				}
			}
		}

		/**
		 * Get the single instance of the class.
		 *
		 * @since 1.6.0
		 * @return Gutena_Forms_Field_Block
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}
	}
endif;
