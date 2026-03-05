<?php
/**
 * Weekly summary report admin settings module.
 *
 * @since 1.7.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Weekly_Summary_Report' ) && class_exists( 'Gutena_Forms_Forms_Settings' ) ) :
	/**
	 * Settings module for weekly email summary reports (enable, email address).
	 *
	 * @since 1.7.0
	 * @package Gutena Forms
	 */
	class Gutena_Forms_Weekly_Summary_Report extends Gutena_Forms_Forms_Settings {
		/**
		 * Cached options: enable_weekly_summary, summary_email.
		 *
		 * @since 1.7.0
		 * @var array $settings
		 */
		private $settings = array();

		/**
		 * Load weekly report options from the database.
		 *
		 * @since 1.7.0
		 */
		public function __construct() {
			$this->settings = get_option(
				'gutena_forms_weekly_report',
				array(
					'recipient_email' => false,
					'enabled'         => false,
				)
			);
		}

		/**
		 * Register the weekly-summary settings module with the gutena_forms__settings filter.
		 *
		 * @since 1.7.0
		 */
		public static function register_module() {
			add_filter(
				'gutena_forms__settings',
				function ( $settings ) {
					$settings['weekly-summary'] = __CLASS__;
					return $settings;
				}
			);
		}

		/**
		 * Get settings definition for weekly summary (toggle, email, submit).
		 *
		 * @since 1.7.0
		 * @return array
		 */
		public function get_settings() {
			return array(
				'title'       => __( 'Weekly Forms Summary', 'gutena-forms' ),
				'description' => __( 'Would you like to receive a weekly report showing how your forms are performing? Enable Email Summaries to receive a detailed report every week at your selected email address.', 'gutena-forms' ),
				'fields'      => array(
					array(
						'id'      => 'enabled',
						'type'    => 'toggle',
						'name'    => __( 'Enable Weekly Summary Reports', 'gutena-forms' ),
						'default' => false,
						'value'   => $this->settings['enabled'],
					),
					array(
						'id'      => 'recipient_email',
						'type'    => 'email',
						'name'    => __( 'Email', 'gutena-forms' ),
						'desc'    => __( 'Enter the email address where you want to receive the weekly reports.', 'gutena-forms' ),
						'default' => get_bloginfo( 'admin_email' ),
						'value'   => $this->settings['recipient_email'],
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
		 * Save weekly report settings to options table.
		 *
		 * @since 1.7.0
		 * @param array $settings Settings to save (enable_weekly_summary, summary_email).
		 * @return bool True on success.
		 */
		public function save_settings( $settings ) {
			update_option( 'gutena_forms_weekly_report', $settings );

			return true;
		}
	}

	Gutena_Forms_Weekly_Summary_Report::register_module();
endif;
