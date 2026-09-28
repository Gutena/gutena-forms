<?php
/**
 * Product tour module bootstrap.
 *
 * @since 1.9.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Tour' ) ) :
	/**
	 * Loads tour preferences, REST endpoints, and editor assets.
	 *
	 * @since 1.9.0
	 */
	class Gutena_Forms_Tour {
		/**
		 * Singleton instance.
		 *
		 * @var Gutena_Forms_Tour|null
		 */
		private static $instance = null;

		/**
		 * Get singleton instance.
		 *
		 * @since 1.9.0
		 * @return Gutena_Forms_Tour
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Register module hooks.
		 *
		 * @since 1.9.0
		 */
		public static function register_module() {
			require_once __DIR__ . '/class-tour-preferences.php';
			require_once __DIR__ . '/class-tour-endpoints.php';

			Gutena_Forms_Tour_Endpoints::get_instance();
			self::get_instance();
		}

		/**
		 * Constructor.
		 *
		 * @since 1.9.0
		 */
		private function __construct() {
			add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_tour_assets' ) );
			add_filter( 'redirect_post_location', array( $this, 'preserve_tour_step_on_redirect' ), 10, 2 );
		}

		/**
		 * Keep the tour step query arg when WordPress redirects post-new.php.
		 *
		 * @since 1.9.0
		 * @param string $location Redirect URL.
		 * @param int    $post_id  Post ID.
		 * @return string
		 */
		public function preserve_tour_step_on_redirect( $location, $post_id ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( ! isset( $_GET['gf_tour_step'] ) ) {
				return $location;
			}

			if ( 'gutena_forms' !== get_post_type( $post_id ) ) {
				return $location;
			}

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$step = absint( wp_unslash( $_GET['gf_tour_step'] ) );

			if ( $step < 0 || $step > 15 ) {
				return $location;
			}

			return add_query_arg( 'gf_tour_step', $step, $location );
		}

		/**
		 * Enqueue the block-editor tour bootstrap on Gutena Forms CPT screens.
		 *
		 * @since 1.9.0
		 */
		public function enqueue_editor_tour_assets() {
			$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

			if ( ! $screen || 'gutena_forms' !== $screen->post_type ) {
				return;
			}

			$asset_file = GUTENA_FORMS_DIR_PATH . 'includes/admin/dashboard/build/editor-index.asset.php';

			if ( ! file_exists( $asset_file ) ) {
				return;
			}

			$asset = include $asset_file;

			wp_register_style(
				'gutena-forms-editor-tour',
				GUTENA_FORMS_PLUGIN_URL . 'includes/admin/dashboard/build/editor-index.css',
				array(),
				$asset['version']
			);

			wp_enqueue_style( 'gutena-forms-editor-tour' );

			wp_enqueue_script(
				'gutena-forms-editor-tour',
				GUTENA_FORMS_PLUGIN_URL . 'includes/admin/dashboard/build/editor-index.js',
				$asset['dependencies'],
				$asset['version'],
				true
			);

			wp_localize_script(
				'gutena-forms-editor-tour',
				'gutenaFormsTour',
				array(
					'adminURL'    => esc_url( admin_url() ),
					'preferences' => Gutena_Forms_Tour_Preferences::get(),
					'dashboardURL' => esc_url( admin_url( 'admin.php?page=gutena-forms' ) ),
				)
			);
		}
	}

	Gutena_Forms_Tour::register_module();
endif;
