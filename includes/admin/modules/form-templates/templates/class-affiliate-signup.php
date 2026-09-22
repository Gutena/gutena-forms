<?php
/**
 * Affiliate Signup form template.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Template_Affiliate_Signup' ) ) :
	/**
	 * Affiliate Signup template definition.
	 *
	 * @since 2.2.0
	 * @package Gutena Forms
	 */
	class Gutena_Forms_Template_Affiliate_Signup extends Gutena_Forms_Abstract_Form_Template {
		/**
		 * {@inheritDoc}
		 */
		public static function get_template() {
			return array(
				'id'              => 'affiliate-signup',
				'title'           => __( 'Affiliate Signup', 'gutena-forms' ),
				'description'     => __( 'Onboard affiliates with website, promotion method, and social profile details.', 'gutena-forms' ),
				'category'        => 'marketing',
				'is_pro'          => false,
				'preview_image'   => 'assets/img/templates/affiliate-signup.png',
				'submit_label'    => __( 'Apply to Join', 'gutena-forms' ),
				'success_message' => __( 'Thank you for applying to our affiliate program. We will review your application soon.', 'gutena-forms' ),
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
						__( 'Website URL', 'gutena-forms' ),
						array(
							'isRequired'  => true,
							'placeholder' => __( 'https://example.com', 'gutena-forms' ),
						)
					),
					Gutena_Forms_Form_Template_Fields::textarea(
						3,
						__( 'How will you promote us?', 'gutena-forms' ),
						array(
							'isRequired'  => true,
							'placeholder' => __( 'Describe your promotion strategy', 'gutena-forms' ),
						)
					),
					Gutena_Forms_Form_Template_Fields::text(
						4,
						__( 'Social media handles', 'gutena-forms' ),
						array(
							'placeholder' => __( 'e.g. @yourhandle', 'gutena-forms' ),
						)
					),
					Gutena_Forms_Form_Template_Fields::optin(
						5,
						__( 'I agree to the affiliate program terms', 'gutena-forms' )
					),
				),
			);
		}
	}
endif;
