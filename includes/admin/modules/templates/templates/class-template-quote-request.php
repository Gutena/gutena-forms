<?php
/**
 * Quote Request template.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Template_Quote_Request' ) ) :
	/**
	 * Quote Request form template.
	 *
	 * @since 2.2.0
	 */
	class Gutena_Forms_Template_Quote_Request extends Gutena_Forms_Abstract_Form_Template {
		use Gutena_Forms_Template_Spacing_Trait;

		public function get_id() {
			return 'quote-request';
		}

		public function get_title() {
			return __( 'Request a Quote', 'gutena-forms' );
		}

		public function get_description() {
			return __( 'Collect project details to provide a custom quote.', 'gutena-forms' );
		}

		public function is_free() {
			return false;
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
						'type'  => __( 'select · Web Design / Development / Marketing / Consulting', 'gutena-forms' ),
					),
					array(
						'label' => __( 'Budget', 'gutena-forms' ),
						'type'  => __( 'select · Under $1k / $1k–$5k / $5k–$10k / $10k+', 'gutena-forms' ),
					),
					array(
						'label' => __( 'Details', 'gutena-forms' ),
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
					$builder::phone_field( 'f_2', __( 'Phone', 'gutena-forms' ), '', true ),
					$builder::dropdown_field(
						'f_3',
						__( 'Service Type', 'gutena-forms' ),
						array(
							__( 'Web Design', 'gutena-forms' ),
							__( 'Development', 'gutena-forms' ),
							__( 'Marketing', 'gutena-forms' ),
							__( 'Consulting', 'gutena-forms' ),
						)
					),
					$builder::dropdown_field(
						'f_4',
						__( 'Budget', 'gutena-forms' ),
						array(
							__( 'Under $1,000', 'gutena-forms' ),
							__( '$1,000 - $5,000', 'gutena-forms' ),
							__( '$5,000 - $10,000', 'gutena-forms' ),
							__( '$10,000+', 'gutena-forms' ),
						),
						false
					),
					$builder::textarea_field( 'f_5', __( 'Details', 'gutena-forms' ) ),
				),
				__( 'Get a quote', 'gutena-forms' )
			);
		}
	}
endif;
