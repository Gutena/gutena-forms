<?php
/**
 * Class Gutena Forms MCP Settings
 *
 * @since 1.8.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_AI' ) && class_exists( 'Gutena_Forms_Forms_Settings' ) ) :
	class Gutena_Forms_AI extends Gutena_Forms_Forms_Settings {
	
		public $settings = array();
	
		public function __construct() {
			$this->settings = get_option( 'gutena_forms__ai_settings', array() );
		}
		
		public static function register_module() {
			
			include_once plugin_dir_path( __FILE__ ) . 'abilities/class-abilities.php';
			include_once plugin_dir_path( __FILE__ ) . 'mcp/class-mcp.php';
			
			add_filter(
				'gutena_forms__settings',
				function ( $settings ) {
					$settings['ai-settings'] = __CLASS__;
					return $settings;
				}
			);
		}
		
		public function get_settings() {
			return array(
				'title'       => __( 'AI Tools & Settings', 'gutena-forms' ),
				'description' => __( '', 'gutena-forms' ),
				'fields'      => array(
					array(
						'id'      => 'enable-mcp',
						'type'    => 'toggle',
						'name'    => __( 'Enable MCP', 'gutena-forms' ),
						'default' => false,
						'value'   => $this->settings['enable-mcp'],
					),
					
					array(
						'id'   => 'submit_button',
						'type' => 'submit',
						'name' => __( 'Save Settings', 'gutena-forms' ),
					),
				),
			);
		}
		
		public function save_settings( $settings ) {
			update_option( 'gutena_forms__ai_settings', $settings );
			
			return true;
		}
	}

	Gutena_Forms_AI::register_module();
endif;
