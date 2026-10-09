<?php
/**
 * Appointment Request template.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Template_Appointment_Request' ) ) :
	/**
	 * Appointment Request form template.
	 *
	 * @since 2.2.0
	 */
	class Gutena_Forms_Template_Appointment_Request extends Gutena_Forms_Abstract_Form_Template {
		use Gutena_Forms_Template_Spacing_Trait;

		public function get_id() {
			return 'appointment-request';
		}

		public function get_title() {
			return __( 'Appointment Request', 'gutena-forms' );
		}

		public function get_description() {
			return __( 'Let visitors request an appointment with preferred date, time, and reason.', 'gutena-forms' );
		}

		public function is_free() {
			return false;
		}

		public function get_category() {
			return 'booking-forms';
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
						'label' => __( 'Phone', 'gutena-forms' ),
						'type'  => 'tel',
					),
					array(
						'label' => __( 'Appointment type', 'gutena-forms' ),
						'type'  => __( 'radio · Consultation / Treatment / Follow-up', 'gutena-forms' ),
					),
					array(
						'label' => __( 'Date', 'gutena-forms' ),
						'type'  => 'date',
					),
					array(
						'label' => __( 'Time preference', 'gutena-forms' ),
						'type'  => __( 'select · Morning / Afternoon / Evening', 'gutena-forms' ),
					),
					array(
						'label' => __( 'Reason', 'gutena-forms' ),
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
					$builder::phone_field( 'f_2', __( 'Phone', 'gutena-forms' ), '', true ),
					$builder::radio_field(
						'f_3',
						__( 'Appointment type', 'gutena-forms' ),
						array(
							__( 'Consultation', 'gutena-forms' ),
							__( 'Treatment', 'gutena-forms' ),
							__( 'Follow-up', 'gutena-forms' ),
						),
						false,
						true,
						3
					),
					$builder::date_field( 'f_4', __( 'Date', 'gutena-forms' ) ),
					$builder::dropdown_field(
						'f_5',
						__( 'Time preference', 'gutena-forms' ),
						array(
							__( 'Morning', 'gutena-forms' ),
							__( 'Afternoon', 'gutena-forms' ),
							__( 'Evening', 'gutena-forms' ),
						),
						false
					),
					$builder::textarea_field( 'f_6', __( 'Reason', 'gutena-forms' ), __( 'Enter description', 'gutena-forms' ), 5, false ),
				)
			);
		}
	}
endif;
