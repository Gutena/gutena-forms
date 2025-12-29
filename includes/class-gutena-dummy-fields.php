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
			$this->field_groups = array(
				'date-field-group'        => array(
					'name' => 'gutena/date-field-group',
					'type' => 'date',
					'title' => 'Date Field Group',
					'dir'   => '',
				),
				'time-field-group'        => array(
					'name' => 'gutena/time-field-group',
					'type' => 'time',
					'title' => 'Time Field Group',
					'dir'   => '',
				),
				'phone-field-group'       => array(
					'name' => 'gutena/phone-field-group',
					'type' => 'phone',
					'title' => 'Phone Field Group',
					'dir'   => '',
				),
				'country-field-group'     => array(
					'name' => 'gutena/country-field-group',
					'type' => 'country',
					'title' => 'Country Field Group',
					'dir'   => '',
				),
				'state-field-group'       => array(
					'name' => 'gutena/state-field-group',
					'type' => 'state',
					'title' => 'State Field Group',
					'dir'   => '',
				),
				'file-upload-field-group' => array(
					'name' => 'gutena/file-upload-field-group',
					'type' => 'file-upload',
					'title' => 'File Upload Field Group',
					'dir'   => '',
				),
				'url-field-group'         => array(
					'name' => 'gutena/url-field-group',
					'type' => 'url',
					'title' => 'URL Field Group',
					'dir'   => '',
				),
				'hidden-field-group'      => array(
					'name' => 'gutena/hidden-field-group',
					'type' => 'hidden',
					'title' => 'Hidden Field Group',
					'dir'   => '',
				),
				'rating-field-group'      => array(
					'name' => 'gutena/rating-field-group',
					'type' => 'rating',
					'title' => 'Rating Field Group',
					'dir'   => '',
				),
				'password-field-group'    => array(
					'name' => 'gutena/password-field-group',
					'type' => 'password',
					'title' => 'Password Field Group',
					'dir'   => '',
				),
			);

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

			foreach ( $this->field_groups as $k => $v ) {
				if ( file_exists( GUTENA_FORMS_DIR_PATH . 'build/form-fields/pro/' . $k . '/block.json' ) ) {
					$fields[ $k ] = $v;
					$fields[ $k ]['dir'] = GUTENA_FORMS_DIR_PATH . 'build/form-fields/pro/' . $k;
				}
			}

			return $fields;
		}
	}

	Gutena_Dummy_Fields::get_instance();
endif;
