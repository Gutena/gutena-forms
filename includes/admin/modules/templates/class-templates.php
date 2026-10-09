<?php
/**
 * Templates module bootstrap.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Templates' ) ) :
	/**
	 * Loads template registry, model, and REST endpoints.
	 *
	 * @since 2.2.0
	 */
	class Gutena_Forms_Templates {

		/**
		 * Singleton instance.
		 *
		 * @since 2.2.0
		 * @var Gutena_Forms_Templates|null
		 */
		private static $instance = null;

		/**
		 * Get singleton instance.
		 *
		 * @since 2.2.0
		 * @return Gutena_Forms_Templates
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor.
		 *
		 * @since 2.2.0
		 */
		private function __construct() {
			require_once GUTENA_FORMS_DIR_PATH . 'includes/admin/modules/templates/helpers/class-templates-block-builder.php';
			require_once GUTENA_FORMS_DIR_PATH . 'includes/admin/modules/templates/helpers/class-templates-block-serializer.php';
			require_once GUTENA_FORMS_DIR_PATH . 'includes/admin/modules/templates/helpers/trait-template-spacing.php';
			require_once GUTENA_FORMS_DIR_PATH . 'includes/admin/modules/templates/contracts/interface-form-template.php';
			require_once GUTENA_FORMS_DIR_PATH . 'includes/admin/modules/templates/abstract-class-form-template.php';
			require_once GUTENA_FORMS_DIR_PATH . 'includes/admin/modules/templates/registry/class-templates-loader.php';
			require_once GUTENA_FORMS_DIR_PATH . 'includes/admin/modules/templates/class-templates-registry.php';
			require_once GUTENA_FORMS_DIR_PATH . 'includes/admin/modules/templates/class-templates-model.php';
			require_once GUTENA_FORMS_DIR_PATH . 'includes/admin/modules/templates/class-templates-endpoints.php';

			add_action( 'load-post-new.php', array( $this, 'redirect_post_new' ) );
		}

		/**
		 * Redirect direct post-new.php visits to the template library entry screen.
		 *
		 * @since 2.2.0
		 */
		public function redirect_post_new() {
			global $typenow;

			if ( 'gutena_forms' !== $typenow || ! current_user_can( 'manage_options' ) ) {
				return;
			}

			wp_safe_redirect( admin_url( 'admin.php?page=gutena-forms#/create' ) );
			exit;
		}
	}

	Gutena_Forms_Templates::get_instance();
endif;
