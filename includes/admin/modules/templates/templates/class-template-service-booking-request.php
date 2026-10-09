<?php
/**
 * Service Booking Request template.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Template_Service_Booking_Request' ) ) :
	/**
	 * Service Booking Request form template.
	 *
	 * @since 2.2.0
	 */
	class Gutena_Forms_Template_Service_Booking_Request extends Gutena_Forms_Abstract_Form_Template {
		use Gutena_Forms_Template_Spacing_Trait;

		public function get_id() {
			return 'service-booking-request';
		}

		public function get_title() {
			return __( 'Service Booking Request', 'gutena-forms' );
		}

		public function get_description() {
			return __( 'Let customers request a service appointment with preferred date and details.', 'gutena-forms' );
		}

		public function is_free() {
			return true;
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
						'label' => __( 'Service', 'gutena-forms' ),
						'type'  => __( 'select · Consultation / Repair / Installation / Other', 'gutena-forms' ),
					),
					array(
						'label' => __( 'Preferred date', 'gutena-forms' ),
						'type'  => 'date',
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
					$builder::dropdown_field(
						'f_2',
						__( 'Service', 'gutena-forms' ),
						array(
							__( 'Cleaning', 'gutena-forms' ),
							__( 'Plumbing', 'gutena-forms' ),
							__( 'Electrical', 'gutena-forms' ),
							__( 'Landscaping', 'gutena-forms' ),
						)
					),
					$builder::date_field_for_template( 'f_3', __( 'Date', 'gutena-forms' ) ),
					$builder::textarea_field( 'f_4', __( 'Notes', 'gutena-forms' ), __( 'Enter description', 'gutena-forms' ), 5, false ),
				)
			);
		}
	}
endif;
