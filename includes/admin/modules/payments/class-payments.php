<?php
/**
 * Payments settings module.
 *
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Payments' ) && class_exists( 'Gutena_Forms_Forms_Settings' ) ) :
	class Gutena_Forms_Payments extends Gutena_Forms_Forms_Settings {

		public static function register_module() {
			add_filter(
				'gutena_forms__settings',
				function ( $settings ) {
					$settings['payment-methods'] = __CLASS__;
					return $settings;
				}
			);

			include_once GUTENA_FORMS_DIR_PATH . 'includes/admin/modules/payments/class-payments-endpoints.php';
			include_once GUTENA_FORMS_DIR_PATH . 'includes/admin/modules/payments/abstract-payment-gateway.php';
			include_once GUTENA_FORMS_DIR_PATH . 'includes/admin/modules/payments/stripe/class-stripe-connect.php';
			include_once GUTENA_FORMS_DIR_PATH . 'includes/admin/modules/payments/stripe/class-stripe.php';
		}

		public function get_settings() {
			return array(
				'title'       => __( 'Payments', 'gutena-forms' ),
				'description' => __( 'Connect and manage your payment gateways to securely accept transactions through your forms.', 'gutena-forms' ),
				'fields'      => array(
					array(
						'type' => 'template',
						'name' => 'payment-methods',
					),
				),
			);
		}

		public function save_settings( $settings ) {
			return true;
		}
	}

	Gutena_Forms_Payments::register_module();
endif;
