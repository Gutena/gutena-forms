<?php
/**
 * Affiliate Signup template.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Template_Affiliate_Signup' ) ) :
	/**
	 * Affiliate Signup form template.
	 *
	 * @since 2.2.0
	 */
	class Gutena_Forms_Template_Affiliate_Signup extends Gutena_Forms_Abstract_Form_Template {
		use Gutena_Forms_Template_Spacing_Trait;

		public function get_id() {
			return 'affiliate-signup';
		}

		public function get_title() {
			return __( 'Affiliate Signup', 'gutena-forms' );
		}

		public function get_description() {
			return __( 'Collect affiliate applications with promotion details and payment info.', 'gutena-forms' );
		}

		public function is_free() {
			return false;
		}

		public function get_category() {
			return 'marketing';
		}

		public function get_preview() {
			return array(
				'image'  => $this->get_preview_image_filename(),
				'fields' => array(
					array(
						'label' => __( 'Full Name', 'gutena-forms' ),
						'type'  => 'text',
					),
					array(
						'label' => __( 'Email', 'gutena-forms' ),
						'type'  => 'email',
					),
					array(
						'label' => __( 'Website', 'gutena-forms' ),
						'type'  => 'url',
					),
					array(
						'label' => __( 'Promotion Method', 'gutena-forms' ),
						'type'  => __( 'select · Blog / Social / Email / Paid Ads', 'gutena-forms' ),
					),
					array(
						'label' => __( 'Audience Size', 'gutena-forms' ),
						'type'  => __( 'select · Under 1k / 1k–10k / 10k–100k / 100k+', 'gutena-forms' ),
					),
					array(
						'label' => __( 'Payment Email', 'gutena-forms' ),
						'type'  => 'email',
					),
					array(
						'label' => __( 'Why affiliate', 'gutena-forms' ),
						'type'  => 'textarea',
					),
					array(
						'label' => __( 'Terms', 'gutena-forms' ),
						'type'  => 'checkbox',
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
					$builder::text_field( 'f_0', __( 'Full Name', 'gutena-forms' ), __( 'Enter name', 'gutena-forms' ) ),
					$builder::email_field( 'f_1', __( 'Email Address', 'gutena-forms' ) ),
					$builder::url_field( 'f_2', __( 'Website / Social Media URL', 'gutena-forms' ), __( 'Website / Social Media URL', 'gutena-forms' ), true ),
					$builder::dropdown_field(
						'f_3',
						__( 'Promotion Method', 'gutena-forms' ),
						array(
							__( 'Blog', 'gutena-forms' ),
							__( 'Social Media', 'gutena-forms' ),
							__( 'Email Marketing', 'gutena-forms' ),
							__( 'Paid Ads', 'gutena-forms' ),
						)
					),
					$builder::dropdown_field(
						'f_4',
						__( 'Audience / Traffic Size', 'gutena-forms' ),
						array(
							__( 'Under 1,000', 'gutena-forms' ),
							__( '1,000 - 10,000', 'gutena-forms' ),
							__( '10,000 - 100,000', 'gutena-forms' ),
							__( '100,000+', 'gutena-forms' ),
						),
						false
					),
					$builder::email_field( 'f_5', __( 'Payment Email', 'gutena-forms' ) ),
					$builder::textarea_field( 'f_6', __( 'Why do you want to become an affiliate?', 'gutena-forms' ) ),
					$builder::optin_field( 'f_7', __( 'I accept the Terms & Conditions', 'gutena-forms' ) ),
				),
				__( 'Apply as affiliate', 'gutena-forms' )
			);
		}
	}
endif;
