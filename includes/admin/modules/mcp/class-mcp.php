<?php

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_MCP' ) && class_exists( 'Gutena_Forms_Forms_Settings' ) ) :
	class Gutena_Forms_MCP extends Gutena_Forms_Forms_Settings {
		public static function register_module() {
			add_filter(
				'gutena_forms__settings',
				function ( $settings ) {

					$settings['mcp'] = __CLASS__;

					return $settings;
				}
			);
		}
		
		public function get_settings() {
			$settings = array(
				'title'        => __( 'MCP Settings', 'gutena-forms' ),
				'description'  => sprintf(
					__( 'Configure the AI client permissions and MCP settings. %1$s', 'gutena-forms' ),
					'<a href="#">' . __( 'View documentation', 'gutena-forms' ) . '</a>'
				),
			);

			if ( ! class_exists( 'WP\MCP\Core\McpAdapter' ) ) {
				$settings['fields'] = array(
					array(
						'type' => 'template',
						'name' => 'mcp',
					),
				);
			} else {
				global $current_user;
				$settings['fields'] = array(
					array(
						'type'  => 'toggle',
						'name'  => __( 'Enable Abilities', 'gutena-forms' ),
						'desc'  => __( 'When enabled, your forms and entries are registered with the WordPress Abilities API, allowing AI clients to read the data only. When disabled, AI clients have no access to your forms or entries.', 'gutena-forms' ),
						'id'    => 'abilities_enabled',
					),
					array(
						'type'  => 'toggle',
						'name'  => __( 'Enable MCP Server', 'gutena-forms' ),
						'desc'  => __( 'Enable the Gutena Form MCP (Model Context Protocol) server. Requires the WordPress Abilities API and MCP adapter.', 'gutena-forms' ),
						'id'    => 'mcp_enabled',
					),
					array(
						'name'     => 'mcp-configuration',
						'type'     => 'field-template',
						'username' => $current_user->user_login,
						'apiURL'   => rest_url( 'gutena-forms/v1/mcp' ),
					),
					
					array(
						'id'   => 'submit_button',
						'type' => 'submit',
						'name' => __( 'Save Settings', 'gutena-forms' ),
					),
				);
			}
			
			return $settings;
		}
		
		public function save_settings( $settings ) {
			// TODO: Implement save_settings() method.
		}
	}

	Gutena_Forms_MCP::register_module();
endif;
