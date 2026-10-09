<?php
/**
 * Event RSVP template.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Template_Event_Rsvp' ) ) :
	/**
	 * Event RSVP form template.
	 *
	 * @since 2.2.0
	 */
	class Gutena_Forms_Template_Event_Rsvp extends Gutena_Forms_Abstract_Form_Template {
		use Gutena_Forms_Template_Spacing_Trait;

		public function get_id() {
			return 'event-rsvp';
		}

		public function get_title() {
			return __( 'Event RSVP', 'gutena-forms' );
		}

		public function get_description() {
			return __( 'Collect quick RSVPs for in-person events with guest count.', 'gutena-forms' );
		}

		public function is_free() {
			return true;
		}

		public function get_category() {
			return 'event-planning';
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
						'label' => __( 'Attending', 'gutena-forms' ),
						'type'  => __( 'radio · Yes / No / Maybe', 'gutena-forms' ),
					),
					array(
						'label' => __( 'Guests', 'gutena-forms' ),
						'type'  => 'number',
					),
					array(
						'label' => __( 'Notes', 'gutena-forms' ),
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
						__( 'Attending', 'gutena-forms' ),
						array(
							__( 'Yes', 'gutena-forms' ),
							__( 'No', 'gutena-forms' ),
							__( 'Maybe', 'gutena-forms' ),
						),
						false,
						true,
						3
					),
					$builder::number_field( 'f_3', __( 'Guests', 'gutena-forms' ), __( 'Enter num', 'gutena-forms' ) ),
					$builder::textarea_field( 'f_4', __( 'Notes', 'gutena-forms' ), __( 'Enter description', 'gutena-forms' ), 5, false ),
				)
			);
		}
	}
endif;
