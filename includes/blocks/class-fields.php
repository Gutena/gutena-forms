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
				array(
					'name' => 'stripe',
					'args' => array(
						'render_callback' => array( Gutena_Forms_Stripe_Field_Block::get_instance(), 'render_block' ),
					),
				),
				array(
					'name' => 'square',
					'args' => array(
						'render_callback' => array( Gutena_Forms_Square_Field_Block::get_instance(), 'render_block' ),
					),
				),
			);
			
			add_filter( 'gutena_forms__register_form_fields', array( $this, 'register_fields' ) );
			add_filter( 'gutena_forms_map_block_field_schema', array( $this, 'map_block_field_schema' ), 10, 3 );
			add_filter( 'register_block_type_args', array( $this, 'filter_stripe_field_block_args' ), 10, 2 );
			add_filter( 'register_block_type_args', array( $this, 'filter_square_field_block_args' ), 10, 2 );
		}

		/**
		 * Hide Stripe field from inserter when the gateway toggle is off.
		 *
		 * @since 2.0.0
		 * @param array  $args       Block registration args.
		 * @param string $block_type Block name.
		 * @return array
		 */
		public function filter_stripe_field_block_args( $args, $block_type ) {
			if ( 'gutena/stripe-field' !== $block_type ) {
				return $args;
			}

			if ( gutena_forms_is_stripe_gateway_enabled() ) {
				return $args;
			}

			if ( ! isset( $args['supports'] ) || ! is_array( $args['supports'] ) ) {
				$args['supports'] = array();
			}

			$args['supports']['inserter'] = false;

			return $args;
		}

		/**
		 * Hide Square field from inserter when the gateway toggle is off.
		 *
		 * @since 2.1.0
		 * @param array  $args       Block registration args.
		 * @param string $block_type Block name.
		 * @return array
		 */
		public function filter_square_field_block_args( $args, $block_type ) {
			if ( 'gutena/square-field' !== $block_type ) {
				return $args;
			}

			if ( gutena_forms_is_square_gateway_enabled() ) {
				return $args;
			}

			if ( ! isset( $args['supports'] ) || ! is_array( $args['supports'] ) ) {
				$args['supports'] = array();
			}

			$args['supports']['inserter'] = false;

			return $args;
		}

		/**
		 * Map standalone field blocks into the form schema on save.
		 *
		 * @param array  $form_schema Form schema being built.
		 * @param array  $block       Parsed block.
		 * @param string $form_id     Current form ID.
		 * @return array
		 */
		public function map_block_field_schema( $form_schema, $block, $form_id ) {
			if ( empty( $form_id ) || empty( $block['blockName'] ) || empty( $block['attrs']['nameAttr'] ) ) {
				return $form_schema;
			}

			$standalone_block_names = array();
			foreach ( $this->fields as $field ) {
				$standalone_block_names[] = 'gutena/' . $field['name'] . '-field';
			}

			if ( in_array( $block['blockName'], $standalone_block_names, true ) ) {
				$form_schema[ $form_id ]['form_fields'][ $block['attrs']['nameAttr'] ] = array_merge(
					Gutena_Forms_Helper::merge_block_default_attributes(
						$block['blockName'],
						$block['attrs']
					),
					array(
						'blockName' => $block['blockName'],
						'fieldType' => str_replace( '-field', '', str_replace( 'gutena/', '', $block['blockName'] ) ),
					)
				);
			}

			return $form_schema;
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
