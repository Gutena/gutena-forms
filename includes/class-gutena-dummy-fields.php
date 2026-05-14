<?php
/**
 * Class Gutena Dummy_Fields
 *
 * @since 1.5.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Dummy_Fields' ) ) :
	/**
	 * Gutena Dummy_Fields Class
	 *
	 * @since 1.5.0
	 */
	class Gutena_Dummy_Fields {
		/**
		 * The singleton instance.
		 *
		 * @since 1.5.0
		 * @var Gutena_Dummy_Fields $instance The single instance of the class.
		 */
		private static $instance;

		/**
		 * Field groups.
		 *
		 * @since 1.5.0
		 * @var array $field_groups The field groups.
		 */
		private $field_groups = array();

		/**
		 * Get the singleton instance of the class.
		 *
		 * @since 1.5.0
		 * @return Gutena_Dummy_Fields
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor.
		 *
		 * @since 1.5.0
		 */
		private function __construct() {
			add_filter( 'gutena_forms__register_fields', array( $this, 'register_fields' ) );
		}

		/**
		 * Registering dummy fields.
		 *
		 * @since 1.5.0
		 * @param array $fields Registered fields.
		 *
		 * @return array
		 */
		public function register_fields( $fields ) {
			if ( is_gutena_forms_pro() ) {
				return $fields;
			}

			return $fields;
		}
	}

	Gutena_Dummy_Fields::get_instance();
endif;
