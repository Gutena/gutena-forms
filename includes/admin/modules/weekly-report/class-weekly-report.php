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
					'enable_weekly_summary' => false,
					'summary_email'         => false,
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
				'description' => __( 'Manage user access and permissions to control form data security and privacy.', 'gutena-forms' ),
				'fields'      => array(
					array(
						'id'      => 'enable_weekly_summary',
						'type'    => 'toggle',
						'name'    => __( 'Enable Weekly Summary Reports', 'gutena-forms' ),
						'desc'    => __( 'Enable to receive weekly email reports of your forms.', 'gutena-forms' ),
						'default' => false,
						'value'   => $this->settings['enable_weekly_summary'],
					),
					array(
						'id'      => 'summary_email',
						'type'    => 'email',
						'name'    => __( 'Email', 'gutena-forms' ),
						'desc'    => __( 'Enter the email address where you want to receive the weekly reports.', 'gutena-forms' ),
						'default' => get_bloginfo( 'admin_email' ),
						'value'   => $this->settings['summary_email'],
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
