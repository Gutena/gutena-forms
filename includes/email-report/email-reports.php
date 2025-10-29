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
		private static $instance;

		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		private function __construct() {
			add_action( 'init', array( $this, 'register_cron_event' ) );
			add_action( 'init', array( $this, 'weekly_report' ) ); // todo change 'init' to 'gutena_weekly_summary_report'
			add_filter( 'gutena_forms__get_total_entries', array( $this, 'query_total_entries' ) );
			add_filter( 'gutena_forms__get_entries', array( $this, 'query_entries' ) );
		}

		/**
		 * Register Cron Event
		 */
		public function register_cron_event() {
			if ( ! wp_next_scheduled( 'gutena_weekly_summary_report' ) ) {
				wp_schedule_event( time(), 'weekly', 'gutena_weekly_summary_report' );
			}
		}

		/**
		 * Weekly Report
		 */
		public function weekly_report() {
			exit( $this->get_body() );
		}

		private function get_subject() {
			return apply_filters( 'gutena_forms__email_report_subject',
			'Your Weekly Gutena Form Report'
			);
		}

	private function get_body() {
		ob_start();
		include_once 'templates/email-report-template.php';
		return ob_get_clean();
	}

	/**
	 * Query Total Entries
	 *
	 * @param int    $total Total entries.
	 * @param string $period Time period (week, month, etc.).
	 * @return int Total entries count.
	 */
	public function query_total_entries( $total ) {
		// If total is already set, return it
		if ( $total > 0 ) {
			return $total;
		}

		global $wpdb;

		// Calculate date range for the week
		$start_date = gmdate( 'Y-m-d H:i:s', strtotime( '-7 days' ) );
		$end_date   = gmdate( 'Y-m-d H:i:s' );

		// Query form entries from the database
		$table_name = $wpdb->prefix . 'gutenaforms_entries';
		$sql        = 'SELECT COUNT(*) FROM %i WHERE added_time >= %s AND added_time <= %s AND trash = 0';
		$sql        = $wpdb->prepare( $sql, $table_name, $start_date, $end_date );
		$total      = $wpdb->get_var( $sql );

		return (int) $total;
	}

	/**
	 * Query Entries
	 *
	 * @param array  $entries Entries array.
	 * @param string $period  Time period (week, month, etc.).
	 * @return array Entries with form name and count.
	 */
	public function query_entries( $entries ) {
		// If entries are already set, return them
		if ( ! empty( $entries ) ) {
			return $entries;
		}

		global $wpdb;

		// Calculate date range for the week
		$start_date = gmdate( 'Y-m-d H:i:s', strtotime( '-7 days' ) );
		$end_date   = gmdate( 'Y-m-d H:i:s' );

		// Query form entries grouped by form
		$table_entries = $wpdb->prefix . 'gutenaforms_entries';
		$table_forms   = $wpdb->prefix . 'gutenaforms';
		$sql           = 'SELECT f.form_name, COUNT(*) as count FROM %i e INNER JOIN %i f ON e.form_id = f.form_id WHERE e.added_time >= %s AND e.added_time <= %s AND e.trash = 0 GROUP BY e.form_id ORDER BY count DESC';
		$sql           = $wpdb->prepare( $sql, $table_entries, $table_forms, $start_date, $end_date );
		$results       = $wpdb->get_results( $sql );

		// Format results
		$entries = array();
		if ( $results ) {
			foreach ( $results as $row ) {
				$entries[] = array(
					'form-name' => $row->form_name,
					'count'     => (int) $row->count,
				);
			}
		}

		return $entries;
	}
}

	Gutena_Forms_Email_Reports::get_instance();
endif;
