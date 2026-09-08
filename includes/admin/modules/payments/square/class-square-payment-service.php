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

		/**
		 * Payment context stored during form validation to be saved when entry is created.
		 *
		 * @var array
		 */
		private static $current_payment_context = array();

		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		private function __construct() {
			add_action( 'rest_api_init', array( $this, 'register_webhook_route' ) );
		}

		/**
		 * Execute a Square API request with automatic token renewal on HTTP 401.
		 *
		 * @param string     $url          Full API endpoint URL.
		 * @param string     $method       HTTP method.
		 * @param array|null $body         Request body.
		 * @param string     $access_token OAuth access token.
		 * @param string     $payment_mode test|live.
		 * @return array|WP_Error
		 */
		public function square_api_request( $url, $method = 'GET', $body = null, $access_token = '', $payment_mode = 'test' ) {
			if ( empty( $access_token ) ) {
				$credentials  = $this->get_square_credentials( $payment_mode );
				$access_token = sanitize_text_field( $credentials['access_token'] ?? '' );
			}

			if ( empty( $access_token ) ) {
				return new WP_Error( 'square_no_token', __( 'Square access token is missing.', 'gutena-forms' ) );
			}

			$args = array(
				'method'  => strtoupper( $method ),
				'timeout' => 30,
				'headers' => array(
					'Authorization'  => 'Bearer ' . $access_token,
					'Square-Version' => self::SQUARE_API_VERSION,
					'Content-Type'   => 'application/json',
				),
			);

			if ( ! is_null( $body ) ) {
				$args['body'] = is_array( $body ) ? wp_json_encode( $body ) : $body;
			}

			$response = wp_remote_request( $url, $args );

			if ( is_wp_error( $response ) ) {
				return $response;
			}

			$code          = (int) wp_remote_retrieve_response_code( $response );
			$response_body = json_decode( wp_remote_retrieve_body( $response ), true );

			// Auto renew token on 401 and retry once
			if ( 401 === $code && class_exists( 'Gutena_Forms_Square_Connect' ) ) {
				$renewed = Gutena_Forms_Square_Connect::get_instance()->renew_access_token();
				if ( ! is_wp_error( $renewed ) && ! empty( $renewed['access_token'] ) ) {
					$access_token                      = sanitize_text_field( $renewed['access_token'] );
					$args['headers']['Authorization'] = 'Bearer ' . $access_token;
					$response                          = wp_remote_request( $url, $args );
					if ( ! is_wp_error( $response ) ) {
						$code          = (int) wp_remote_retrieve_response_code( $response );
						$response_body = json_decode( wp_remote_retrieve_body( $response ), true );
					}
				}
			}

			if ( $code < 200 || $code >= 300 ) {
				$error_msg = __( 'Square request failed.', 'gutena-forms' );
				if ( is_array( $response_body ) && ! empty( $response_body['errors'][0]['detail'] ) ) {
					$error_msg = sanitize_text_field( $response_body['errors'][0]['detail'] );
				} elseif ( is_array( $response_body ) && ! empty( $response_body['errors'][0]['code'] ) ) {
					$error_msg = sanitize_text_field( $response_body['errors'][0]['code'] );
				}

				return new WP_Error( 'square_api_error', $error_msg, array( 'status' => $code, 'body' => $response_body ) );
			}

			return is_array( $response_body ) ? $response_body : array();
		}

		/**
		 * Map form billing interval to Square subscription cadence.
		 *
		 * @param string $interval Billing interval key.
		 * @return string Square cadence string.
		 */
		public static function map_billing_interval_to_square_cadence( $interval ) {
			$map = array(
				'daily'          => 'DAILY',
				'weekly'         => 'WEEKLY',
				'biweekly'       => 'EVERY_TWO_WEEKS',
				'every_2_weeks'  => 'EVERY_TWO_WEEKS',
				'monthly'        => 'MONTHLY',
				'every_2_months' => 'EVERY_TWO_MONTHS',
				'quarterly'      => 'QUARTERLY',
				'every_3_months' => 'QUARTERLY',
				'every_4_months' => 'EVERY_FOUR_MONTHS',
				'every_6_months' => 'EVERY_SIX_MONTHS',
				'semi_annually'  => 'EVERY_SIX_MONTHS',
				'yearly'         => 'ANNUAL',
				'annual'         => 'ANNUAL',
				'every_2_years'  => 'EVERY_TWO_YEARS',
			);

			return $map[ sanitize_key( $interval ) ] ?? 'MONTHLY';
		}

		/**
		 * Create Customer, Card on File, Catalog Plan, and Subscription in Square.
		 *
		 * @param string $api_base         Square API base URL.
		 * @param string $access_token     Square access token.
		 * @param string $location_id      Square location ID.
		 * @param string $currency         Currency code.
		 * @param int    $amount_cents     Recurring amount in cents.
		 * @param string $payment_token    Card payment token nonce.
		 * @param array  $square_field     Square field config.
		 * @param array  $customer_details Customer details.
		 * @param string $form_name        Form display name.
		 * @param string $form_id          Form ID.
		 * @param string $payment_mode     test|live.
		 * @return array|WP_Error
		 */
		private function create_square_subscription( $api_base, $access_token, $location_id, $currency, $amount_cents, $payment_token, $square_field, $customer_details, $form_name, $form_id, $payment_mode = 'test' ) {
			$plan_name = sanitize_text_field( $square_field['subscriptionPlanName'] ?? '' );
			if ( empty( $plan_name ) ) {
				return new WP_Error( 'subscription_plan_required', __( 'Subscription plan name is required.', 'gutena-forms' ) );
			}

			$customer_email = sanitize_email( $customer_details['customer_email'] ?? '' );
			if ( empty( $customer_email ) ) {
				return new WP_Error( 'customer_email_required', __( 'Customer email is required for subscription payments.', 'gutena-forms' ) );
			}

			// 1. Create / Retrieve Square Customer
			$customer_name_parts = explode( ' ', trim( $customer_details['customer_name'] ?? '' ), 2 );
			$given_name          = sanitize_text_field( $customer_name_parts[0] ?? '' );
			$family_name         = sanitize_text_field( $customer_name_parts[1] ?? '' );

			$customer_body = array(
				'idempotency_key' => wp_generate_uuid4(),
				'given_name'      => $given_name ? $given_name : 'Customer',
				'email_address'   => $customer_email,
				'note'            => sprintf( 'Gutena Forms: %s (Form ID: %s)', sanitize_text_field( $form_name ), sanitize_text_field( $form_id ) ),
			);
			if ( ! empty( $family_name ) ) {
				$customer_body['family_name'] = $family_name;
			}

			$customer_response = $this->square_api_request(
				$api_base . '/v2/customers',
				'POST',
				$customer_body,
				$access_token,
				$payment_mode
			);

			if ( is_wp_error( $customer_response ) ) {
				return $customer_response;
			}

			$customer_id = sanitize_text_field( $customer_response['customer']['id'] ?? '' );
			if ( empty( $customer_id ) ) {
				return new WP_Error( 'square_customer_failed', __( 'Unable to create Square customer for subscription.', 'gutena-forms' ) );
			}

			// 2. Create Card on File for Customer
			$card_body = array(
				'idempotency_key' => wp_generate_uuid4(),
				'source_id'       => $payment_token,
				'card'            => array(
					'customer_id'     => $customer_id,
					'cardholder_name' => sanitize_text_field( $customer_details['customer_name'] ?? '' ),
				),
			);

			$card_response = $this->square_api_request(
				$api_base . '/v2/cards',
				'POST',
				$card_body,
				$access_token,
				$payment_mode
			);

			if ( is_wp_error( $card_response ) ) {
				return $card_response;
			}

			$card_id = sanitize_text_field( $card_response['card']['id'] ?? '' );
			if ( empty( $card_id ) ) {
				return new WP_Error( 'square_card_failed', __( 'Unable to save card on file for subscription.', 'gutena-forms' ) );
			}
			$card_details = $card_response['card'] ?? array();

			// 3. Create Subscription Plan in Catalog
			$billing_interval = sanitize_key( $square_field['billingInterval'] ?? 'monthly' );
			$cadence          = self::map_billing_interval_to_square_cadence( $billing_interval );
			$billing_cycles   = sanitize_key( $square_field['billingCycles'] ?? 'never' );
			$custom_cycles    = isset( $square_field['customBillingCycles'] ) ? absint( $square_field['customBillingCycles'] ) : 0;

			$phase = array(
				'cadence'               => $cadence,
				'recurring_price_money' => array(
					'amount'   => (int) $amount_cents,
					'currency' => $currency,
				),
			);
			if ( 'custom' === $billing_cycles && $custom_cycles > 0 ) {
				$phase['periods'] = $custom_cycles;
			}

			$plan_body = array(
				'idempotency_key' => wp_generate_uuid4(),
				'object'          => array(
					'type'                   => 'SUBSCRIPTION_PLAN',
					'id'                     => '#plan_' . wp_generate_uuid4(),
					'subscription_plan_data' => array(
						'name'   => sanitize_text_field( $plan_name ),
						'phases' => array( $phase ),
					),
				),
			);

			$plan_response = $this->square_api_request(
				$api_base . '/v2/catalog/object',
				'POST',
				$plan_body,
				$access_token,
				$payment_mode
			);

			if ( is_wp_error( $plan_response ) ) {
				return $plan_response;
			}

			$plan_variation_id = '';
			if ( ! empty( $plan_response['catalog_object']['subscription_plan_data']['subscription_plan_variations'][0]['id'] ) ) {
				$plan_variation_id = sanitize_text_field( $plan_response['catalog_object']['subscription_plan_data']['subscription_plan_variations'][0]['id'] );
			} elseif ( ! empty( $plan_response['catalog_object']['id'] ) ) {
				$plan_id = sanitize_text_field( $plan_response['catalog_object']['id'] );
				// Create explicit variation for this plan.
				$variation_body = array(
					'idempotency_key' => wp_generate_uuid4(),
					'object'          => array(
						'type'                             => 'SUBSCRIPTION_PLAN_VARIATION',
						'id'                               => '#variation_' . wp_generate_uuid4(),
						'subscription_plan_variation_data' => array(
							'name'                 => sanitize_text_field( $plan_name ),
							'phases'               => array( $phase ),
							'subscription_plan_id' => $plan_id,
						),
					),
				);
				$var_response = $this->square_api_request(
					$api_base . '/v2/catalog/object',
					'POST',
					$variation_body,
					$access_token,
					$payment_mode
				);
				if ( ! is_wp_error( $var_response ) && ! empty( $var_response['catalog_object']['id'] ) ) {
					$plan_variation_id = sanitize_text_field( $var_response['catalog_object']['id'] );
				} else {
					$plan_variation_id = $plan_id;
				}
			}

			if ( empty( $plan_variation_id ) ) {
				return new WP_Error( 'square_plan_failed', __( 'Unable to create subscription plan in Square.', 'gutena-forms' ) );
			}

			// 4. Create Subscription
			$subscription_body = array(
				'idempotency_key'   => wp_generate_uuid4(),
				'location_id'       => $location_id,
				'plan_variation_id' => $plan_variation_id,
				'customer_id'       => $customer_id,
				'card_id'           => $card_id,
			);

			$tz = wp_timezone_string();
			if ( ! empty( $tz ) && false === strpos( $tz, '+' ) && false === strpos( $tz, '-' ) ) {
				$subscription_body['timezone'] = $tz;
			}

			$sub_response = $this->square_api_request(
				$api_base . '/v2/subscriptions',
				'POST',
				$subscription_body,
				$access_token,
				$payment_mode
			);

			if ( is_wp_error( $sub_response ) ) {
				return $sub_response;
			}

			$subscription_obj = $sub_response['subscription'] ?? array();
			if ( empty( $subscription_obj['id'] ) ) {
				return new WP_Error( 'square_subscription_failed', __( 'Unable to create Square subscription.', 'gutena-forms' ) );
			}

			return array(
				'subscription'      => $subscription_obj,
				'customer'          => $customer_response['customer'] ?? array(),
				'card'              => $card_details,
				'plan_variation_id' => $plan_variation_id,
			);
		}

		/**
		 * Validate and process Square payment on form submission.
		 *
		 * @param string $form_id Form ID.
		 * @param array  $schema  Form schema.
		 * @return true|WP_Error
		 */
		public function validate_submission_payment( $form_id, $schema ) {
			$square_field = $this->find_square_field( $schema );
			if ( empty( $square_field ) ) {
				return true;
			}

			if ( ! gutena_forms_is_square_gateway_enabled() ) {
				return new WP_Error( 'square_disabled', __( 'Square payment gateway is currently disabled.', 'gutena-forms' ) );
			}

			$form_square = $this->resolve_form_payment_square( $form_id );
			if ( empty( $form_square['connected'] ) ) {
				return new WP_Error( 'square_not_connected', __( 'Square payment is not connected for this form.', 'gutena-forms' ) );
			}

			$field_id  = ! empty( $square_field['nameAttr'] ) ? sanitize_key( $square_field['nameAttr'] ) : 'square_payment';
			$token_key = $field_id . '_payment_token';

			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$payment_token = isset( $_POST[ $token_key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $token_key ] ) ) : '';
			if ( empty( $payment_token ) ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Missing
				foreach ( $_POST as $post_key => $post_val ) {
					if ( is_string( $post_key ) && '_payment_token' === substr( $post_key, -14 ) && ! empty( $post_val ) ) {
						$payment_token = sanitize_text_field( wp_unslash( $post_val ) );
						break;
					}
				}
			}

			if ( empty( $payment_token ) ) {
				return new WP_Error( 'missing_square_token', __( 'Please complete your card details to make payment.', 'gutena-forms' ) );
			}

			$payment_mode = in_array( $form_square['payment_mode'] ?? 'test', array( 'live', 'test' ), true ) ? $form_square['payment_mode'] : 'test';
			$credentials  = $this->get_square_credentials( $payment_mode );
			if ( empty( $credentials['access_token'] ) && class_exists( 'Gutena_Forms_Square_Connect' ) ) {
				$credentials = Gutena_Forms_Square_Connect::get_stored_credentials();
			}
			$access_token = sanitize_text_field( $credentials['access_token'] ?? '' );
			if ( empty( $access_token ) ) {
				return new WP_Error( 'square_no_token', __( 'Square access token is missing. Please reconnect Square in settings.', 'gutena-forms' ) );
			}

			$location_id = sanitize_text_field( $form_square['location_id'] ?? '' );
			if ( empty( $location_id ) && ! empty( $form_square['business_locations'][0]['id'] ) ) {
				$location_id = sanitize_text_field( $form_square['business_locations'][0]['id'] );
			}
			if ( empty( $location_id ) && class_exists( 'Gutena_Forms_Square_Connect' ) ) {
				$global_square = Gutena_Forms_Square_Connect::get_public_settings();
				$location_id   = sanitize_text_field( $global_square['location_id'] ?? '' );
				if ( empty( $location_id ) && ! empty( $global_square['business_locations'][0]['id'] ) ) {
					$location_id = sanitize_text_field( $global_square['business_locations'][0]['id'] );
				}
			}
			if ( empty( $location_id ) ) {
				return new WP_Error( 'square_no_location', __( 'Square business location is not selected. Please select a location in Square settings.', 'gutena-forms' ) );
			}

			$currency = strtoupper( sanitize_text_field( $form_square['merchant_currency'] ?? '' ) );
			if ( empty( $currency ) && class_exists( 'Gutena_Forms_Square_Connect' ) ) {
				$global_square = Gutena_Forms_Square_Connect::get_public_settings();
				$currency      = strtoupper( sanitize_text_field( $global_square['merchant_currency'] ?? 'USD' ) );
			}
			if ( empty( $currency ) ) {
				$currency = 'USD';
			}

			$field_values     = $this->get_posted_field_values();
			$amount_cents     = $this->calculate_amount_cents( $square_field, $field_values, $currency );
			$customer_details = $this->resolve_customer_details( $square_field, $field_values );
			$form_name        = $this->resolve_form_name( $form_id, $schema );
			$payment_type     = sanitize_key( $square_field['paymentType'] ?? 'one_time' );
			$api_base         = self::get_api_base_url( $payment_mode );

			if ( $amount_cents <= 0 ) {
				return new WP_Error( 'invalid_amount', __( 'Payment amount must be greater than zero.', 'gutena-forms' ) );
			}

			// Handle Subscription Payment (Pro feature)
			if ( 'subscription' === $payment_type ) {
				if ( ! function_exists( 'is_gutena_forms_pro' ) || ! is_gutena_forms_pro() ) {
					return new WP_Error( 'subscription_requires_pro', __( 'Square subscription payments require Gutena Forms Pro.', 'gutena-forms' ) );
				}

				$sub_result = $this->create_square_subscription(
					$api_base,
					$access_token,
					$location_id,
					$currency,
					$amount_cents,
					$payment_token,
					$square_field,
					$customer_details,
					$form_name,
					$form_id,
					$payment_mode
				);

				if ( is_wp_error( $sub_result ) ) {
					return $sub_result;
				}

				self::$current_payment_context = array(
					'form_id'          => $form_id,
					'form_name'        => $form_name,
					'square_field'     => $square_field,
					'payment_obj'      => $sub_result['subscription'],
					'card_details'     => $sub_result['card'],
					'payment_mode'     => $payment_mode,
					'amount_cents'     => $amount_cents,
					'currency'         => $currency,
					'customer_details' => $customer_details,
					'is_subscription'  => true,
				);

				return true;
			}

			// Handle One-Time Payment
			$body = array(
				'source_id'       => $payment_token,
				'idempotency_key' => wp_generate_uuid4(),
				'amount_money'    => array(
					'amount'   => (int) $amount_cents,
					'currency' => $currency,
				),
				'location_id'     => $location_id,
				'autocomplete'    => true,
				'note'            => sprintf( 'Gutena Forms: %s', sanitize_text_field( $form_name ) ),
			);

			if ( ! empty( $customer_details['customer_email'] ) ) {
				$body['buyer_email_address'] = $customer_details['customer_email'];
			}

			$response = $this->square_api_request(
				$api_base . '/v2/payments',
				'POST',
				$body,
				$access_token,
				$payment_mode
			);

			if ( is_wp_error( $response ) ) {
				return $response;
			}

			if ( empty( $response['payment'] ) ) {
				return new WP_Error( 'square_charge_failed', __( 'Payment failed. Please check your card information and try again.', 'gutena-forms' ) );
			}

			$payment_obj = $response['payment'];

			self::$current_payment_context = array(
				'form_id'          => $form_id,
				'form_name'        => $form_name,
				'square_field'     => $square_field,
				'payment_obj'      => $payment_obj,
				'payment_mode'     => $payment_mode,
				'amount_cents'     => $amount_cents,
				'currency'         => $currency,
				'customer_details' => $customer_details,
				'is_subscription'  => false,
			);

			return true;
		}

		/**
		 * Persist payment record for an entry in gutenaforms_payments and entry meta.
		 *
		 * @param int    $entry_id       Entry ID.
		 * @param array  $form_data      Form submission data.
		 * @param string $block_form_id  Block form ID.
		 * @param array  $field_schema   Field schema.
		 * @return bool
		 */
		public function save_payment_for_entry( $entry_id, $form_data, $block_form_id, $field_schema ) {
			if ( ! class_exists( 'Gutena_Forms_Entry_Payment' ) ) {
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

			$context = self::$current_payment_context;
			if ( empty( $context ) || empty( $context['payment_obj']['id'] ) ) {
				return false;
			}

			$payment_obj       = $context['payment_obj'];
			$square_field      = $context['square_field'];
			$payment_mode      = $context['payment_mode'];
			$amount_cents      = $context['amount_cents'];
			$currency          = $context['currency'];
			$customer_details  = $context['customer_details'];
			$payment_id        = sanitize_text_field( $payment_obj['id'] );
			$is_subscription   = ! empty( $context['is_subscription'] ) || 'subscription' === ( $square_field['paymentType'] ?? '' );

			$normalized_status = $is_subscription
				? ( in_array( strtoupper( $payment_obj['status'] ?? '' ), array( 'ACTIVE', 'COMPLETED' ), true ) || 'succeeded' === ( $payment_obj['status'] ?? '' ) ? 'succeeded' : Gutena_Forms_Entry_Payment::normalize_square_status( $payment_obj['status'] ?? 'succeeded' ) )
				: Gutena_Forms_Entry_Payment::normalize_square_status( $payment_obj['status'] ?? 'COMPLETED' );

			if ( '' === $normalized_status ) {
				$normalized_status = 'succeeded';
			}

			$card       = ! empty( $context['card_details'] ) ? $context['card_details'] : ( $payment_obj['card_details']['card'] ?? array() );
			$card_brand = sanitize_text_field( $card['card_brand'] ?? '' );
			$last4      = sanitize_text_field( $card['last_4'] ?? '' );
			$exp_month  = absint( $card['exp_month'] ?? 0 );
			$exp_year   = absint( $card['exp_year'] ?? 0 );

			$form_id_num = absint( $field_schema['form_id'] ?? 0 );
			if ( ! $form_id_num && class_exists( 'Gutena_Forms_Entries_Model' ) ) {
				$form_id_num = Gutena_Forms_Entries_Model::get_instance()->get_form_id_by_entry_id( $entry_id );
			}

			$form_name = ! empty( $context['form_name'] ) ? sanitize_text_field( $context['form_name'] ) : '';
			if ( empty( $form_name ) || 0 === strpos( $form_name, 'gutena_forms_id_' ) ) {
				$form_name = $this->resolve_form_name( $block_form_id, $field_schema );
			}
			if ( ( empty( $form_name ) || 0 === strpos( $form_name, 'gutena_forms_id_' ) ) && $form_id_num && class_exists( 'Gutena_Forms_Forms_Model' ) ) {
				$model_name = Gutena_Forms_Forms_Model::get_instance()->get_name_by_id( $form_id_num );
				if ( ! empty( $model_name ) && 0 !== strpos( $model_name, 'gutena_forms_id_' ) ) {
					$form_name = $model_name;
				}
			}
			if ( empty( $form_name ) || 0 === strpos( $form_name, 'gutena_forms_id_' ) ) {
				$form_name = __( 'Contact Form', 'gutena-forms' );
			}

			$payment_type  = $is_subscription ? 'subscription' : 'one_time';
			$dashboard_url = $is_subscription
				? self::get_subscription_dashboard_url( $payment_id, $payment_mode )
				: self::get_dashboard_url( $payment_id, $payment_mode );

			$payment_record = array(
				'entry_id'               => $entry_id,
				'form_id'                => $form_id_num,
				'form_name'              => $form_name,
				'gateway'                => 'square',
				'payment_id'             => $payment_id,
				'transaction_id'         => $payment_id,
				'amount'                 => $amount_cents,
				'currency'               => $currency,
				'status'                 => $normalized_status,
				'payment_mode'           => $payment_mode,
				'payment_type'           => $payment_type,
				'customer_name'          => $customer_details['customer_name'] ?? '',
				'customer_email'         => $customer_details['customer_email'] ?? '',
				'payment_method_details' => array(
					'card_brand' => $card_brand,
					'last4'      => $last4,
					'exp_month'  => $exp_month,
					'exp_year'   => $exp_year,
				),
				'square_dashboard_url'   => $dashboard_url,
				'gateway_dashboard_url'  => $dashboard_url,
				'raw_response'           => $payment_obj,
				'added_time'             => current_time( 'mysql' ),
			);

			$saved = Gutena_Forms_Entry_Payment::get_instance()->save_for_entry( $entry_id, $payment_record );

			Gutena_Forms_Entry_Payment::get_instance()->append_log(
				$entry_id,
				array(
					'event'          => $is_subscription ? 'subscription_created' : 'payment_authorized',
					'transaction_id' => $payment_id,
					'gateway'        => 'square',
					'amount'         => Gutena_Forms_Entry_Payment::format_amount( $amount_cents, $currency ),
					'status'         => $is_subscription ? __( 'Active', 'gutena-forms' ) : Gutena_Forms_Entry_Payment::status_label( $normalized_status ),
					'user_id'        => get_current_user_id(),
					'mode'           => $payment_mode,
					'created_at'     => gmdate( 'Y-m-d H:i:s' ),
				)
			);

			do_action( 'gutena_forms_square_payment_saved', $entry_id, $payment_record );
			do_action( 'gutena_forms_payment_completed', $entry_id, $payment_record );

			self::$current_payment_context = array();

			return $saved;
		}

		/**
		 * Find Square field definition in a form schema, posted config, or payment token.
		 *
		 * @param array  $schema          Form schema.
		 * @param string $square_field_id Optional nameAttr filter.
		 * @return array|null
		 */
		public function find_square_field( $schema = array(), $square_field_id = '' ) {
			if ( ! empty( $schema['form_fields'] ) && is_array( $schema['form_fields'] ) ) {
				foreach ( $schema['form_fields'] as $field_id => $field_data ) {
					if ( ! is_array( $field_data ) ) {
						continue;
					}

					if (
						( isset( $field_data['fieldType'] ) && 'square' === $field_data['fieldType'] ) ||
						( isset( $field_data['blockName'] ) && 'gutena/square-field' === $field_data['blockName'] ) ||
						( 'square_payment' === $field_id )
					) {
						$field_data['nameAttr'] = ! empty( $field_data['nameAttr'] ) ? $field_data['nameAttr'] : $field_id;
						return $field_data;
					}
				}
			}

			// Fallback: Check posted square_config
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			foreach ( $_POST as $key => $value ) {
				if ( is_string( $key ) && '_square_config' === substr( $key, -14 ) ) {
					$decoded = json_decode( wp_unslash( $value ), true );
					if ( is_array( $decoded ) ) {
						$decoded['nameAttr'] = ! empty( $decoded['nameAttr'] ) ? sanitize_key( $decoded['nameAttr'] ) : sanitize_key( str_replace( '_square_config', '', $key ) );
						return $decoded;
					}
				}
			}

			// Fallback: Check if payment token is posted
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			foreach ( $_POST as $key => $value ) {
				if ( is_string( $key ) && '_payment_token' === substr( $key, -14 ) && ! empty( $value ) ) {
					$name_attr = sanitize_key( str_replace( '_payment_token', '', $key ) );
					return array(
						'nameAttr'    => $name_attr ? $name_attr : 'square_payment',
						'blockName'   => 'gutena/square-field',
						'fieldType'   => 'square',
						'paymentType' => 'one_time',
						'amountType'  => 'fixed',
						'fixedAmount' => 0,
					);
				}
			}

			return null;
		}

		/**
		 * Resolve effective Square settings for a form.
		 *
		 * @param string $form_id Form ID.
		 * @return array
		 */
		private function resolve_form_payment_square( $form_id ) {
			$schema       = class_exists( 'Gutena_Forms_Helper' )
				? Gutena_Forms_Helper::get_form_schema_record( $form_id )
				: ( function_exists( 'gutena_forms_get_form_schema_option' ) ? gutena_forms_get_form_schema_option( $form_id, false ) : false );
			$block_square = is_array( $schema ) && ! empty( $schema['form_attrs']['paymentSquare'] )
				? $schema['form_attrs']['paymentSquare']
				: array();

			if ( class_exists( 'Gutena_Forms_Form_Block' ) ) {
				return Gutena_Forms_Form_Block::get_effective_payment_square( $block_square );
			}

			return is_array( $block_square ) ? $block_square : array();
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
		 * Calculate amount in lowest currency unit (cents).
		 *
		 * @param array  $square_field Square field config.
		 * @param array  $field_values Submitted field values.
		 * @param string $currency     Currency code.
		 * @return int
		 */
		private function calculate_amount_cents( $square_field, $field_values, $currency = 'USD' ) {
			$amount_type   = sanitize_key( $square_field['amountType'] ?? 'fixed' );
			$amount        = 0.0;
			$zero_decimals = array( 'BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF', 'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF' );
			$is_zero_dec   = in_array( strtoupper( $currency ), $zero_decimals, true );

			if ( 'fixed' === $amount_type || 'subscription' === ( $square_field['paymentType'] ?? '' ) ) {
				$amount = isset( $square_field['fixedAmount'] ) ? floatval( $square_field['fixedAmount'] ) : 0.0;
			} elseif ( 'variable' === $amount_type ) {
				$var_field = sanitize_key( $square_field['variableAmountField'] ?? '' );
				$raw_val   = isset( $field_values[ $var_field ] ) ? (string) $field_values[ $var_field ] : '';
				$amount    = floatval( preg_replace( '/[^0-9.]/', '', $raw_val ) );

				$min_amount = isset( $square_field['minimumAmount'] ) ? floatval( $square_field['minimumAmount'] ) : 0.0;
				if ( $min_amount > 0 && $amount < $min_amount ) {
					$amount = $min_amount;
				}
			}

			if ( $is_zero_dec ) {
				return (int) round( $amount );
			}

			return (int) round( $amount * 100 );
		}

		/**
		 * Resolve customer name and email from mapped form fields.
		 *
		 * @param array $square_field Square field config.
		 * @param array $field_values Posted field values keyed by nameAttr.
		 * @return array{customer_name:string,customer_email:string}
		 */
		private function resolve_customer_details( $square_field, $field_values ) {
			$email_field = sanitize_key( $square_field['customerEmailField'] ?? '' );
			$name_field  = sanitize_key( $square_field['customerNameField'] ?? '' );

			$customer_email = isset( $field_values[ $email_field ] ) ? sanitize_email( $field_values[ $email_field ] ) : '';
			$customer_name  = isset( $field_values[ $name_field ] ) ? sanitize_text_field( $field_values[ $name_field ] ) : '';

			return array(
				'customer_name'  => $customer_name,
				'customer_email' => $customer_email,
			);
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
			} elseif ( isset( $obj['subscription'] ) && is_array( $obj['subscription'] ) ) {
				$obj = $obj['subscription'];
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
			if ( empty( $credentials['access_token'] ) && class_exists( 'Gutena_Forms_Square_Connect' ) ) {
				$credentials = Gutena_Forms_Square_Connect::get_stored_credentials();
			}
			$access_token = sanitize_text_field( $credentials['access_token'] ?? '' );
			if ( empty( $access_token ) ) {
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
						'Authorization'  => 'Bearer ' . $access_token,
						'Square-Version' => self::SQUARE_API_VERSION,
						'Content-Type'   => 'application/json',
					),
					'body'    => wp_json_encode( $body ),
				)
			);

			if ( is_wp_error( $response ) ) {
				return $response;
			}

			$code          = (int) wp_remote_retrieve_response_code( $response );
			$response_body = json_decode( wp_remote_retrieve_body( $response ), true );

			// If access token expired, try renewing and retry once.
			if ( 401 === $code && class_exists( 'Gutena_Forms_Square_Connect' ) ) {
				$renewed = Gutena_Forms_Square_Connect::get_instance()->renew_access_token();
				if ( ! is_wp_error( $renewed ) && ! empty( $renewed['access_token'] ) ) {
					$access_token = sanitize_text_field( $renewed['access_token'] );
					$response     = wp_remote_post(
						$base . '/v2/refunds',
						array(
							'timeout' => 20,
							'headers' => array(
								'Authorization'  => 'Bearer ' . $access_token,
								'Square-Version' => self::SQUARE_API_VERSION,
								'Content-Type'   => 'application/json',
							),
							'body'    => wp_json_encode( $body ),
						)
					);
					if ( ! is_wp_error( $response ) ) {
						$code          = (int) wp_remote_retrieve_response_code( $response );
						$response_body = json_decode( wp_remote_retrieve_body( $response ), true );
					}
				}
			}

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
		 * Build Square dashboard URL for a subscription.
		 *
		 * @param string $subscription_id Square subscription ID.
		 * @param string $payment_mode    test|live.
		 * @return string
		 */
		public static function get_subscription_dashboard_url( $subscription_id, $payment_mode = 'test' ) {
			$subscription_id = sanitize_text_field( $subscription_id );
			if ( '' === $subscription_id ) {
				return '';
			}

			$host = 'live' === $payment_mode
				? 'https://squareup.com'
				: 'https://squareupsandbox.com';

			return esc_url_raw( $host . '/dashboard/subscriptions/' . rawurlencode( $subscription_id ) );
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
		 * Resolve friendly human-readable form name.
		 *
		 * @param string $form_id Form block ID or numeric ID.
		 * @param array  $schema  Form schema.
		 * @return string
		 */
		private function resolve_form_name( $form_id, $schema = array() ) {
			// 1. Check posted formName (sent in FormData).
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			if ( ! empty( $_POST['formName'] ) ) {
				$posted_name = sanitize_text_field( wp_unslash( $_POST['formName'] ) );
				if ( 0 !== strpos( $posted_name, 'gutena_forms_id_' ) && '' !== $posted_name ) {
					return $posted_name;
				}
			}

			// 2. Check schema form_attrs formName.
			if ( ! empty( $schema['form_attrs']['formName'] ) ) {
				$schema_name = sanitize_text_field( $schema['form_attrs']['formName'] );
				if ( 0 !== strpos( $schema_name, 'gutena_forms_id_' ) && '' !== $schema_name ) {
					return $schema_name;
				}
			}

			// 3. Check database table wp_gutenaforms.
			global $wpdb;
			if ( ! empty( $wpdb ) ) {
				$table = $wpdb->prefix . 'gutenaforms';
				$db_name = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT form_name FROM {$table} WHERE block_form_id = %s OR form_id = %d LIMIT 1",
						sanitize_key( $form_id ),
						absint( $form_id )
					)
				);
				if ( ! empty( $db_name ) && 0 !== strpos( $db_name, 'gutena_forms_id_' ) ) {
					return sanitize_text_field( $db_name );
				}
			}

			// 4. Check Gutena_Forms_Forms_Model.
			if ( class_exists( 'Gutena_Forms_Forms_Model' ) && is_numeric( $form_id ) && absint( $form_id ) > 0 ) {
				$model_name = Gutena_Forms_Forms_Model::get_instance()->get_name_by_id( absint( $form_id ) );
				if ( ! empty( $model_name ) && 0 !== strpos( $model_name, 'gutena_forms_id_' ) ) {
					return sanitize_text_field( $model_name );
				}
			}

			return __( 'Contact Form', 'gutena-forms' );
		}

		/**
		 * @param string $payment_mode Optional mode override.
		 * @return array
		 */
		private function get_square_credentials( $payment_mode = 'test' ) {
			unset( $payment_mode );

			if ( class_exists( 'Gutena_Forms_Square_Connect' ) ) {
				$creds = Gutena_Forms_Square_Connect::get_stored_credentials();
				if ( ! empty( $creds['access_token'] ) ) {
					return $creds;
				}
			}

			$all = get_option( 'gutena_forms__payment_credentials', array() );
			if ( ! is_array( $all ) || empty( $all['square'] ) || ! is_array( $all['square'] ) ) {
				return array();
			}

			return $all['square'];
		}
	}

	Gutena_Forms_Square_Payment_Service::get_instance();
endif;
