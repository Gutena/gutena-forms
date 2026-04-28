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

		private function __construct() {
			add_filter( 'gutena_forms__register_fields', array( $this, 'register_fields' ), 10, 2 );
		}

		/**
		 * Registering fields
		 *
		 * @param array  $fields Array of registered fields.
		 * @param string $path Path of blocks.
		 *
		 * @return array
		 */
		public function register_fields( $fields, $path ) {
			$fields[] = array(
				'name'  => 'text-field',
				'attrs' => array(),
			);
			$fields[] = array(
				'name'  => 'opt-in-field',
				'attrs' => array(),
			);
			$fields[] = array(
				'name'  => 'dropdown-field',
				'attrs' => array(),
			);
			$fields[] = array(
				'name'  => 'email-field',
				'attrs' => array(),
			);
			$fields[] = array(
				'name'  => 'number-field',
				'attrs' => array(),
			);
			$fields[] = array(
				'name'  => 'textarea-field',
				'attrs' => array(),
			);
			$fields[] = array(
				'name'  => 'range-field',
				'attrs' => array(),
			);
			$fields[] = array(
				'name'  => 'radio-field',
				'attrs' => array(),
			);
			$fields[] = array(
				'name'  => 'checkbox-field',
				'attrs' => array(),
			);

			return $fields;
		}

		/**
		 * Register field blocks.
		 *
		 * @since 1.5.0
		 */
		public function register_block() {
			$dir          = GUTENA_FORMS_DIR_PATH . 'build/blocks/form-field-blocks/';
			$field_blocks = apply_filters(
				'gutena_forms__register_fields',
				array(),
				$dir
			);

			usort(
				$field_blocks,
				function ( $a, $b ) {
					return strcmp( $a['name'], $b['name'] );
				}
			);

			foreach ( $field_blocks as $k => $field_block ) {
				if ( ! isset( $field_block['dir'] ) ) {
					$field_block['dir'] = $dir;
				}

				if ( file_exists( $field_block['dir'] . $field_block['name'] . '/block.json' ) ) {
					register_block_type( $field_block['dir'] . $field_block['name'], $field_block['attrs'] );
				} elseif ( file_exists( $field_block['dir'] . 'block.json' ) ) {
					register_block_type( $field_block['dir'], $field_block['attrs'] );
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
