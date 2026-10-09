<?php
/**
 * Customer Satisfaction Survey template.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Template_Customer_Satisfaction_Survey' ) ) :
	/**
	 * Customer Satisfaction Survey form template.
	 *
	 * @since 2.2.0
	 */
	class Gutena_Forms_Template_Customer_Satisfaction_Survey extends Gutena_Forms_Abstract_Form_Template {
		use Gutena_Forms_Template_Spacing_Trait;

		public function get_id() {
			return 'customer-satisfaction-survey';
		}

		public function get_title() {
			return __( 'Customer Satisfaction Survey', 'gutena-forms' );
		}

		public function get_description() {
			return __( 'Gather detailed customer satisfaction feedback with star ratings.', 'gutena-forms' );
		}

		public function is_free() {
			return false;
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
						'label' => __( 'Quality', 'gutena-forms' ),
						'type'  => 'rating',
					),
					array(
						'label' => __( 'Price', 'gutena-forms' ),
						'type'  => 'rating',
					),
					array(
						'label' => __( 'Overall Satisfaction', 'gutena-forms' ),
						'type'  => 'rating',
					),
					array(
						'label' => __( 'Feedback', 'gutena-forms' ),
						'type'  => 'textarea',
					),
					array(
						'label' => __( 'Support', 'gutena-forms' ),
						'type'  => __( 'radio · Yes / No / Not Sure', 'gutena-forms' ),
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
					$builder::text_field( 'f_0', __( 'Name', 'gutena-forms' ), __( 'Enter name', 'gutena-forms' ), false ),
					$builder::email_field( 'f_1', __( 'Email', 'gutena-forms' ), __( 'Enter email', 'gutena-forms' ), false ),
					$builder::rating_field( 'f_2', __( 'Quality', 'gutena-forms' ) ),
					$builder::rating_field( 'f_3', __( 'Price', 'gutena-forms' ) ),
					$builder::rating_field( 'f_4', __( 'Purchase Experience', 'gutena-forms' ) ),
					$builder::rating_field( 'f_5', __( 'Usage Experience', 'gutena-forms' ) ),
					$builder::rating_field( 'f_6', __( 'Customer Service', 'gutena-forms' ) ),
					$builder::rating_field( 'f_7', __( 'Overall, how satisfied are you with the product / service?', 'gutena-forms' ), true ),
					$builder::textarea_field( 'f_8', __( 'What do you like about our product / service?', 'gutena-forms' ), __( 'Enter description', 'gutena-forms' ), 5, false ),
					$builder::textarea_field( 'f_9', __( 'What do you dislike about our product / service?', 'gutena-forms' ), __( 'Enter description', 'gutena-forms' ), 5, false ),
					$builder::rating_field( 'f_10', __( 'Behaviour', 'gutena-forms' ) ),
					$builder::rating_field( 'f_11', __( 'Knowledge', 'gutena-forms' ) ),
					$builder::rating_field( 'f_12', __( 'Promptness', 'gutena-forms' ) ),
					$builder::radio_field(
						'f_13',
						__( 'Would you use our customer support in the future?', 'gutena-forms' ),
						array(
							__( 'Yes', 'gutena-forms' ),
							__( 'No', 'gutena-forms' ),
							__( 'Not Sure', 'gutena-forms' ),
						),
						false,
						true,
						3
					),
					$builder::textarea_field( 'f_14', __( 'How can we improve our customer support further?', 'gutena-forms' ), __( 'Enter description', 'gutena-forms' ), 5, false ),
				)
			);
		}
	}
endif;
