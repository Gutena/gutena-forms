<?php
/**
 * Product Feedback form template.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Template_Product_Feedback' ) ) :
	/**
	 * Product Feedback template definition.
	 *
	 * @since 2.2.0
	 * @package Gutena Forms
	 */
	class Gutena_Forms_Template_Product_Feedback extends Gutena_Forms_Abstract_Form_Template {
		/**
		 * {@inheritDoc}
		 */
		public static function get_template() {
			return array(
				'id'              => 'product-feedback',
				'title'           => __( 'Product Feedback Form', 'gutena-forms' ),
				'description'     => __( 'Gather product feedback with ratings, comments, and feature requests.', 'gutena-forms' ),
				'category'        => 'surveys-feedback',
				'is_pro'          => false,
				'preview_image'   => 'assets/img/templates/product-feedback.png',
				'submit_label'    => __( 'Send Feedback', 'gutena-forms' ),
				'success_message' => __( 'Thank you for your product feedback. It helps us build a better experience.', 'gutena-forms' ),
				'fields'          => array(
					Gutena_Forms_Form_Template_Fields::email(
						0,
						__( 'Email address', 'gutena-forms' ),
						array(
							'isRequired'  => true,
							'placeholder' => __( 'example@gmail.com', 'gutena-forms' ),
						)
					),
					Gutena_Forms_Form_Template_Fields::dropdown(
						1,
						__( 'Product used', 'gutena-forms' ),
						array(
							__( 'Web app', 'gutena-forms' ),
							__( 'Mobile app', 'gutena-forms' ),
							__( 'Plugin', 'gutena-forms' ),
							__( 'Other', 'gutena-forms' ),
						),
						array(
							'isRequired' => true,
						)
					),
					Gutena_Forms_Form_Template_Fields::range(
						2,
						__( 'Overall rating', 'gutena-forms' ),
						array(
							'isRequired' => true,
							'minMaxStep' => array(
								'min'  => 1,
								'max'  => 5,
								'step' => 1,
							),
						)
					),
					Gutena_Forms_Form_Template_Fields::textarea(
						3,
						__( 'Your feedback', 'gutena-forms' ),
						array(
							'isRequired'  => true,
							'placeholder' => __( 'Share your experience with the product', 'gutena-forms' ),
						)
					),
					Gutena_Forms_Form_Template_Fields::textarea(
						4,
						__( 'Feature requests', 'gutena-forms' ),
						array(
							'placeholder' => __( 'What features would you like to see next?', 'gutena-forms' ),
						)
					),
				),
			);
		}
	}
endif;
