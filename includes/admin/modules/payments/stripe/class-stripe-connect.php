<?php

/**

 * Stripe connect / OAuth service.

 *

 * Credentials are stored server-side only in gutena_forms__payment_credentials.

 * OAuth is handled via Gutena Stripe middleware (https://connect2.apiexperts.io/).

 *

 * @package Gutena Forms

 */



defined( 'ABSPATH' ) || exit;

if ( ! defined( 'GUTENA_FORMS_STRIPE_MW_URL' ) ) {
	define( 'GUTENA_FORMS_STRIPE_MW_URL', 'https://connect2.apiexperts.io' );
}

if ( ! defined( 'GUTENA_FORMS_STRIPE_MW_SHARED_SECRET' ) ) {
	define( 'GUTENA_FORMS_STRIPE_MW_SHARED_SECRET', 'bxid9mETahJtj0r8E93sfXq2EFB7buhAV77B8JNHXG6dtFIl1x2YarFpILH5CkeM' );
}

if ( ! class_exists( 'Gutena_Forms_Stripe_Connect' ) ) :

	class Gutena_Forms_Stripe_Connect {

		const CREDENTIALS_OPTION = 'gutena_forms__payment_credentials';

		const SETTINGS_OPTION    = 'gutena_forms__payment_settings';

		const GATEWAY_ID         = 'stripe';

		const STRIPE_WEBHOOKS_URL  = 'https://dashboard.stripe.com/webhooks';
		const OAUTH_TTL            = 600;



		private static $instance;



		public static function get_instance() {

			if ( is_null( self::$instance ) ) {

				self::$instance = new self();

			}



			return self::$instance;

		}



		private function __construct() {

			add_action( 'admin_post_gutena_forms_stripe_mw_finish', array( $this, 'handle_middleware_finish' ) );

		}



		/**

		 * Middleware host URL (no trailing slash).

		 *

		 * @return string

		 */

		public static function get_middleware_url() {

			if ( defined( 'GUTENA_FORMS_STRIPE_MW_URL' ) && GUTENA_FORMS_STRIPE_MW_URL ) {

				return untrailingslashit( (string) GUTENA_FORMS_STRIPE_MW_URL );

			}



			return untrailingslashit(

				(string) apply_filters(

					'gutena_forms_stripe_middleware_url',

					'https://connect2.apiexperts.io'

				)

			);

		}



		/**

		 * Shared HMAC secret (must match middleware host).

		 *

		 * @return string

		 */

		public static function get_shared_secret() {

			if ( defined( 'GUTENA_FORMS_STRIPE_MW_SHARED_SECRET' ) && GUTENA_FORMS_STRIPE_MW_SHARED_SECRET ) {

				return (string) GUTENA_FORMS_STRIPE_MW_SHARED_SECRET;

			}

			return (string) apply_filters( 'gutena_forms_stripe_mw_shared_secret', '' );

		}



		/**

		 * Public payment settings safe for REST / block editor (no secrets).

		 *

		 * @return array

		 */

		public static function get_public_settings() {

			$settings = get_option( self::SETTINGS_OPTION, array() );

			if ( ! is_array( $settings ) || ! isset( $settings[ self::GATEWAY_ID ] ) ) {

				return array();

			}



			$stripe = is_array( $settings[ self::GATEWAY_ID ] ) ? $settings[ self::GATEWAY_ID ] : array();



			return array(

				'enable'                 => ! empty( $stripe['enable'] ),

				'payment_mode'           => in_array( $stripe['payment_mode'] ?? 'test', array( 'live', 'test' ), true ) ? $stripe['payment_mode'] : 'test',

				'currency'               => sanitize_text_field( $stripe['currency'] ?? 'USD' ),

				'currency_sign_position' => self::sanitize_sign_position( $stripe['currency_sign_position'] ?? 'left' ),

				'connected'              => ! empty( $stripe['connected'] ),

				'account_name'           => sanitize_text_field( $stripe['account_name'] ?? '' ),

				'webhook_connected'      => ! empty( $stripe['webhook_connected'] ),

				'webhook_slots_exceeded' => ! empty( $stripe['webhook_slots_exceeded'] ),

				'has_publishable_key_test' => ! empty( self::get_publishable_key( 'test' ) ),

				'has_publishable_key_live' => ! empty( self::get_publishable_key( 'live' ) ),

			);

		}

		/**
		 * Stripe publishable key for frontend Elements (platform key for Connect).
		 *
		 * Define GUTENA_FORMS_STRIPE_PUBLISHABLE_KEY_TEST / _LIVE in wp-config.php,
		 * or filter gutena_forms_stripe_publishable_key.
		 *
		 * @since 2.1.0
		 * @param string $payment_mode test|live.
		 * @return string
		 */
		public static function get_publishable_key( $payment_mode = 'test' ) {
			$payment_mode = in_array( $payment_mode, array( 'live', 'test' ), true ) ? $payment_mode : 'test';

			$settings = get_option( self::SETTINGS_OPTION, array() );
			if ( is_array( $settings ) && ! empty( $settings[ self::GATEWAY_ID ] ) && is_array( $settings[ self::GATEWAY_ID ] ) ) {
				$stored_key = $settings[ self::GATEWAY_ID ][ 'publishable_key_' . $payment_mode ] ?? $settings[ self::GATEWAY_ID ]['publishable_key'] ?? '';
				if ( ! empty( $stored_key ) ) {
					return self::sanitize_publishable_key( $stored_key );
				}
			}

			$constant     = 'live' === $payment_mode
				? 'GUTENA_FORMS_STRIPE_PUBLISHABLE_KEY_LIVE'
				: 'GUTENA_FORMS_STRIPE_PUBLISHABLE_KEY_TEST';

			$key = defined( $constant ) ? (string) constant( $constant ) : '';

			/**
			 * Filter Stripe publishable key used on the frontend.
			 *
			 * @since 2.1.0
			 * @param string $key          Publishable key.
			 * @param string $payment_mode Payment mode.
			 */
			return self::sanitize_publishable_key( apply_filters( 'gutena_forms_stripe_publishable_key', $key, $payment_mode ) );
		}

		/**
		 * Whether the active publishable key comes from wp-config / filter (platform) vs site settings (connected account).
		 *
		 * @since 2.1.0
		 * @param string $payment_mode test|live.
		 * @return bool
		 */
		public static function uses_platform_publishable_key( $payment_mode = 'test' ) {
			$payment_mode = in_array( $payment_mode, array( 'live', 'test' ), true ) ? $payment_mode : 'test';

			$settings = get_option( self::SETTINGS_OPTION, array() );
			if ( is_array( $settings ) && ! empty( $settings[ self::GATEWAY_ID ] ) && is_array( $settings[ self::GATEWAY_ID ] ) ) {
				$stored_key = $settings[ self::GATEWAY_ID ][ 'publishable_key_' . $payment_mode ] ?? $settings[ self::GATEWAY_ID ]['publishable_key'] ?? '';
				if ( ! empty( $stored_key ) ) {
					return false;
				}
			}

			return ! empty( self::get_publishable_key( $payment_mode ) );
		}

		/**
		 * Stripe.js connected account header — only when using a platform publishable key.
		 *
		 * @since 2.1.0
		 * @param string $payment_mode test|live.
		 * @return string
		 */
		public static function get_stripe_js_account_id( $payment_mode = 'test' ) {
			if ( ! self::uses_platform_publishable_key( $payment_mode ) ) {
				return '';
			}

			return self::get_connected_account_id();
		}

		/**
		 * @since 2.1.0
		 * @param string $key Publishable key.
		 * @return string
		 */
		public static function sanitize_publishable_key( $key ) {
			$key = sanitize_text_field( (string) $key );
			if ( '' === $key ) {
				return '';
			}

			if ( 0 !== strpos( $key, 'pk_' ) ) {
				return '';
			}

			return $key;
		}

		/**
		 * Connected Stripe account ID for direct charges.
		 *
		 * @since 2.1.0
		 * @return string
		 */
		public static function get_connected_account_id() {
			$credentials = get_option( self::CREDENTIALS_OPTION, array() );
			if ( ! is_array( $credentials ) || empty( $credentials[ self::GATEWAY_ID ]['account_id'] ) ) {
				return '';
			}

			return sanitize_text_field( $credentials[ self::GATEWAY_ID ]['account_id'] );
		}



		/**

		 * Settings slice for new forms (defaultSettings: true, no secrets).

		 *

		 * @return array

		 */

		public static function get_form_default_settings() {

			$public = self::get_public_settings();

			if ( empty( $public ) ) {

				$public = array(

					'enable'                 => false,

					'payment_mode'           => 'test',

					'currency'               => 'USD',

					'currency_sign_position' => 'left',

					'connected'              => false,

					'account_name'           => '',

					'webhook_connected'      => false,

					'webhook_slots_exceeded' => false,

				);

			}



			$public['defaultSettings'] = true;



			return $public;

		}



		/**

		 * Build signed OAuth start URL on middleware host.

		 *

		 * @param string $payment_mode live|test.

		 * @return string|WP_Error

		 */

		public function get_connect_url( $payment_mode = 'test' ) {

			$payment_mode = in_array( $payment_mode, array( 'live', 'test' ), true ) ? $payment_mode : 'test';

			$secret       = self::get_shared_secret();



			if ( '' === $secret ) {

				return new WP_Error(

					'stripe_oauth_unconfigured',

					__( 'Stripe middleware shared secret is not configured.', 'gutena-forms' )

				);

			}



			$handoff = wp_create_nonce( 'gutena_forms_stripe_connect' );

			update_option( 'gutena_forms_stripe_oauth_state', $handoff, false );



			$site    = trailingslashit( home_url() );

			$user_id = get_current_user_id();

			$ts      = time();

			$payload = $site . '|' . $handoff . '|' . $user_id . '|' . $payment_mode . '|' . $ts;

			$sig     = hash_hmac( 'sha256', $payload, $secret );



			$start_url = self::get_middleware_url() . '/wp-json/gutena-forms-stripe-mw/v1/start';



			return add_query_arg(

				array(

					'site'    => $site,

					'handoff' => $handoff,

					'user_id' => $user_id,

					'mode'    => $payment_mode,

					'ts'      => $ts,

					'sig'     => $sig,

				),

				$start_url

			);

		}



		/**

		 * Middleware redirects here after OAuth; fetch tokens via signed pickup.

		 *

		 * @return void

		 */

		public function handle_middleware_finish() {

			if ( ! current_user_can( 'manage_options' ) ) {

				wp_die( esc_html__( 'Unauthorized.', 'gutena-forms' ), '', array( 'response' => 403 ) );

			}



			// phpcs:ignore WordPress.Security.NonceVerification.Recommended

			$pickup = isset( $_GET['pickup'] ) ? sanitize_text_field( wp_unslash( $_GET['pickup'] ) ) : '';

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended

			$sig    = isset( $_GET['sig'] ) ? sanitize_text_field( wp_unslash( $_GET['sig'] ) ) : '';



			$secret = self::get_shared_secret();

			if ( '' === $pickup || '' === $sig || '' === $secret ) {

				$this->set_connect_notice( 'error', __( 'Connection failed. Please try again.', 'gutena-forms' ) );

				$this->redirect_to_stripe_settings();

			}



			$expected = hash_hmac( 'sha256', $pickup, $secret );

			if ( ! hash_equals( $expected, $sig ) ) {

				$this->set_connect_notice( 'error', __( 'Connection failed. Please try again.', 'gutena-forms' ) );

				$this->redirect_to_stripe_settings();

			}



			$site = trailingslashit( home_url() );

			$pickup_sig = hash_hmac( 'sha256', $pickup . '|' . $site, $secret );



			$response = wp_remote_post(

				self::get_middleware_url() . '/wp-json/gutena-forms-stripe-mw/v1/pickup',

				array(

					'timeout' => 20,

					'headers' => array(

						'Content-Type' => 'application/json',

					),

					'body'    => wp_json_encode(

						array(

							'pickup_id' => $pickup,

							'site'      => $site,

							'sig'       => $pickup_sig,

						)

					),

				)

			);



			if ( is_wp_error( $response ) ) {

				$this->set_connect_notice( 'error', __( 'Connection failed. Please try again.', 'gutena-forms' ) );

				$this->redirect_to_stripe_settings();

			}



			$code = (int) wp_remote_retrieve_response_code( $response );

			$body = json_decode( wp_remote_retrieve_body( $response ), true );



			if ( 200 !== $code || ! is_array( $body ) || empty( $body['access_token'] ) ) {

				$this->set_connect_notice( 'error', __( 'Connection failed. Please try again.', 'gutena-forms' ) );

				$this->redirect_to_stripe_settings();

			}



			$payment_mode = in_array( $body['mode'] ?? 'test', array( 'live', 'test' ), true ) ? $body['mode'] : 'test';

			$account_id   = sanitize_text_field( $body['stripe_user_id'] ?? '' );

			$account_name = $this->fetch_account_display_name( $account_id, $body['access_token'] );



			$this->complete_connection(

				array(

					'account_id'    => $account_id,

					'access_token'  => sanitize_text_field( $body['access_token'] ),

					'refresh_token' => sanitize_text_field( $body['refresh_token'] ?? '' ),

					'payment_mode'  => $payment_mode,

					'account_name'  => $account_name,

					'publishable_key' => sanitize_text_field( $body['publishable_key'] ?? '' ),

				)

			);

		}



		/**

		 * Store credentials, create webhook, set notice, redirect.

		 *

		 * @param array $data Connection payload.

		 * @return void

		 */

		private function complete_connection( $data ) {

			$this->store_credentials(

				array(

					'account_id'    => $data['account_id'],

					'access_token'  => $data['access_token'],

					'refresh_token' => $data['refresh_token'],

					'payment_mode'  => $data['payment_mode'],

				)

			);



			$this->update_gateway_settings(

				array(

					'connected'              => true,

					'account_name'           => $data['account_name'] ? $data['account_name'] : __( 'Stripe Account', 'gutena-forms' ),

					'payment_mode'           => $data['payment_mode'],

					'webhook_connected'      => false,

					'webhook_slots_exceeded' => false,

				)

			);



			if ( ! empty( $data['publishable_key'] ) ) {
				$mode_key = 'publishable_key_' . ( in_array( $data['payment_mode'] ?? 'test', array( 'live', 'test' ), true ) ? $data['payment_mode'] : 'test' );
				$this->update_gateway_settings(
					array(
						$mode_key => self::sanitize_publishable_key( $data['publishable_key'] ),
					)
				);
			}



			$webhook_result = $this->create_webhook();



			if ( is_wp_error( $webhook_result ) ) {

				if ( 'webhook_slots_exceeded' === $webhook_result->get_error_code() ) {

					$this->update_gateway_settings( array( 'webhook_slots_exceeded' => true ) );

				}

			} else {

				$this->update_gateway_settings(

					array(

						'webhook_connected'      => true,

						'webhook_slots_exceeded' => false,

					)

				);

			}



			delete_option( 'gutena_forms_stripe_oauth_state' );

			$this->set_connect_notice( 'success', __( 'Your stripe account has been successfully connected.', 'gutena-forms' ) );

			$this->redirect_to_stripe_settings();

		}



		/**

		 * Fetch connected account display name from Stripe.

		 *

		 * @param string $account_id   Stripe account id.

		 * @param string $access_token Connected account access token.

		 * @return string

		 */

		private function fetch_account_display_name( $account_id, $access_token ) {

			if ( empty( $account_id ) || empty( $access_token ) ) {

				return '';

			}



			$response = wp_remote_get(

				'https://api.stripe.com/v1/accounts/' . rawurlencode( $account_id ),

				array(

					'timeout' => 15,

					'headers' => array(

						'Authorization' => 'Bearer ' . $access_token,

					),

				)

			);



			if ( is_wp_error( $response ) ) {

				return '';

			}



			$body = json_decode( wp_remote_retrieve_body( $response ), true );

			if ( ! is_array( $body ) ) {

				return '';

			}



			if ( ! empty( $body['business_profile']['name'] ) ) {

				return sanitize_text_field( $body['business_profile']['name'] );

			}



			if ( ! empty( $body['settings']['dashboard']['display_name'] ) ) {

				return sanitize_text_field( $body['settings']['dashboard']['display_name'] );

			}



			if ( ! empty( $body['email'] ) ) {

				return sanitize_email( $body['email'] );

			}



			return '';

		}



		/**

		 * Disconnect Stripe and remove stored credentials.

		 *

		 * @return true|WP_Error

		 */

		public function disconnect() {

			delete_option( self::CREDENTIALS_OPTION );



			$this->update_gateway_settings(

				array(

					'connected'              => false,

					'account_name'           => '',

					'webhook_connected'      => false,

					'webhook_slots_exceeded' => false,

				)

			);



			return true;

		}



		/**

		 * Retry webhook creation after user frees a Stripe slot.

		 *

		 * @return array|WP_Error

		 */

		public function retry_webhook() {

			if ( ! $this->is_connected() ) {

				return new WP_Error( 'not_connected', __( 'Connect your Stripe account first.', 'gutena-forms' ) );

			}



			$result = $this->create_webhook();



			if ( is_wp_error( $result ) ) {

				if ( 'webhook_slots_exceeded' === $result->get_error_code() ) {

					$this->update_gateway_settings( array( 'webhook_slots_exceeded' => true ) );

				}



				return $result;

			}



			$this->update_gateway_settings(

				array(

					'webhook_connected'      => true,

					'webhook_slots_exceeded' => false,

				)

			);



			return array(

				'success' => true,

				'message' => __( 'Webhook successfully connected, all Stripe events are being tracked.', 'gutena-forms' ),

			);

		}



		/**

		 * @return bool

		 */

		public function is_connected() {

			$credentials = $this->get_credentials();

			$settings    = self::get_public_settings();



			return ! empty( $credentials['access_token'] ) && ! empty( $settings['connected'] );

		}



		/**

		 * @return array

		 */

		private function get_credentials() {

			$credentials = get_option( self::CREDENTIALS_OPTION, array() );

			if ( ! is_array( $credentials ) || ! isset( $credentials[ self::GATEWAY_ID ] ) ) {

				return array();

			}



			return is_array( $credentials[ self::GATEWAY_ID ] ) ? $credentials[ self::GATEWAY_ID ] : array();

		}



		/**

		 * @param array $credentials Credential payload.

		 */

		private function store_credentials( $credentials ) {

			$all = get_option( self::CREDENTIALS_OPTION, array() );

			if ( ! is_array( $all ) ) {

				$all = array();

			}



			$all[ self::GATEWAY_ID ] = array(

				'account_id'    => sanitize_text_field( $credentials['account_id'] ?? '' ),

				'access_token'  => sanitize_text_field( $credentials['access_token'] ?? '' ),

				'refresh_token' => sanitize_text_field( $credentials['refresh_token'] ?? '' ),

				'payment_mode'  => in_array( $credentials['payment_mode'] ?? 'test', array( 'live', 'test' ), true )

					? $credentials['payment_mode']

					: 'test',

			);



			update_option( self::CREDENTIALS_OPTION, $all, false );

		}



		/**

		 * @param array $partial Partial gateway settings.

		 */

		private function update_gateway_settings( $partial ) {

			$all = get_option( self::SETTINGS_OPTION, array() );

			if ( ! is_array( $all ) ) {

				$all = array();

			}

			if ( ! isset( $all[ self::GATEWAY_ID ] ) || ! is_array( $all[ self::GATEWAY_ID ] ) ) {

				$all[ self::GATEWAY_ID ] = array();

			}



			$all[ self::GATEWAY_ID ] = array_merge( $all[ self::GATEWAY_ID ], $partial );

			update_option( self::SETTINGS_OPTION, $all );

		}



		/**

		 * Create Stripe webhook on the connected account.

		 *

		 * @return true|WP_Error

		 */

		private function create_webhook() {

			$credentials = $this->get_credentials();



			if ( empty( $credentials['access_token'] ) ) {

				return new WP_Error( 'missing_credentials', __( 'Stripe credentials are missing.', 'gutena-forms' ) );

			}



			/**

			 * Filter webhook creation result.

			 *

			 * Return true on success, WP_Error on failure. Use error code webhook_slots_exceeded when Stripe free slots are full.

			 *

			 * @param null|true|WP_Error $result      Default null (create via Stripe API).

			 * @param array              $credentials Stripe credentials (server-side).

			 */

			$result = apply_filters( 'gutena_forms_stripe_create_webhook', null, $credentials );



			if ( null !== $result ) {

				return $result;

			}



			$webhook_url = rest_url( 'gutena-forms/v1/payments/stripe/webhook' );



			$response = wp_remote_post(

				'https://api.stripe.com/v1/webhook_endpoints',

				array(

					'timeout' => 20,

					'headers' => array(

						'Authorization' => 'Bearer ' . $credentials['access_token'],

					),

					'body'    => array(
						'url'            => $webhook_url,
						'enabled_events' => array(
							'checkout.session.completed',
							'payment_intent.succeeded',
							'payment_intent.payment_failed',
							'invoice.paid',
							'customer.subscription.created',
						),
					),

				)

			);



			if ( is_wp_error( $response ) ) {

				return $response;

			}



			$code = (int) wp_remote_retrieve_response_code( $response );

			$body = json_decode( wp_remote_retrieve_body( $response ), true );



			if ( $code >= 200 && $code < 300 ) {

				return true;

			}



			$error_message = '';

			if ( is_array( $body ) && ! empty( $body['error']['message'] ) ) {

				$error_message = sanitize_text_field( $body['error']['message'] );

			}



			if ( $error_message && false !== stripos( $error_message, 'maximum' ) ) {

				return new WP_Error(

					'webhook_slots_exceeded',

					__( 'Gutena Forms could not create a webhook because your Stripe account has run out of free slots. Webhooks are needed to receive updates about payments.', 'gutena-forms' )

				);

			}



			return new WP_Error(

				'webhook_create_failed',

				$error_message ? $error_message : __( 'Failed to create Stripe webhook.', 'gutena-forms' )

			);

		}



		/**

		 * @param string $type success|error.

		 * @param string $message Notice message.

		 */

		private function set_connect_notice( $type, $message ) {

			set_transient(

				'gutena_forms_stripe_connect_notice',

				array(

					'type'    => $type,

					'message' => $message,

				),

				60

			);

		}



		private function redirect_to_stripe_settings() {

			wp_safe_redirect( admin_url( 'admin.php?page=gutena-forms#/settings/settings/payment/stripe' ) );

			exit;

		}



		/**

		 * @param string $position Sign position key.

		 * @return string

		 */

		public static function sanitize_sign_position( $position ) {

			$allowed = array( 'left', 'right', 'left_space', 'right_space' );



			return in_array( $position, $allowed, true ) ? $position : 'left';

		}



		/**

		 * Consume and return connect notice for dashboard.

		 *

		 * @return array|null

		 */

		public static function consume_connect_notice() {

			$notice = get_transient( 'gutena_forms_stripe_connect_notice' );

			delete_transient( 'gutena_forms_stripe_connect_notice' );



			if ( is_array( $notice ) ) {

				return $notice;

			}



			// phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if ( isset( $_GET['gutena_stripe'] ) && 'error' === sanitize_key( wp_unslash( $_GET['gutena_stripe'] ) ) ) {

				return array(

					'type'    => 'error',

					'message' => __( 'Connection failed. Please try again.', 'gutena-forms' ),

				);

			}



			return null;

		}

	}



	Gutena_Forms_Stripe_Connect::get_instance();

endif;


