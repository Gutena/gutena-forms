<?php
/**
 * Customer Satisfaction Survey form template.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Template_Customer_Satisfaction_Survey' ) ) :
	/**
	 * Customer Satisfaction Survey template definition.
	 *
	 * @since 2.2.0
	 * @package Gutena Forms
	 */
	class Gutena_Forms_Template_Customer_Satisfaction_Survey extends Gutena_Forms_Abstract_Form_Template {
		/**
		 * {@inheritDoc}
		 */
		public static function get_template() {
			return array(
				'id'              => 'customer-satisfaction-survey',
				'title'           => __( 'Customer Satisfaction Survey', 'gutena-forms' ),
				'description'     => __( 'Measure customer satisfaction with ratings and open-ended feedback.', 'gutena-forms' ),
				'category'        => 'surveys-feedback',
				'is_pro'          => false,
				'preview_image'   => 'assets/img/templates/customer-satisfaction-survey.png',
				'submit_label'    => __( 'Submit Survey', 'gutena-forms' ),
				'success_message' => __( 'Thank you for sharing your feedback. We appreciate your time.', 'gutena-forms' ),
				'fields'          => array(
					Gutena_Forms_Form_Template_Fields::text(
						0,
						__( 'Name', 'gutena-forms' ),
						array(
							'placeholder' => __( 'Enter your name (optional)', 'gutena-forms' ),
						)
					),
					Gutena_Forms_Form_Template_Fields::email(
						1,
						__( 'Email address', 'gutena-forms' ),
						array(
							'placeholder' => __( 'example@gmail.com (optional)', 'gutena-forms' ),
						)
					),
					Gutena_Forms_Form_Template_Fields::range(
						2,
						__( 'Overall satisfaction', 'gutena-forms' ),
						array(
							'isRequired' => true,
							'minMaxStep' => array(
								'min'  => 1,
								'max'  => 10,
								'step' => 1,
							),
						)
					),
					Gutena_Forms_Form_Template_Fields::textarea(
						3,
						__( 'What did you like most?', 'gutena-forms' ),
						array(
							'placeholder' => __( 'Share what worked well for you', 'gutena-forms' ),
						)
					),
					Gutena_Forms_Form_Template_Fields::textarea(
						4,
						__( 'What could we improve?', 'gutena-forms' ),
						array(
							'placeholder' => __( 'Share suggestions for improvement', 'gutena-forms' ),
						)
					),
					Gutena_Forms_Form_Template_Fields::radio(
						5,
						__( 'Would you recommend us?', 'gutena-forms' ),
						array(
							__( 'Yes', 'gutena-forms' ),
							__( 'No', 'gutena-forms' ),
							__( 'Maybe', 'gutena-forms' ),
						),
						array(
							'isRequired' => true,
						)
					),
				),
			);
		}
	}
endif;
