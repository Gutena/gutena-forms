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
					'output_schema'       => array(),
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
					'output_schema'       => array(),
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
					'output_schema'       => array(),
				)
			);
		}

		public function permission_callback() {
			return is_user_logged_in() && current_user_can( 'manage_options' );
		}

		public function get_all_forms() {
		
		}
		
		public function get_all_entries() {
			$forms = get_posts(
				array(
					'post_type'      => 'gutena_forms',
					'post_status'    => 'publish',
					'posts_per_page' => -1,
				)
			);
			
			return array_map(
				function ( $form ) {
					
					return array(
						'id' => $form->ID,
						'name' => $form->post_title,
					);
				},
				$forms
			);
		}
		
		public function get_form_entries() {
		
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