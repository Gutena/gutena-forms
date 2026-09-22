<?php
/**
 * Event RSVP form template.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Template_Event_RSVP' ) ) :
	/**
	 * Event RSVP template definition.
	 *
	 * @since 2.2.0
	 * @package Gutena Forms
	 */
	class Gutena_Forms_Template_Event_RSVP extends Gutena_Forms_Abstract_Form_Template {
		/**
		 * {@inheritDoc}
		 */
		public static function get_template() {
			return array(
				'id'              => 'event-rsvp',
				'title'           => __( 'Event RSVP', 'gutena-forms' ),
				'description'     => __( 'Collect RSVPs with attendance status, guest count, and dietary preferences.', 'gutena-forms' ),
				'category'        => 'event-planning',
				'is_pro'          => false,
				'preview_image'   => 'assets/img/templates/event-rsvp.png',
				'submit_label'    => __( 'Send RSVP', 'gutena-forms' ),
				'success_message' => __( 'Thank you for your RSVP. We look forward to seeing you at the event.', 'gutena-forms' ),
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
					Gutena_Forms_Form_Template_Fields::radio(
						2,
						__( 'Will you attend?', 'gutena-forms' ),
						array(
							__( 'Yes', 'gutena-forms' ),
							__( 'No', 'gutena-forms' ),
							__( 'Maybe', 'gutena-forms' ),
						),
						array(
							'isRequired' => true,
						)
					),
					Gutena_Forms_Form_Template_Fields::number(
						3,
						__( 'Number of guests', 'gutena-forms' ),
						array(
							'isRequired'  => true,
							'placeholder' => __( '0', 'gutena-forms' ),
							'minMaxStep'  => array(
								'min'  => 0,
								'max'  => 20,
								'step' => 1,
							),
						)
					),
					Gutena_Forms_Form_Template_Fields::textarea(
						4,
						__( 'Dietary restrictions', 'gutena-forms' ),
						array(
							'placeholder' => __( 'Let us know about allergies or dietary needs', 'gutena-forms' ),
						)
					),
				),
			);
		}
	}
endif;
