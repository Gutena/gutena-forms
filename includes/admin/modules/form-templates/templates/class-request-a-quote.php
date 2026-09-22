<?php
/**
 * Request a Quote form template.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Template_Request_A_Quote' ) ) :
	/**
	 * Request a Quote template definition.
	 *
	 * @since 2.2.0
	 * @package Gutena Forms
	 */
	class Gutena_Forms_Template_Request_A_Quote extends Gutena_Forms_Abstract_Form_Template {
		/**
		 * {@inheritDoc}
		 */
		public static function get_template() {
			return array(
				'id'              => 'request-a-quote',
				'title'           => __( 'Request a Quote', 'gutena-forms' ),
				'description'     => __( 'Collect quote requests with service needs, project details, and budget range.', 'gutena-forms' ),
				'category'        => 'lead-generation',
				'is_pro'          => false,
				'preview_image'   => 'assets/img/templates/request-a-quote.png',
				'submit_label'    => __( 'Request Quote', 'gutena-forms' ),
				'success_message' => __( 'Your quote request has been submitted. We will send you a proposal soon.', 'gutena-forms' ),
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
						__( 'Phone number', 'gutena-forms' ),
						array(
							'isRequired'  => true,
							'placeholder' => __( 'Enter your phone number', 'gutena-forms' ),
						)
					),
					Gutena_Forms_Form_Template_Fields::dropdown(
						3,
						__( 'Service needed', 'gutena-forms' ),
						array(
							__( 'Design', 'gutena-forms' ),
							__( 'Development', 'gutena-forms' ),
							__( 'Consulting', 'gutena-forms' ),
							__( 'Support', 'gutena-forms' ),
						),
						array(
							'isRequired' => true,
						)
					),
					Gutena_Forms_Form_Template_Fields::textarea(
						4,
						__( 'Project details', 'gutena-forms' ),
						array(
							'isRequired'  => true,
							'placeholder' => __( 'Describe your project requirements', 'gutena-forms' ),
						)
					),
					Gutena_Forms_Form_Template_Fields::dropdown(
						5,
						__( 'Budget range', 'gutena-forms' ),
						array(
							__( 'Under $1,000', 'gutena-forms' ),
							__( '$1,000 - $5,000', 'gutena-forms' ),
							__( '$5,000 - $10,000', 'gutena-forms' ),
							__( 'Over $10,000', 'gutena-forms' ),
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
