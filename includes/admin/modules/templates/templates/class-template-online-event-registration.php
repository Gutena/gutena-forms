<?php
/**
 * Online Event Registration template.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Template_Online_Event_Registration' ) ) :
	/**
	 * Online Event Registration form template.
	 *
	 * @since 2.2.0
	 */
	class Gutena_Forms_Template_Online_Event_Registration extends Gutena_Forms_Abstract_Form_Template {
		use Gutena_Forms_Template_Spacing_Trait;

		public function get_id() {
			return 'online-event-registration';
		}

		public function get_title() {
			return __( 'Online Event Registration', 'gutena-forms' );
		}

		public function get_description() {
			return __( 'Register attendees for webinars and virtual events.', 'gutena-forms' );
		}

		public function is_free() {
			return false;
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
						'label' => __( 'Phone', 'gutena-forms' ),
						'type'  => 'tel',
					),
					array(
						'label' => __( 'Company', 'gutena-forms' ),
						'type'  => 'text',
					),
					array(
						'label' => __( 'Tickets', 'gutena-forms' ),
						'type'  => 'number',
					),
					array(
						'label' => __( 'How did you hear', 'gutena-forms' ),
						'type'  => __( 'radio · Friend / Radio / Social / Other', 'gutena-forms' ),
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
					$builder::text_field( 'f_3', __( 'Company name', 'gutena-forms' ), __( 'Company name', 'gutena-forms' ), false ),
					$builder::number_field( 'f_4', __( 'Number of tickets needed', 'gutena-forms' ), __( 'Number of tickets needed', 'gutena-forms' ) ),
					$builder::radio_field(
						'f_5',
						__( 'How did you hear about this event?', 'gutena-forms' ),
						array(
							__( 'Friend or colleague', 'gutena-forms' ),
							__( 'Radio / TV', 'gutena-forms' ),
							__( 'Social Media', 'gutena-forms' ),
							__( 'Other', 'gutena-forms' ),
						),
						false,
						true,
						2
					),
					$builder::text_field( 'f_6', __( 'If other, please specify', 'gutena-forms' ), __( 'description here', 'gutena-forms' ), false ),
				),
				__( 'Register', 'gutena-forms' )
			);
		}
	}
endif;
