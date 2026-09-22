<?php
/**
 * Lead Capture form template.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Template_Lead_Capture' ) ) :
	/**
	 * Lead Capture template definition.
	 *
	 * @since 2.2.0
	 * @package Gutena Forms
	 */
	class Gutena_Forms_Template_Lead_Capture extends Gutena_Forms_Abstract_Form_Template {
		/**
		 * {@inheritDoc}
		 */
		public static function get_template() {
			return array(
				'id'              => 'lead-capture',
				'title'           => __( 'Lead Capture Form', 'gutena-forms' ),
				'description'     => __( 'Capture leads with contact details, company information, and interest area.', 'gutena-forms' ),
				'category'        => 'lead-generation',
				'is_pro'          => false,
				'preview_image'   => 'assets/img/templates/lead-capture.png',
				'submit_label'    => __( 'Get in Touch', 'gutena-forms' ),
				'success_message' => __( 'Thanks for your interest. Our team will contact you shortly.', 'gutena-forms' ),
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
						__( 'Company', 'gutena-forms' ),
						array(
							'isRequired'  => true,
							'placeholder' => __( 'Enter your company name', 'gutena-forms' ),
						)
					),
					Gutena_Forms_Form_Template_Fields::dropdown(
						3,
						__( 'Area of interest', 'gutena-forms' ),
						array(
							__( 'Product demo', 'gutena-forms' ),
							__( 'Pricing information', 'gutena-forms' ),
							__( 'Partnership', 'gutena-forms' ),
							__( 'General inquiry', 'gutena-forms' ),
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
