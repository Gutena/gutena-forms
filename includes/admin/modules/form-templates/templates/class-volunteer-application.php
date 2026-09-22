<?php
/**
 * Volunteer Application form template.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Template_Volunteer_Application' ) ) :
	/**
	 * Volunteer Application template definition.
	 *
	 * @since 2.2.0
	 * @package Gutena Forms
	 */
	class Gutena_Forms_Template_Volunteer_Application extends Gutena_Forms_Abstract_Form_Template {
		/**
		 * {@inheritDoc}
		 */
		public static function get_template() {
			return array(
				'id'              => 'volunteer-application',
				'title'           => __( 'Volunteer Application', 'gutena-forms' ),
				'description'     => __( 'Collect volunteer applications with availability, skills, and motivation details.', 'gutena-forms' ),
				'category'        => 'application-forms',
				'is_pro'          => false,
				'preview_image'   => 'assets/img/templates/volunteer-application.png',
				'submit_label'    => __( 'Submit Application', 'gutena-forms' ),
				'success_message' => __( 'Thank you for applying to volunteer with us. We will review your application and get back to you soon.', 'gutena-forms' ),
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
						__( 'Availability', 'gutena-forms' ),
						array(
							__( 'Weekdays', 'gutena-forms' ),
							__( 'Weekends', 'gutena-forms' ),
							__( 'Flexible', 'gutena-forms' ),
						),
						array(
							'isRequired' => true,
						)
					),
					Gutena_Forms_Form_Template_Fields::textarea(
						4,
						__( 'Skills and interests', 'gutena-forms' ),
						array(
							'isRequired'  => true,
							'placeholder' => __( 'Tell us about your skills and areas of interest', 'gutena-forms' ),
						)
					),
					Gutena_Forms_Form_Template_Fields::textarea(
						5,
						__( 'Why do you want to volunteer?', 'gutena-forms' ),
						array(
							'isRequired'  => true,
							'placeholder' => __( 'Share your motivation for volunteering', 'gutena-forms' ),
						)
					),
					Gutena_Forms_Form_Template_Fields::optin(
						6,
						__( 'I agree to the volunteer terms and privacy policy', 'gutena-forms' )
					),
				),
			);
		}
	}
endif;
