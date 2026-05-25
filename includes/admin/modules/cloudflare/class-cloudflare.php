<?php
/**
 * Class Cloudflare Turnstile Settings
 *
 * @since 1.8.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Cloudflare' ) && class_exists( 'Gutena_Forms_Forms_Settings' ) ) :
	/**
	 * Cloudflare Turnstile Settings
	 *
	 * @since 1.8.0
	 */
	class Gutena_Forms_Cloudflare extends Gutena_Forms_Forms_Settings {
		/**
		 * Settings for cloudflare
		 *
		 * @since 1.8.0
		 * @var array $settings Settings for cloudflare.
		 */
		public $settings = array();

		/**
		 * The instance of the class
		 *
		 * @since 1.8.0
		 * @var Gutena_Forms_Cloudflare $instance Instance of the class.
		 */
		private static $instance;

		/**
		 * Constructor of the class
		 *
		 * @since 1.8.0
		 */
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

		/**
		 * Getting the instance of the class
		 *
		 * @since 1.8.0
		 * @return Gutena_Forms_Cloudflare
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Registering the whole module
		 *
		 * @since 1.8.0
		 */
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

		/**
		 * Getting the settings for the module
		 *
		 * @since 1.8.0
		 * @return array
		 */
		public function get_settings() {
			return array(
				'id'          => 'cloudflare',
				'title'       => __( 'Cloudflare Turnstile', 'gutena-forms' ),
				'description' => __( 'Secure your forms with Cloudflare Turnstile to block bots without affecting user experience.', 'gutena-forms' ),
				'fields'      => array(
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

		/**
		 * Saving the settings
		 *
		 * @since 1.8.0
		 * @param array $settings The settings to save.
		 *
		 * @return true
		 */
		public function save_settings( $settings ) {
			$previous_global = get_option( 'gutena_forms__cloudflare', array() );
			$settings = Gutena_Forms_Settings_Migrator::sanitize_settings_for_option( $settings );

			$block_settings                    = $settings;
			$block_settings['defaultSettings'] = true;

			Gutena_Forms_Settings_Migrator::update_primary_form( 'cloudflareTurnstile', $block_settings );

			update_option( 'gutena_forms__cloudflare', $settings );
			Gutena_Forms_Settings_Migrator::sync_global_module_to_forms( 'cloudflareTurnstile', $previous_global, $settings );

			return true;
		}

		/**
		 * Running the current module
		 *
		 * @since 1.8.0
		 */
		private function run() {
			add_action( 'gutena_forms__saving_block', array( $this, 'save_cloudflare' ), 10, 3 );
		}

		/**
		 * Saving cloudflare settings.
		 *
		 * @since 1.8.0
		 * @param array   $attributes Block attributes.
		 * @param array   $block Current block.
		 * @param WP_Post $post Post object.
		 */
		public function save_cloudflare( $attributes, $block, $post ) {
			if ( ! Gutena_Forms_Settings_Migrator::is_single_form_site() ) {
				return;
			}

			if ( empty( $attributes['cloudflareTurnstile'] ) || ! is_array( $attributes['cloudflareTurnstile'] ) ) {
				return;
			}

			$this->settings = Gutena_Forms_Settings_Migrator::sanitize_settings_for_option( $attributes['cloudflareTurnstile'] );
			update_option( 'gutena_forms__cloudflare', $this->settings );
		}
	}

	Gutena_Forms_Cloudflare::register_module();
endif;
