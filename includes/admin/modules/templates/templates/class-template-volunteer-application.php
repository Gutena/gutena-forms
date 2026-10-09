<?php
/**
 * Volunteer Application template.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Template_Volunteer_Application' ) ) :
	/**
	 * Volunteer Application form template.
	 *
	 * @since 2.2.0
	 */
	class Gutena_Forms_Template_Volunteer_Application extends Gutena_Forms_Abstract_Form_Template {
		use Gutena_Forms_Template_Spacing_Trait;

		public function get_id() {
			return 'volunteer-application';
		}

		public function get_title() {
			return __( 'Volunteer Application', 'gutena-forms' );
		}

		public function get_description() {
			return __( 'Collect volunteer sign-ups with availability and interests.', 'gutena-forms' );
		}

		public function is_free() {
			return true;
		}

		public function get_category() {
			return 'application-forms';
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
						'label' => __( 'Emergency Contact', 'gutena-forms' ),
						'type'  => 'text',
					),
					array(
						'label' => __( 'Volunteer Role', 'gutena-forms' ),
						'type'  => __( 'select · Event Support / Community Outreach / Administrative / Fundraising', 'gutena-forms' ),
					),
					array(
						'label' => __( 'Availability', 'gutena-forms' ),
						'type'  => __( 'checkbox · Weekdays / Weekends / Evenings / Flexible', 'gutena-forms' ),
					),
					array(
						'label' => __( 'Skills', 'gutena-forms' ),
						'type'  => 'textarea',
					),
					array(
						'label' => __( 'Volunteered Before', 'gutena-forms' ),
						'type'  => __( 'radio · Yes / No', 'gutena-forms' ),
					),
					array(
						'label' => __( 'Reference', 'gutena-forms' ),
						'type'  => __( 'text · name + email', 'gutena-forms' ),
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
					$builder::phone_field_for_template( 'f_2', __( 'Phone', 'gutena-forms' ), '', true ),
					$builder::text_field( 'f_3', __( 'Emergency Contact Name', 'gutena-forms' ), __( 'Enter name', 'gutena-forms' ) ),
					$builder::dropdown_field(
						'f_4',
						__( 'Preferred Volunteer Role', 'gutena-forms' ),
						array(
							__( 'Event Support', 'gutena-forms' ),
							__( 'Community Outreach', 'gutena-forms' ),
							__( 'Administrative', 'gutena-forms' ),
							__( 'Fundraising', 'gutena-forms' ),
						)
					),
					$builder::checkbox_field(
						'f_5',
						__( 'Availability', 'gutena-forms' ),
						array(
							__( 'Weekdays', 'gutena-forms' ),
							__( 'Weekends', 'gutena-forms' ),
							__( 'Evenings', 'gutena-forms' ),
							__( 'Flexible', 'gutena-forms' ),
						),
						false,
						true,
						4
					),
					$builder::textarea_field( 'f_6', __( 'Skills', 'gutena-forms' ) ),
					$builder::radio_field(
						'f_7',
						__( 'Have you volunteered before?', 'gutena-forms' ),
						array(
							__( 'Yes', 'gutena-forms' ),
							__( 'No', 'gutena-forms' ),
						)
					),
					$builder::text_field( 'f_8', __( 'Reference Name', 'gutena-forms' ), __( 'Reference Name', 'gutena-forms' ), false ),
					$builder::email_field( 'f_9', __( 'Reference Email Address', 'gutena-forms' ), __( 'Reference Email Address', 'gutena-forms' ), false ),
				)
			);
		}
	}
endif;
