<?php
/**
 * Beta Access Request form template.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Template_Beta_Access_Request' ) ) :
	/**
	 * Beta Access Request template definition.
	 *
	 * @since 2.2.0
	 * @package Gutena Forms
	 */
	class Gutena_Forms_Template_Beta_Access_Request extends Gutena_Forms_Abstract_Form_Template {
		/**
		 * {@inheritDoc}
		 */
		public static function get_template() {
			return array(
				'id'              => 'beta-access-request',
				'title'           => __( 'Beta Access Request', 'gutena-forms' ),
				'description'     => __( 'Collect beta program applications with role and interest details.', 'gutena-forms' ),
				'category'        => 'support-requests',
				'is_pro'          => false,
				'preview_image'   => 'assets/img/templates/beta-access-request.png',
				'submit_label'    => __( 'Request Access', 'gutena-forms' ),
				'success_message' => __( 'Thank you for requesting beta access. We will notify you when a spot becomes available.', 'gutena-forms' ),
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
					Gutena_Forms_Form_Template_Fields::dropdown(
						2,
						__( 'Your role', 'gutena-forms' ),
						array(
							__( 'Developer', 'gutena-forms' ),
							__( 'Designer', 'gutena-forms' ),
							__( 'Product manager', 'gutena-forms' ),
							__( 'Business owner', 'gutena-forms' ),
							__( 'Other', 'gutena-forms' ),
						),
						array(
							'isRequired' => true,
						)
					),
					Gutena_Forms_Form_Template_Fields::textarea(
						3,
						__( 'Why are you interested in the beta?', 'gutena-forms' ),
						array(
							'isRequired'  => true,
							'placeholder' => __( 'Tell us why you want early access', 'gutena-forms' ),
						)
					),
					Gutena_Forms_Form_Template_Fields::optin(
						4,
						__( 'I agree to receive beta program updates', 'gutena-forms' )
					),
				),
			);
		}
	}
endif;
