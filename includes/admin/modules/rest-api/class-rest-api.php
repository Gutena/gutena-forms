<?php
/**
 * Gutena Forms REST API Settings
 *
 * Enables a public REST endpoint so external apps (e.g. a chat bot) can
 * submit form entries directly into Gutena Forms storage.
 *
 * @since 2.1.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Rest_API_Settings' ) && class_exists( 'Gutena_Forms_Forms_Settings' ) ) :
	/**
	 * Class Gutena_Forms_Rest_API_Settings
	 *
	 * @since 2.1.0
	 */
	class Gutena_Forms_Rest_API_Settings extends Gutena_Forms_Forms_Settings {

		/**
		 * REST API settings.
		 *
		 * @since 2.1.0
		 * @var array $settings
		 */
		public $settings;

		/**
		 * Gutena_Forms_Rest_API_Settings Construct
		 *
		 * @since 2.1.0
		 */
		public function __construct() {
			$this->settings = get_option( 'gutena_forms_rest_api_settings', array() );
			if ( ! is_array( $this->settings ) ) {
				$this->settings = array();
			}
		}

		/**
		 * Register Module
		 *
		 * @since 2.1.0
		 */
		public static function register_module() {
			add_filter(
				'gutena_forms__settings',
				function ( $settings ) {
					$settings['rest-api'] = __CLASS__;
					return $settings;
				}
			);

			include_once plugin_dir_path( __FILE__ ) . 'class-rest-api-endpoint.php';
		}

		/**
		 * Get Settings
		 *
		 * @since 2.1.0
		 * @return array
		 */
		public function get_settings() {
			$enabled      = ! empty( $this->settings['rest_api_enabled'] );
			$require_key   = ! empty( $this->settings['require_api_key'] );
			$api_key      = isset( $this->settings['api_key'] ) ? $this->settings['api_key'] : '';

			$settings = array(
				'title'       => __( 'REST API', 'gutena-forms' ),
				'description' => sprintf(
					/* translators: %1$s: documentation link. */
					__( 'Enable a REST API endpoint so external apps (e.g. a chat bot) can submit form entries directly into your Gutena Forms storage. %1$s', 'gutena-forms' ),
					'<a target="_blank" href="https://gutenaforms.com/docs/rest-api">' . __( 'View documentation', 'gutena-forms' ) . '</a>'
				),
			);

			$settings['fields'] = array(
				array(
					'id'    => 'rest_api_enabled',
					'type'  => 'toggle',
					'name'  => __( 'Enable REST API', 'gutena-forms' ),
					'desc'  => __( 'When enabled, the /submit-entry endpoint accepts submissions. When disabled, the endpoint returns a 404.', 'gutena-forms' ),
					'value' => $enabled,
				),
				array(
					'id'    => 'require_api_key',
					'type'  => 'toggle',
					'name'  => __( 'Require API Key', 'gutena-forms' ),
					'desc'  => __( 'Require an X-Gutena-API-Key header on every request. Strongly recommended to prevent unauthorized submissions.', 'gutena-forms' ),
					'value' => $require_key,
					'attrs' => array(
						'depends_on' => array( 'rest_api_enabled' ),
					),
				),
				array(
					'name'     => 'rest-api-info',
					'type'     => 'field-template',
					'endpoint' => rest_url( 'gutena-forms/v1/submit-entry' ),
					'apiKey'   => $api_key,
					'attrs'    => array(
						'visible_when' => array( 'rest_api_enabled' ),
					),
				),
				array(
					'id'   => 'submit_button',
					'type' => 'submit',
					'name' => __( 'Save Settings', 'gutena-forms' ),
				),
			);

			return $settings;
		}

		/**
		 * Saving settings
		 *
		 * @since 2.1.0
		 * @param array $settings Settings to save.
		 * @return bool
		 */
		public function save_settings( $settings ) {
			if ( ! is_array( $settings ) ) {
				return false;
			}

			// Preserve the existing API key; generate one on first enable.
			if ( empty( $this->settings['api_key'] ) ) {
				$settings['api_key'] = bin2hex( random_bytes( 16 ) );
			} else {
				$settings['api_key'] = $this->settings['api_key'];
			}

			// REST API disabled implies API key requirement is disabled too.
			if ( empty( $settings['rest_api_enabled'] ) ) {
				$settings['require_api_key'] = false;
			}

			update_option( 'gutena_forms_rest_api_settings', $settings );

			return true;
		}
	}

	Gutena_Forms_Rest_API_Settings::register_module();
endif;
