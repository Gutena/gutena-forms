<?php
/**
 * AI Abilities Settings Class
 *
 * @since 1.8.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Abilities' ) ) :
	class Gutena_Forms_Abilities {
		private static $instance;
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}
		}

		private function __construct() {
			add_action( 'wp_abilities_api_categories_init', array( $this, 'api_categories_init' ) );
			add_action( 'wp_abilities_api_init', array( $this, 'api_init' ) );
			
			add_filter(
				'gutena_forms__abilities',
				function ( $x ) {
					
					$x[] = array(
						'gutena-forms' => array(
							'label'       => __( 'Gutena Forms', 'gutena-forms' ),
							'description' => __( 'Abilities related to Gutena Forms plugin.', 'gutena-forms' ),
							'abilities'   => array(
								'gutena-forms/get-forms'   => array(
									'auth'          => true,
									'label'         => __( 'Get Forms', 'gutena-forms' ),
									'output_schema' => array(
										'type'  => 'array',
										'items' => array(
											'properties' => array(
												'id'  => array( 'type' => 'integer' ),
												'title' => array( 'type' => 'string' ),
											)
										),
									),
									'execute_callback' => function () {
										return array( 1 => 'Hello World' );
									}
								),
								'gutena-forms/get-entries' => array(
									'auth'          => true,
									'label'         => __( 'Get Forms', 'gutena-forms' ),
									'output_schema' => array(
										'type'  => 'array',
										'items' => array(
											'properties' => array(
												'id'  => array( 'type' => 'integer' ),
												'title' => array( 'type' => 'string' ),
											)
										),
									),
									'execute_callback' => function () {
										return array( 1 => 'Hello World' );
									}
								),
							),
						),
					);
					
					return $x;
				}
			);
		}
		
		public function api_categories_init() {
			$abilities = apply_filters( 'gutena_forms__abilities', array() );
			
			foreach ( $abilities as $ability_category => $ability_args ) {
				wp_register_ability_category(
					$ability_category,
					$ability_args,
				);
			}
		}

		public function api_init() {
			$abilities = apply_filters( 'gutena_forms__abilities', array() );
			
			foreach ( $abilities as $ability_category => $ability_args ) {
				foreach ( $ability_args['abilities'] as $namespace => $ability ) {
					$ability['category'] = $ability_category;
					if ( isset( $ability['auth'] ) && $ability['auth'] ) {
						if ( is_callable( $ability['auth'] ) ) {
							$ability['permission_callback'] = $ability['auth'];
						} else {
							$ability['permission_callback'] = array( $this, 'permission_callback' );
						}
					} else {
						$ability['permission_callback'] = '__return_true';
					}
					wp_register_ability( $namespace, $ability );
				}
			}
		}
		
		public function permission_callback() {
			return is_user_logged_in() && current_user_can( 'manage_options' );
		}
	}
	
	Gutena_Forms_Abilities::get_instance();
endif;
