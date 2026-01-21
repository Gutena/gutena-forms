<?php
/**
 * Gutena Forms Entries Admin Module
 *
 * @since 1.6.0
 * @package GutenaForms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Entries' ) && class_exists( 'Gutena_Forms_Forms_Settings' ) ) :
	/**
	 * Gutena Forms Entries Admin Module
	 *
	 * @since 1.6.0
	 */
	class Gutena_Forms_Entries extends Gutena_Forms_Forms_Settings {
		/**
		 * Singleton instance
		 *
		 * @since 1.6.0
		 * @var Gutena_Forms_Entries $instance Singleton instance of the class.
		 */
		private static $instance;

		/**
		 * Register Module
		 *
		 * @since 1.6.0
		 */
		public static function register_module() {
			add_filter(
				'gutena_forms__settings',
				function ( $settings ) {
					$settings['entries'] = __CLASS__;
					$settings['entry']   = __CLASS__;
					return $settings;
				}
			);

			self::get_instance();
		}

		/**
		 * Get singleton instance
		 *
		 * @since 1.6.0
		 * @return Gutena_Forms_Entries
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @since 1.6.0
		 */
		public function __construct() {
			require_once plugin_dir_path( __FILE__ ) . 'class-entries-endpoints.php';
		}

		/**
		 * Get Settings
		 *
		 * @since 1.6.0
		 * @return array
		 */
		public function get_settings() {
			return array(
				'fields' => array(
					array(
						'type' => 'template',
						'name' => 'entries',
					)
				),
			);
		}

		/**
		 * Save Settings
		 *
		 * @since 1.6.0
		 * @param array $settings Settings to save.
		 */
		public function save_settings( $settings ) {
			// placeholder function.
		}

		public function get_entries_count_by_form_id( $form_id ) {
			global $wpdb;
			$store         = new Gutena_Forms_Store();
			$block_form_id = get_post_meta( $form_id, 'gutena_form_id', true );
			$sql           = 'SELECT COUNT( gutenaFormsEntries.entry_id ) FROM %i gutenaForms LEFT JOIN %i gutenaFormsEntries ON gutenaForms.form_id = gutenaFormsEntries.form_id WHERE gutenaForms.block_form_id = %s';
			$sql           = $wpdb->prepare(
				$sql,
				$store->table_gutenaforms,
				$store->table_gutenaforms_entries,
				$block_form_id
			);

			return intval( $wpdb->get_var( $sql ) );
		}
	}

	Gutena_Forms_Entries::register_module();
endif;
