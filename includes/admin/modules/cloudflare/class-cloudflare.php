<?php
/**
 * Class Cloudflare Turnstile Settings
 *
 * @since 1.8.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Cloudflare' ) && class_exists( 'Gutena_Forms_Forms_Settings' ) ) :
	class Gutena_Forms_Cloudflare extends Gutena_Forms_Forms_Settings {
		public $settings = array();

		private static $instance;

		public function __construct() {
			$this->settings = get_option(
				'gutena_forms__cloudflare',
				array()
			);

			if ( ! is_array( $this->settings ) ) {
				$this->settings = array();
			}

			$this->run();
		}

		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		public static function register_module() {
			add_filter(
				'gutena_forms__settings',
				function ( $settings ) {

					$settings['cloudflare-turnstile'] = __CLASS__;

					return $settings;
				}
			);

			self::get_instance();
		}

		public function get_settings() {
			return array(
				'id'          => 'cloudflare',
				'title'	 	  => __( 'Cloudflare Turnstile', 'gutena-forms' ),
				'description' => sprintf(
					__( 'Enter your Turnstile API keys below to enable the Cloudflare Turnstile CAPTCHA option. See %1$s to obtain your API keys.', 'gutena-forms' ),
					'<a href="https://developers.cloudflare.com/turnstile/" target="_blank">' . __( 'instructions', 'gutena-forms' ) . '</a>'
				),
				'fields' => array(
					array(
						'id'      => 'enable',
						'type'    => 'toggle',
						'name'    => __( 'Enable Cloudflare', 'gutena-forms' ),
						'default' => false,
						'value'   => isset( $this->settings['enable'] ) ? $this->settings['enable'] : false,
					),
					array(
						'id'      => 'site_key',
						'type'    => 'text',
						'name'    => __( 'Site Key', 'gutena-forms' ),
						'default' => false,
						'value'   => isset( $this->settings['site_key'] ) ? $this->settings['site_key'] : '',
						'attrs'   => array(
							'placeholder' => 'Enter your Cloudflare Turnstile Site Key',
						),
					),
					array(
						'id'      => 'secret_key',
						'type'    => 'text',
						'name'    => __( 'Secret Key', 'gutena-forms' ),
						'default' => false,
						'value'   => isset( $this->settings['secret_key'] ) ? $this->settings['secret_key'] : '',
						'attrs'   => array(
							'placeholder' => 'Enter your Cloudflare Turnstile Secret Key',
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

		public function save_settings( $settings ) {
			update_option( 'gutena_forms__cloudflare', $settings );

			return true;
		}

		private function run() {
			add_action( 'gutena_forms__saving_block', array( $this, 'save_cloudflare' ), 10, 3 );
		}

		/**
		 * @param array   $attributes
		 * @param array   $block
		 * @param WP_Post $post
		 */
		public function save_cloudflare( $attributes, $block, $post ) {
			$default_settings = get_option( 'gutena_forms__cloudflare', array() );

			if ( ! empty( $this->settings ) || ! empty( $default_settings ) ) {
				return;
			}

			if ( empty( $attributes['cloudflareTurnstile'] ) || ! is_array( $attributes['cloudflareTurnstile'] ) ) {
				return;
			}

			foreach ( $attributes['cloudflareTurnstile'] as $k => $v ) {
				if ( in_array( $k, array( 'enable', 'site_key', 'secret_key' ), true ) ) {
					$this->settings[ $k ] = $v;
				}
			}

			$this->save_settings( $this->settings );
		}
	}

	Gutena_Forms_Cloudflare::register_module();
endif;
