<?php
/**
 * Demo Request form template.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Template_Demo_Request' ) ) :
	/**
	 * Demo Request template definition.
	 *
	 * @since 2.2.0
	 * @package Gutena Forms
	 */
	class Gutena_Forms_Template_Demo_Request extends Gutena_Forms_Abstract_Form_Template {
		/**
		 * {@inheritDoc}
		 */
		public static function get_template() {
			return array(
				'id'              => 'demo-request',
				'title'           => __( 'Demo Request', 'gutena-forms' ),
				'description'     => __( 'Let prospects request a product demo with company and use case details.', 'gutena-forms' ),
				'category'        => 'support-requests',
				'is_pro'          => false,
				'preview_image'   => 'assets/img/templates/demo-request.png',
				'submit_label'    => __( 'Request Demo', 'gutena-forms' ),
				'success_message' => __( 'Your demo request has been received. We will schedule a session with you soon.', 'gutena-forms' ),
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
						__( 'Team size', 'gutena-forms' ),
						array(
							__( '1-10', 'gutena-forms' ),
							__( '11-50', 'gutena-forms' ),
							__( '51-200', 'gutena-forms' ),
							__( '200+', 'gutena-forms' ),
						),
						array(
							'isRequired' => true,
						)
					),
					Gutena_Forms_Form_Template_Fields::textarea(
						4,
						__( 'Use case', 'gutena-forms' ),
						array(
							'isRequired'  => true,
							'placeholder' => __( 'Tell us how you plan to use our product', 'gutena-forms' ),
						)
					),
				),
			);
		}
	}
endif;
