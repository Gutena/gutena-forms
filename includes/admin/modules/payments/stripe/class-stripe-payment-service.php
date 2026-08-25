<?php
/**
 * Stripe payment operations (refunds, webhook updates).
 *
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Stripe_Payment_Service' ) ) :
	class Gutena_Forms_Stripe_Payment_Service {

		private static $instance;

		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		private function __construct() {
			add_action( 'rest_api_init', array( $this, 'register_webhook_route' ) );
		}

		public function register_webhook_route() {
			register_rest_route(
				'gutena-forms/v1',
				'/payments/stripe/webhook',
				array(
					'methods'             => 'POST',
					'permission_callback' => '__return_true',
					'callback'            => array( $this, 'handle_webhook' ),
				)
			);
		}

		/**
		 * Process Stripe webhook event and update entry payment status.
		 *
		 * @param WP_REST_Request $request Request.
		 * @return WP_REST_Response
		 */
		public function handle_webhook( $request ) {
			$payload = $request->get_body();
			$event   = json_decode( $payload, true );

			if ( ! is_array( $event ) || empty( $event['type'] ) ) {
				return rest_ensure_response( array( 'received' => false ), 400 );
			}

			/**
			 * Filter webhook handling before default processor.
			 *
			 * @param null|true|WP_Error $result Default null.
			 * @param array              $event  Stripe event payload.
			 */
			$filtered = apply_filters( 'gutena_forms_stripe_webhook_event', null, $event );
			if ( null !== $filtered ) {
				if ( is_wp_error( $filtered ) ) {
					return rest_ensure_response( array( 'error' => $filtered->get_error_message() ), 400 );
				}
				return rest_ensure_response( array( 'received' => true ) );
			}

			$this->process_event( $event );

			return rest_ensure_response( array( 'received' => true ) );
		}

		/**
		 * @param array $event Stripe event.
		 * @return void
		 */
		private function process_event( $event ) {
			$type = sanitize_text_field( $event['type'] );
			$obj  = isset( $event['data']['object'] ) && is_array( $event['data']['object'] ) ? $event['data']['object'] : array();

			$entry_id = $this->resolve_entry_id_from_object( $obj );
			if ( ! $entry_id ) {
				return;
			}

			$payment_model = Gutena_Forms_Entry_Payment::get_instance();
			$payment       = $payment_model->get_by_entry_id( $entry_id );
			if ( ! is_array( $payment ) ) {
				return;
			}

			$status_map = array(
				'payment_intent.succeeded'         => 'succeeded',
				'payment_intent.payment_failed'    => 'failed',
				'payment_intent.processing'        => 'processing',
				'charge.refunded'                  => 'refunded',
				'checkout.session.completed'       => 'succeeded',
			);

			if ( ! isset( $status_map[ $type ] ) ) {
				return;
			}

			$new_status = $status_map[ $type ];
			$payment_model->update_for_entry(
				$entry_id,
				array(
					'status' => $new_status,
				)
			);

			$payment_model->append_log(
				$entry_id,
				array(
					'event'          => 'payment_verification',
					'transaction_id' => sanitize_text_field( $payment['transaction_id'] ?? '' ),
					'gateway'        => 'stripe',
					'amount'         => Gutena_Forms_Entry_Payment::format_amount( absint( $payment['amount'] ?? 0 ), $payment['currency'] ?? 'USD' ),
					'status'         => Gutena_Forms_Entry_Payment::status_label( $new_status ),
					'user_id'        => 0,
					'mode'           => sanitize_text_field( $payment['payment_mode'] ?? 'test' ),
					'created_at'     => gmdate( 'Y-m-d H:i:s' ),
				)
			);
		}

		/**
		 * @param array $object Stripe object.
		 * @return int
		 */
		private function resolve_entry_id_from_object( $object ) {
			if ( ! empty( $object['metadata']['entry_id'] ) ) {
				return absint( $object['metadata']['entry_id'] );
			}

			$transaction_id = '';
			if ( ! empty( $object['id'] ) && is_string( $object['id'] ) && 0 === strpos( $object['id'], 'pi_' ) ) {
				$transaction_id = sanitize_text_field( $object['id'] );
			} elseif ( ! empty( $object['payment_intent'] ) ) {
				$transaction_id = sanitize_text_field( $object['payment_intent'] );
			}

			if ( $transaction_id && class_exists( 'Gutena_Forms_Entry_Payment' ) ) {
				$entry_id = Gutena_Forms_Entry_Payment::get_instance()->get_entry_id_by_transaction_id( $transaction_id );
				if ( $entry_id ) {
					return $entry_id;
				}
			}

			if ( ! empty( $object['client_reference_id'] ) ) {
				return absint( $object['client_reference_id'] );
			}

			return 0;
		}

		/**
		 * Issue a Stripe refund for an entry payment.
		 *
		 * @param int    $entry_id Entry ID.
		 * @param int    $amount   Refund amount in cents.
		 * @param string $notes    Optional refund notes.
		 * @return array|WP_Error
		 */
		public function refund_entry_payment( $entry_id, $amount, $notes = '' ) {
			$entry_id = absint( $entry_id );
			$amount   = absint( $amount );

			$payment_model = Gutena_Forms_Entry_Payment::get_instance();
			$payment       = $payment_model->get_by_entry_id( $entry_id );

			if ( ! is_array( $payment ) ) {
				return new WP_Error( 'no_payment', __( 'No payment found for this entry.', 'gutena-forms' ) );
			}

			if ( ! Gutena_Forms_Entry_Payment::can_refund( $payment ) ) {
				return new WP_Error( 'not_refundable', __( 'This payment cannot be refunded.', 'gutena-forms' ) );
			}

			$refundable = absint( $payment['amount'] ?? 0 ) - absint( $payment['refunded_amount'] ?? 0 );
			if ( $amount <= 0 || $amount > $refundable ) {
				return new WP_Error(
					'invalid_amount',
					sprintf(
						/* translators: %s: maximum refundable amount */
						__( 'Maximum refundable amount: %s', 'gutena-forms' ),
						Gutena_Forms_Entry_Payment::format_amount( $refundable, $payment['currency'] ?? 'USD' )
					)
				);
			}

			$credentials = $this->get_stripe_credentials();
			if ( empty( $credentials['access_token'] ) ) {
				return new WP_Error( 'stripe_not_connected', __( 'Stripe is not connected.', 'gutena-forms' ) );
			}

			$payment_intent = sanitize_text_field( $payment['transaction_id'] ?? '' );
			if ( empty( $payment_intent ) ) {
				return new WP_Error( 'missing_transaction', __( 'Transaction ID is missing.', 'gutena-forms' ) );
			}

			$response = wp_remote_post(
				'https://api.stripe.com/v1/refunds',
				array(
					'timeout' => 20,
					'headers' => array(
						'Authorization' => 'Bearer ' . $credentials['access_token'],
					),
					'body'    => array(
						'payment_intent' => $payment_intent,
						'amount'         => $amount,
						'metadata'       => array(
							'entry_id'     => (string) $entry_id,
							'refund_notes' => sanitize_text_field( $notes ),
						),
					),
				)
			);

			if ( is_wp_error( $response ) ) {
				return $response;
			}

			$code = (int) wp_remote_retrieve_response_code( $response );
			$body = json_decode( wp_remote_retrieve_body( $response ), true );

			if ( $code < 200 || $code >= 300 ) {
				$message = is_array( $body ) && ! empty( $body['error']['message'] )
					? sanitize_text_field( $body['error']['message'] )
					: __( 'Refund failed. Please try again.', 'gutena-forms' );

				return new WP_Error( 'stripe_refund_failed', $message );
			}

			$new_refunded = absint( $payment['refunded_amount'] ?? 0 ) + $amount;
			$new_status   = ( $new_refunded >= absint( $payment['amount'] ?? 0 ) ) ? 'refunded' : sanitize_text_field( $payment['status'] ?? 'succeeded' );

			$payment_model->update_for_entry(
				$entry_id,
				array(
					'status'          => $new_status,
					'refunded_amount' => $new_refunded,
					'refund_notes'    => sanitize_text_field( $notes ),
				)
			);

			$payment_model->append_log(
				$entry_id,
				array(
					'event'          => 'refund',
					'transaction_id' => sanitize_text_field( $body['id'] ?? $payment_intent ),
					'gateway'        => 'stripe',
					'amount'         => Gutena_Forms_Entry_Payment::format_amount( $amount, $payment['currency'] ?? 'USD' ),
					'status'         => Gutena_Forms_Entry_Payment::status_label( $new_status ),
					'user_id'        => get_current_user_id(),
					'mode'           => sanitize_text_field( $payment['payment_mode'] ?? 'test' ),
					'created_at'     => gmdate( 'Y-m-d H:i:s' ),
				)
			);

			return array(
				'success' => true,
				'message' => __( 'Refund processed successfully.', 'gutena-forms' ),
				'payment' => $payment_model->get_public_details( $entry_id ),
			);
		}

		/**
		 * @return array
		 */
		private function get_stripe_credentials() {
			$all = get_option( 'gutena_forms__payment_credentials', array() );
			if ( ! is_array( $all ) || empty( $all['stripe'] ) || ! is_array( $all['stripe'] ) ) {
				return array();
			}

			return $all['stripe'];
		}
	}

	Gutena_Forms_Stripe_Payment_Service::get_instance();
endif;
