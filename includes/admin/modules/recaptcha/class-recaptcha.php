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

		private static $instance;

		public $settings = array();

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
				'title'	 	  => __( 'Google reCAPTCHA Settings', 'gutena-forms' ),
				'description' => sprintf(
					__( 'Enter the API keys for each reCAPTCHA type you use. Each type needs its own set of keys, which you can get from. %1$s', 'gutena-forms' ),
					'<a href="https://www.google.com/recaptcha/admin" target="_blank">' . __( 'Generate API keys', 'gutena-forms' ) . '</a>'
				),
				'fields' => array(
					array(
						'id'      => 'enable',
						'type'    => 'toggle',
						'name'    => __( 'Enable Google reCAPTCHA Security', 'gutena-forms' ),
						'desc'    => __( 'Enable Google reCAPTCHA to protect your forms from automated spam submissions.', 'gutena-forms' ),
						'default' => false,
						'value'   => $this->settings['enable'],
					),
					array(
						'id'      => 'type',
						'type'    => 'radio-group',
						'name'    => __( 'reCAPTCHA Version', 'gutena-forms' ),
						'default' => 'v2',
						'value'   => $this->settings['type'],
						'attrs'   => array(
							'options' => array(
								'v2' => __( 'reCAPTCHA v2', 'gutena-forms' ),
								'v3' => __( 'reCAPTCHA v3', 'gutena-forms' ),
							),
						),
					),
					array(
						'id'      => 'site_key',
						'type'    => 'text',
						'name'    => __( 'Site Key', 'gutena-forms' ),
						'default' => '',
						'value'   => $this->settings['site_key'],
						'attrs'   => array(
							'placeholder' => __( 'Enter your reCAPTCHA site key', 'gutena-forms' ),
						),
					),
					array(
						'id'      => 'secret_key',
						'type'    => 'text',
						'name'    => __( 'Secret Key', 'gutena-forms' ),
						'default' => '',
						'value'   => $this->settings['secret_key'],
						'attrs'   => array(
							'placeholder' => __( 'Enter your reCAPTCHA secret key', 'gutena-forms' ),
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
		 * Save settings to storage.
		 *
		 * @since 1.8.0
		 * @param array $settings Settings to save.
		 */
		public function save_settings( $settings ) {
			update_option( 'gutena_forms__recaptcha', $settings );

			return true;
		}

		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		public function run() {
			add_action( 'gutena_forms__saving_block', array( $this, 'save_recaptcha_settings' ), 10, 3 );
		}

		/**
		 * @param array   $attributes
		 * @param array   $block
		 * @param WP_Post $post
		 */
		public function save_recaptcha_settings( $attributes, $block, $post ) {
			$default_settings = get_option( 'gutena_forms__recaptcha', array() );
			if ( ! empty( $this->settings ) || ! empty( $default_settings ) ) {
				return;
			}

			foreach ( $attributes['recaptcha'] as $k => $v ) {
				$this->settings[ $k ] = $v;
			}

			$this->save_settings( $this->settings );
		}
	}

	Gutena_Forms_ReCAPTCHA::register_module();
endif;
