<?php
/**
 * Demo Request template.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Template_Demo_Request' ) ) :
	/**
	 * Demo Request form template.
	 *
	 * @since 2.2.0
	 */
	class Gutena_Forms_Template_Demo_Request extends Gutena_Forms_Abstract_Form_Template {
		use Gutena_Forms_Template_Spacing_Trait;

		public function get_id() {
			return 'demo-request';
		}

		public function get_title() {
			return __( 'Demo Request', 'gutena-forms' );
		}

		public function get_description() {
			return __( 'Let prospects request a product demo with preferred date.', 'gutena-forms' );
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
						'label' => __( 'Business Email', 'gutena-forms' ),
						'type'  => 'email',
					),
					array(
						'label' => __( 'Company', 'gutena-forms' ),
						'type'  => 'text',
					),
					array(
						'label' => __( 'Date', 'gutena-forms' ),
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
					$builder::email_field( 'f_1', __( 'Business Email', 'gutena-forms' ) ),
					$builder::text_field( 'f_2', __( 'Company', 'gutena-forms' ), __( 'Company', 'gutena-forms' ) ),
					$builder::date_field_for_template( 'f_3', __( 'Date', 'gutena-forms' ) ),
					$builder::textarea_field( 'f_4', __( 'Notes', 'gutena-forms' ), __( 'Enter description', 'gutena-forms' ), 5, false ),
				),
				__( 'Request Demo', 'gutena-forms' )
			);
		}
	}
endif;
