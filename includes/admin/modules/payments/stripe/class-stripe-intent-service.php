<?php
/**
 * Stripe PaymentIntent creation and verification for embedded checkout.
 *
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Stripe_Intent_Service' ) ) :
	/**
	 * Handles PaymentIntent lifecycle for form submissions.
	 */
	class Gutena_Forms_Stripe_Intent_Service {

		/**
		 * Singleton instance.
		 *
		 * @var Gutena_Forms_Stripe_Intent_Service|null
		 */
		private static $instance = null;

		/**
		 * Get instance.
		 *
		 * @return Gutena_Forms_Stripe_Intent_Service
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor.
		 */
		private function __construct() {
			add_action( 'gutena_forms_after_entry_saved', array( $this, 'maybe_save_entry_payment' ), 10, 4 );
		}

		/**
		 * Verify frontend form nonce for public payment REST routes.
		 *
		 * @param WP_REST_Request $request Request.
		 * @return bool
		 */
		public static function verify_form_nonce( $request ) {
			$nonce = $request->get_header( 'X-Gutena-Nonce' );
			if ( empty( $nonce ) ) {
				$nonce = $request->get_param( 'nonce' );
			}

			return ! empty( $nonce ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $nonce ) ), 'gutena_Forms' );
		}

		/**
		 * Public Stripe config for a form (no secrets).
		 *
		 * @param WP_REST_Request $request Request.
		 * @return WP_REST_Response
		 */
		public function rest_get_public_config( $request ) {
			$form_id = sanitize_key( (string) $request->get_param( 'form_id' ) );
			$config  = $this->get_public_config( $form_id );

			if ( is_wp_error( $config ) ) {
				return rest_ensure_response(
					array(
						'success' => false,
						'message' => $config->get_error_message(),
						'code'    => $config->get_error_code(),
					)
				);
			}

			return rest_ensure_response(
				array(
					'success' => true,
					'config'  => $config,
				)
			);
		}

		/**
		 * Create a PaymentIntent for the current form submission.
		 *
		 * @param WP_REST_Request $request Request.
		 * @return WP_REST_Response
		 */
		public function rest_create_payment_intent( $request ) {
			$form_id         = sanitize_key( (string) $request->get_param( 'form_id' ) );
			$stripe_field_id = sanitize_key( (string) $request->get_param( 'stripe_field_id' ) );
			$field_values    = $request->get_param( 'field_values' );

			if ( ! is_array( $field_values ) ) {
				$field_values = array();
			}

			$result = $this->create_payment_intent( $form_id, $field_values, $stripe_field_id );

			if ( is_wp_error( $result ) ) {
				return rest_ensure_response(
					array(
						'success' => false,
						'message' => $result->get_error_message(),
						'code'    => $result->get_error_code(),
					)
				);
			}

			return rest_ensure_response(
				array(
					'success' => true,
					'intent'  => $result,
				)
			);
		}

		/**
		 * Build public config array for frontend Elements.
		 *
		 * @param string $form_id Form ID.
		 * @return array|WP_Error
		 */
		public function get_public_config( $form_id ) {
			if ( ! gutena_forms_is_stripe_gateway_enabled() ) {
				return new WP_Error( 'stripe_disabled', __( 'Stripe payments are disabled.', 'gutena-forms' ) );
			}

			$payment_stripe = $this->resolve_form_payment_stripe( $form_id );
			if ( empty( $payment_stripe['connected'] ) ) {
				return new WP_Error( 'stripe_not_connected', __( 'Stripe is not connected for this form.', 'gutena-forms' ) );
			}

			$mode            = in_array( $payment_stripe['payment_mode'] ?? 'test', array( 'live', 'test' ), true ) ? $payment_stripe['payment_mode'] : 'test';
			$publishable_key = class_exists( 'Gutena_Forms_Stripe_Connect' )
				? Gutena_Forms_Stripe_Connect::get_publishable_key( $mode )
				: '';

			if ( empty( $publishable_key ) ) {
				return new WP_Error(
					'stripe_publishable_key_missing',
					__( 'Stripe publishable key is not configured. Add it under Gutena Forms → Settings → Payment → Stripe.', 'gutena-forms' )
				);
			}

			return array(
				'publishable_key' => $publishable_key,
				'account_id'      => class_exists( 'Gutena_Forms_Stripe_Connect' )
					? Gutena_Forms_Stripe_Connect::get_stripe_js_account_id( $mode )
					: '',
				'currency'        => sanitize_text_field( $payment_stripe['currency'] ?? 'USD' ),
				'payment_mode'    => $mode,
			);
		}

		/**
		 * Create PaymentIntent on the connected Stripe account.
		 *
		 * @param string $form_id         Form ID.
		 * @param array  $field_values    Submitted field values keyed by nameAttr.
		 * @param string $stripe_field_id Stripe field nameAttr.
		 * @return array|WP_Error
		 */
		public function create_payment_intent( $form_id, $field_values, $stripe_field_id = '' ) {
			$schema = $this->get_form_schema( $form_id );
			if ( empty( $schema ) ) {
				return new WP_Error( 'invalid_form', __( 'Form not found.', 'gutena-forms' ) );
			}

			$stripe_field = $this->find_stripe_field( $schema, $stripe_field_id );
			if ( empty( $stripe_field ) ) {
				return new WP_Error( 'stripe_field_missing', __( 'Payment field is not configured for this form.', 'gutena-forms' ) );
			}

			$payment_type = sanitize_key( $stripe_field['paymentType'] ?? 'one_time' );
			if ( 'subscription' === $payment_type ) {
				return new WP_Error(
					'subscription_unsupported',
					__( 'Subscription payments are not yet supported on the frontend. Use one-time payment for now.', 'gutena-forms' )
				);
			}

			$payment_stripe = $this->resolve_form_payment_stripe( $form_id );
			$currency       = sanitize_text_field( $payment_stripe['currency'] ?? 'USD' );
			$amount_cents   = $this->calculate_amount_cents( $stripe_field, $field_values, $currency );

			if ( is_wp_error( $amount_cents ) ) {
				return $amount_cents;
			}

			$credentials = $this->get_stripe_credentials();
			if ( empty( $credentials['access_token'] ) ) {
				return new WP_Error( 'stripe_not_connected', __( 'Stripe is not connected.', 'gutena-forms' ) );
			}

			$field_id = ! empty( $stripe_field['nameAttr'] ) ? sanitize_key( $stripe_field['nameAttr'] ) : 'stripe_payment';

			$response = wp_remote_post(
				'https://api.stripe.com/v1/payment_intents',
				array(
					'timeout' => 20,
					'headers' => array(
						'Authorization' => 'Bearer ' . $credentials['access_token'],
					),
					'body'    => array(
						'amount'                    => $amount_cents,
						'currency'                  => strtolower( $currency ),
						'payment_method_types[]'    => 'card',
						'metadata[form_id]'         => $form_id,
						'metadata[stripe_field_id]' => $field_id,
						'metadata[gutena_forms]'    => '1',
					),
				)
			);

			if ( is_wp_error( $response ) ) {
				return $response;
			}

			$code = (int) wp_remote_retrieve_response_code( $response );
			$body = json_decode( wp_remote_retrieve_body( $response ), true );

			if ( $code < 200 || $code >= 300 || ! is_array( $body ) ) {
				$message = is_array( $body ) && ! empty( $body['error']['message'] )
					? sanitize_text_field( $body['error']['message'] )
					: __( 'Unable to create payment. Please try again.', 'gutena-forms' );

				return new WP_Error( 'stripe_intent_failed', $message );
			}

			$config = $this->get_public_config( $form_id );
			if ( is_wp_error( $config ) ) {
				return $config;
			}

			return array(
				'payment_intent_id' => sanitize_text_field( $body['id'] ?? '' ),
				'client_secret'     => sanitize_text_field( $body['client_secret'] ?? '' ),
				'amount'            => $amount_cents,
				'currency'          => $currency,
				'publishable_key'   => $config['publishable_key'],
				'account_id'        => $config['account_id'],
			);
		}

		/**
		 * Validate payment during form submission.
		 *
		 * @param string $form_id Form ID.
		 * @param array  $schema  Form schema.
		 * @return true|WP_Error
		 */
		public function validate_submission_payment( $form_id, $schema ) {
			if ( ! gutena_forms_is_stripe_gateway_enabled() ) {
				return true;
			}

			$stripe_field = $this->find_stripe_field( $schema );
			if ( empty( $stripe_field ) ) {
				return true;
			}

			$payment_stripe = $this->resolve_form_payment_stripe( $form_id );
			if ( empty( $payment_stripe['connected'] ) ) {
				return new WP_Error( 'stripe_not_connected', __( 'Payment could not be processed. Stripe is not connected.', 'gutena-forms' ) );
			}

			$field_id      = ! empty( $stripe_field['nameAttr'] ) ? sanitize_key( $stripe_field['nameAttr'] ) : 'stripe_payment';
			$intent_id_key = $field_id . '_payment_intent';
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$intent_id = isset( $_POST[ $intent_id_key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $intent_id_key ] ) ) : '';

			if ( empty( $intent_id ) ) {
				return new WP_Error( 'payment_required', __( 'Please complete the payment details before submitting.', 'gutena-forms' ) );
			}

			$intent = $this->retrieve_payment_intent( $intent_id );
			if ( is_wp_error( $intent ) ) {
				return $intent;
			}

			if ( empty( $intent['status'] ) || ! in_array( $intent['status'], array( 'succeeded', 'processing' ), true ) ) {
				return new WP_Error( 'payment_incomplete', __( 'Payment was not completed. Please check your card details and try again.', 'gutena-forms' ) );
			}

			$metadata_form_id = sanitize_key( $intent['metadata']['form_id'] ?? '' );
			if ( $metadata_form_id && $metadata_form_id !== $form_id ) {
				return new WP_Error( 'payment_form_mismatch', __( 'Payment verification failed.', 'gutena-forms' ) );
			}

			$field_values = $this->get_posted_field_values();
			$currency     = sanitize_text_field( $payment_stripe['currency'] ?? 'USD' );
			$expected     = $this->calculate_amount_cents( $stripe_field, $field_values, $currency );

			if ( is_wp_error( $expected ) ) {
				return $expected;
			}

			$received_amount = absint( $intent['amount'] ?? 0 );
			if ( $received_amount !== $expected ) {
				return new WP_Error( 'payment_amount_mismatch', __( 'Payment amount does not match the form total.', 'gutena-forms' ) );
			}

			$received_currency = strtoupper( sanitize_text_field( $intent['currency'] ?? '' ) );
			if ( $received_currency && $received_currency !== strtoupper( $currency ) ) {
				return new WP_Error( 'payment_currency_mismatch', __( 'Payment currency does not match the form settings.', 'gutena-forms' ) );
			}

			return true;
		}

		/**
		 * Persist payment details after an entry is saved.
		 *
		 * @param int    $entry_id       Entry ID.
		 * @param array  $form_data      Submitted form data.
		 * @param string $block_form_id  Block form ID.
		 * @param array  $field_schema   Field schema.
		 * @return void
		 */
		public function maybe_save_entry_payment( $entry_id, $form_data, $block_form_id, $field_schema ) {
			unset( $form_data );

			if ( ! class_exists( 'Gutena_Forms_Entry_Payment' ) ) {
				return;
			}

			$schema = $this->get_form_schema( $block_form_id );
			if ( empty( $schema ) ) {
				return;
			}

			$stripe_field = $this->find_stripe_field( $schema );
			if ( empty( $stripe_field ) ) {
				return;
			}

			$field_id  = ! empty( $stripe_field['nameAttr'] ) ? sanitize_key( $stripe_field['nameAttr'] ) : 'stripe_payment';
			$intent_id = $this->get_posted_payment_intent_id( $field_id );
			if ( empty( $intent_id ) ) {
				return;
			}

			$intent = $this->retrieve_payment_intent( $intent_id );
			if ( is_wp_error( $intent ) ) {
				return;
			}

			$payment_stripe = $this->resolve_form_payment_stripe( $block_form_id );
			$currency       = sanitize_text_field( $payment_stripe['currency'] ?? 'USD' );
			$mode           = sanitize_key( $payment_stripe['payment_mode'] ?? 'test' );
			$form_post_id   = isset( $field_schema['form_id'] ) ? absint( $field_schema['form_id'] ) : 0;
			$form_name      = '';

			if ( $form_post_id && class_exists( 'Gutena_Forms_Forms_Model' ) ) {
				$form_name = Gutena_Forms_Forms_Model::get_instance()->get_name_by_id( $form_post_id );
			}

			$customer_email = $this->resolve_mapped_field_value( $stripe_field['customerEmailField'] ?? '' );
			$customer_name  = $this->resolve_mapped_field_value( $stripe_field['customerNameField'] ?? '' );
			$dashboard_url  = $this->get_stripe_dashboard_url( $intent_id, $mode );

			$payment = array(
				'gateway'              => 'stripe',
				'gateway_label'        => 'Stripe',
				'payment_id'           => sanitize_text_field( $intent['id'] ?? $intent_id ),
				'payment_mode'         => $mode,
				'payment_method'       => 'Stripe',
				'payment_type'         => sanitize_key( $stripe_field['paymentType'] ?? 'one_time' ),
				'transaction_id'       => sanitize_text_field( $intent['id'] ?? $intent_id ),
				'amount'               => absint( $intent['amount'] ?? 0 ),
				'currency'             => strtoupper( sanitize_text_field( $intent['currency'] ?? $currency ) ),
				'status'               => sanitize_text_field( $intent['status'] ?? 'pending' ),
				'customer_name'        => sanitize_text_field( $customer_name ),
				'customer_email'       => sanitize_email( $customer_email ),
				'transaction_date'     => gmdate( 'Y-m-d H:i:s' ),
				'received_on'            => gmdate( 'Y-m-d H:i:s' ),
				'stripe_dashboard_url' => esc_url_raw( $dashboard_url ),
				'form_id'              => $form_post_id,
				'form_name'            => sanitize_text_field( $form_name ),
				'logs'                 => array(
					array(
						'event'          => 'payment',
						'transaction_id' => sanitize_text_field( $intent['id'] ?? $intent_id ),
						'gateway'        => 'stripe',
						'amount'         => Gutena_Forms_Entry_Payment::format_amount( absint( $intent['amount'] ?? 0 ), $currency ),
						'status'         => Gutena_Forms_Entry_Payment::status_label( $intent['status'] ?? 'pending' ),
						'user_id'        => 0,
						'mode'           => $mode,
						'created_at'     => gmdate( 'Y-m-d H:i:s' ),
					),
				),
			);

			Gutena_Forms_Entry_Payment::get_instance()->save_for_entry( absint( $entry_id ), $payment );
		}

		/**
		 * Retrieve PaymentIntent from Stripe.
		 *
		 * @param string $intent_id PaymentIntent ID.
		 * @return array|WP_Error
		 */
		public function retrieve_payment_intent( $intent_id ) {
			$intent_id = sanitize_text_field( $intent_id );
			if ( empty( $intent_id ) ) {
				return new WP_Error( 'missing_intent', __( 'Payment reference is missing.', 'gutena-forms' ) );
			}

			$credentials = $this->get_stripe_credentials();
			if ( empty( $credentials['access_token'] ) ) {
				return new WP_Error( 'stripe_not_connected', __( 'Stripe is not connected.', 'gutena-forms' ) );
			}

			$response = wp_remote_get(
				'https://api.stripe.com/v1/payment_intents/' . rawurlencode( $intent_id ),
				array(
					'timeout' => 20,
					'headers' => array(
						'Authorization' => 'Bearer ' . $credentials['access_token'],
					),
				)
			);

			if ( is_wp_error( $response ) ) {
				return $response;
			}

			$code = (int) wp_remote_retrieve_response_code( $response );
			$body = json_decode( wp_remote_retrieve_body( $response ), true );

			if ( $code < 200 || $code >= 300 || ! is_array( $body ) ) {
				$message = is_array( $body ) && ! empty( $body['error']['message'] )
					? sanitize_text_field( $body['error']['message'] )
					: __( 'Unable to verify payment.', 'gutena-forms' );

				return new WP_Error( 'stripe_intent_lookup_failed', $message );
			}

			return $body;
		}

		/**
		 * Calculate charge amount in cents.
		 *
		 * @param array  $stripe_field Stripe field schema.
		 * @param array  $field_values Posted values keyed by nameAttr.
		 * @param string $currency     Currency code.
		 * @return int|WP_Error
		 */
		public function calculate_amount_cents( $stripe_field, $field_values, $currency ) {
			$amount_type  = sanitize_key( $stripe_field['amountType'] ?? 'fixed' );
			$minimum      = isset( $stripe_field['minimumAmount'] ) ? floatval( $stripe_field['minimumAmount'] ) : 0;
			$amount       = 0.0;

			if ( 'fixed' === $amount_type ) {
				$amount = isset( $stripe_field['fixedAmount'] ) ? floatval( $stripe_field['fixedAmount'] ) : 0;
			} elseif ( 'variable' === $amount_type ) {
				$source_field = sanitize_key( $stripe_field['variableAmountField'] ?? '' );
				$raw_value    = isset( $field_values[ $source_field ] ) ? $field_values[ $source_field ] : '';
				$amount       = floatval( $raw_value );
			}

			if ( $amount <= 0 ) {
				return new WP_Error( 'invalid_amount', __( 'Please enter a valid payment amount.', 'gutena-forms' ) );
			}

			if ( $minimum > 0 && $amount < $minimum ) {
				return new WP_Error(
					'amount_below_minimum',
					sprintf(
						/* translators: %s: minimum amount */
						__( 'Payment amount must be at least %s.', 'gutena-forms' ),
						number_format_i18n( $minimum, 2 )
					)
				);
			}

			return self::to_smallest_currency_unit( $amount, $currency );
		}

		/**
		 * Convert major currency units to Stripe smallest unit.
		 *
		 * @param float  $amount   Amount in major units.
		 * @param string $currency Currency code.
		 * @return int
		 */
		public static function to_smallest_currency_unit( $amount, $currency ) {
			$zero_decimal = array( 'BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF', 'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF' );
			$currency     = strtoupper( sanitize_text_field( $currency ) );

			if ( in_array( $currency, $zero_decimal, true ) ) {
				return absint( round( $amount ) );
			}

			return absint( round( $amount * 100 ) );
		}

		/**
		 * Find stripe field definition in schema.
		 *
		 * @param array  $schema          Form schema.
		 * @param string $stripe_field_id Optional nameAttr filter.
		 * @return array|null
		 */
		public function find_stripe_field( $schema, $stripe_field_id = '' ) {
			if ( empty( $schema['form_fields'] ) || ! is_array( $schema['form_fields'] ) ) {
				return null;
			}

			foreach ( $schema['form_fields'] as $name_attr => $field ) {
				if ( ! is_array( $field ) ) {
					continue;
				}

				$block_name = $field['blockName'] ?? '';
				$field_type = $field['fieldType'] ?? '';

				if ( 'gutena/stripe-field' !== $block_name && 'stripe' !== $field_type ) {
					continue;
				}

				if ( $stripe_field_id && sanitize_key( $stripe_field_id ) !== sanitize_key( $name_attr ) ) {
					continue;
				}

				$field['nameAttr'] = $name_attr;
				return $field;
			}

			return null;
		}

		/**
		 * @param string $form_id Form ID.
		 * @return array
		 */
		private function get_form_schema( $form_id ) {
			if ( empty( $form_id ) ) {
				return array();
			}

			$schema = gutena_forms_get_form_schema_option( $form_id, false );
			return is_array( $schema ) ? $schema : array();
		}

		/**
		 * @param string $form_id Form ID.
		 * @return array
		 */
		private function resolve_form_payment_stripe( $form_id ) {
			$schema         = $this->get_form_schema( $form_id );
			$block_stripe   = is_array( $schema ) && ! empty( $schema['form_attrs']['paymentStripe'] )
				? $schema['form_attrs']['paymentStripe']
				: array();

			if ( class_exists( 'Gutena_Forms_Form_Block' ) ) {
				return Gutena_Forms_Form_Block::get_effective_payment_stripe( $block_stripe );
			}

			return is_array( $block_stripe ) ? $block_stripe : array();
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

		/**
		 * Collect posted field values for amount calculation.
		 *
		 * @return array
		 */
		private function get_posted_field_values() {
			$values = array();

			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			foreach ( $_POST as $key => $value ) {
				$key = sanitize_key( wp_unslash( $key ) );
				if ( is_array( $value ) ) {
					$values[ $key ] = Gutena_Forms_Helper::sanitize_array( wp_unslash( $value ), true );
					$values[ $key ] = implode( ', ', $values[ $key ] );
				} else {
					$values[ $key ] = sanitize_text_field( wp_unslash( $value ) );
				}
			}

			return $values;
		}

		/**
		 * @param string $field_id Stripe field nameAttr.
		 * @return string
		 */
		private function get_posted_payment_intent_id( $field_id ) {
			$key = sanitize_key( $field_id ) . '_payment_intent';
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			return isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : '';
		}

		/**
		 * @param string $name_attr Mapped field nameAttr.
		 * @return string
		 */
		private function resolve_mapped_field_value( $name_attr ) {
			$name_attr = sanitize_key( $name_attr );
			if ( empty( $name_attr ) ) {
				return '';
			}

			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			if ( ! isset( $_POST[ $name_attr ] ) ) {
				return '';
			}

			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$value = wp_unslash( $_POST[ $name_attr ] );
			if ( is_array( $value ) ) {
				$value = Gutena_Forms_Helper::sanitize_array( $value, true );
				return implode( ' ', $value );
			}

			return sanitize_text_field( $value );
		}

		/**
		 * @param string $intent_id PaymentIntent ID.
		 * @param string $mode      test|live.
		 * @return string
		 */
		private function get_stripe_dashboard_url( $intent_id, $mode ) {
			$base = 'live' === $mode ? 'https://dashboard.stripe.com' : 'https://dashboard.stripe.com/test';
			return $base . '/payments/' . rawurlencode( $intent_id );
		}
	}

	Gutena_Forms_Stripe_Intent_Service::get_instance();
endif;
