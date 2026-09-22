<?php
/**
 * Maintenance Request form template.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Template_Maintenance_Request' ) ) :
	/**
	 * Maintenance Request template definition.
	 *
	 * @since 2.2.0
	 * @package Gutena Forms
	 */
	class Gutena_Forms_Template_Maintenance_Request extends Gutena_Forms_Abstract_Form_Template {
		/**
		 * {@inheritDoc}
		 */
		public static function get_template() {
			return array(
				'id'              => 'maintenance-request',
				'title'           => __( 'Maintenance Request', 'gutena-forms' ),
				'description'     => __( 'Accept maintenance requests with issue type, description, and urgency level.', 'gutena-forms' ),
				'category'        => 'support-requests',
				'is_pro'          => false,
				'preview_image'   => 'assets/img/templates/maintenance-request.png',
				'submit_label'    => __( 'Submit Request', 'gutena-forms' ),
				'success_message' => __( 'Your maintenance request has been submitted. Our team will respond as soon as possible.', 'gutena-forms' ),
				'fields'          => array(
					Gutena_Forms_Form_Template_Fields::text(
						0,
						__( 'Full name', 'gutena-forms' ),
						array(
							'isRequired'  => true,
							'placeholder' => __( 'Enter your full name', 'gutena-forms' ),
						)
					),
					Gutena_Forms_Form_Template_Fields::email(
						1,
						__( 'Email address', 'gutena-forms' ),
						array(
							'isRequired'  => true,
							'placeholder' => __( 'example@gmail.com', 'gutena-forms' ),
						)
					),
					Gutena_Forms_Form_Template_Fields::text(
						2,
						__( 'Property or location', 'gutena-forms' ),
						array(
							'isRequired'  => true,
							'placeholder' => __( 'Enter the property or location', 'gutena-forms' ),
						)
					),
					Gutena_Forms_Form_Template_Fields::dropdown(
						3,
						__( 'Issue type', 'gutena-forms' ),
						array(
							__( 'Plumbing', 'gutena-forms' ),
							__( 'Electrical', 'gutena-forms' ),
							__( 'HVAC', 'gutena-forms' ),
							__( 'General repair', 'gutena-forms' ),
							__( 'Other', 'gutena-forms' ),
						),
						array(
							'isRequired' => true,
						)
					),
					Gutena_Forms_Form_Template_Fields::textarea(
						4,
						__( 'Issue description', 'gutena-forms' ),
						array(
							'isRequired'  => true,
							'placeholder' => __( 'Describe the issue in detail', 'gutena-forms' ),
						)
					),
					Gutena_Forms_Form_Template_Fields::radio(
						5,
						__( 'Urgency', 'gutena-forms' ),
						array(
							__( 'Low', 'gutena-forms' ),
							__( 'Medium', 'gutena-forms' ),
							__( 'High', 'gutena-forms' ),
						),
						array(
							'isRequired' => true,
						)
					),
				),
			);
		}
	}
endif;
