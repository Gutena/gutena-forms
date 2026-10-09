<?php
/**
 * Maintenance Request template.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Template_Maintenance_Request' ) ) :
	/**
	 * Maintenance Request form template.
	 *
	 * @since 2.2.0
	 */
	class Gutena_Forms_Template_Maintenance_Request extends Gutena_Forms_Abstract_Form_Template {
		use Gutena_Forms_Template_Spacing_Trait;

		public function get_id() {
			return 'maintenance-request';
		}

		public function get_title() {
			return __( 'Maintenance Request', 'gutena-forms' );
		}

		public function get_description() {
			return __( 'Allow users to submit maintenance requests with priority and issue details.', 'gutena-forms' );
		}

		public function is_free() {
			return true;
		}

		public function get_category() {
			return 'supports-requests';
		}

		public function get_preview() {
			return array(
				'image'  => $this->get_preview_image_filename(),
				'fields' => array(
					array(
						'label' => __( 'Your Name', 'gutena-forms' ),
						'type'  => 'text',
					),
					array(
						'label' => __( 'Your Email', 'gutena-forms' ),
						'type'  => 'email',
					),
					array(
						'label' => __( 'Subject', 'gutena-forms' ),
						'type'  => 'text',
					),
					array(
						'label' => __( 'Priority', 'gutena-forms' ),
						'type'  => __( 'select · Low / Medium / High / Urgent', 'gutena-forms' ),
					),
					array(
						'label' => __( 'Issue Details', 'gutena-forms' ),
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
					$builder::text_field( 'f_0', __( 'Your Name', 'gutena-forms' ), __( 'Enter name', 'gutena-forms' ) ),
					$builder::email_field( 'f_1', __( 'Your Email', 'gutena-forms' ) ),
					$builder::text_field( 'f_2', __( 'Subject', 'gutena-forms' ), __( 'Company', 'gutena-forms' ) ),
					$builder::dropdown_field(
						'f_3',
						__( 'Priority', 'gutena-forms' ),
						array(
							__( 'Low', 'gutena-forms' ),
							__( 'Medium', 'gutena-forms' ),
							__( 'High', 'gutena-forms' ),
							__( 'Urgent', 'gutena-forms' ),
						)
					),
					$builder::textarea_field( 'f_4', __( 'Issue Details', 'gutena-forms' ) ),
				),
				__( 'Submit request', 'gutena-forms' )
			);
		}
	}
endif;
