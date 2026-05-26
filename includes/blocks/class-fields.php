<?php
/**
 * Gutena Forms new Fields
 *
 * @since 1.9.1
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Fields' ) ) :
	class Gutena_Forms_Fields {
		private static $instance;
		
		private $fields = array();
		
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new Gutena_Forms_Fields();
			}

			return self::$instance;
		}
		
		private function __construct() {
			$this->fields = array(
				array(
					'name' => 'checkbox',
					'args' => array(),
				),
				array(
					'name' => 'dropdown',
					'args' => array(),
				),
				array(
					'name' => 'email',
					'args' => array(),
				),
				array(
					'name' => 'number',
					'args' => array(),
				),
				array(
					'name' => 'optin',
					'args' => array(),
				),
				array(
					'name' => 'radio',
					'args' => array(),
				),
				array(
					'name' => 'range',
					'args' => array(),
				),
				array(
					'name' => 'text',
					'args' => array(),
				),
				array(
					'name' => 'textarea',
					'args' => array(),
				),
			);
			
			add_filter( 'gutena_forms__register_form_fields', array( $this, 'register_fields' ) );
		}
		
		public function register_fields( $fields ) {
			
			foreach ( $this->fields as $field ) {
				$field['path'] = GUTENA_FORMS_DIR_PATH . 'build/blocks/form-field-blocks/' . $field['name'] . '-field';
				$fields[]      = $field;
			}
			
			return $fields;
		}
		
		public function register_blocks() {
			$fields = apply_filters( 'gutena_forms__register_form_fields', array() );

			usort(
				$fields,
				function ( $field_a, $field_b ) {
					return strcmp( $field_a['name'], $field_b['name'] );
				}
			);

			foreach ( $fields as $field ) {
				if ( file_exists( $field['path'] . '/block.json' ) ) {
					if ( ! isset( $field['args'] ) ) {
						$field['args'] = array();
					}
					register_block_type( $field['path'], $field['args'] );
				}
			}
		}
	}
endif;
