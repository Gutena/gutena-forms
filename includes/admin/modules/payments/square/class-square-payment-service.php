<?php
/**
 * Square payment operations (refunds, webhook updates).
 *
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Square_Payment_Service' ) ) :
	class Gutena_Forms_Square_Payment_Service {

		const SQUARE_API_VERSION = '2024-01-18';

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
				'/payments/square/webhook',
				array(
					'methods'             => 'POST',
					'permission_callback' => '__return_true',
					'callback'            => array( $this, 'handle_webhook' ),
				)
			);
		}

		/**
		 * @param WP_REST_Request $request Request.
		 * @return WP_REST_Response
		 */
		public function handle_webhook( $request ) {
			$payload = json_decode( $request->get_body(), true );

			if ( ! is_array( $payload ) || empty( $payload['type'] ) ) {
				return rest_ensure_response( array( 'received' => false ), 400 );
			}

			/**
			 * Filter Square webhook handling before default processor.
			 *
			 * @param null|true|WP_Error $result  Default null.
			 * @param array              $payload Square webhook payload.
			 */
			$filtered = apply_filters( 'gutena_forms_square_webhook_event', null, $payload );
			if ( null !== $filtered ) {
				if ( is_wp_error( $filtered ) ) {
					return rest_ensure_response( array( 'error' => $filtered->get_error_message() ), 400 );
				}
				return rest_ensure_response( array( 'received' => true ) );
			}

			$this->process_event( $payload );

			return rest_ensure_response( array( 'received' => true ) );
		}

		/**
		 * @param array $event Square webhook payload.
		 * @return void
		 */
		private function process_event( $event ) {
			$type = sanitize_text_field( $event['type'] ?? '' );
			$obj  = isset( $event['data']['object'] ) && is_array( $event['data']['object'] )
				? $event['data']['object']
				: array();

			if ( isset( $obj['payment'] ) && is_array( $obj['payment'] ) ) {
				$obj = $obj['payment'];
			}

			if ( isset( $obj['refund'] ) && is_array( $obj['refund'] ) ) {
				$this->process_refund_event( $obj['refund'] );
				return;
			}

			if ( empty( $obj['id'] ) ) {
				return;
			}

			$entry_id = $this->resolve_entry_id_from_payment( $obj );
			if ( ! $entry_id ) {
				return;
			}

			$payment_model = Gutena_Forms_Entry_Payment::get_instance();
			$payment       = $payment_model->get_by_entry_id( $entry_id );
			if ( ! is_array( $payment ) || 'square' !== ( $payment['gateway'] ?? '' ) ) {
				return;
			}

			$square_status = sanitize_text_field( $obj['status'] ?? '' );
			$new_status    = Gutena_Forms_Entry_Payment::normalize_square_status( $square_status );

			if ( in_array( $type, array( 'payment.updated', 'payment.created' ), true ) && '' !== $new_status ) {
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
						'transaction_id' => sanitize_text_field( $obj['id'] ?? $payment['transaction_id'] ?? '' ),
						'gateway'        => 'square',
						'amount'         => Gutena_Forms_Entry_Payment::format_amount( absint( $payment['amount'] ?? 0 ), $payment['currency'] ?? 'USD' ),
						'status'         => Gutena_Forms_Entry_Payment::status_label( $new_status ),
						'user_id'        => 0,
						'mode'           => sanitize_text_field( $payment['payment_mode'] ?? 'test' ),
						'created_at'     => gmdate( 'Y-m-d H:i:s' ),
					)
				);
			}
		}

		/**
		 * @param array $refund Square refund object.
		 * @return void
		 */
		private function process_refund_event( $refund ) {
			$payment_id = sanitize_text_field( $refund['payment_id'] ?? '' );
			if ( '' === $payment_id || ! class_exists( 'Gutena_Forms_Entry_Payment' ) ) {
				return;
			}

			$entry_id = Gutena_Forms_Entry_Payment::get_instance()->get_entry_id_by_transaction_id( $payment_id );
			if ( ! $entry_id ) {
				return;
			}

			$payment_model = Gutena_Forms_Entry_Payment::get_instance();
			$payment       = $payment_model->get_by_entry_id( $entry_id );
			if ( ! is_array( $payment ) ) {
				return;
			}

			$refund_amount = isset( $refund['amount_money']['amount'] ) ? absint( $refund['amount_money']['amount'] ) : 0;
			$new_refunded  = absint( $payment['refunded_amount'] ?? 0 );
			if ( $refund_amount > 0 ) {
				$new_refunded = max( $new_refunded, $refund_amount );
			}

			$new_status = ( $new_refunded >= absint( $payment['amount'] ?? 0 ) ) ? 'refunded' : sanitize_text_field( $payment['status'] ?? 'succeeded' );

			$payment_model->update_for_entry(
				$entry_id,
				array(
					'status'          => $new_status,
					'refunded_amount' => $new_refunded,
				)
			);
		}

		/**
		 * @param array $payment Square payment object.
		 * @return int
		 */
		private function resolve_entry_id_from_payment( $payment ) {
			if ( ! empty( $payment['reference_id'] ) ) {
				return absint( $payment['reference_id'] );
			}

			$payment_id = sanitize_text_field( $payment['id'] ?? '' );
			if ( $payment_id && class_exists( 'Gutena_Forms_Entry_Payment' ) ) {
				return Gutena_Forms_Entry_Payment::get_instance()->get_entry_id_by_transaction_id( $payment_id );
			}

			return 0;
		}

		/**
		 * Issue a Square refund for an entry payment.
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

			if ( 'square' !== ( $payment['gateway'] ?? '' ) ) {
				return new WP_Error( 'invalid_gateway', __( 'This entry is not a Square payment.', 'gutena-forms' ) );
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

			$credentials = $this->get_square_credentials( $payment['payment_mode'] ?? 'test' );
			if ( empty( $credentials['access_token'] ) ) {
				return new WP_Error( 'square_not_connected', __( 'Square is not connected.', 'gutena-forms' ) );
			}

			$payment_id = sanitize_text_field( $payment['payment_id'] ?? $payment['transaction_id'] ?? '' );
			if ( '' === $payment_id ) {
				return new WP_Error( 'missing_transaction', __( 'Transaction ID is missing.', 'gutena-forms' ) );
			}

			$currency = strtoupper( sanitize_text_field( $payment['currency'] ?? 'USD' ) );
			$base     = self::get_api_base_url( $payment['payment_mode'] ?? 'test' );

			$body = array(
				'idempotency_key' => wp_generate_uuid4(),
				'payment_id'      => $payment_id,
				'amount_money'    => array(
					'amount'   => $amount,
					'currency' => $currency,
				),
			);

			if ( '' !== $notes ) {
				$body['reason'] = sanitize_text_field( $notes );
			}

			$response = wp_remote_post(
				$base . '/v2/refunds',
				array(
					'timeout' => 20,
					'headers' => array(
						'Authorization'  => 'Bearer ' . $credentials['access_token'],
						'Square-Version' => self::SQUARE_API_VERSION,
						'Content-Type'   => 'application/json',
					),
					'body'    => wp_json_encode( $body ),
				)
			);

			if ( is_wp_error( $response ) ) {
				return $response;
			}

			$code         = (int) wp_remote_retrieve_response_code( $response );
			$response_body = json_decode( wp_remote_retrieve_body( $response ), true );

			if ( $code < 200 || $code >= 300 ) {
				$message = __( 'Refund failed. Please try again.', 'gutena-forms' );
				if ( is_array( $response_body ) && ! empty( $response_body['errors'][0]['detail'] ) ) {
					$message = sanitize_text_field( $response_body['errors'][0]['detail'] );
				} elseif ( is_array( $response_body ) && ! empty( $response_body['errors'][0]['code'] ) ) {
					$message = sanitize_text_field( $response_body['errors'][0]['code'] );
				}

				return new WP_Error( 'square_refund_failed', $message );
			}

			$new_refunded = absint( $payment['refunded_amount'] ?? 0 ) + $amount;
			$new_status   = ( $new_refunded >= absint( $payment['amount'] ?? 0 ) ) ? 'refunded' : sanitize_text_field( $payment['status'] ?? 'succeeded' );

			$refund_id = '';
			if ( is_array( $response_body ) && ! empty( $response_body['refund']['id'] ) ) {
				$refund_id = sanitize_text_field( $response_body['refund']['id'] );
			}

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
					'transaction_id' => $refund_id ? $refund_id : $payment_id,
					'gateway'        => 'square',
					'amount'         => Gutena_Forms_Entry_Payment::format_amount( $amount, $currency ),
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
		 * Build Square dashboard URL for a payment.
		 *
		 * @param string $payment_id   Square payment ID.
		 * @param string $payment_mode test|live.
		 * @return string
		 */
		public static function get_dashboard_url( $payment_id, $payment_mode = 'test' ) {
			$payment_id = sanitize_text_field( $payment_id );
			if ( '' === $payment_id ) {
				return '';
			}

			$host = 'live' === $payment_mode
				? 'https://squareup.com'
				: 'https://squareupsandbox.com';

			return esc_url_raw( $host . '/dashboard/sales/transactions/' . rawurlencode( $payment_id ) );
		}

		/**
		 * @param string $payment_mode test|live.
		 * @return string
		 */
		public static function get_api_base_url( $payment_mode = 'test' ) {
			return 'live' === $payment_mode
				? 'https://connect.squareup.com'
				: 'https://connect.squareupsandbox.com';
		}

		/**
		 * @param string $payment_mode Optional mode override.
		 * @return array
		 */
		private function get_square_credentials( $payment_mode = 'test' ) {
			unset( $payment_mode );

			$all = get_option( 'gutena_forms__payment_credentials', array() );
			if ( ! is_array( $all ) || empty( $all['square'] ) || ! is_array( $all['square'] ) ) {
				return array();
			}

			return $all['square'];
		}
	}

	Gutena_Forms_Square_Payment_Service::get_instance();
endif;
