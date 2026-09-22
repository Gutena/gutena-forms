<?php
/**
 * Newsletter Signup form template.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Template_Newsletter_Signup' ) ) :
	/**
	 * Newsletter Signup template definition.
	 *
	 * @since 2.2.0
	 * @package Gutena Forms
	 */
	class Gutena_Forms_Template_Newsletter_Signup extends Gutena_Forms_Abstract_Form_Template {
		/**
		 * {@inheritDoc}
		 */
		public static function get_template() {
			return array(
				'id'              => 'newsletter-signup',
				'title'           => __( 'Newsletter Signup', 'gutena-forms' ),
				'description'     => __( 'Grow your mailing list with a simple newsletter subscription form.', 'gutena-forms' ),
				'category'        => 'marketing',
				'is_pro'          => false,
				'preview_image'   => 'assets/img/templates/newsletter-signup.png',
				'submit_label'    => __( 'Subscribe', 'gutena-forms' ),
				'success_message' => __( 'You have successfully subscribed to our newsletter.', 'gutena-forms' ),
				'fields'          => array(
					Gutena_Forms_Form_Template_Fields::text(
						0,
						__( 'First name', 'gutena-forms' ),
						array(
							'placeholder' => __( 'Enter your first name', 'gutena-forms' ),
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
					Gutena_Forms_Form_Template_Fields::optin(
						2,
						__( 'I agree to receive marketing emails', 'gutena-forms' )
					),
				),
			);
		}
	}
endif;
