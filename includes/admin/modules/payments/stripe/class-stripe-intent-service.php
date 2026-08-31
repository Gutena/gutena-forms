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
		 * PaymentIntent ID supplied outside of $_POST (failed-attempt REST logging).
		 *
		 * @var string
		 */
		private static $payment_intent_override = '';

		/**
		 * Optional payment failure message for REST logging.
		 *
		 * @var string
		 */
		private static $payment_error_override = '';

		/**
		 * Field values supplied during REST payment logging.
		 *
		 * @var array<string, mixed>
		 */
		private static $payment_field_values = array();

		/**
		 * Cached payment context from the current validated submission.
		 *
		 * @var array<string, mixed>
		 */
		private static $validated_payment_context = array();

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
		 * Extract the Gutena Forms frontend nonce from a REST request.
		 *
		 * @param WP_REST_Request $request Request.
		 * @return string
		 */
		public static function get_form_nonce_from_request( $request ) {
			$nonce = $request->get_header( 'X-Gutena-Nonce' );

			if ( empty( $nonce ) && ! empty( $_SERVER['HTTP_X_GUTENA_NONCE'] ) ) {
				$nonce = wp_unslash( $_SERVER['HTTP_X_GUTENA_NONCE'] );
			}

			if ( empty( $nonce ) ) {
				$nonce = $request->get_param( 'nonce' );
			}

			if ( empty( $nonce ) ) {
				$json = $request->get_json_params();
				if ( is_array( $json ) && ! empty( $json['nonce'] ) ) {
					$nonce = $json['nonce'];
				}
			}

			return is_string( $nonce ) ? sanitize_text_field( wp_unslash( $nonce ) ) : '';
		}

		/**
		 * Verify frontend form nonce for public payment REST routes.
		 *
		 * @param WP_REST_Request $request Request.
		 * @return true|WP_Error
		 */
		public static function ensure_form_nonce( $request ) {
			$nonce = self::get_form_nonce_from_request( $request );

			if ( empty( $nonce ) ) {
				return new WP_Error(
					'gutena_forms_missing_nonce',
					__( 'Payment security token is missing. Please refresh the page and try again.', 'gutena-forms' ),
					array( 'status' => 403 )
				);
			}

			if ( ! wp_verify_nonce( $nonce, 'gutena_Forms' ) ) {
				return new WP_Error(
					'gutena_forms_invalid_nonce',
					__( 'Payment security token expired. Please refresh the page and try again.', 'gutena-forms' ),
					array( 'status' => 403 )
				);
			}

			return true;
		}

		/**
		 * Format nonce validation errors for frontend Stripe requests.
		 *
		 * @param WP_Error $error Nonce error.
		 * @return WP_REST_Response
		 */
		private static function rest_nonce_error_response( $error ) {
			$status = 403;
			$data   = $error->get_error_data();
			if ( is_array( $data ) && ! empty( $data['status'] ) ) {
				$status = absint( $data['status'] );
			}

			$response = rest_ensure_response(
				array(
					'success' => false,
					'message' => $error->get_error_message(),
					'code'    => $error->get_error_code(),
				)
			);
			$response->set_status( $status );

			return $response;
		}

		/**
		 * Public Stripe config for a form (no secrets).
		 *
		 * @param WP_REST_Request $request Request.
		 * @return WP_REST_Response
		 */
		public function rest_get_public_config( $request ) {
			$nonce_check = self::ensure_form_nonce( $request );
			if ( is_wp_error( $nonce_check ) ) {
				return self::rest_nonce_error_response( $nonce_check );
			}

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
			$nonce_check = self::ensure_form_nonce( $request );
			if ( is_wp_error( $nonce_check ) ) {
				return self::rest_nonce_error_response( $nonce_check );
			}

			$form_id         = sanitize_key( (string) $request->get_param( 'form_id' ) );
			$stripe_field_id = sanitize_key( (string) $request->get_param( 'stripe_field_id' ) );
			$field_values    = $request->get_param( 'field_values' );

			if ( ! is_array( $field_values ) ) {
				$field_values = array();
			}

			$stripe_field_override = $request->get_param( 'stripe_field' );
			if ( ! is_array( $stripe_field_override ) ) {
				$stripe_field_override = null;
			}

			$result = $this->create_payment_intent( $form_id, $field_values, $stripe_field_id, $stripe_field_override );

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
		 * Log a payment attempt (failed or incomplete) as an entry + payment record.
		 *
		 * @param WP_REST_Request $request Request.
		 * @return WP_REST_Response
		 */
		public function rest_log_payment_attempt( $request ) {
			$nonce_check = self::ensure_form_nonce( $request );
			if ( is_wp_error( $nonce_check ) ) {
				return self::rest_nonce_error_response( $nonce_check );
			}

			$form_id               = sanitize_key( (string) $request->get_param( 'form_id' ) );
			$stripe_field_id       = sanitize_key( (string) $request->get_param( 'stripe_field_id' ) );
			$payment_intent_id     = sanitize_text_field( (string) $request->get_param( 'payment_intent_id' ) );
			$field_values          = $request->get_param( 'field_values' );
			$error_message         = sanitize_text_field( (string) $request->get_param( 'error_message' ) );
			$stripe_field_override = $request->get_param( 'stripe_field' );

			if ( ! is_array( $field_values ) ) {
				$field_values = array();
			}

			if ( ! is_array( $stripe_field_override ) ) {
				$stripe_field_override = null;
			}

			$result = $this->log_payment_attempt(
				$form_id,
				$field_values,
				$stripe_field_id,
				$stripe_field_override,
				$payment_intent_id,
				$error_message
			);

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
					'success'  => true,
					'entry_id' => absint( $result['entry_id'] ?? 0 ),
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
		 * @param string $form_id               Form ID.
		 * @param array  $field_values          Submitted field values keyed by nameAttr.
		 * @param string $stripe_field_id       Stripe field nameAttr.
		 * @param array|null $stripe_field_override Field config sent from rendered payment block.
		 * @return array|WP_Error
		 */
		public function create_payment_intent( $form_id, $field_values, $stripe_field_id = '', $stripe_field_override = null ) {
			$schema = $this->get_form_schema( $form_id );
			if ( empty( $schema ) ) {
				return new WP_Error( 'invalid_form', __( 'Form not found.', 'gutena-forms' ) );
			}

			$stripe_field = $this->find_stripe_field( $schema, $stripe_field_id );
			if ( empty( $stripe_field ) && is_array( $stripe_field_override ) ) {
				$stripe_field = $this->normalize_stripe_field_config( $stripe_field_override, $stripe_field_id );
			}

			if ( empty( $stripe_field ) ) {
				return new WP_Error( 'stripe_field_missing', __( 'Payment field is not configured for this form.', 'gutena-forms' ) );
			}

			$payment_type = sanitize_key( $stripe_field['paymentType'] ?? 'one_time' );
			if ( 'subscription' === $payment_type ) {
				if ( ! is_gutena_forms_pro() ) {
					return new WP_Error(
						'subscription_pro_required',
						__( 'Subscription payments require Gutena Forms Pro.', 'gutena-forms' )
					);
				}

				return $this->create_subscription_payment_intent(
					$form_id,
					$stripe_field,
					$field_values,
					$stripe_field_id,
					$stripe_field_override
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

			$stripe_field   = $this->enrich_stripe_field_customer_mapping( $stripe_field );
			$customer       = $this->resolve_customer_details( $stripe_field, $field_values, null, null, 0 );
			$customer_email = $customer['customer_email'];
			$customer_name  = $customer['customer_name'];

			$intent_body = array(
				'amount'                    => $amount_cents,
				'currency'                  => strtolower( $currency ),
				'payment_method_types[]'    => 'card',
				'metadata[form_id]'         => $form_id,
				'metadata[stripe_field_id]' => $field_id,
				'metadata[gutena_forms]'    => '1',
			);

			if ( $customer_email ) {
				$intent_body['receipt_email']             = $customer_email;
				$intent_body['metadata[customer_email]'] = $customer_email;
			}

			if ( $customer_name ) {
				$intent_body['metadata[customer_name]'] = $customer_name;
			}

			$response = wp_remote_post(
				'https://api.stripe.com/v1/payment_intents',
				array(
					'timeout' => 20,
					'headers' => array(
						'Authorization' => 'Bearer ' . $credentials['access_token'],
					),
					'body'    => $intent_body,
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

			$intent_id    = $this->resolve_payment_intent_id( '' );
			$stripe_field = $this->find_stripe_field( $schema );
			if ( empty( $stripe_field ) ) {
				$stripe_field = $this->get_stripe_field_from_post( $schema );
			}

			if ( empty( $stripe_field ) && empty( $intent_id ) ) {
				return true;
			}

			if ( empty( $stripe_field ) ) {
				$stripe_field = $this->get_stripe_field_from_post( array( 'form_fields' => array() ) );
			}

			if ( empty( $stripe_field ) ) {
				$stripe_field = $this->build_fallback_stripe_field();
			}

			$payment_stripe = $this->resolve_form_payment_stripe( $form_id );
			if ( empty( $payment_stripe['connected'] ) ) {
				return new WP_Error( 'stripe_not_connected', __( 'Payment could not be processed. Stripe is not connected.', 'gutena-forms' ) );
			}

			$field_id = ! empty( $stripe_field['nameAttr'] ) ? sanitize_key( $stripe_field['nameAttr'] ) : 'stripe_payment';
			if ( empty( $intent_id ) ) {
				$intent_id = $this->resolve_payment_intent_id( $field_id );
			}

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

			$stripe_field            = $this->enrich_stripe_field_customer_mapping( $stripe_field );
			$field_values            = $this->get_posted_field_values();
			$currency                = sanitize_text_field( $payment_stripe['currency'] ?? 'USD' );
			$skip_amount_validation  = empty( $stripe_field['fixedAmount'] ) && 'fixed' === sanitize_key( $stripe_field['amountType'] ?? 'fixed' ) && empty( $stripe_field['variableAmountField'] );
			$expected                = $skip_amount_validation
				? absint( $intent['amount'] ?? 0 )
				: $this->calculate_amount_cents( $stripe_field, $field_values, $currency );

			if ( is_wp_error( $expected ) ) {
				return $expected;
			}

			$received_amount = absint( $intent['amount'] ?? 0 );
			if ( ! $skip_amount_validation && $received_amount !== $expected ) {
				return new WP_Error( 'payment_amount_mismatch', __( 'Payment amount does not match the form total.', 'gutena-forms' ) );
			}

			$received_currency = strtoupper( sanitize_text_field( $intent['currency'] ?? '' ) );
			if ( $received_currency && $received_currency !== strtoupper( $currency ) ) {
				return new WP_Error( 'payment_currency_mismatch', __( 'Payment currency does not match the form settings.', 'gutena-forms' ) );
			}

			self::$validated_payment_context = array(
				'form_id'      => $form_id,
				'stripe_field' => $stripe_field,
				'intent_id'    => $intent_id,
				'intent'       => $intent,
			);

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
			$this->save_payment_for_entry( $entry_id, $form_data, $block_form_id, $field_schema );
		}

		/**
		 * Persist payment details for a saved entry when a PaymentIntent was submitted.
		 *
		 * @param int    $entry_id       Entry ID.
		 * @param array  $form_data      Submitted form data.
		 * @param string $block_form_id  Block form ID.
		 * @param array  $field_schema   Field schema.
		 * @return bool
		 */
		public function save_payment_for_entry( $entry_id, $form_data, $block_form_id, $field_schema ) {
			if ( ! class_exists( 'Gutena_Forms_Entry_Payment' ) || ! is_array( $field_schema ) ) {
				return false;
			}

			$entry_id = absint( $entry_id );
			if ( ! $entry_id ) {
				return false;
			}

			$existing_payment = Gutena_Forms_Entry_Payment::get_instance()->get_by_entry_id( $entry_id );
			if ( is_array( $existing_payment ) && ! empty( $existing_payment['transaction_id'] ) ) {
				return true;
			}

			$intent_id = $this->resolve_payment_intent_id( '' );
			if ( empty( $intent_id ) ) {
				return false;
			}

			$stripe_field = $this->resolve_stripe_field_for_entry( $block_form_id, $field_schema );
			if ( empty( $stripe_field ) ) {
				$stripe_field = $this->get_stripe_field_from_post( array( 'form_fields' => array() ) );
			}

			if ( empty( $stripe_field ) ) {
				$stripe_field = $this->build_fallback_stripe_field();
			}

			if ( empty( $stripe_field ) ) {
				return false;
			}

			$stripe_field = $this->enrich_stripe_field_customer_mapping( $stripe_field );

			$field_id = ! empty( $stripe_field['nameAttr'] ) ? sanitize_key( $stripe_field['nameAttr'] ) : 'stripe_payment';
			$intent   = $this->resolve_payment_intent_for_entry( $field_id, $block_form_id );
			if ( empty( $intent ) || ! is_array( $intent ) ) {
				return false;
			}

			$saved = $this->persist_entry_payment(
				$entry_id,
				$block_form_id,
				$field_schema,
				$stripe_field,
				$intent,
				self::$payment_error_override,
				$this->get_request_field_values(),
				is_array( $form_data ) ? $form_data : null
			);

			self::$validated_payment_context = array();

			return $saved;
		}

		/**
		 * Resolve Stripe field config for entry payment persistence.
		 *
		 * @param string $block_form_id Block form ID.
		 * @param array  $field_schema  Entry field schema.
		 * @return array|null
		 */
		private function resolve_stripe_field_for_entry( $block_form_id, $field_schema ) {
			if (
				! empty( self::$validated_payment_context['stripe_field'] )
				&& is_array( self::$validated_payment_context['stripe_field'] )
				&& ( empty( self::$validated_payment_context['form_id'] ) || self::$validated_payment_context['form_id'] === $block_form_id )
			) {
				return self::$validated_payment_context['stripe_field'];
			}

			$schema = array(
				'form_fields' => $this->filter_entry_field_schema( $field_schema ),
			);

			$stripe_field = $this->find_stripe_field( $schema );
			if ( ! empty( $stripe_field ) ) {
				return $stripe_field;
			}

			$full_schema = $this->get_form_schema( $block_form_id );
			if ( ! empty( $full_schema ) ) {
				$stripe_field = $this->find_stripe_field( $full_schema );
				if ( ! empty( $stripe_field ) ) {
					return $stripe_field;
				}

				$stripe_field = $this->get_stripe_field_from_post( $full_schema );
				if ( ! empty( $stripe_field ) ) {
					return $stripe_field;
				}
			}

			return $this->get_stripe_field_from_post( array( 'form_fields' => array() ) );
		}

		/**
		 * Resolve PaymentIntent payload for entry payment persistence.
		 *
		 * @param string $field_id      Stripe field nameAttr.
		 * @param string $block_form_id Block form ID.
		 * @return array|null
		 */
		private function resolve_payment_intent_for_entry( $field_id, $block_form_id ) {
			if (
				! empty( self::$validated_payment_context['intent'] )
				&& is_array( self::$validated_payment_context['intent'] )
				&& ( empty( self::$validated_payment_context['form_id'] ) || self::$validated_payment_context['form_id'] === $block_form_id )
			) {
				return self::$validated_payment_context['intent'];
			}

			$intent_id = $this->resolve_payment_intent_id( $field_id );
			if ( empty( $intent_id ) ) {
				return null;
			}

			$intent = $this->retrieve_payment_intent( $intent_id );
			if ( is_wp_error( $intent ) ) {
				return null;
			}

			return $intent;
		}

		/**
		 * Create an entry and failed payment log when card confirmation fails.
		 *
		 * @param string     $form_id               Block form ID.
		 * @param array      $field_values          Posted field values.
		 * @param string     $stripe_field_id       Stripe field nameAttr.
		 * @param array|null $stripe_field_override Stripe field config from DOM.
		 * @param string     $payment_intent_id     PaymentIntent ID.
		 * @param string     $error_message         Stripe/client error message.
		 * @return array|WP_Error
		 */
		public function log_payment_attempt( $form_id, $field_values, $stripe_field_id, $stripe_field_override, $payment_intent_id, $error_message = '' ) {
			if ( empty( $payment_intent_id ) ) {
				return new WP_Error( 'missing_intent', __( 'Payment reference is missing.', 'gutena-forms' ) );
			}

			if ( ! class_exists( 'Gutena_Forms_Manage_Store' ) || ! class_exists( 'Gutena_Forms_Entry_Payment' ) ) {
				return new WP_Error( 'storage_unavailable', __( 'Payment logging is unavailable.', 'gutena-forms' ) );
			}

			$schema = $this->get_form_schema( $form_id );
			if ( empty( $schema['form_fields'] ) || ! is_array( $schema['form_fields'] ) ) {
				return new WP_Error( 'invalid_form', __( 'Form not found.', 'gutena-forms' ) );
			}

			$stripe_field = $this->find_stripe_field( $schema, $stripe_field_id );
			if ( empty( $stripe_field ) && is_array( $stripe_field_override ) ) {
				$stripe_field = $this->normalize_stripe_field_config( $stripe_field_override, $stripe_field_id );
			}

			if ( empty( $stripe_field ) ) {
				return new WP_Error( 'stripe_field_missing', __( 'Payment field is not configured for this form.', 'gutena-forms' ) );
			}

			$intent = $this->retrieve_payment_intent( $payment_intent_id );
			if ( is_wp_error( $intent ) ) {
				return $intent;
			}

			$form_data = $this->build_form_data_from_field_values( $field_values, $schema['form_fields'] );
			if ( empty( $form_data ) ) {
				return new WP_Error( 'missing_form_data', __( 'Unable to save the submission data.', 'gutena-forms' ) );
			}

			self::$payment_intent_override = sanitize_text_field( $payment_intent_id );
			self::$payment_error_override  = sanitize_text_field( $error_message );
			self::$payment_field_values    = is_array( $field_values ) ? $field_values : array();

			$saved = Gutena_Forms_Manage_Store::get_instance()->save_form_entry(
				$form_data,
				$form_id,
				$schema['form_fields']
			);

			self::$payment_intent_override = '';
			self::$payment_error_override  = '';
			self::$payment_field_values    = array();

			if ( ! $saved ) {
				return new WP_Error( 'entry_save_failed', __( 'Unable to save the submission entry.', 'gutena-forms' ) );
			}

			$entry_id = $this->get_latest_entry_id_for_form( $form_id );
			if ( ! $entry_id ) {
				return new WP_Error( 'entry_missing', __( 'Entry was not created.', 'gutena-forms' ) );
			}

			return array(
				'entry_id' => $entry_id,
			);
		}

		/**
		 * Save payment row + logs for an entry.
		 *
		 * @param int    $entry_id      Entry ID.
		 * @param string $block_form_id Block form ID.
		 * @param array  $field_schema  Field schema (may include form_id).
		 * @param array  $stripe_field  Stripe field config.
		 * @param array  $intent        Stripe PaymentIntent payload.
		 * @param array  $intent        Stripe PaymentIntent payload.
		 * @param string $error_message Optional failure message.
		 * @param array  $field_values  Optional field values when $_POST is unavailable.
		 * @param array  $form_data     Optional submitted entry data keyed by nameAttr.
		 * @return bool
		 */
		private function persist_entry_payment( $entry_id, $block_form_id, $field_schema, $stripe_field, $intent, $error_message = '', $field_values = null, $form_data = null ) {
			if ( ! class_exists( 'Gutena_Forms_Entry_Payment' ) || ! $entry_id || ! is_array( $intent ) ) {
				return false;
			}

			if ( '' === $error_message && ! empty( self::$payment_error_override ) ) {
				$error_message = self::$payment_error_override;
			}

			$payment = $this->build_payment_payload(
				$entry_id,
				$block_form_id,
				$field_schema,
				$stripe_field,
				$intent,
				$error_message,
				$field_values,
				$form_data
			);

			$saved = Gutena_Forms_Entry_Payment::get_instance()->save_for_entry( $entry_id, $payment );

			if ( $saved && ! empty( $intent['id'] ) ) {
				$this->attach_entry_to_payment_intent(
					sanitize_text_field( $intent['id'] ),
					$entry_id,
					$block_form_id
				);
			}

			return $saved;
		}

		/**
		 * Build payment payload for entry storage.
		 *
		 * @param int    $entry_id      Entry ID.
		 * @param string $block_form_id Block form ID.
		 * @param array  $field_schema  Field schema.
		 * @param array  $stripe_field  Stripe field config.
		 * @param array  $intent        Stripe PaymentIntent payload.
		 * @param string $error_message Optional failure message.
		 * @param array  $field_values  Optional field values when $_POST is unavailable.
		 * @param array  $form_data     Optional submitted entry data keyed by nameAttr.
		 * @return array
		 */
		private function build_payment_payload( $entry_id, $block_form_id, $field_schema, $stripe_field, $intent, $error_message = '', $field_values = null, $form_data = null ) {
			$entry_id       = absint( $entry_id );
			$intent_id      = sanitize_text_field( $intent['id'] ?? '' );
			$payment_stripe = $this->resolve_form_payment_stripe( $block_form_id );
			$currency       = sanitize_text_field( $payment_stripe['currency'] ?? 'USD' );
			$mode           = sanitize_key( $payment_stripe['payment_mode'] ?? 'test' );
			$form_post_id   = isset( $field_schema['form_id'] ) ? absint( $field_schema['form_id'] ) : 0;
			$form_name      = '';

			if ( ! $form_post_id && $entry_id && class_exists( 'Gutena_Forms_Entries_Model' ) ) {
				$form_post_id = Gutena_Forms_Entries_Model::get_instance()->get_form_id_by_entry_id( $entry_id );
			}

			if ( $form_post_id && class_exists( 'Gutena_Forms_Forms_Model' ) ) {
				$form_name = Gutena_Forms_Forms_Model::get_instance()->get_name_by_id( $form_post_id );
			}

			$stripe_field = $this->enrich_stripe_field_customer_mapping( $stripe_field );
			$customer       = $this->resolve_customer_details(
				$stripe_field,
				is_array( $field_values ) ? $field_values : $this->get_request_field_values(),
				$form_data,
				$intent,
				$entry_id
			);
			$customer_name  = $customer['customer_name'];
			$customer_email = $customer['customer_email'];
			$amount_cents   = absint( $intent['amount'] ?? 0 );
			$status         = $this->normalize_payment_status( $intent['status'] ?? 'pending', $error_message );
			$dashboard_url  = $this->get_stripe_dashboard_url( $intent_id, $mode );
			$now            = gmdate( 'Y-m-d H:i:s' );

			$logs = array(
				array(
					'event'          => 'payment_verification',
					'transaction_id' => $intent_id,
					'gateway'        => 'stripe',
					'amount'         => Gutena_Forms_Entry_Payment::format_amount( $amount_cents, $currency ),
					'status'         => Gutena_Forms_Entry_Payment::status_label( $status ),
					'user_id'        => 0,
					'mode'           => $mode,
					'created_at'     => $now,
				),
			);

			if ( $error_message && 'failed' === $status ) {
				$logs[] = array(
					'event'          => 'payment_failed',
					'transaction_id' => $intent_id,
					'gateway'        => 'stripe',
					'amount'         => Gutena_Forms_Entry_Payment::format_amount( $amount_cents, $currency ),
					'status'         => Gutena_Forms_Entry_Payment::status_label( 'failed' ),
					'user_id'        => 0,
					'mode'           => $mode,
					'created_at'     => $now,
					'message'        => sanitize_text_field( $error_message ),
				);
			}

			$payment_type = $this->resolve_payment_type( $stripe_field, $intent );

			return array(
				'gateway'              => 'stripe',
				'gateway_label'        => 'Stripe',
				'payment_id'           => $intent_id,
				'payment_mode'         => $mode,
				'payment_method'       => 'Stripe',
				'payment_type'         => $payment_type,
				'subscription_id'      => sanitize_text_field( $intent['metadata']['subscription_id'] ?? '' ),
				'subscription_plan_name' => sanitize_text_field( $stripe_field['subscriptionPlanName'] ?? '' ),
				'transaction_id'       => $intent_id,
				'amount'               => $amount_cents,
				'currency'             => strtoupper( sanitize_text_field( $intent['currency'] ?? $currency ) ),
				'status'               => $status,
				'customer_name'        => sanitize_text_field( $customer_name ),
				'customer_email'       => sanitize_email( $customer_email ),
				'transaction_date'     => $now,
				'received_on'          => $now,
				'stripe_dashboard_url' => esc_url_raw( $dashboard_url ),
				'form_id'              => $form_post_id,
				'form_name'            => sanitize_text_field( $form_name ),
				'logs'                 => $logs,
			);
		}

		/**
		 * Normalize Stripe intent status for Gutena payment records.
		 *
		 * @param string $stripe_status Stripe PaymentIntent status.
		 * @param string $error_message Optional client error message.
		 * @return string
		 */
		private function normalize_payment_status( $stripe_status, $error_message = '' ) {
			$stripe_status = sanitize_text_field( $stripe_status );

			if ( in_array( $stripe_status, array( 'succeeded', 'processing' ), true ) ) {
				return $stripe_status;
			}

			if ( $error_message || in_array( $stripe_status, array( 'requires_payment_method', 'requires_action', 'canceled' ), true ) ) {
				return 'failed';
			}

			return $stripe_status ? $stripe_status : 'pending';
		}

		/**
		 * Attach entry metadata to a PaymentIntent for webhook correlation.
		 *
		 * @param string $intent_id     PaymentIntent ID.
		 * @param int    $entry_id      Entry ID.
		 * @param string $block_form_id Block form ID.
		 * @return void
		 */
		private function attach_entry_to_payment_intent( $intent_id, $entry_id, $block_form_id ) {
			$credentials = $this->get_stripe_credentials();
			if ( empty( $credentials['access_token'] ) || empty( $intent_id ) || ! $entry_id ) {
				return;
			}

			wp_remote_post(
				'https://api.stripe.com/v1/payment_intents/' . rawurlencode( $intent_id ),
				array(
					'timeout' => 15,
					'headers' => array(
						'Authorization' => 'Bearer ' . $credentials['access_token'],
					),
					'body'    => array(
						'metadata[entry_id]' => (string) absint( $entry_id ),
						'metadata[form_id]'  => sanitize_key( $block_form_id ),
					),
				)
			);
		}

		/**
		 * Tag subscription PaymentIntents so saved records can detect payment type.
		 *
		 * @param string $intent_id       PaymentIntent ID.
		 * @param string $subscription_id Stripe Subscription ID.
		 * @param string $form_id         Form ID.
		 * @param string $field_id        Stripe field nameAttr.
		 * @param string $plan_name       Subscription plan name.
		 * @return void
		 */
		private function update_payment_intent_subscription_metadata( $intent_id, $subscription_id, $form_id, $field_id, $plan_name ) {
			$credentials = $this->get_stripe_credentials();
			if ( empty( $credentials['access_token'] ) || empty( $intent_id ) ) {
				return;
			}

			wp_remote_post(
				'https://api.stripe.com/v1/payment_intents/' . rawurlencode( $intent_id ),
				array(
					'timeout' => 15,
					'headers' => array(
						'Authorization' => 'Bearer ' . $credentials['access_token'],
					),
					'body'    => array(
						'metadata[payment_type]'            => 'subscription',
						'metadata[subscription_id]'         => sanitize_text_field( $subscription_id ),
						'metadata[form_id]'                 => sanitize_key( $form_id ),
						'metadata[stripe_field_id]'         => sanitize_key( $field_id ),
						'metadata[subscription_plan_name]'  => sanitize_text_field( $plan_name ),
						'metadata[gutena_forms]'            => '1',
					),
				)
			);
		}

		/**
		 * Build entry payload from posted field values.
		 *
		 * @param array $field_values Posted values keyed by nameAttr.
		 * @param array $form_fields  Form field schema map.
		 * @return array
		 */
		private function build_form_data_from_field_values( $field_values, $form_fields ) {
			$form_data = array();

			if ( ! is_array( $field_values ) || ! is_array( $form_fields ) ) {
				return $form_data;
			}

			foreach ( $field_values as $name_attr => $raw_value ) {
				$name_attr = sanitize_key( $name_attr );

				if ( empty( $form_fields[ $name_attr ] ) || ! is_array( $form_fields[ $name_attr ] ) ) {
					continue;
				}

				$field = $form_fields[ $name_attr ];
				$type  = sanitize_key( $field['fieldType'] ?? '' );

				if ( 'stripe' === $type || 'gutena/stripe-field' === ( $field['blockName'] ?? '' ) ) {
					continue;
				}

				if ( is_array( $raw_value ) ) {
					$raw_value = Gutena_Forms_Helper::sanitize_array( wp_unslash( $raw_value ), true );
					$value     = implode( ', ', $raw_value );
				} else {
					$value = sanitize_textarea_field( wp_unslash( (string) $raw_value ) );
				}

				$field_name = sanitize_text_field(
					empty( $field['fieldName'] ) ? str_ireplace( '_', ' ', $name_attr ) : $field['fieldName']
				);

				$form_data[ $name_attr ] = array(
					'label'     => $field_name,
					'value'     => $value,
					'fieldType' => $type ? $type : 'text',
					'raw_value' => is_array( $raw_value ) ? $raw_value : wp_unslash( (string) $raw_value ),
				);
			}

			return $form_data;
		}

		/**
		 * Keep only field definitions from the entry schema payload.
		 *
		 * @param array $field_schema Field schema passed to entry hooks.
		 * @return array
		 */
		private function filter_entry_field_schema( $field_schema ) {
			$fields = array();

			foreach ( $field_schema as $key => $field ) {
				if ( ! is_array( $field ) ) {
					continue;
				}

				$fields[ $key ] = $field;
			}

			return $fields;
		}

		/**
		 * Resolve the latest entry ID for a block form ID.
		 *
		 * @param string $block_form_id Block form ID.
		 * @return int
		 */
		private function get_latest_entry_id_for_form( $block_form_id ) {
			global $wpdb;

			$store = Gutena_Forms_Store::get_instance();
			$block_form_id = sanitize_key( $block_form_id );

			if ( empty( $block_form_id ) ) {
				return 0;
			}

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$form_id = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT form_id FROM {$store->table_gutenaforms} WHERE block_form_id = %s AND published = 1 LIMIT 1",
					$block_form_id
				)
			);

			if ( empty( $form_id ) ) {
				return 0;
			}

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$entry_id = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT entry_id FROM {$store->table_gutenaforms_entries} WHERE form_id = %d ORDER BY entry_id DESC LIMIT 1",
					absint( $form_id )
				)
			);

			return absint( $entry_id );
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
			$payment_type = sanitize_key( $stripe_field['paymentType'] ?? 'one_time' );
			$amount_type  = sanitize_key( $stripe_field['amountType'] ?? 'fixed' );
			$minimum      = isset( $stripe_field['minimumAmount'] ) ? floatval( $stripe_field['minimumAmount'] ) : 0;
			$amount       = 0.0;

			if ( 'subscription' === $payment_type ) {
				$amount = isset( $stripe_field['fixedAmount'] ) ? floatval( $stripe_field['fixedAmount'] ) : 0;
			} elseif ( 'fixed' === $amount_type ) {
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

			$field = $this->locate_stripe_field( $schema['form_fields'], $stripe_field_id );

			if ( empty( $field ) && ! empty( $stripe_field_id ) ) {
				$field = $this->locate_stripe_field( $schema['form_fields'], '' );
			}

			return $field;
		}

		/**
		 * Locate a stripe field entry in a form_fields map.
		 *
		 * @param array  $form_fields     Form field schema map.
		 * @param string $stripe_field_id Optional nameAttr filter.
		 * @return array|null
		 */
		private function locate_stripe_field( $form_fields, $stripe_field_id = '' ) {
			foreach ( $form_fields as $name_attr => $field ) {
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

			$schema = class_exists( 'Gutena_Forms_Helper' )
				? Gutena_Forms_Helper::get_form_schema_record( $form_id )
				: gutena_forms_get_form_schema_option( $form_id, false );

			if ( ! is_array( $schema ) ) {
				return array();
			}

			$stored_fields = isset( $schema['form_fields'] ) && is_array( $schema['form_fields'] )
				? $schema['form_fields']
				: array();
			$block_markup  = ! empty( $schema['block_markup'] ) ? $schema['block_markup'] : '';

			if ( class_exists( 'Gutena_Forms_Helper' ) ) {
				$schema['form_fields'] = Gutena_Forms_Helper::resolve_form_fields_schema(
					$form_id,
					$stored_fields,
					$block_markup
				);
			}

			return $schema;
		}

		/**
		 * Normalize Stripe field config sent from the rendered payment block.
		 *
		 * @param array  $config          Raw field config.
		 * @param string $stripe_field_id Expected field nameAttr.
		 * @return array|null
		 */
		private function normalize_stripe_field_config( $config, $stripe_field_id = '' ) {
			if ( ! is_array( $config ) ) {
				return null;
			}

			$name_attr = ! empty( $config['nameAttr'] )
				? sanitize_key( $config['nameAttr'] )
				: sanitize_key( $stripe_field_id );

			if ( empty( $name_attr ) ) {
				$name_attr = 'stripe_payment';
			}

			if ( ! empty( $stripe_field_id ) && sanitize_key( $stripe_field_id ) !== $name_attr ) {
				$name_attr = sanitize_key( $stripe_field_id );
			}

			$amount_type = sanitize_key( $config['amountType'] ?? 'fixed' );
			if ( ! in_array( $amount_type, array( 'fixed', 'variable' ), true ) ) {
				$amount_type = 'fixed';
			}

			$field = array(
				'nameAttr'             => $name_attr,
				'blockName'            => 'gutena/stripe-field',
				'fieldType'            => 'stripe',
				'paymentType'          => sanitize_key( $config['paymentType'] ?? 'one_time' ),
				'amountType'           => $amount_type,
				'fixedAmount'          => isset( $config['fixedAmount'] ) ? floatval( $config['fixedAmount'] ) : 0,
				'variableAmountField'  => sanitize_key( $config['variableAmountField'] ?? '' ),
				'minimumAmount'        => isset( $config['minimumAmount'] ) ? floatval( $config['minimumAmount'] ) : 0,
				'customerEmailField'   => sanitize_key( $config['customerEmailField'] ?? '' ),
				'customerNameField'    => sanitize_key( $config['customerNameField'] ?? '' ),
				'subscriptionPlanName' => sanitize_text_field( $config['subscriptionPlanName'] ?? '' ),
				'billingInterval'      => sanitize_key( $config['billingInterval'] ?? 'monthly' ),
				'billingCycles'        => sanitize_key( $config['billingCycles'] ?? 'never' ),
				'customBillingCycles'  => isset( $config['customBillingCycles'] ) ? absint( $config['customBillingCycles'] ) : 1,
			);

			if ( 'subscription' === $field['paymentType'] ) {
				return $field;
			}

			if ( 'variable' === $amount_type && empty( $field['variableAmountField'] ) ) {
				return null;
			}

			return $field;
		}

		/**
		 * Read Stripe field config submitted with the form.
		 *
		 * @param array $schema Form schema.
		 * @return array|null
		 */
		private function get_stripe_field_from_post( $schema ) {
			unset( $schema );

			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			foreach ( $_POST as $key => $value ) {
				if ( ! is_string( $key ) || '_stripe_config' !== substr( $key, -15 ) ) {
					continue;
				}

				$decoded = json_decode( wp_unslash( $value ), true );
				if ( ! is_array( $decoded ) ) {
					continue;
				}

				$field_id = sanitize_key( str_replace( '_stripe_config', '', $key ) );
				return $this->normalize_stripe_field_config( $decoded, $field_id );
			}

			return null;
		}

		/**
		 * Build a minimal Stripe field definition from posted PaymentIntent keys.
		 *
		 * @return array|null
		 */
		private function build_fallback_stripe_field() {
			$from_post = $this->get_stripe_field_from_post( array( 'form_fields' => array() ) );
			if ( is_array( $from_post ) ) {
				return $from_post;
			}

			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			foreach ( $_POST as $key => $value ) {
				if ( ! is_string( $key ) || ! is_string( $value ) || '_payment_intent' !== substr( $key, -15 ) || '' === $value ) {
					continue;
				}

				$field_id = sanitize_key( str_replace( '_payment_intent', '', $key ) );
				if ( empty( $field_id ) ) {
					$field_id = 'stripe_payment';
				}

				return $this->normalize_stripe_field_config(
					array(
						'nameAttr'    => $field_id,
						'amountType'  => 'fixed',
						'fixedAmount' => 0,
						'paymentType' => 'one_time',
					),
					$field_id
				);
			}

			return null;
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
			return $this->resolve_payment_intent_id( $field_id );
		}

		/**
		 * Resolve PaymentIntent ID from POST or REST logging context.
		 *
		 * @param string $field_id Stripe field nameAttr.
		 * @return string
		 */
		private function resolve_payment_intent_id( $field_id ) {
			if ( ! empty( self::$payment_intent_override ) ) {
				return sanitize_text_field( self::$payment_intent_override );
			}

			if (
				! empty( self::$validated_payment_context['intent_id'] )
				&& ( empty( self::$validated_payment_context['form_id'] ) || ! empty( $field_id ) )
			) {
				return sanitize_text_field( self::$validated_payment_context['intent_id'] );
			}

			$key = sanitize_key( $field_id ) . '_payment_intent';
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			if ( isset( $_POST[ $key ] ) && '' !== $_POST[ $key ] ) {
				return sanitize_text_field( wp_unslash( $_POST[ $key ] ) );
			}

			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			foreach ( $_POST as $post_key => $post_value ) {
				if ( ! is_string( $post_key ) || ! is_string( $post_value ) ) {
					continue;
				}

				if ( '_payment_intent' === substr( $post_key, -15 ) && '' !== $post_value ) {
					return sanitize_text_field( wp_unslash( $post_value ) );
				}
			}

			return '';
		}

		/**
		 * @param string $name_attr Mapped field nameAttr.
		 * @return string
		 */
		private function resolve_mapped_field_value( $name_attr ) {
			return $this->resolve_customer_field_value( $name_attr );
		}

		/**
		 * Merge customer mapping fields from the rendered Stripe block config when missing.
		 *
		 * @param array $stripe_field Stripe field config.
		 * @return array
		 */
		private function enrich_stripe_field_customer_mapping( $stripe_field ) {
			if ( ! is_array( $stripe_field ) ) {
				return $stripe_field;
			}

			$from_post = $this->get_stripe_field_from_post( array( 'form_fields' => array() ) );
			if ( ! is_array( $from_post ) ) {
				return $stripe_field;
			}

			if ( empty( $stripe_field['customerEmailField'] ) && ! empty( $from_post['customerEmailField'] ) ) {
				$stripe_field['customerEmailField'] = sanitize_key( $from_post['customerEmailField'] );
			}

			if ( empty( $stripe_field['customerNameField'] ) && ! empty( $from_post['customerNameField'] ) ) {
				$stripe_field['customerNameField'] = sanitize_key( $from_post['customerNameField'] );
			}

			$post_priority_keys = array(
				'paymentType'          => 'sanitize_key',
				'subscriptionPlanName' => 'sanitize_text_field',
				'billingInterval'      => 'sanitize_key',
				'billingCycles'        => 'sanitize_key',
				'customBillingCycles'  => 'absint',
				'amountType'           => 'sanitize_key',
				'fixedAmount'          => 'floatval',
				'variableAmountField'  => 'sanitize_key',
				'minimumAmount'        => 'floatval',
			);

			foreach ( $post_priority_keys as $config_key => $sanitizer ) {
				if ( ! array_key_exists( $config_key, $from_post ) || '' === $from_post[ $config_key ] ) {
					continue;
				}

				$stripe_field[ $config_key ] = call_user_func( $sanitizer, $from_post[ $config_key ] );
			}

			return $stripe_field;
		}

		/**
		 * Resolve whether a payment is one-time or subscription.
		 *
		 * @param array      $stripe_field Stripe field config.
		 * @param array|null $intent       Stripe PaymentIntent payload.
		 * @return string
		 */
		private function resolve_payment_type( $stripe_field, $intent = null ) {
			$from_post = $this->get_stripe_field_from_post( array( 'form_fields' => array() ) );
			if ( is_array( $from_post ) && ! empty( $from_post['paymentType'] ) ) {
				$post_type = sanitize_key( $from_post['paymentType'] );
				if ( in_array( $post_type, array( 'one_time', 'subscription' ), true ) ) {
					return $post_type;
				}
			}

			if ( is_array( $stripe_field ) && ! empty( $stripe_field['paymentType'] ) ) {
				$field_type = sanitize_key( $stripe_field['paymentType'] );
				if ( in_array( $field_type, array( 'one_time', 'subscription' ), true ) ) {
					return $field_type;
				}
			}

			if ( is_array( $intent ) ) {
				if ( ! empty( $intent['metadata']['payment_type'] ) ) {
					$metadata_type = sanitize_key( $intent['metadata']['payment_type'] );
					if ( 'subscription' === $metadata_type ) {
						return 'subscription';
					}
				}

				if ( ! empty( $intent['metadata']['subscription_id'] ) || ! empty( $intent['invoice'] ) ) {
					return 'subscription';
				}
			}

			return 'one_time';
		}

		/**
		 * Resolve customer name and email from mapped form fields.
		 *
		 * @param array      $stripe_field Stripe field config.
		 * @param array      $field_values Posted field values keyed by nameAttr.
		 * @param array|null $form_data    Submitted entry data keyed by nameAttr.
		 * @param array|null $intent       Stripe PaymentIntent payload.
		 * @param int        $entry_id     Entry ID.
		 * @return array{customer_name:string,customer_email:string}
		 */
		private function resolve_customer_details( $stripe_field, $field_values, $form_data = null, $intent = null, $entry_id = 0 ) {
			$email_field = sanitize_key( $stripe_field['customerEmailField'] ?? '' );
			$name_field  = sanitize_key( $stripe_field['customerNameField'] ?? '' );

			$customer_email = $this->resolve_customer_field_value( $email_field, $field_values, $form_data, $entry_id );
			$customer_name  = $this->resolve_customer_field_value( $name_field, $field_values, $form_data, $entry_id );

			if ( is_array( $intent ) ) {
				if ( empty( $customer_email ) ) {
					$customer_email = sanitize_email( $intent['receipt_email'] ?? '' );
				}

				if ( empty( $customer_email ) && ! empty( $intent['metadata']['customer_email'] ) ) {
					$customer_email = sanitize_email( $intent['metadata']['customer_email'] );
				}

				if ( empty( $customer_name ) && ! empty( $intent['metadata']['customer_name'] ) ) {
					$customer_name = sanitize_text_field( $intent['metadata']['customer_name'] );
				}

				$billing_name = $this->extract_stripe_billing_detail( $intent, 'name' );
				if ( empty( $customer_name ) && '' !== $billing_name ) {
					$customer_name = $billing_name;
				}

				$billing_email = $this->extract_stripe_billing_detail( $intent, 'email' );
				if ( empty( $customer_email ) && '' !== $billing_email ) {
					$customer_email = sanitize_email( $billing_email );
				}
			}

			return array(
				'customer_name'  => sanitize_text_field( $customer_name ),
				'customer_email' => sanitize_email( $customer_email ),
			);
		}

		/**
		 * Extract billing detail from a Stripe PaymentIntent payload.
		 *
		 * @param array  $intent PaymentIntent payload.
		 * @param string $key    Billing detail key.
		 * @return string
		 */
		private function extract_stripe_billing_detail( $intent, $key ) {
			if ( ! is_array( $intent ) ) {
				return '';
			}

			$key = sanitize_key( $key );

			if ( ! empty( $intent['charges']['data'] ) && is_array( $intent['charges']['data'] ) ) {
				foreach ( $intent['charges']['data'] as $charge ) {
					if ( ! is_array( $charge ) || empty( $charge['billing_details'][ $key ] ) ) {
						continue;
					}

					return 'email' === $key
						? sanitize_email( $charge['billing_details'][ $key ] )
						: sanitize_text_field( $charge['billing_details'][ $key ] );
				}
			}

			if ( ! empty( $intent['payment_method'] ) && is_array( $intent['payment_method'] ) ) {
				$billing = $intent['payment_method']['billing_details'] ?? array();
				if ( ! empty( $billing[ $key ] ) ) {
					return 'email' === $key
						? sanitize_email( $billing[ $key ] )
						: sanitize_text_field( $billing[ $key ] );
				}
			}

			if ( ! empty( $intent['latest_charge'] ) && is_string( $intent['latest_charge'] ) ) {
				return '';
			}

			return '';
		}

		/**
		 * Resolve a mapped customer field from submitted data, POST, or saved entry values.
		 *
		 * @param string     $name_attr    Field nameAttr.
		 * @param array|null $field_values Optional values map.
		 * @param array|null $form_data    Optional submitted entry data keyed by nameAttr.
		 * @param int        $entry_id     Optional entry ID for DB lookup.
		 * @return string
		 */
		private function resolve_customer_field_value( $name_attr, $field_values = null, $form_data = null, $entry_id = 0 ) {
			$name_attr = sanitize_key( $name_attr );
			if ( empty( $name_attr ) ) {
				return '';
			}

			if ( is_array( $form_data ) && isset( $form_data[ $name_attr ] ) ) {
				$value = $this->extract_submitted_field_value( $form_data[ $name_attr ] );
				if ( '' !== $value ) {
					return $value;
				}
			}

			if ( is_array( $field_values ) && array_key_exists( $name_attr, $field_values ) ) {
				$value = $this->normalize_field_value( $field_values[ $name_attr ] );
				if ( '' !== $value ) {
					return $value;
				}
			}

			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			if ( isset( $_POST[ $name_attr ] ) ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Missing
				$value = $this->normalize_field_value( wp_unslash( $_POST[ $name_attr ] ) );
				if ( '' !== $value ) {
					return $value;
				}
			}

			if ( $entry_id ) {
				$value = $this->get_entry_field_value( $entry_id, $name_attr );
				if ( '' !== $value ) {
					return $value;
				}
			}

			return '';
		}

		/**
		 * @param mixed $data Submitted field payload.
		 * @return string
		 */
		private function extract_submitted_field_value( $data ) {
			if ( is_array( $data ) ) {
				if ( array_key_exists( 'raw_value', $data ) ) {
					return $this->normalize_field_value( $data['raw_value'] );
				}

				if ( array_key_exists( 'value', $data ) ) {
					return $this->normalize_field_value( $data['value'] );
				}
			}

			return $this->normalize_field_value( $data );
		}

		/**
		 * @param mixed $value Raw field value.
		 * @return string
		 */
		private function normalize_field_value( $value ) {
			if ( is_array( $value ) ) {
				$value = Gutena_Forms_Helper::sanitize_array( wp_unslash( $value ), true );
				return sanitize_text_field( implode( ' ', $value ) );
			}

			return sanitize_text_field( wp_unslash( (string) $value ) );
		}

		/**
		 * Read a saved entry field value by nameAttr.
		 *
		 * @param int    $entry_id  Entry ID.
		 * @param string $name_attr Field nameAttr.
		 * @return string
		 */
		private function get_entry_field_value( $entry_id, $name_attr ) {
			global $wpdb;

			$entry_id  = absint( $entry_id );
			$name_attr = sanitize_key( $name_attr );

			if ( ! $entry_id || empty( $name_attr ) || ! class_exists( 'Gutena_Forms_Store' ) ) {
				return '';
			}

			$store = Gutena_Forms_Store::get_instance();

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$value = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT field_value FROM {$store->table_gutenaforms_field_value} WHERE entry_id = %d AND field_name = %s LIMIT 1",
					$entry_id,
					$name_attr
				)
			);

			return $value ? sanitize_text_field( $value ) : '';
		}

		/**
		 * Field values available to payment persistence during the current request.
		 *
		 * @return array<string, mixed>
		 */
		private function get_request_field_values() {
			if ( ! empty( self::$payment_field_values ) && is_array( self::$payment_field_values ) ) {
				return self::$payment_field_values;
			}

			return $this->get_posted_field_values();
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

		/**
		 * Create a Stripe subscription and return the first invoice PaymentIntent.
		 *
		 * @param string     $form_id               Form ID.
		 * @param array      $stripe_field          Stripe field config.
		 * @param array      $field_values          Submitted field values.
		 * @param string     $stripe_field_id       Stripe field nameAttr.
		 * @param array|null $stripe_field_override Optional override config.
		 * @return array|WP_Error
		 */
		private function create_subscription_payment_intent( $form_id, $stripe_field, $field_values, $stripe_field_id = '', $stripe_field_override = null ) {
			unset( $stripe_field_id, $stripe_field_override );

			$payment_stripe = $this->resolve_form_payment_stripe( $form_id );
			$currency       = sanitize_text_field( $payment_stripe['currency'] ?? 'USD' );
			$amount_cents   = $this->calculate_amount_cents( $stripe_field, $field_values, $currency );

			if ( is_wp_error( $amount_cents ) ) {
				return $amount_cents;
			}

			$plan_name = sanitize_text_field( $stripe_field['subscriptionPlanName'] ?? '' );
			if ( '' === $plan_name ) {
				return new WP_Error( 'subscription_plan_required', __( 'Subscription plan name is required.', 'gutena-forms' ) );
			}

			$credentials = $this->get_stripe_credentials();
			if ( empty( $credentials['access_token'] ) ) {
				return new WP_Error( 'stripe_not_connected', __( 'Stripe is not connected.', 'gutena-forms' ) );
			}

			$field_id = ! empty( $stripe_field['nameAttr'] ) ? sanitize_key( $stripe_field['nameAttr'] ) : 'stripe_payment';

			$stripe_field   = $this->enrich_stripe_field_customer_mapping( $stripe_field );
			$customer       = $this->resolve_customer_details( $stripe_field, $field_values, null, null, 0 );
			$customer_email = $customer['customer_email'];
			$customer_name  = $customer['customer_name'];

			if ( empty( $customer_email ) ) {
				return new WP_Error( 'customer_email_required', __( 'Customer email is required for subscription payments.', 'gutena-forms' ) );
			}

			if ( empty( $customer_name ) ) {
				return new WP_Error( 'customer_name_required', __( 'Customer name is required for subscription payments.', 'gutena-forms' ) );
			}

			$billing_interval      = sanitize_key( $stripe_field['billingInterval'] ?? 'monthly' );
			$billing_cycles        = sanitize_key( $stripe_field['billingCycles'] ?? 'never' );
			$custom_billing_cycles = isset( $stripe_field['customBillingCycles'] ) ? absint( $stripe_field['customBillingCycles'] ) : 1;
			$recurring             = $this->map_billing_interval_to_stripe( $billing_interval );

			if ( is_wp_error( $recurring ) ) {
				return $recurring;
			}

			$stripe_customer = $this->stripe_api_post(
				'customers',
				array(
					'email'                     => $customer_email,
					'name'                      => $customer_name,
					'metadata[form_id]'         => $form_id,
					'metadata[stripe_field_id]' => $field_id,
					'metadata[gutena_forms]'    => '1',
				),
				$credentials['access_token']
			);

			if ( is_wp_error( $stripe_customer ) ) {
				return $stripe_customer;
			}

			$customer_id = sanitize_text_field( $stripe_customer['id'] ?? '' );
			if ( empty( $customer_id ) ) {
				return new WP_Error( 'stripe_customer_failed', __( 'Unable to create Stripe customer.', 'gutena-forms' ) );
			}

			$product = $this->stripe_api_post(
				'products',
				array(
					'name'                      => $plan_name,
					'metadata[form_id]'         => $form_id,
					'metadata[stripe_field_id]' => $field_id,
				),
				$credentials['access_token']
			);

			if ( is_wp_error( $product ) ) {
				return $product;
			}

			$product_id = sanitize_text_field( $product['id'] ?? '' );
			if ( empty( $product_id ) ) {
				return new WP_Error( 'stripe_product_failed', __( 'Unable to create subscription product.', 'gutena-forms' ) );
			}

			$price_body = array(
				'product'                    => $product_id,
				'unit_amount'                => $amount_cents,
				'currency'                   => strtolower( $currency ),
				'recurring[interval]'        => $recurring['interval'],
				'metadata[form_id]'          => $form_id,
				'metadata[stripe_field_id]'  => $field_id,
			);

			if ( ! empty( $recurring['interval_count'] ) && $recurring['interval_count'] > 1 ) {
				$price_body['recurring[interval_count]'] = $recurring['interval_count'];
			}

			$price = $this->stripe_api_post( 'prices', $price_body, $credentials['access_token'] );
			if ( is_wp_error( $price ) ) {
				return $price;
			}

			$price_id = sanitize_text_field( $price['id'] ?? '' );
			if ( empty( $price_id ) ) {
				return new WP_Error( 'stripe_price_failed', __( 'Unable to create subscription price.', 'gutena-forms' ) );
			}

			$subscription_body = array(
				'customer'                                        => $customer_id,
				'items[0][price]'                                 => $price_id,
				'collection_method'                               => 'charge_automatically',
				'payment_behavior'                                => 'default_incomplete',
				'payment_settings[save_default_payment_method]'   => 'on_subscription',
				'payment_settings[payment_method_types][0]'       => 'card',
				'metadata[form_id]'                               => $form_id,
				'metadata[stripe_field_id]'                       => $field_id,
				'metadata[gutena_forms]'                          => '1',
				'metadata[subscription_plan_name]'                => $plan_name,
				'metadata[billing_interval]'                      => $billing_interval,
				'metadata[billing_cycles]'                        => $billing_cycles,
				'metadata[customer_email]'                        => $customer_email,
				'metadata[customer_name]'                         => $customer_name,
			);
			$subscription_body['expand[0]'] = 'latest_invoice.confirmation_secret';
			$subscription_body['expand[1]'] = 'latest_invoice.payment_intent';

			$cancel_at = $this->calculate_subscription_cancel_at( $billing_interval, $billing_cycles, $custom_billing_cycles );
			if ( $cancel_at > 0 ) {
				$subscription_body['cancel_at'] = $cancel_at;
			}

			$subscription = $this->stripe_api_post( 'subscriptions', $subscription_body, $credentials['access_token'] );
			if ( is_wp_error( $subscription ) ) {
				return $subscription;
			}

			$subscription_id = sanitize_text_field( $subscription['id'] ?? '' );
			$payment_intent  = $this->resolve_subscription_payment_intent( $subscription, $credentials['access_token'] );

			if ( is_wp_error( $payment_intent ) ) {
				return $payment_intent;
			}

			$intent_id     = sanitize_text_field( $payment_intent['id'] ?? '' );
			$client_secret = sanitize_text_field( $payment_intent['client_secret'] ?? '' );

			if ( empty( $intent_id ) && ! empty( $client_secret ) ) {
				$intent_id = $this->extract_payment_intent_id_from_client_secret( $client_secret );
			}

			if ( empty( $intent_id ) || empty( $client_secret ) ) {
				return new WP_Error(
					'stripe_subscription_intent_missing',
					__( 'Unable to start subscription payment. Please try again.', 'gutena-forms' )
				);
			}

			$this->update_payment_intent_subscription_metadata(
				$intent_id,
				$subscription_id,
				$form_id,
				$field_id,
				$plan_name
			);

			$config = $this->get_public_config( $form_id );
			if ( is_wp_error( $config ) ) {
				return $config;
			}

			return array(
				'payment_intent_id' => $intent_id,
				'client_secret'     => $client_secret,
				'subscription_id'   => $subscription_id,
				'amount'            => $amount_cents,
				'currency'          => $currency,
				'publishable_key'   => $config['publishable_key'],
				'account_id'        => $config['account_id'],
				'payment_type'      => 'subscription',
			);
		}

		/**
		 * Map Gutena billing interval to Stripe recurring params.
		 *
		 * @param string $billing_interval Billing interval key.
		 * @return array{interval: string, interval_count: int}|WP_Error
		 */
		private function map_billing_interval_to_stripe( $billing_interval ) {
			switch ( sanitize_key( $billing_interval ) ) {
				case 'daily':
					return array(
						'interval'       => 'day',
						'interval_count' => 1,
					);
				case 'weekly':
					return array(
						'interval'       => 'week',
						'interval_count' => 1,
					);
				case 'monthly':
					return array(
						'interval'       => 'month',
						'interval_count' => 1,
					);
				case 'quarterly':
					return array(
						'interval'       => 'month',
						'interval_count' => 3,
					);
				case 'yearly':
					return array(
						'interval'       => 'year',
						'interval_count' => 1,
					);
			}

			return new WP_Error( 'invalid_billing_interval', __( 'Invalid subscription billing interval.', 'gutena-forms' ) );
		}

		/**
		 * Calculate subscription cancel_at timestamp for fixed billing cycles.
		 *
		 * @param string $billing_interval      Billing interval key.
		 * @param string $billing_cycles        Billing cycles key.
		 * @param int    $custom_billing_cycles Custom payment count.
		 * @return int Unix timestamp, or 0 for never.
		 */
		private function calculate_subscription_cancel_at( $billing_interval, $billing_cycles, $custom_billing_cycles ) {
			if ( 'never' === sanitize_key( $billing_cycles ) ) {
				return 0;
			}

			$payment_count = 0;
			if ( in_array( $billing_cycles, array( '2', '3', '4', '5' ), true ) ) {
				$payment_count = absint( $billing_cycles );
			} elseif ( 'custom' === $billing_cycles ) {
				$payment_count = max( 1, min( 100, absint( $custom_billing_cycles ) ) );
			}

			if ( $payment_count <= 0 ) {
				return 0;
			}

			$now = time();

			switch ( sanitize_key( $billing_interval ) ) {
				case 'daily':
					return $now + ( $payment_count * DAY_IN_SECONDS );
				case 'weekly':
					return $now + ( $payment_count * WEEK_IN_SECONDS );
				case 'monthly':
					return (int) strtotime( '+' . $payment_count . ' months', $now );
				case 'quarterly':
					return (int) strtotime( '+' . ( $payment_count * 3 ) . ' months', $now );
				case 'yearly':
					return (int) strtotime( '+' . $payment_count . ' years', $now );
			}

			return 0;
		}

		/**
		 * Resolve the first invoice PaymentIntent for a subscription.
		 *
		 * @param array  $subscription Stripe subscription payload.
		 * @param string $access_token Connected account access token.
		 * @return array|WP_Error
		 */
		private function resolve_subscription_payment_intent( $subscription, $access_token ) {
			if ( ! is_array( $subscription ) ) {
				return new WP_Error(
					'stripe_subscription_intent_missing',
					__( 'Unable to start subscription payment. Please try again.', 'gutena-forms' )
				);
			}

			$intent = $this->extract_payment_intent_from_subscription( $subscription );
			if ( $this->payment_intent_is_usable( $intent ) ) {
				return $intent;
			}

			$subscription_id = $this->resolve_stripe_resource_id( $subscription );
			if ( $subscription_id ) {
				$refetched = $this->stripe_api_get(
					'subscriptions/' . rawurlencode( $subscription_id ),
					array(
						'expand[0]' => 'latest_invoice.confirmation_secret',
						'expand[1]' => 'latest_invoice.payment_intent',
					),
					$access_token
				);

				if ( ! is_wp_error( $refetched ) ) {
					$intent = $this->extract_payment_intent_from_subscription( $refetched );
					if ( $this->payment_intent_is_usable( $intent ) ) {
						return $intent;
					}
					$subscription = $refetched;
				}
			}

			$invoice_id = $this->resolve_stripe_resource_id( $subscription['latest_invoice'] ?? '' );
			if ( $invoice_id ) {
				$invoice = $this->stripe_api_get(
					'invoices/' . rawurlencode( $invoice_id ),
					array(
						'expand[0]' => 'confirmation_secret',
						'expand[1]' => 'payment_intent',
					),
					$access_token
				);

				if ( ! is_wp_error( $invoice ) ) {
					$intent = $this->extract_payment_intent_from_invoice( $invoice );
					if ( $this->payment_intent_is_usable( $intent ) ) {
						return $intent;
					}

					$intent_id = $this->resolve_stripe_resource_id( $invoice['payment_intent'] ?? '' );
					if ( $intent_id ) {
						$retrieved = $this->retrieve_payment_intent_with_token( $intent_id, $access_token );
						if ( ! is_wp_error( $retrieved ) && $this->payment_intent_is_usable( $retrieved ) ) {
							return $retrieved;
						}
					}
				}
			}

			return new WP_Error(
				'stripe_subscription_intent_missing',
				__( 'Unable to start subscription payment. Please try again.', 'gutena-forms' )
			);
		}

		/**
		 * Extract PaymentIntent object from a subscription payload.
		 *
		 * @param array $subscription Stripe subscription payload.
		 * @return array
		 */
		private function extract_payment_intent_from_subscription( $subscription ) {
			if ( ! is_array( $subscription['latest_invoice'] ?? null ) ) {
				return array();
			}

			return $this->extract_payment_intent_from_invoice( $subscription['latest_invoice'] );
		}

		/**
		 * Extract PaymentIntent object from an invoice payload.
		 *
		 * @param array $invoice Stripe invoice payload.
		 * @return array
		 */
		private function extract_payment_intent_from_invoice( $invoice ) {
			if ( ! is_array( $invoice ) ) {
				return array();
			}

			// Stripe API 2025-03-31+ uses confirmation_secret on invoices for Payment Element flows.
			if ( is_array( $invoice['confirmation_secret'] ?? null ) ) {
				$confirmation  = $invoice['confirmation_secret'];
				$client_secret = sanitize_text_field( $confirmation['client_secret'] ?? '' );

				if ( '' !== $client_secret ) {
					$intent_id = sanitize_text_field( $confirmation['payment_intent'] ?? '' );
					if ( '' === $intent_id ) {
						$intent_id = $this->extract_payment_intent_id_from_client_secret( $client_secret );
					}

					return array(
						'id'            => $intent_id,
						'client_secret' => $client_secret,
					);
				}
			}

			$payment_intent = $invoice['payment_intent'] ?? null;
			if ( is_array( $payment_intent ) ) {
				return $this->normalize_payment_intent_payload( $payment_intent );
			}

			return array();
		}

		/**
		 * Ensure PaymentIntent payloads include both id and client_secret when possible.
		 *
		 * @param array $payment_intent Stripe PaymentIntent payload.
		 * @return array
		 */
		private function normalize_payment_intent_payload( $payment_intent ) {
			if ( ! is_array( $payment_intent ) ) {
				return array();
			}

			$client_secret = sanitize_text_field( $payment_intent['client_secret'] ?? '' );
			$intent_id     = sanitize_text_field( $payment_intent['id'] ?? '' );

			if ( '' === $intent_id && '' !== $client_secret ) {
				$intent_id = $this->extract_payment_intent_id_from_client_secret( $client_secret );
			}

			if ( '' === $client_secret && '' === $intent_id ) {
				return array();
			}

			return array(
				'id'            => $intent_id,
				'client_secret' => $client_secret,
			);
		}

		/**
		 * Parse a PaymentIntent ID from a Stripe client secret.
		 *
		 * @param string $client_secret Stripe client secret.
		 * @return string
		 */
		private function extract_payment_intent_id_from_client_secret( $client_secret ) {
			$client_secret = sanitize_text_field( $client_secret );

			if ( preg_match( '/^(pi_[A-Za-z0-9]+)_secret_/', $client_secret, $matches ) ) {
				return sanitize_text_field( $matches[1] );
			}

			return '';
		}

		/**
		 * @param array|string $resource Stripe object or ID.
		 * @return string
		 */
		private function resolve_stripe_resource_id( $resource ) {
			if ( is_string( $resource ) && '' !== $resource ) {
				return sanitize_text_field( $resource );
			}

			if ( is_array( $resource ) && ! empty( $resource['id'] ) ) {
				return sanitize_text_field( $resource['id'] );
			}

			return '';
		}

		/**
		 * @param array $intent PaymentIntent payload.
		 * @return bool
		 */
		private function payment_intent_is_usable( $intent ) {
			return is_array( $intent )
				&& ! empty( $intent['client_secret'] )
				&& ( ! empty( $intent['id'] ) || ! empty( $this->extract_payment_intent_id_from_client_secret( $intent['client_secret'] ?? '' ) ) );
		}

		/**
		 * Retrieve PaymentIntent using a specific access token.
		 *
		 * @param string $intent_id    PaymentIntent ID.
		 * @param string $access_token Connected account access token.
		 * @return array|WP_Error
		 */
		private function retrieve_payment_intent_with_token( $intent_id, $access_token ) {
			$intent_id = sanitize_text_field( $intent_id );
			if ( empty( $intent_id ) || empty( $access_token ) ) {
				return new WP_Error( 'missing_intent', __( 'Payment reference is missing.', 'gutena-forms' ) );
			}

			return $this->stripe_api_get( 'payment_intents/' . rawurlencode( $intent_id ), array(), $access_token );
		}

		/**
		 * GET from the Stripe API using a connected account access token.
		 *
		 * @param string $path         Stripe API resource path.
		 * @param array  $query        Query args.
		 * @param string $access_token Connected account access token.
		 * @return array|WP_Error
		 */
		private function stripe_api_get( $path, $query, $access_token ) {
			$path = ltrim( $path, '/' );
			$url  = 'https://api.stripe.com/v1/' . $path;

			if ( ! empty( $query ) && is_array( $query ) ) {
				$url .= '?' . http_build_query( $query, '', '&', PHP_QUERY_RFC3986 );
			}

			$response = wp_remote_get(
				$url,
				array(
					'timeout' => 20,
					'headers' => array(
						'Authorization' => 'Bearer ' . $access_token,
					),
				)
			);

			if ( is_wp_error( $response ) ) {
				return $response;
			}

			$code = (int) wp_remote_retrieve_response_code( $response );
			$data = json_decode( wp_remote_retrieve_body( $response ), true );

			if ( $code < 200 || $code >= 300 || ! is_array( $data ) ) {
				$message = is_array( $data ) && ! empty( $data['error']['message'] )
					? sanitize_text_field( $data['error']['message'] )
					: __( 'Stripe request failed. Please try again.', 'gutena-forms' );

				return new WP_Error( 'stripe_api_error', $message );
			}

			return $data;
		}

		/**
		 * POST to the Stripe API using a connected account access token.
		 *
		 * @param string $path         Stripe API resource path.
		 * @param array  $body         Request body.
		 * @param string $access_token Connected account access token.
		 * @return array|WP_Error
		 */
		private function stripe_api_post( $path, $body, $access_token ) {
			$path = ltrim( sanitize_text_field( $path ), '/' );

			$response = wp_remote_post(
				'https://api.stripe.com/v1/' . $path,
				array(
					'timeout' => 20,
					'headers' => array(
						'Authorization' => 'Bearer ' . $access_token,
					),
					'body'    => $body,
				)
			);

			if ( is_wp_error( $response ) ) {
				return $response;
			}

			$code = (int) wp_remote_retrieve_response_code( $response );
			$data = json_decode( wp_remote_retrieve_body( $response ), true );

			if ( $code < 200 || $code >= 300 || ! is_array( $data ) ) {
				$message = is_array( $data ) && ! empty( $data['error']['message'] )
					? sanitize_text_field( $data['error']['message'] )
					: __( 'Stripe request failed. Please try again.', 'gutena-forms' );

				return new WP_Error( 'stripe_api_error', $message );
			}

			return $data;
		}
	}

	Gutena_Forms_Stripe_Intent_Service::get_instance();
endif;
