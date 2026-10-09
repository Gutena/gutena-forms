<?php
/**
 * Beta Access Request template.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Template_Beta_Access_Request' ) ) :
	/**
	 * Beta Access Request form template.
	 *
	 * @since 2.2.0
	 */
	class Gutena_Forms_Template_Beta_Access_Request extends Gutena_Forms_Abstract_Form_Template {
		use Gutena_Forms_Template_Spacing_Trait;

		public function get_id() {
			return 'beta-access-request';
		}

		public function get_title() {
			return __( 'Beta Access Request', 'gutena-forms' );
		}

		public function get_description() {
			return __( 'Collect beta access requests with role and use case details.', 'gutena-forms' );
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
						'label' => __( 'Role', 'gutena-forms' ),
						'type'  => __( 'select · Developer / Designer / PM / Business Owner', 'gutena-forms' ),
					),
					array(
						'label' => __( 'Website', 'gutena-forms' ),
						'type'  => 'url',
					),
					array(
						'label' => __( 'Use Case Details', 'gutena-forms' ),
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
						__( 'Role', 'gutena-forms' ),
						array(
							__( 'Developer', 'gutena-forms' ),
							__( 'Designer', 'gutena-forms' ),
							__( 'Product Manager', 'gutena-forms' ),
							__( 'Business Owner', 'gutena-forms' ),
						)
					),
					$builder::url_field( 'f_3', __( 'Website', 'gutena-forms' ), __( 'Company', 'gutena-forms' ), false ),
					$builder::textarea_field( 'f_4', __( 'Use Case Details', 'gutena-forms' ), __( 'Enter description', 'gutena-forms' ), 5, false ),
				),
				__( 'Request Beta Access', 'gutena-forms' )
			);
		}
	}
endif;
