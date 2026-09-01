<?php
/**
 * Payment gateway REST endpoints.
 *
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Payments_Endpoints' ) ) :
	class Gutena_Forms_Payments_Endpoints {
		private static $instance;

		private $settings;

		private function __construct() {
			$this->settings = get_option( 'gutena_forms__payment_settings', array() );
			if ( ! is_array( $this->settings ) ) {
				$this->settings = array();
			}

			add_filter( 'gutena_forms__rest_routs', array( $this, 'rest_routes' ), 10, 2 );
		}

		public function rest_routes( $routes, $server ) {
			$routes[] = array(
				'route'    => 'payments/get-all',
				'methods'  => $server::READABLE,
				'auth'     => true,
				'callback' => array( $this, 'get_all_payments' ),
			);

			$routes[] = array(
				'route'    => 'payments/toggle',
				'methods'  => $server::CREATABLE,
				'auth'     => true,
				'callback' => array( $this, 'toggle_payment_gateway' ),
			);

			$routes[] = array(
				'route'    => 'payments/stripe/connect',
				'methods'  => $server::CREATABLE,
				'auth'     => true,
				'callback' => array( $this, 'stripe_connect' ),
			);

			$routes[] = array(
				'route'    => 'payments/stripe/disconnect',
				'methods'  => $server::CREATABLE,
				'auth'     => true,
				'callback' => array( $this, 'stripe_disconnect' ),
			);

			$routes[] = array(
				'route'    => 'payments/stripe/retry-webhook',
				'methods'  => $server::CREATABLE,
				'auth'     => true,
				'callback' => array( $this, 'stripe_retry_webhook' ),
			);

			$routes[] = array(
				'route'    => 'payments/stripe/connect-notice',
				'methods'  => $server::READABLE,
				'auth'     => true,
				'callback' => array( $this, 'stripe_connect_notice' ),
			);

			$routes[] = array(
				'route'    => 'payments/square/connect',
				'methods'  => $server::CREATABLE,
				'auth'     => true,
				'callback' => array( $this, 'square_connect' ),
			);

			$routes[] = array(
				'route'    => 'payments/square/disconnect',
				'methods'  => $server::CREATABLE,
				'auth'     => true,
				'callback' => array( $this, 'square_disconnect' ),
			);

			$routes[] = array(
				'route'    => 'payments/square/connect-notice',
				'methods'  => $server::READABLE,
				'auth'     => true,
				'callback' => array( $this, 'square_connect_notice' ),
			);

			$routes[] = array(
				'route'    => 'payments/square/status',
				'methods'  => $server::READABLE,
				'auth'     => true,
				'callback' => array( $this, 'square_connection_status' ),
			);

			if ( class_exists( 'Gutena_Forms_Stripe_Intent_Service' ) ) {
				$intent_service = Gutena_Forms_Stripe_Intent_Service::get_instance();

				$routes[] = array(
					'route'    => 'payments/stripe/public-config',
					'methods'  => $server::CREATABLE,
					'auth'     => false,
					'callback' => array( $intent_service, 'rest_get_public_config' ),
				);

				$routes[] = array(
					'route'    => 'payments/stripe/create-intent',
					'methods'  => $server::CREATABLE,
					'auth'     => false,
					'callback' => array( $intent_service, 'rest_create_payment_intent' ),
				);

				$routes[] = array(
					'route'    => 'payments/stripe/log-attempt',
					'methods'  => $server::CREATABLE,
					'auth'     => false,
					'callback' => array( $intent_service, 'rest_log_payment_attempt' ),
				);
			}

			$routes[] = array(
				'route'    => 'entry/payment',
				'methods'  => $server::READABLE,
				'auth'     => true,
				'callback' => array( $this, 'get_entry_payment' ),
			);

			$routes[] = array(
				'route'    => 'payments/entries/get-all',
				'methods'  => $server::READABLE,
				'auth'     => true,
				'callback' => array( $this, 'get_all_payment_entries' ),
			);

			$routes[] = array(
				'route'    => 'entry/payment/refund',
				'methods'  => $server::CREATABLE,
				'auth'     => true,
				'is-pro'   => true,
				'callback' => array( $this, 'refund_entry_payment' ),
			);

			return $routes;
		}

		public function get_all_payments() {
			$gateways = apply_filters( 'gutena_forms__payment_gateways', array() );

			foreach ( $gateways as $slug => $gateway ) {
				$object = new $gateway();

				if ( $object instanceof Gutena_Forms_Payment_Gateway_Settings ) {
					$gateways[ $slug ] = array(
						'title'     => $object->title,
						'desc'      => $object->description,
						'name'      => $object->id,
						'enabled'   => $object->is_enabled,
						'connected' => ! empty( $object->settings['connected'] ),
						'icon'      => $object->id,
					);
				} else {
					unset( $gateways[ $slug ] );
				}
			}

			return rest_ensure_response(
				array(
					'payments' => $gateways,
				)
			);
		}

		public function toggle_payment_gateway( $request ) {
			$gateway = sanitize_key( (string) $request->get_param( 'gateway' ) );
			$status  = $request->get_param( 'toggle' );

			if ( '' === $gateway ) {
				return rest_ensure_response(
					array(
						'success' => false,
						'message' => __( 'Invalid payment gateway.', 'gutena-forms' ),
					)
				);
			}

			$this->settings = get_option( 'gutena_forms__payment_settings', array() );
			if ( ! is_array( $this->settings ) ) {
				$this->settings = array();
			}
			if ( ! isset( $this->settings[ $gateway ] ) || ! is_array( $this->settings[ $gateway ] ) ) {
				$this->settings[ $gateway ] = array();
			}

			$this->settings[ $gateway ]['enable'] = ( 'true' === $status );
			update_option( 'gutena_forms__payment_settings', $this->settings );

			return rest_ensure_response(
				array(
					'success' => true,
					'message' => sprintf(
						/* translators: 1: gateway name, 2: enabled or disabled */
						__( 'Payment gateway %1$s has been %2$s', 'gutena-forms' ),
						$gateway,
						'true' === $status ? __( 'enabled', 'gutena-forms' ) : __( 'disabled', 'gutena-forms' )
					),
				)
			);
		}

		public function stripe_connect( $request ) {
			if ( ! class_exists( 'Gutena_Forms_Stripe_Connect' ) ) {
				return rest_ensure_response(
					array(
						'success' => false,
						'message' => __( 'Stripe connection is unavailable.', 'gutena-forms' ),
					)
				);
			}

			$payment_mode = sanitize_key( (string) $request->get_param( 'payment_mode' ) );
			$connect      = Gutena_Forms_Stripe_Connect::get_instance();
			$url          = $connect->get_connect_url( $payment_mode );

			if ( is_wp_error( $url ) ) {
				return rest_ensure_response(
					array(
						'success' => false,
						'message' => $url->get_error_message(),
					)
				);
			}

			return rest_ensure_response(
				array(
					'success'      => true,
					'redirect_url' => esc_url_raw( $url ),
				)
			);
		}

		public function stripe_disconnect( $request ) {
			unset( $request );

			if ( ! class_exists( 'Gutena_Forms_Stripe_Connect' ) ) {
				return rest_ensure_response(
					array(
						'success' => false,
						'message' => __( 'Stripe connection is unavailable.', 'gutena-forms' ),
					)
				);
			}

			Gutena_Forms_Stripe_Connect::get_instance()->disconnect();

			return rest_ensure_response(
				array(
					'success' => true,
					'message' => __( 'Stripe account disconnected.', 'gutena-forms' ),
				)
			);
		}

		public function stripe_retry_webhook( $request ) {
			unset( $request );

			if ( ! class_exists( 'Gutena_Forms_Stripe_Connect' ) ) {
				return rest_ensure_response(
					array(
						'success' => false,
						'message' => __( 'Stripe connection is unavailable.', 'gutena-forms' ),
					)
				);
			}

			$result = Gutena_Forms_Stripe_Connect::get_instance()->retry_webhook();

			if ( is_wp_error( $result ) ) {
				return rest_ensure_response(
					array(
						'success' => false,
						'message' => $result->get_error_message(),
						'code'    => $result->get_error_code(),
					)
				);
			}

			return rest_ensure_response( $result );
		}

		public function stripe_connect_notice( $request ) {
			unset( $request );

			if ( ! class_exists( 'Gutena_Forms_Stripe_Connect' ) ) {
				return rest_ensure_response( array( 'notice' => null ) );
			}

			$notice = Gutena_Forms_Stripe_Connect::consume_connect_notice();

			return rest_ensure_response(
				array(
					'notice' => $notice,
				)
			);
		}

		public function square_connect( $request ) {
			if ( ! class_exists( 'Gutena_Forms_Square_Connect' ) ) {
				return rest_ensure_response(
					array(
						'success' => false,
						'message' => __( 'Square connection is unavailable.', 'gutena-forms' ),
					)
				);
			}

			$payment_mode = sanitize_key( (string) $request->get_param( 'payment_mode' ) );
			$connect      = Gutena_Forms_Square_Connect::get_instance();
			$url          = $connect->get_connect_url( $payment_mode );

			if ( is_wp_error( $url ) ) {
				return rest_ensure_response(
					array(
						'success' => false,
						'message' => $url->get_error_message(),
					)
				);
			}

			return rest_ensure_response(
				array(
					'success'      => true,
					'redirect_url' => esc_url_raw( $url ),
				)
			);
		}

		public function square_disconnect( $request ) {
			unset( $request );

			if ( ! class_exists( 'Gutena_Forms_Square_Connect' ) ) {
				return rest_ensure_response(
					array(
						'success' => false,
						'message' => __( 'Square connection is unavailable.', 'gutena-forms' ),
					)
				);
			}

			Gutena_Forms_Square_Connect::get_instance()->disconnect();

			return rest_ensure_response(
				array(
					'success' => true,
					'message' => __( 'Square account disconnected.', 'gutena-forms' ),
				)
			);
		}

		public function square_connect_notice( $request ) {
			unset( $request );

			if ( ! class_exists( 'Gutena_Forms_Square_Connect' ) ) {
				return rest_ensure_response( array( 'notice' => null ) );
			}

			$notice = Gutena_Forms_Square_Connect::consume_connect_notice();

			return rest_ensure_response(
				array(
					'notice' => $notice,
				)
			);
		}

		public function square_connection_status( $request ) {
			unset( $request );

			if ( ! class_exists( 'Gutena_Forms_Square_Connect' ) ) {
				return rest_ensure_response(
					array(
						'connected' => false,
					)
				);
			}

			$public = Gutena_Forms_Square_Connect::get_public_settings();

			return rest_ensure_response(
				array(
					'connected'              => ! empty( $public['connected'] ),
					'payment_mode'           => $public['payment_mode'] ?? 'test',
					'connected_payment_mode' => $public['connected_payment_mode'] ?? ( $public['payment_mode'] ?? 'test' ),
					'account_name'           => $public['account_name'] ?? '',
					'merchant_currency'      => $public['merchant_currency'] ?? '',
					'location_id'            => $public['location_id'] ?? '',
					'business_locations'     => $public['business_locations'] ?? array(),
				)
			);
		}

		public function get_entry_payment( $request ) {
			if ( ! class_exists( 'Gutena_Forms_Entry_Payment' ) ) {
				return rest_ensure_response(
					array(
						'success' => false,
						'message' => __( 'Payment module unavailable.', 'gutena-forms' ),
					)
				);
			}

			$entry_id = absint( $request->get_param( 'id' ) );
			if ( ! $entry_id ) {
				return rest_ensure_response(
					array(
						'success' => false,
						'message' => __( 'Invalid entry ID.', 'gutena-forms' ),
					)
				);
			}

			return rest_ensure_response(
				array(
					'success' => true,
					'payment' => Gutena_Forms_Entry_Payment::get_instance()->get_public_details( $entry_id ),
				)
			);
		}

		public function get_all_payment_entries( $request ) {
			unset( $request );

			if ( ! class_exists( 'Gutena_Forms_Entry_Payment' ) ) {
				return rest_ensure_response(
					array(
						'success'  => false,
						'payments' => array(),
						'message'  => __( 'Payment module unavailable.', 'gutena-forms' ),
					)
				);
			}

			return rest_ensure_response(
				array(
					'success'  => true,
					'payments' => Gutena_Forms_Entry_Payment::get_instance()->get_all_list_items(),
				)
			);
		}

		/**
		 * Refund an entry payment (Stripe or Square).
		 *
		 * @param WP_REST_Request $request Request.
		 * @return WP_REST_Response
		 */
		public function refund_entry_payment( $request ) {
			if ( ! function_exists( 'is_gutena_forms_pro' ) || ! is_gutena_forms_pro() ) {
				return rest_ensure_response(
					array(
						'success' => false,
						'message' => __( 'Refunds require Gutena Forms Pro.', 'gutena-forms' ),
					),
					403
				);
			}

			$entry_id = absint( $request->get_param( 'id' ) );
			$amount   = absint( $request->get_param( 'amount' ) );
			$notes    = sanitize_textarea_field( (string) $request->get_param( 'notes' ) );

			if ( ! $entry_id || ! $amount ) {
				return rest_ensure_response(
					array(
						'success' => false,
						'message' => __( 'Invalid refund request.', 'gutena-forms' ),
					),
					400
				);
			}

			if ( ! class_exists( 'Gutena_Forms_Entry_Payment' ) ) {
				return rest_ensure_response(
					array(
						'success' => false,
						'message' => __( 'Payment module unavailable.', 'gutena-forms' ),
					),
					500
				);
			}

			$payment = Gutena_Forms_Entry_Payment::get_instance()->get_by_entry_id( $entry_id );
			$gateway = is_array( $payment ) ? sanitize_key( $payment['gateway'] ?? 'stripe' ) : 'stripe';

			if ( 'square' === $gateway ) {
				if ( ! class_exists( 'Gutena_Forms_Square_Payment_Service' ) ) {
					return rest_ensure_response(
						array(
							'success' => false,
							'message' => __( 'Square refunds are unavailable.', 'gutena-forms' ),
						),
						500
					);
				}

				$result = Gutena_Forms_Square_Payment_Service::get_instance()->refund_entry_payment( $entry_id, $amount, $notes );
			} elseif ( class_exists( 'Gutena_Forms_Stripe_Payment_Service' ) ) {
				$result = Gutena_Forms_Stripe_Payment_Service::get_instance()->refund_entry_payment( $entry_id, $amount, $notes );
			} else {
				return rest_ensure_response(
					array(
						'success' => false,
						'message' => __( 'Payment gateway is unavailable.', 'gutena-forms' ),
					),
					500
				);
			}

			if ( is_wp_error( $result ) ) {
				return rest_ensure_response(
					array(
						'success' => false,
						'message' => $result->get_error_message(),
					),
					400
				);
			}

			return rest_ensure_response( $result );
		}

		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}
	}

	Gutena_Forms_Payments_Endpoints::get_instance();
endif;

