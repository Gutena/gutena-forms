<?php

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Abilities' ) ) {
	class Gutena_Forms_Abilities {
		private static $instance;

		public function __construct() {
			$settings = get_option( 'gutena_forms_mcp_settings', array() );
			
			if ( empty( $settings ) && ! is_array( $settings ) ) {
				return;
			}
			
			if ( ! isset( $settings['abilities_enabled'] ) ) {
				return;
			}
			
			if ( $settings['abilities_enabled'] ) {
				$this->run();
			}
		}
		
		private function run() {
			add_action( 'wp_abilities_api_categories_init', array( $this, 'categories_init' ) );
			add_action( 'wp_abilities_api_init', array( $this, 'api_init' ) );
		}
		
		public function categories_init() {
			wp_register_ability_category(
				'gutena-forms',
				array(
					'label'       => __( 'Gutena Forms', 'gutena-forms' ),
					'description' => __( 'Abilities related to Gutena Forms plugin, including permissions for forms and entries.', 'gutena-forms' ),
				)
			);
		}
		
		public function api_init() {
			wp_register_ability(
				'gutena-forms/get-all-forms',
				array(
					'label'               => __( 'Get All Forms', 'gutena-forms' ),
					'description'         => __( 'Retrieve all forms.', 'gutena-forms' ),
					'category'            => 'gutena-forms',
					'execute_callback'    => array( $this, 'get_all_forms' ),
					'permission_callback' => array( $this, 'permission_callback' ),
					'output_schema'       => array(
						'type'  => 'array',
						'items' => array(
							'type' => 'object',
							'properties' => array(
								'id'        => array( 'type' => 'integer' ),
								'datetime'  => array( 'type' => 'string' ),
								'title'     => array( 'type' => 'string' ),
								'status'    => array( 'type' => 'string' ),
								'entries'   => array( 'type' => 'string' ),
								'author'    => array( 'type' => 'string' ),
								'permalink' => array( 'type' => 'string' ),
							),
						),
					),
				)
			);
			wp_register_ability(
				'gutena-forms/get-all-entires',
				array(
					'label'               => __( 'Get All Entries', 'gutena-forms' ),
					'description'         => __( 'Retrieve all entries.', 'gutena-forms' ),
					'category'            => 'gutena-forms',
					'execute_callback'    => array( $this, 'get_all_entries' ),
					'permission_callback' => array( $this, 'permission_callback' ),
					'output_schema'       => array(
						'type'  => 'array',
						'items' => array(
							'type'       => 'object',
							'properties' => array(
								'entry_id'    => array( 'type' => 'integer' ),
								'form_id'     => array( 'type' => 'integer' ),
								'form_name'   => array( 'type' => 'string' ),
								'datetime'    => array( 'type' => 'string' ),
								'value'       => array( 'type' => 'object' ),
								'first_value' => array( 'type' => 'string' ),
								'status'      => array( 'type' => 'string' ),
								'starred'     => array( 'type' => 'boolean' ),
							),
						),
					),
				)
			);
			wp_register_ability(
				'gutena-forms/get-form-entries',
				array(
					'label'               => __( 'Get Entries', 'gutena-forms' ),
					'description'         => __( 'Retrieve all entries of a form by form id.', 'gutena-forms' ),
					'category'            => 'gutena-forms',
					'execute_callback'    => array( $this, 'get_form_entries' ),
					'permission_callback' => array( $this, 'permission_callback' ),
					'output_schema'       => array(
						'type'  => 'array',
						'items' => array(
							'type'       => 'object',
							'properties' => array(
								'entry_id'    => array( 'type' => 'integer' ),
								'form_id'     => array( 'type' => 'integer' ),
								'form_name'   => array( 'type' => 'string' ),
								'datetime'    => array( 'type' => 'string' ),
								'value'       => array( 'type' => 'object' ),
								'first_value' => array( 'type' => 'string' ),
								'status'      => array( 'type' => 'string' ),
								'starred'     => array( 'type' => 'boolean' ),
							),
						),
					),
					'input_schema'       => array(
						'type'       => 'object',
						'properties' => array(
							'form_id' => array(
								'type'        => 'integer',
								'description' => __( 'Form id', 'gutena-forms' ),
							),
						),
						'required'   => array( 'form_id' ),
					),
				)
			);
			wp_register_ability(
				'gutena-forms/get-entry-details',
				array(
					'label'               => __( 'Get Entry Details', 'gutena-forms' ),
					'description'         => __( 'Retrieve entry data by entry id.', 'gutena-forms' ),
					'category'            => 'gutena-forms',
					'execute_callback'    => array( $this, 'get_entry_details' ),
					'permission_callback' => array( $this, 'permission_callback' ),
					'output_schema'       => array(
						'type'  => 'array',
						'items' => array(
							'type'       => 'object',
							'properties' => array(
								'label' => array( 'type' => 'string' ),
								'value' => array( 'type' => 'object' ),
							),
						),
					),
					'input_schema'       => array(
						'type'       => 'object',
						'properties' => array(
							'entry_id' => array(
								'type'        => 'integer',
								'description' => __( 'Entry Id', 'gutena-forms' ),
							),
						),
						'required'   => array( 'entry_id' ),
					),
				)
			);
		}

		public function permission_callback() {
			return is_user_logged_in() && current_user_can( 'manage_options' );
		}

		public function get_all_forms() {
			return Gutena_Forms_Forms_Model::get_instance()->get_all();
		}
		
		public function get_all_entries() {
			return Gutena_Forms_Entries_Model::get_instance()->get_all();
		}
		
		public function get_form_entries( $input ) {
			$form_id  = $input['form_id'];
			$block_id = Gutena_Forms_Forms_Model::get_instance()->get_block_id( $form_id );

			return Gutena_Forms_Entries_Model::get_instance()->get_all( $block_id );
		}

		public function get_entry_details( $input ) {
			$entry_id = $input['entry_id'];
			
			$data = Gutena_Forms_Entries_Model::get_instance()->get_data( $entry_id );
			$data = maybe_unserialize( $data );
			$entry_data = array();

			foreach ( $data as $value ) {
				$entry_data[] = array(
					'label' => $value['label'],
					'value' => $value['value'],
				);
			}
			
			return $entry_data;
		}
		
		public static function get_instance() {
			if ( ! self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}
	}
	
	Gutena_Forms_Abilities::get_instance();
}