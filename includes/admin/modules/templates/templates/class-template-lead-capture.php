<?php
/**
 * Lead Capture template.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Template_Lead_Capture' ) ) :
	/**
	 * Lead Capture form template.
	 *
	 * @since 2.2.0
	 */
	class Gutena_Forms_Template_Lead_Capture extends Gutena_Forms_Abstract_Form_Template {
		use Gutena_Forms_Template_Spacing_Trait;

		public function get_id() {
			return 'lead-capture';
		}

		public function get_title() {
			return __( 'Lead Capture', 'gutena-forms' );
		}

		public function get_description() {
			return __( 'Capture leads with name, email, phone, and project details.', 'gutena-forms' );
		}

		public function is_free() {
			return true;
		}

		public function get_category() {
			return 'lead-generation';
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
						'label' => __( 'Service Type', 'gutena-forms' ),
						'type'  => __( 'select · Consulting / Design / Development / Marketing', 'gutena-forms' ),
					),
					array(
						'label' => __( 'Project Details', 'gutena-forms' ),
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
					$builder::phone_field_for_template( 'f_2', __( 'Phone', 'gutena-forms' ), '', true ),
					$builder::dropdown_field(
						'f_3',
						__( 'Service Type', 'gutena-forms' ),
						array(
							__( 'Consulting', 'gutena-forms' ),
							__( 'Design', 'gutena-forms' ),
							__( 'Development', 'gutena-forms' ),
							__( 'Marketing', 'gutena-forms' ),
						)
					),
					$builder::textarea_field( 'f_4', __( 'Project Details', 'gutena-forms' ) ),
				)
			);
		}
	}
endif;
