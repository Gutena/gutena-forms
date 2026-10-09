<?php
/**
 * Abstract base class for form templates.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Abstract_Form_Template' ) ) :
	/**
	 * Base implementation for form template classes.
	 *
	 * @since 2.2.0
	 */
	abstract class Gutena_Forms_Abstract_Form_Template implements Gutena_Forms_Form_Template_Interface {

		/**
		 * Whether the template requires Pro.
		 *
		 * @since 2.2.0
		 * @return bool
		 */
		public function is_pro() {
			return ! $this->is_free();
		}

		/**
		 * Preview image filename under assets/img/templates/.
		 *
		 * Override in a template class when a dedicated preview image is available.
		 *
		 * @since 2.2.0
		 * @return string
		 */
		protected function get_preview_image_filename() {
			return 'default.png';
		}

		/**
		 * Convert template to registry array format.
		 *
		 * @since 2.2.0
		 * @return array
		 */
		public function to_array() {
			return array(
				'id'          => $this->get_id(),
				'title'       => $this->get_title(),
				'description' => $this->get_description(),
				'category'    => $this->get_category(),
				'is_free'     => $this->is_free(),
				'is_pro'      => $this->is_pro(),
				'preview'     => $this->get_preview(),
				'form_attrs'  => $this->get_form_attrs(),
				'innerBlocks' => $this->get_inner_blocks(),
			);
		}
	}
endif;
