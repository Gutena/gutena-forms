<?php
/**
 * Form template contract.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! interface_exists( 'Gutena_Forms_Form_Template_Interface' ) ) :
	/**
	 * Contract for form template definitions.
	 *
	 * @since 2.2.0
	 */
	interface Gutena_Forms_Form_Template_Interface {
		/**
		 * Template slug.
		 *
		 * @return string
		 */
		public function get_id();

		/**
		 * Template title.
		 *
		 * @return string
		 */
		public function get_title();

		/**
		 * Template description.
		 *
		 * @return string
		 */
		public function get_description();

		/**
		 * Whether the template is available on the free tier.
		 *
		 * @return bool
		 */
		public function is_free();

		/**
		 * Template category slug.
		 *
		 * @return string
		 */
		public function get_category();

		/**
		 * Preview metadata for the library UI.
		 *
		 * @return array
		 */
		public function get_preview();

		/**
		 * gutena/forms block attributes.
		 *
		 * @return array
		 */
		public function get_form_attrs();

		/**
		 * Inner block templates.
		 *
		 * @return array
		 */
		public function get_inner_blocks();
	}
endif;
