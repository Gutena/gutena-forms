<?php
/**
 * Honeypot Settings Class
 *
 * @since 1.7.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Honeypot' ) && class_exists( 'Gutena_Forms_Forms_Settings' ) ) :
	/**
	 * Honeypot anti-spam settings: enable/disable and time limit.
	 *
	 * @since 1.7.0
	 * @package Gutena Forms
	 */
	class Gutena_Forms_Honeypot extends Gutena_Forms_Forms_Settings {
		/**
		 * Current honeypot options (enable_honeypot, time_limit).
		 *
		 * @since 1.7.0
		 * @var array $settings
		 */
		public $settings = array();

		/**
		 * The instance of the class.
		 *
		 * @since 1.8.0
		 * @var Gutena_Forms_Honeypot $instance The instance of the class.
		 */
		private static $instance;

		/**
		 * Load saved honeypot options from the database.
		 *
		 * @since 1.7.0
		 */
		public function __construct() {
			$this->settings = get_option( 'gutena_forms__honeypot', array() );
			if ( ! is_array( $this->settings ) ) {
				$this->settings = array();
			}

			$this->run();
		}

		/**
		 * Get honeypot settings definition (title, description, toggle, number, submit).
		 *
		 * @since 1.7.0
		 * @return array
		 */
		public function get_settings() {
			return array(
				'title'       => __( 'Honeypot Field Settings', 'gutena-forms' ),
				'description' => __( 'Use Honeypot protection to silently block spam bots without affecting real users.', 'gutena-forms' ),
				'fields'      => array(
					array(
						'id'      => 'enable',
						'type'    => 'toggle',
						'name'    => __( 'Enable Honeypot Security', 'gutena-forms' ),
						'default' => false,
						'value'   => isset( $this->settings['enable'] ) ? $this->settings['enable'] : false,
					),
					array(
						'id'      => 'timeCheckValue',
						'type'    => 'number',
						'name'    => __( 'Time Limit (In Seconds)', 'gutena-forms' ),
						'default' => 6,
						'value'   => isset( $this->settings['timeCheckValue'] ) ? $this->settings['timeCheckValue'] : 6,
						'attrs'   => array(
							'min'  => 1,
							'step' => 1,
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
		 * Save honeypot settings to options table.
		 *
		 * @since 1.7.0
		 * @param array $settings Settings to save (enable_honeypot, time_limit, etc.).
		 * @return bool True on success.
		 */
		public function save_settings( $settings ) {
			$previous_global = get_option( 'gutena_forms__honeypot', array() );
			$settings = Gutena_Forms_Settings_Migrator::sanitize_settings_for_option( $settings );

			$block_settings                    = $settings;
			$block_settings['defaultSettings'] = true;

			Gutena_Forms_Settings_Migrator::update_primary_form( 'honeypot', $block_settings );
			update_option( 'gutena_forms__honeypot', $settings );
			Gutena_Forms_Settings_Migrator::sync_global_module_to_forms( 'honeypot', $previous_global, $settings );

			return true;
		}

		/**
		 * Register the honeypot settings module with the gutena_forms__settings filter.
		 *
		 * @since 1.7.0
		 */
		public static function register_module() {
			add_filter(
				'gutena_forms__settings',
				function ( $settings ) {

					$settings['honeypot'] = __CLASS__;

					return $settings;
				}
			);

			self::get_instance();
		}

		/**
		 * Getting the instance of the class
		 *
		 * @since 1.8.0
		 * @return Gutena_Forms_Honeypot
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Running the module
		 *
		 * @since 1.8.0
		 */
		private function run() {
			add_action( 'gutena_forms__saving_block', array( $this, 'save_honeypot' ), 10, 3 );
		}

		/**
		 * Saving the default settings.
		 *
		 * @since 1.8.0
		 * @param array   $attributes The attiributes of the block being saved, including the honeypot settings.
		 * @param array   $blocks All blocks of the post.
		 * @param WP_Post $post Current post object.
		 */
		public function save_honeypot( $attributes, $blocks, $post ) {
			$honeypot = isset( $attributes['honeypot'] ) && is_array( $attributes['honeypot'] ) ? $attributes['honeypot'] : array();

			if ( ! Gutena_Forms_Settings_Migrator::is_single_form_site() ) {
				return;
			}

			if ( empty( $honeypot ) ) {
				return;
			}

			$this->settings = Gutena_Forms_Settings_Migrator::sanitize_settings_for_option( $honeypot );
			update_option( 'gutena_forms__honeypot', $this->settings );
		}
	}

	Gutena_Forms_Honeypot::register_module();
endif;
