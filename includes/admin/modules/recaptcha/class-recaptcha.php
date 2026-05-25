<?php
/**
 * Class Google reCAPTCHA Module
 *
 * @since 1.8.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_ReCAPTCHA' ) && class_exists( 'Gutena_Forms_Forms_Settings' ) ) :
	/**
	 * Google reCAPTCHA Module class.
	 *
	 * @since 1.8.0
	 */
	class Gutena_Forms_ReCAPTCHA extends Gutena_Forms_Forms_Settings {
		/**
		 * The instance of the class
		 *
		 * @since 1.8.0
		 * @var Gutena_Forms_ReCAPTCHA $instance The instance of the class.
		 */
		private static $instance;

		/**
		 * Recaptcha settings
		 *
		 * @since 1.8.0
		 * @var array $settings Recaptcha settings.
		 */
		public $settings = array();

		/**
		 * ReCAPTCHA Constructor.
		 *
		 * @since 1.8.0
		 */
		public function __construct() {
			$this->settings = get_option( 'gutena_forms__recaptcha', array() );

			$this->run();
		}

		/**
		 * Register this settings module (e.g. add to gutena_forms__settings filter).
		 *
		 * @since 1.8.0
		 */
		public static function register_module() {
			add_filter(
				'gutena_forms__settings',
				function ( $settings ) {
					$settings['google-recaptcha'] = __CLASS__;
					return $settings;
				}
			);

			self::get_instance();
		}

		/**
		 * Get the settings definition (title, description, fields) for the admin UI.
		 *
		 * @since 1.8.0
		 * @return array
		 */
		public function get_settings() {
			return array(
				'id'          => 'recaptcha',
				'title'       => __( 'Google reCAPTCHA Settings', 'gutena-forms' ),
				'description' => __( 'Protect your forms using Google reCAPTCHA to prevent automated spam.', 'gutena-forms' ),
				'fields'      => array(
					array(
						'id'      => 'enable',
						'type'    => 'toggle',
						'name'    => __( 'Enable Google reCAPTCHA Security', 'gutena-forms' ),
						'default' => false,
						'value'   => isset( $this->settings['enable'] ) ? $this->settings['enable'] : false,
					),
					array(
						'id'      => 'type',
						'type'    => 'radio-group',
						'name'    => __( 'reCAPTCHA Version', 'gutena-forms' ),
						'default' => 'v2',
						'value'   => isset( $this->settings['type'] ) ? $this->settings['type'] : 'v2',
						'attrs'   => array(
							'options' => array(
								'v2' => __( 'reCAPTCHA v2', 'gutena-forms' ),
								'v3' => __( 'reCAPTCHA v3', 'gutena-forms' ),
							),
						),
					),
					array(
						'id'      => 'v2_site_key',
						'type'    => 'text',
						'name'    => __( 'V2 Site Key', 'gutena-forms' ),
						'default' => '',
						'value'   => $this->get_value( 'site_key', 'v2' ),
						'attrs'   => array(
							'placeholder' => __( 'Enter you reCAPTCHA site key ', 'gutena-forms' ),
						),
					),
					array(
						'id'      => 'v2_secret_key',
						'type'    => 'text',
						'name'    => __( 'V2 Secret Key', 'gutena-forms' ),
						'default' => '',
						'value'   => $this->get_value( 'secret_key', 'v2' ),
						'attrs'   => array(
							'placeholder' => __( 'Enter you reCAPTCHA secret key ', 'gutena-forms' ),
						),
					),
					array(
						'id'      => 'v3_site_key',
						'type'    => 'text',
						'name'    => __( 'V3 Site Key', 'gutena-forms' ),
						'default' => '',
						'value'   => $this->get_value( 'site_key', 'v3' ),
						'attrs'   => array(
							'placeholder' => __( 'Enter you reCAPTCHA site key ', 'gutena-forms' ),
						),
					),
					array(
						'id'      => 'v3_secret_key',
						'type'    => 'text',
						'name'    => __( 'V3 Secret Key', 'gutena-forms' ),
						'default' => '',
						'value'   => $this->get_value( 'secret_key', 'v3' ),
						'attrs'   => array(
							'placeholder' => __( 'Enter you reCAPTCHA secret key ', 'gutena-forms' ),
						),
					),
					array(
						'id'   => 'submit_button',
						'type' => 'submit',
						'name' => __( 'Save Settings', 'gutena-forms' ),
					),
				),
			);
		}

		/**
		 * Getting the value with fallback.
		 *
		 * @since 1.8.0
		 * @param string $key Site|Secret key.
		 * @param string $version version.
		 *
		 * @return string
		 */
		private function get_value( $key, $version ) {
			if ( ! empty( $this->settings[ $version . '_' . $key ] ) ) {
				return $this->settings[ $version . '_' . $key ];
			}

			return isset( $this->settings[ $key ] ) ? $this->settings[ $key ] : '';
		}

		/**
		 * Resolve effective reCAPTCHA settings with hierarchical key lookup.
		 *
		 * Priority:
		 * 1) Version-specific keys ({version}_site_key / {version}_secret_key)
		 * 2) Generic keys (site_key / secret_key)
		 *
		 * @since 1.8.0
		 * @param array $settings Raw settings array.
		 * @return array
		 */
		public static function resolve_settings( $settings ) {
			$settings = is_array( $settings ) ? $settings : array();
			$type     = ( ! empty( $settings['type'] ) && in_array( $settings['type'], array( 'v2', 'v3' ), true ) ) ? $settings['type'] : 'v2';

			$site_key   = ! empty( $settings[ $type . '_site_key' ] ) ? $settings[ $type . '_site_key' ] : ( isset( $settings['site_key'] ) ? $settings['site_key'] : '' );
			$secret_key = ! empty( $settings[ $type . '_secret_key' ] ) ? $settings[ $type . '_secret_key' ] : ( isset( $settings['secret_key'] ) ? $settings['secret_key'] : '' );

			return array(
				'enable'         => ! empty( $settings['enable'] ),
				'type'           => $type,
				'site_key'       => $site_key,
				'secret_key'     => $secret_key,
				'thresholdScore' => isset( $settings['thresholdScore'] ) ? floatval( $settings['thresholdScore'] ) : 0.5,
			);
		}

		/**
		 * Save settings to storage.
		 *
		 * @since 1.8.0
		 * @param array $settings Settings to save.
		 */
		public function save_settings( $settings ) {
			$previous_global = get_option( 'gutena_forms__recaptcha', array() );
			$settings = Gutena_Forms_Settings_Migrator::sanitize_settings_for_option( $settings );

			$block_settings                    = $settings;
			$block_settings['defaultSettings'] = true;

			Gutena_Forms_Settings_Migrator::update_primary_form( 'recaptcha', $block_settings );
			update_option( 'gutena_forms__recaptcha', $settings );
			Gutena_Forms_Settings_Migrator::sync_global_module_to_forms( 'recaptcha', $previous_global, $settings );
			return true;
		}

		/**
		 * Getting the instance of the class
		 *
		 * @since 1.8.0
		 * @return Gutena_Forms_ReCAPTCHA
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Running whole module
		 */
		public function run() {
			add_action( 'gutena_forms__saving_block', array( $this, 'save_recaptcha_settings' ), 10, 3 );
		}

		/**
		 * Saving recaptcha settings
		 *
		 * @since 1.8.0
		 * @param array   $attributes Block attributes.
		 * @param array   $block All blocks.
		 * @param WP_Post $post Post object.
		 */
		public function save_recaptcha_settings( $attributes, $block, $post ) {
			if ( ! Gutena_Forms_Settings_Migrator::is_single_form_site() ) {
				return;
			}

			$recaptcha = isset( $attributes['recaptcha'] ) && is_array( $attributes['recaptcha'] ) ? $attributes['recaptcha'] : array();
			if ( empty( $recaptcha ) ) {
				return;
			}

			$this->settings = Gutena_Forms_Settings_Migrator::sanitize_settings_for_option( $recaptcha );
			update_option( 'gutena_forms__recaptcha', $this->settings );
		}
	}

	Gutena_Forms_ReCAPTCHA::register_module();
endif;
