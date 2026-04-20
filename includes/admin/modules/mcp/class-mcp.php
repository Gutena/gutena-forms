<?php
/**
 * Gutena Forms mcp Server
 *
 * @since 1.8.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_MCP' ) && class_exists( 'Gutena_Forms_Forms_Settings' ) ) :
	/**
	 * Class Gutena Forms MCP
	 *
	 * @since 1.8.0
	 */
	class Gutena_Forms_MCP extends Gutena_Forms_Forms_Settings {
		/**
		 * MCP Server settings.
		 *
		 * @since 1.8.0
		 * @var array $settings MCP Server settings.
		 */
		public $settings;

		/**
		 * Gutena_Forms_MCP Construct
		 *
		 * @since 1.8.0
		 */
		public function __construct() {
			$this->settings = get_option( 'gutena_forms_mcp_settings', array() );
		}

		/**
		 * Register Module
		 *
		 * @since 1.8.0
		 */
		public static function register_module() {
			add_filter(
				'gutena_forms__settings',
				function ( $settings ) {

					$settings['mcp'] = __CLASS__;

					return $settings;
				}
			);

			include_once plugin_dir_path( __FILE__ ) . 'abilities/class-abilities.php';
			include_once plugin_dir_path( __FILE__ ) . 'mcp-server/class-mcp-server.php';
		}

		/**
		 * Get Settings
		 *
		 * @since 1.8.0
		 * @return array
		 */
		public function get_settings() {
			$settings = array(
				'title'       => __( 'MCP Settings', 'gutena-forms' ),
				'description' => sprintf(
					// translators: %1$s documentation link.
					__( 'Configure the AI client permissions and MCP settings. %1$s', 'gutena-forms' ),
					'<a href="https://gutenaforms.com/docs/ai-features/mcp-server">' . __( 'View documentation', 'gutena-forms' ) . '</a>'
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
						'value' => $this->settings['abilities_enabled'],
					),
					array(
						'type'  => 'toggle',
						'name'  => __( 'Enable MCP Server', 'gutena-forms' ),
						'desc'  => __( 'Enable the Gutena Form MCP (Model Context Protocol) server. Requires the WordPress Abilities API and MCP adapter.', 'gutena-forms' ),
						'id'    => 'mcp_enabled',
						'value' => $this->settings['mcp_enabled'],
						'attrs' => array(
							'depends_on' => array( 'abilities_enabled' ),
						),
					),
					array(
						'name'     => 'mcp-configuration',
						'type'     => 'field-template',
						'username' => $current_user->user_login,
						'apiURL'   => rest_url( 'gutena-forms/v1/mcp' ),
						'attrs'    => array(
							'visible_when' => array( 'abilities_enabled', 'mcp_enabled' ),
						),
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

		/**
		 * Saving settings
		 *
		 * @since 1.8.0
		 * @param array $settings Settings to save.
		 *
		 * @return bool
		 */
		public function save_settings( $settings ) {
			if ( ! is_array( $settings ) ) {
				return false;
			}

			// Abilities off implies MCP server must be off (matches runtime gate in class-mcp-server.php).
			if ( empty( $settings['abilities_enabled'] ) ) {
				$settings['mcp_enabled'] = false;
			}

			update_option( 'gutena_forms_mcp_settings', $settings );

			return true;
		}
	}

	Gutena_Forms_MCP::register_module();
endif;
