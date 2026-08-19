<?php

/**

 * Stripe payment gateway settings.

 *

 * @package Gutena Forms

 */



defined( 'ABSPATH' ) || exit;



if ( ! class_exists( 'Gutena_Forms_Stripe' ) && class_exists( 'Gutena_Forms_Payment_Gateway_Settings' ) ) :

	class Gutena_Forms_Stripe extends Gutena_Forms_Payment_Gateway_Settings {



		private static $instance;



		public function __construct() {

			$this->id          = 'stripe';

			$this->title       = 'Stripe';

			$this->description = __( 'Accept payments via Stripe in your WordPress forms to easily collect one-time, donation, and recurring payments.', 'gutena-forms' );



			parent::__construct();

		}



		public static function register_module() {

			add_filter(

				'gutena_forms__payment_gateways',

				function ( $gateways ) {

					$gateways['stripe'] = __CLASS__;

					return $gateways;

				}

			);



			self::get_instance();

		}



		public function get_settings() {

			$public = class_exists( 'Gutena_Forms_Stripe_Connect' )

				? Gutena_Forms_Stripe_Connect::get_public_settings()

				: array();



			return array(

				'id'          => $this->id,

				'title'       => __( 'Stripe Account Settings', 'gutena-forms' ),

				'description' => __( 'Connect your Stripe account to start accepting payments through your forms.', 'gutena-forms' ),

				'back'        => '/settings/settings/payment-methods',

				'fields'      => array(

					array(

						'type' => 'template',

						'name' => 'stripe-settings',

					),

				),

				'values'      => array(

					'payment_mode'           => $public['payment_mode'] ?? ( $this->settings['payment_mode'] ?? 'test' ),

					'currency'               => $public['currency'] ?? ( $this->settings['currency'] ?? 'USD' ),

					'currency_sign_position' => $public['currency_sign_position'] ?? ( $this->settings['currency_sign_position'] ?? 'left' ),

					'connected'              => ! empty( $public['connected'] ) || ! empty( $this->settings['connected'] ),

					'account_name'           => $public['account_name'] ?? ( $this->settings['account_name'] ?? '' ),

					'webhook_connected'      => ! empty( $public['webhook_connected'] ) || ! empty( $this->settings['webhook_connected'] ),

					'webhook_slots_exceeded' => ! empty( $public['webhook_slots_exceeded'] ) || ! empty( $this->settings['webhook_slots_exceeded'] ),

					'stripe_dashboard_url'   => 'https://dashboard.stripe.com/webhooks',

				),

			);

		}



		public function save_settings( $settings ) {
			if ( is_array( $settings ) ) {
				unset(
					$settings['connected'],
					$settings['account_name'],
					$settings['webhook_connected'],
					$settings['webhook_slots_exceeded']
				);
			}

			parent::save_settings( $settings );

			return true;
		}



		public static function get_instance() {

			if ( is_null( self::$instance ) ) {

				self::$instance = new self();

			}



			return self::$instance;

		}

	}



	Gutena_Forms_Stripe::register_module();

endif;


