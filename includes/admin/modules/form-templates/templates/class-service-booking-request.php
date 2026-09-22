<?php
/**
 * Service Booking Request form template.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Template_Service_Booking_Request' ) ) :
	/**
	 * Service Booking Request template definition.
	 *
	 * @since 2.2.0
	 * @package Gutena Forms
	 */
	class Gutena_Forms_Template_Service_Booking_Request extends Gutena_Forms_Abstract_Form_Template {
		/**
		 * {@inheritDoc}
		 */
		public static function get_template() {
			return array(
				'id'              => 'service-booking-request',
				'title'           => __( 'Service Booking Request', 'gutena-forms' ),
				'description'     => __( 'Let customers request a service appointment with preferred date and time.', 'gutena-forms' ),
				'category'        => 'booking-forms',
				'is_pro'          => false,
				'preview_image'   => 'assets/img/templates/service-booking-request.png',
				'submit_label'    => __( 'Request Booking', 'gutena-forms' ),
				'success_message' => __( 'Your booking request has been received. We will confirm your appointment shortly.', 'gutena-forms' ),
				'fields'          => array(
					Gutena_Forms_Form_Template_Fields::text(
						0,
						__( 'Full name', 'gutena-forms' ),
						array(
							'isRequired'  => true,
							'placeholder' => __( 'Enter your full name', 'gutena-forms' ),
						)
					),
					Gutena_Forms_Form_Template_Fields::email(
						1,
						__( 'Email address', 'gutena-forms' ),
						array(
							'isRequired'  => true,
							'placeholder' => __( 'example@gmail.com', 'gutena-forms' ),
						)
					),
					Gutena_Forms_Form_Template_Fields::text(
						2,
						__( 'Phone number', 'gutena-forms' ),
						array(
							'isRequired'  => true,
							'placeholder' => __( 'Enter your phone number', 'gutena-forms' ),
						)
					),
					Gutena_Forms_Form_Template_Fields::dropdown(
						3,
						__( 'Service type', 'gutena-forms' ),
						array(
							__( 'Consultation', 'gutena-forms' ),
							__( 'Installation', 'gutena-forms' ),
							__( 'Maintenance', 'gutena-forms' ),
							__( 'Other', 'gutena-forms' ),
						),
						array(
							'isRequired' => true,
						)
					),
					Gutena_Forms_Form_Template_Fields::text(
						4,
						__( 'Preferred date', 'gutena-forms' ),
						array(
							'isRequired'  => true,
							'placeholder' => __( 'e.g. March 15, 2026', 'gutena-forms' ),
						)
					),
					Gutena_Forms_Form_Template_Fields::text(
						5,
						__( 'Preferred time', 'gutena-forms' ),
						array(
							'isRequired'  => true,
							'placeholder' => __( 'e.g. 10:00 AM', 'gutena-forms' ),
						)
					),
					Gutena_Forms_Form_Template_Fields::textarea(
						6,
						__( 'Additional notes', 'gutena-forms' ),
						array(
							'placeholder' => __( 'Share any special requests or details', 'gutena-forms' ),
						)
					),
				),
			);
		}
	}
endif;
