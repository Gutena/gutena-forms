<?php
/**
 * Product Feedback template.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Template_Product_Feedback' ) ) :
	/**
	 * Product Feedback form template.
	 *
	 * @since 2.2.0
	 */
	class Gutena_Forms_Template_Product_Feedback extends Gutena_Forms_Abstract_Form_Template {
		use Gutena_Forms_Template_Spacing_Trait;

		public function get_id() {
			return 'product-feedback';
		}

		public function get_title() {
			return __( 'Product Feedback', 'gutena-forms' );
		}

		public function get_description() {
			return __( 'Collect product feedback with ratings and improvement areas.', 'gutena-forms' );
		}

		public function is_free() {
			return true;
		}

		public function get_category() {
			return 'surveys-feedback';
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
					array(
						'label' => __( 'Overall Rating', 'gutena-forms' ),
						'type'  => __( 'radio · Excellent / Good / Average / Poor', 'gutena-forms' ),
					),
					array(
						'label' => __( 'Areas for Improvement', 'gutena-forms' ),
						'type'  => __( 'checkbox · Usability / Features / Performance / Support', 'gutena-forms' ),
					),
					array(
						'label' => __( 'Comments', 'gutena-forms' ),
						'type'  => 'textarea',
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
					$builder::radio_field(
						'f_2',
						__( 'Overall Rating', 'gutena-forms' ),
						array(
							__( 'Excellent', 'gutena-forms' ),
							__( 'Good', 'gutena-forms' ),
							__( 'Average', 'gutena-forms' ),
							__( 'Poor', 'gutena-forms' ),
						),
						true,
						true,
						4
					),
					$builder::checkbox_field(
						'f_3',
						__( 'Areas for Improvement', 'gutena-forms' ),
						array(
							__( 'Usability', 'gutena-forms' ),
							__( 'Features', 'gutena-forms' ),
							__( 'Performance', 'gutena-forms' ),
							__( 'Customer Support', 'gutena-forms' ),
						),
						false,
						true,
						4
					),
					$builder::textarea_field( 'f_4', __( 'Comments', 'gutena-forms' ), __( 'Enter description', 'gutena-forms' ), 5, false ),
				),
				__( 'Send Feedback', 'gutena-forms' )
			);
		}
	}
endif;
