<?php
/**
 * Gutena Forms Email Reports Class
 *
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Email_Reports' ) ) :

	/**
	 * Gutena Forms Email Reports Class
	 */
	class Gutena_Forms_Email_Reports {
		/**
		 * Singleton instance
		 *
		 * @var Gutena_Forms_Email_Reports Singleton instance of the class.
		 */
		private static $instance;

		/**
		 * Get singleton instance.
		 *
		 * @return Gutena_Forms_Email_Reports
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor.
		 */
		private function __construct() {
			add_action( 'init', array( $this, 'register_cron_schedule' ) );
			add_action( 'admin_init', array( $this, 'admin_init' ) );
			add_action( 'gutena_forms_weekly_report', array( $this, 'gutena_forms_weekly_report' ) );
			add_action( 'gutena_forms_entries_load_custom_page', array( $this, 'settings_fields' ) );

			$this->filter_email_data();
		}

		public function settings_fields() {
			if ( isset( $_GET['pagetype'] ) && 'forms-summary-report' === sanitize_text_field( wp_unslash( $_GET['pagetype'] ) ) ) {
				echo '<div style="padding: 0 20px" class="wrap">
				<form action="options.php" method="post">';

				settings_fields( 'gutena_forms_weekly_report' );
				do_settings_sections( 'weekly-forms-summary' );
				submit_button();

				echo '</form>
			</div>';
			}
		}

		public function admin_init() {
			register_setting( 'gutena_forms_weekly_report', 'gutena_forms_weekly_report', array( $this, 'sanitize_form_args' ) );

			add_settings_section( 'gutenaforms-weekly-report', __( 'Weekly Forms Summary', 'gutena-forms' ), '__return_null', 'weekly-forms-summary' );
			add_settings_field( 'weekly-report-enable', __( 'Enable', 'gutena-forms' ), array( $this, 'enable_disable_field' ), 'weekly-forms-summary', 'gutenaforms-weekly-report', array( 'label_for' => 'enable' ) );
			add_settings_field( 'weekly-report-email', __( 'Email', 'gutena-forms' ), array( $this, 'email_field' ), 'weekly-forms-summary', 'gutenaforms-weekly-report', array( 'label_for' => 'email' ) );
		}

		public function enable_disable_field() {
			$settings = get_option( 'gutena_forms_weekly_report', array( 'enabled' => 1 ) );
			$enabled  = is_array( $settings ) && ! empty( $settings['enabled'] ) ? $settings['enabled'] : '';
			?>
			<input type="checkbox" id="enable" name="gutena_forms_weekly_report[enabled]" value="1" <?php checked( $enabled, '1' ); ?> />
			<p class="desc">
				<strong>
					<?php esc_html_e( 'Enable to receive weekly email reports of your forms.', 'gutena-forms' ); ?>
				</strong>
			</p>
			<?php
		}

		public function email_field() {
			$settings = get_option( 'gutena_forms_weekly_report', array( 'recipient_email' => get_option( 'admin_email', '' ) ) );
			$email    = is_array( $settings ) && ! empty( $settings['recipient_email'] ) ? $settings['recipient_email'] : '';
			?>
			<input type="email" id="recipient_email" name="gutena_forms_weekly_report[recipient_email]" value="<?php echo esc_attr( $email ); ?>" class="regular-text" />
			<p class="desc">
				<strong>
					<?php esc_html_e( 'Enter the email address where you want to receive the weekly reports.', 'gutena-forms' ); ?>
				</strong>
			</p>
			<?php
		}

		public function sanitize_form_args( $fields ) {
			return $fields;
		}

		/**
		 * Register cron schedule for weekly reports.
		 */
		public function register_cron_schedule() {
			$settings = get_option( 'gutena_forms_weekly_report' );
			if ( is_array( $settings ) && ! empty( $settings['enabled'] ) ) {
				if ( ! wp_next_scheduled( 'gutena_forms_weekly_report' ) ) {
					wp_schedule_event( current_time( 'timestamp' ), 'weekly', 'gutena_forms_weekly_report' );
				}
			} else {
				// Unschedule if previously scheduled.
				$timestamp = wp_next_scheduled( 'gutena_forms_weekly_report' );
				if ( $timestamp ) {
					wp_unschedule_event( $timestamp, 'gutena_forms_weekly_report' );
				}
			}
		}

		/**
		 * Send weekly forms report email.
		 */
		public function gutena_forms_weekly_report() {
			$headers  = array(
				'Content-Type: text/html; charset=UTF-8',
			);
			$subject  = __( 'Your Weekly Gutena Form Report', 'gutena-forms' );
			$settings = get_option( 'gutena_forms_weekly_report' );
			$email    = is_array( $settings ) && ! empty( $settings['recipient_email'] ) ? $settings['recipient_email'] : '';

			wp_mail( $email, $subject, $this->get_email_content(), $headers );
		}

		/**
		 * Get email content.
		 *
		 * @return false|string
		 */
		private function get_email_content() {
			ob_start();
			require plugin_dir_path( __FILE__ ) . 'templates/email-report-template.php';
			return ob_get_clean();
		}

		/**
		 * Filter email data.
		 */
		private function filter_email_data() {
			add_filter( 'gutena_forms__get_total_entries', array( $this, 'get_total_entries' ) );
			add_filter( 'gutena_forms__get_entries', array( $this, 'get_entries' ) );
		}

		/**
		 * Get total entries in the last week.
		 *
		 * @return string
		 */
		public function get_total_entries() {
			global $wpdb;
			$last_week = date( 'Y-m-d H:i:s', strtotime( '-7 days' ) );
			$sql 	   = 'SELECT COUNT( * ) FROM %i WHERE added_time >= %s AND trash = 0;';
			$sql       = $wpdb->prepare( $sql, $wpdb->prefix . 'gutenaforms_entries', $last_week );
			return $wpdb->get_var( $sql );
		}

		/**
		 * Get entries grouped by form in the last week.
		 *
		 * @return array
		 */
		public function get_entries() {
			global $wpdb;
			$last_week = date( 'Y-m-d H:i:s', strtotime( '-7 days' ) );
			$sql       = 'SELECT COUNT(*) as entries_count, gutenaforms.form_name, gutenaforms.form_id FROM %i gutenaforms LEFT JOIN %i gutenaforms_entries ON gutenaforms.form_id = gutenaforms_entries.form_id WHERE gutenaforms.published = 1 AND gutenaforms_entries.trash = 0 AND gutenaforms_entries.added_time >= %s GROUP BY gutenaforms_entries.form_id;';
			$sql       = $wpdb->prepare( $sql, $wpdb->prefix . 'gutenaforms', $wpdb->prefix . 'gutenaforms_entries', $last_week );
			return $wpdb->get_results( $sql, ARRAY_A );
		}

		public static function calculate_percentage_change( $new_value, $old_value ) {
			if ( 0 == $old_value ) {
				return 0;
			}
			$percent = ( ( $new_value - $old_value ) / $old_value ) * 100;

			return round( $percent, 2 );
		}
	}

	Gutena_Forms_Email_Reports::get_instance();
endif;
