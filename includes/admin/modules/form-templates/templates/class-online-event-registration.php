<?php
/**
 * Online Event Registration form template.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Template_Online_Event_Registration' ) ) :
	/**
	 * Online Event Registration template definition.
	 *
	 * @since 2.2.0
	 * @package Gutena Forms
	 */
	class Gutena_Forms_Template_Online_Event_Registration extends Gutena_Forms_Abstract_Form_Template {
		/**
		 * {@inheritDoc}
		 */
		public static function get_template() {
			return array(
				'id'              => 'online-event-registration',
				'title'           => __( 'Online Event Registration', 'gutena-forms' ),
				'description'     => __( 'Register attendees for webinars and virtual events with professional details.', 'gutena-forms' ),
				'category'        => 'event-planning',
				'is_pro'          => false,
				'preview_image'   => 'assets/img/templates/online-event-registration.png',
				'submit_label'    => __( 'Register Now', 'gutena-forms' ),
				'success_message' => __( 'You are registered for the event. Check your email for joining details.', 'gutena-forms' ),
				'fields'          => array(
					Gutena_Forms_Form_Template_Fields::text(
						0,
						__( 'First name', 'gutena-forms' ),
						array(
							'isRequired'  => true,
							'placeholder' => __( 'Enter your first name', 'gutena-forms' ),
						)
					),
					Gutena_Forms_Form_Template_Fields::text(
						1,
						__( 'Last name', 'gutena-forms' ),
						array(
							'isRequired'  => true,
							'placeholder' => __( 'Enter your last name', 'gutena-forms' ),
						)
					),
					Gutena_Forms_Form_Template_Fields::email(
						2,
						__( 'Email address', 'gutena-forms' ),
						array(
							'isRequired'  => true,
							'placeholder' => __( 'example@gmail.com', 'gutena-forms' ),
						)
					),
					Gutena_Forms_Form_Template_Fields::text(
						3,
						__( 'Company', 'gutena-forms' ),
						array(
							'placeholder' => __( 'Enter your company name', 'gutena-forms' ),
						)
					),
					Gutena_Forms_Form_Template_Fields::text(
						4,
						__( 'Job title', 'gutena-forms' ),
						array(
							'placeholder' => __( 'Enter your job title', 'gutena-forms' ),
						)
					),
					Gutena_Forms_Form_Template_Fields::dropdown(
						5,
						__( 'How did you hear about this event?', 'gutena-forms' ),
						array(
							__( 'Email newsletter', 'gutena-forms' ),
							__( 'Social media', 'gutena-forms' ),
							__( 'Website', 'gutena-forms' ),
							__( 'Colleague or friend', 'gutena-forms' ),
							__( 'Other', 'gutena-forms' ),
						),
						array(
							'isRequired' => true,
						)
					),
					Gutena_Forms_Form_Template_Fields::optin(
						6,
						__( 'I agree to receive event updates and communications', 'gutena-forms' )
					),
				),
			);
		}
	}
endif;
