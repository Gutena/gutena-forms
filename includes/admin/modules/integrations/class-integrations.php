<?php
/**
 * Class Integrations
 *
 * @since 1.8.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Integrations' ) && class_exists( 'Gutena_Forms_Forms_Settings' ) ) :
	class Gutena_Forms_Integrations extends Gutena_Forms_Forms_Settings {
		public $settings = array();

		public function __construct() {
			$this->settings = get_option( 'gutena_forms__integrations', array() );

			include_once plugin_dir_path( __FILE__ ) . 'class-integrations-endpoints.php';
		}

		public static function register_module() {
			add_filter(
				'gutena_forms__settings',
				function ( $settings ) {

					$settings['integrations'] = __CLASS__;

					return $settings;
				}
			);
		}

		public function get_settings() {

			return array(
				'title'       => __( 'Integrations', 'gutena-forms' ),
				'description' => __( 'Integrate your forms with third-party services to automate your workflow and enhance your form functionality.', 'gutena-forms' ),
				'fields'      => array(
					array(
						'type' => 'template',
						'name' => 'integrations',
					)
				),
			);
		}

		public function save_settings( $settings ) {
			// TODO: Implement save_settings() method.
		}
	}

	Gutena_Forms_Integrations::register_module();
endif;
