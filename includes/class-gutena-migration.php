<?php
/**
 * Gutena Forms Migration Class
 *
 * @since 1.4.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Migration' ) ) :
	/**
	 * Gutena Forms Migration Class
	 *
	 * @since 1.4.0
	 */
	class Gutena_Forms_Migration {
		/**
		 * Singleton instance of the class.
		 *
		 * @since 1.4.0
		 * @var Gutena_Forms_Migration The single instance of the class.
		 */
		private static $instance;

		/**
		 * Private constructor to prevent direct instantiation.
		 *
		 * @since 1.4.0
		 */
		private function __construct() {
		}

		/**
		 * Get the singleton instance of the class.
		 *
		 * @since 1.4.0
		 * @return Gutena_Forms_Migration
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}
	}

	Gutena_Forms_Migration::get_instance();
endif;
