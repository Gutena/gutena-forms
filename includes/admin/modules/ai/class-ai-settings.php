<?php
/**
 * Gutena Forms AI Settings module.
 *
 * Stores the middle server URL and API key used by the AI Form
 * Creation feature. These values are server-side only and are never
 * localized to the frontend.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_AI_Settings' ) && class_exists( 'Gutena_Forms_Forms_Settings' ) ) :
	/**
	 * AI Form Creation settings module.
	 *
	 * @since 2.2.0
	 */
	class Gutena_Forms_AI_Settings extends Gutena_Forms_Forms_Settings {

		/**
		 * Option key where AI settings are persisted.
		 *
		 * @since 2.2.0
		 * @var string
		 */
		const OPTION_KEY = 'gutena_forms_ai_settings';

		/**
		 * Constructor.
		 *
		 * @since 2.2.0
		 */
		public function __construct() {
			$this->settings = get_option( self::OPTION_KEY, array() );
		}

		/**
		 * Register the module via the gutena_forms__settings filter.
		 *
		 * @since 2.2.0
		 */
		public static function register_module() {
			add_filter(
				'gutena_forms__settings',
				function ( $settings ) {
					$settings['ai'] = __CLASS__;
					return $settings;
				}
			);

			// Register the REST endpoints for AI form creation.
			include_once plugin_dir_path( __FILE__ ) . 'class-ai-endpoints.php';
		}

		/**
		 * Get the settings definition for the admin UI.
		 *
		 * @since 2.2.0
		 * @return array Settings array.
		 */
		public function get_settings() {
			$settings = array(
				'title'       => __( 'AI Form Creation', 'gutena-forms' ),
				'description' => __(
					'Configure the connection to the Gutena Forms AI middle server that powers the "Create Using AI" form generation flow.',
					'gutena-forms'
				),
				'fields'      => array(
					array(
						'type'  => 'toggle',
						'name'  => __( 'Enable AI Form Creation', 'gutena-forms' ),
						'desc'  => __(
							'When enabled, users with form creation capability can generate forms using AI from the "Create a Form" screen.',
							'gutena-forms'
						),
						'id'    => 'ai_enabled',
						'value' => ! empty( $this->settings['ai_enabled'] ) ? $this->settings['ai_enabled'] : '',
					),
					array(
						'type'  => 'text',
						'name'  => __( 'Middle Server URL', 'gutena-forms' ),
						'desc'  => __(
							'The base URL of the Gutena Forms AI middle server that handles prompt processing and form generation.',
							'gutena-forms'
						),
						'id'    => 'server_url',
						'value' => isset( $this->settings['server_url'] ) ? $this->settings['server_url'] : '',
						'attrs' => array(
							'depends_on' => array( 'ai_enabled' ),
						),
					),
					array(
						'type'  => 'password',
						'name'  => __( 'API Key', 'gutena-forms' ),
						'desc'  => __(
							'The API key used to authenticate requests to the middle server. Keep this secret.',
							'gutena-forms'
						),
						'id'    => 'api_key',
						'value' => isset( $this->settings['api_key'] ) ? $this->settings['api_key'] : '',
						'attrs' => array(
							'depends_on' => array( 'ai_enabled' ),
						),
					),
					array(
						'id'   => 'submit_button',
						'type' => 'submit',
						'name' => __( 'Save Settings', 'gutena-forms' ),
					),
				),
			);

			return $settings;
		}

		/**
		 * Save settings to storage.
		 *
		 * @since 2.2.0
		 * @param array $settings Settings to save.
		 * @return bool True on success, false on failure.
		 */
		public function save_settings( $settings ) {
			if ( ! is_array( $settings ) ) {
				return false;
			}

			// AI disabled implies the server URL and API key are irrelevant at runtime.
			if ( empty( $settings['ai_enabled'] ) ) {
				$settings['ai_enabled'] = false;
			} else {
				$settings['ai_enabled'] = true;
			}

			// Sanitize stored values.
			$settings['server_url'] = isset( $settings['server_url'] ) ? esc_url_raw( trim( $settings['server_url'] ) ) : '';
			$settings['api_key']    = isset( $settings['api_key'] ) ? sanitize_text_field( $settings['api_key'] ) : '';

			update_option( self::OPTION_KEY, $settings );

			return true;
		}

		/**
		 * Whether AI form creation is enabled and configured.
		 *
		 * @since 2.2.0
		 * @return bool True when enabled and a server URL + API key are present.
		 */
		public static function is_enabled() {
			$settings = get_option( self::OPTION_KEY, array() );

			if ( empty( $settings['ai_enabled'] ) ) {
				return false;
			}

			if ( empty( $settings['server_url'] ) || empty( $settings['api_key'] ) ) {
				return false;
			}

			return true;
		}

		/**
		 * Get the configured middle server URL (trailing slash normalized).
		 *
		 * @since 2.2.0
		 * @return string Server URL or empty string when not configured.
		 */
		public static function get_server_url() {
			$settings = get_option( self::OPTION_KEY, array() );
			$url      = isset( $settings['server_url'] ) ? $settings['server_url'] : '';

			return '' === $url ? '' : trailingslashit( $url );
		}

		/**
		 * Get the configured API key.
		 *
		 * @since 2.2.0
		 * @return string API key or empty string when not configured.
		 */
		public static function get_api_key() {
			$settings = get_option( self::OPTION_KEY, array() );

			return isset( $settings['api_key'] ) ? $settings['api_key'] : '';
		}
	}

	Gutena_Forms_AI_Settings::register_module();
endif;
