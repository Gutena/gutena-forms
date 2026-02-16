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

		public function __construct() {
			$this->settings = get_option(
				'gutena_forms__cloudflare',
				array()
			);
		}
		public static function register_module() {
			add_filter(
				'gutena_forms__settings',
				function ( $settings ) {

					$settings['cloudflare-turnstile'] = __CLASS__;

					return $settings;
				}
			);
		}

		public function get_settings() {
			return array(
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
						'desc'    => __( 'Enable cloudflare for preventing bots.', 'gutena-forms' ),
						'default' => false,
						'value'   => $this->settings['enable'],
					),
					array(
						'id'      => 'site-key',
						'type'    => 'text',
						'name'    => __( 'Site Key', 'gutena-forms' ),
						'default' => false,
						'value'   => $this->settings['site-key'],
						'attrs'   => array(
							'placeholder' => 'Enter your Cloudflare Turnstile Site Key',
						),
					),
					array(
						'id'      => 'secret-key',
						'type'    => 'text',
						'name'    => __( 'Secret Key', 'gutena-forms' ),
						'default' => false,
						'value'   => $this->settings['secret-key'],
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
	}

	Gutena_Forms_Cloudflare::register_module();
endif;
