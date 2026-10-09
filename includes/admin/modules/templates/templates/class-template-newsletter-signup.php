<?php
/**
 * Newsletter Signup template.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Template_Newsletter_Signup' ) ) :
	/**
	 * Newsletter Signup form template.
	 *
	 * @since 2.2.0
	 */
	class Gutena_Forms_Template_Newsletter_Signup extends Gutena_Forms_Abstract_Form_Template {
		use Gutena_Forms_Template_Spacing_Trait;

		public function get_id() {
			return 'newsletter-signup';
		}

		public function get_title() {
			return __( 'Newsletter Signup', 'gutena-forms' );
		}

		public function get_description() {
			return __( 'Capture email subscribers for your newsletter.', 'gutena-forms' );
		}

		public function is_free() {
			return true;
		}

		public function get_category() {
			return 'marketing';
		}

		public function get_preview() {
			return array(
				'image'  => $this->get_preview_image_filename(),
				'fields' => array(
					array(
						'label' => __( 'Name', 'gutena-forms' ),
						'type'  => 'text',
					),
					array(
						'label' => __( 'Email', 'gutena-forms' ),
						'type'  => 'email',
					),
				),
			);
		}

		public function get_form_attrs() {
			return array( 'style' => $this->get_basic_spacing() );
		}

		public function get_inner_blocks() {
			$builder = 'Gutena_Forms_Templates_Block_Builder';
			return $builder::with_form_footer(
				array(
					$builder::text_field( 'f_0', __( 'Name', 'gutena-forms' ), __( 'Enter name', 'gutena-forms' ) ),
					$builder::email_field( 'f_1' ),
				),
				__( 'Subscribe', 'gutena-forms' )
			);
		}
	}
endif;
