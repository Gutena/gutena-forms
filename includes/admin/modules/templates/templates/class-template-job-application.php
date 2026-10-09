<?php
/**
 * Job Application template.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Template_Job_Application' ) ) :
	/**
	 * Job Application form template.
	 *
	 * @since 2.2.0
	 */
	class Gutena_Forms_Template_Job_Application extends Gutena_Forms_Abstract_Form_Template {
		use Gutena_Forms_Template_Spacing_Trait;

		public function get_id() {
			return 'job-application';
		}

		public function get_title() {
			return __( 'Job Application', 'gutena-forms' );
		}

		public function get_description() {
			return __( 'Accept applications with a cover letter and resume upload.', 'gutena-forms' );
		}

		public function is_free() {
			return false;
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
						'label' => __( 'Position', 'gutena-forms' ),
						'type'  => __( 'select · Engineering / Design / Sales / Support', 'gutena-forms' ),
					),
					array(
						'label' => __( 'Cover letter', 'gutena-forms' ),
						'type'  => 'textarea',
					),
					array(
						'label' => __( 'Resume', 'gutena-forms' ),
						'type'  => 'file',
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
						__( 'Position', 'gutena-forms' ),
						array(
							__( 'Designer', 'gutena-forms' ),
							__( 'Developer', 'gutena-forms' ),
							__( 'Marketing Manager', 'gutena-forms' ),
							__( 'Sales Representative', 'gutena-forms' ),
						),
						false
					),
					$builder::text_field( 'f_4', __( 'Cover letter', 'gutena-forms' ), __( 'Enter cover letter', 'gutena-forms' ), false ),
					$builder::email_field( 'f_5', __( 'Reference Email Address', 'gutena-forms' ), __( 'Reference Email Address', 'gutena-forms' ), false ),
					$builder::file_upload_field(
						'f_6',
						__( 'Resume', 'gutena-forms' ),
						__( 'Accepts .jpg, .png and .gif', 'gutena-forms' )
					),
				)
			);
		}
	}
endif;
