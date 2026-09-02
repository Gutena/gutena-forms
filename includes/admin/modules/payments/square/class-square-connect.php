<?php
/**
 * Square connect / OAuth service (API Experts square-mid-server middleware).
 *
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Square_Connect' ) ) :
	class Gutena_Forms_Square_Connect {

		const CREDENTIALS_OPTION = 'gutena_forms__payment_credentials';
		const SETTINGS_OPTION    = 'gutena_forms__payment_settings';
		const GATEWAY_ID         = 'square';
		const OAUTH_CALLBACK     = 'gutena_forms_square_oauth_callback';

		/** @var string API Experts square-mid-server base URL (no trailing slash). */
		const MW_URL = 'https://connect2.apiexperts.io';

		/** @var string Middleware app name (credentials in square-mid-server). */
		const APP_NAME = 'Gutena Form';

		/** @var string Plugin identifier sent to middleware. */
		const PLUG = 'gutena-forms';

		/** @var string Square OAuth scopes (space-separated). */
		const OAUTH_SCOPES = 'MERCHANT_PROFILE_READ PAYMENTS_READ PAYMENTS_WRITE ORDERS_READ ORDERS_WRITE';

		/** @var string Square API version header. */
		const SQUARE_API_VERSION = '2024-01-18';

		private static $instance;

		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		private function __construct() {
			add_action( 'admin_post_' . self::OAUTH_CALLBACK, array( $this, 'handle_oauth_callback' ) );
			add_action( 'admin_post_nopriv_' . self::OAUTH_CALLBACK, array( $this, 'handle_oauth_callback_login_redirect' ) );
			add_action( 'admin_init', array( $this, 'maybe_handle_oauth_admin_page' ), 1 );
		}

		/**
		 * Send unauthenticated OAuth returns through login while preserving callback params.
		 *
		 * @return void
		 */
		public function handle_oauth_callback_login_redirect() {
			if ( is_user_logged_in() ) {
				$this->handle_oauth_callback();
				return;
			}

			wp_safe_redirect( wp_login_url( $this->get_current_oauth_return_url() ) );
			exit;
		}

		/**
		 * Some middleware builds redirect to admin.php instead of admin-post.php.
		 *
		 * @return void
		 */
		public function maybe_handle_oauth_admin_page() {
			if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
				return;
			}

			if ( '' === $this->get_request_param( 'access_token' ) ) {
				return;
			}

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
			if ( 'gutena-forms' !== $page ) {
				return;
			}

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$action = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : '';
			if ( self::OAUTH_CALLBACK === $action ) {
				return;
			}

			$this->process_oauth_callback();
		}

		/**
		 * @return string
		 */
		private function get_current_oauth_return_url() {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return add_query_arg( wp_unslash( $_REQUEST ), admin_url( 'admin-post.php?action=' . self::OAUTH_CALLBACK ) );
		}

		/**
		 * @param string $key Query arg key.
		 * @return string
		 */
		private function get_request_param( $key ) {
			$candidates = array_unique(
				array(
					$key,
					'amp;' . $key,
				)
			);

			foreach ( $candidates as $candidate ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended
				if ( isset( $_REQUEST[ $candidate ] ) ) {
					// phpcs:ignore WordPress.Security.NonceVerification.Recommended
					return trim( (string) wp_unslash( $_REQUEST[ $candidate ] ) );
				}
			}

			return '';
		}

		/**
		 * @return string
		 */
		private function get_oauth_error_message() {
			$message = $this->get_request_param( 'message' );
			if ( '' !== $message ) {
				return sanitize_text_field( $message );
			}

			$error_description = $this->get_request_param( 'error_description' );
			if ( '' !== $error_description ) {
				return sanitize_text_field( $error_description );
			}

			$type = $this->get_request_param( 'type' );
			if ( '' !== $type ) {
				return sanitize_text_field( $type );
			}

			return __( 'Connection failed. Please try again.', 'gutena-forms' );
		}

		/**
		 * @return string test|live
		 */
		private function resolve_payment_mode_from_request() {
			$stored = get_option( 'gutena_forms_square_oauth_mode', '' );
			if ( in_array( $stored, array( 'live', 'test' ), true ) ) {
				return $stored;
			}

			$payment_mode = sanitize_key( $this->get_request_param( 'payment_mode' ) );
			if ( in_array( $payment_mode, array( 'live', 'test' ), true ) ) {
				return $payment_mode;
			}

			$wpep_sandbox = sanitize_text_field( $this->get_request_param( 'wpep_sandbox' ) );
			if ( 'yes' === $wpep_sandbox ) {
				return 'test';
			}
			if ( 'no' === $wpep_sandbox ) {
				return 'live';
			}

			return 'test';
		}

		/**
		 * @return bool
		 */
		private function validate_oauth_state() {
			$usf_state = sanitize_text_field( $this->get_request_param( 'usf_state' ) );

			if ( '' === $usf_state ) {
				return false;
			}

			if ( wp_verify_nonce( $usf_state, 'gutena_forms_square_connect' ) ) {
				return true;
			}

			$expected = get_option( 'gutena_forms_square_oauth_state', '' );

			return '' !== $expected && hash_equals( $expected, $usf_state );
		}

		/**
		 * @param string $access_token Access token.
		 * @param string $payment_mode test|live.
		 * @return bool
		 */
		private function verify_access_token_for_mode( $access_token, $payment_mode ) {
			$payment_mode = in_array( $payment_mode, array( 'live', 'test' ), true ) ? $payment_mode : 'test';
			$base         = 'test' === $payment_mode
				? 'https://connect.squareupsandbox.com'
				: 'https://connect.squareup.com';

			$response = wp_remote_get(
				$base . '/v2/locations',
				array(
					'timeout' => 15,
					'headers' => array(
						'Authorization'  => 'Bearer ' . $access_token,
						'Square-Version' => self::SQUARE_API_VERSION,
					),
				)
			);

			if ( is_wp_error( $response ) ) {
				return false;
			}

			return 200 === (int) wp_remote_retrieve_response_code( $response );
		}

		/**
		 * @param string $access_token Access token.
		 * @param string $preferred_mode test|live.
		 * @return string Verified mode or empty string.
		 */
		private function resolve_verified_payment_mode( $access_token, $preferred_mode = 'test' ) {
			$modes = array( 'test', 'live' );

			if ( in_array( $preferred_mode, $modes, true ) ) {
				$modes = array_merge( array( $preferred_mode ), array_diff( $modes, array( $preferred_mode ) ) );
			}

			foreach ( $modes as $mode ) {
				if ( $this->verify_access_token_for_mode( $access_token, $mode ) ) {
					return $mode;
				}
			}

			return '';
		}

		/**
		 * @param string $access_token Access token.
		 * @param string $payment_mode test|live.
		 * @return bool
		 */
		private function should_accept_oauth_tokens( $access_token, $payment_mode ) {
			if ( $this->validate_oauth_state() ) {
				return true;
			}

			if ( '' !== $this->resolve_verified_payment_mode( $access_token, $payment_mode ) ) {
				return true;
			}

			// Successful middleware token exchange includes merchant + refresh tokens.
			if ( '' !== $this->get_request_param( 'merchant_id' ) && '' !== $this->get_request_param( 'refresh_token' ) ) {
				return true;
			}

			// OAuth flow was started recently and middleware returned a bearer token.
			if ( '' !== get_option( 'gutena_forms_square_oauth_state', '' ) ) {
				return true;
			}

			return false;
		}

		/**
		 * @param string $message Error message.
		 * @return void
		 */
		private function fail_oauth_connection( $message ) {
			update_option(
				'gutena_forms_square_oauth_last_error',
				array(
					'time'    => time(),
					'message' => $message,
					'has_token_param' => '' !== $this->get_request_param( 'access_token' ),
					'method'  => isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) : '',
				),
				false
			);

			$this->set_connect_notice( 'error', $message );
			$this->redirect_to_square_settings();
		}

		public static function get_middleware_url() {
			return untrailingslashit(
				(string) apply_filters( 'gutena_forms_square_middleware_url', self::MW_URL )
			);
		}

		public static function get_app_name() {
			return (string) apply_filters( 'gutena_forms_square_app_name', self::APP_NAME );
		}

		public static function get_plug_name() {
			return (string) apply_filters( 'gutena_forms_square_plug', self::PLUG );
		}

		public static function get_oauth_scopes() {
			return (string) apply_filters( 'gutena_forms_square_oauth_scopes', self::OAUTH_SCOPES );
		}

		public static function get_stored_credentials() {
			$all = get_option( self::CREDENTIALS_OPTION, array() );

			if ( ! is_array( $all ) || empty( $all[ self::GATEWAY_ID ] ) || ! is_array( $all[ self::GATEWAY_ID ] ) ) {
				return array();
			}

			return $all[ self::GATEWAY_ID ];
		}

		public static function get_public_settings() {
			$settings    = get_option( self::SETTINGS_OPTION, array() );
			$credentials = self::get_stored_credentials();
			$has_token   = ! empty( $credentials['access_token'] );
			$instance    = self::get_instance();

			if ( ! is_array( $settings ) || ! isset( $settings[ self::GATEWAY_ID ] ) ) {
				$square = array();
			} else {
				$square = is_array( $settings[ self::GATEWAY_ID ] ) ? $settings[ self::GATEWAY_ID ] : array();
			}

			if ( $has_token && empty( $square['connected'] ) ) {
				$instance->sync_connection_from_credentials( $credentials, $square );
				$settings = get_option( self::SETTINGS_OPTION, array() );
				$square   = is_array( $settings[ self::GATEWAY_ID ] ?? null ) ? $settings[ self::GATEWAY_ID ] : array();
			}

			$connected = $has_token || ! empty( $square['connected'] );

			if ( $connected && $has_token && ( empty( $square['account_name'] ) || empty( $square['business_locations'] ) ) ) {
				$instance->repair_merchant_display( $credentials, $square );
				$settings = get_option( self::SETTINGS_OPTION, array() );
				$square   = is_array( $settings[ self::GATEWAY_ID ] ?? null ) ? $settings[ self::GATEWAY_ID ] : array();
			}

			$locations = self::sanitize_locations( $square['business_locations'] ?? array() );
			$connected_payment_mode = in_array( $credentials['payment_mode'] ?? '', array( 'live', 'test' ), true )
				? $credentials['payment_mode']
				: ( in_array( $square['payment_mode'] ?? 'test', array( 'live', 'test' ), true ) ? $square['payment_mode'] : 'test' );

			return array(
				'enable'                 => ! empty( $square['enable'] ),
				'payment_mode'           => in_array( $square['payment_mode'] ?? 'test', array( 'live', 'test' ), true ) ? $square['payment_mode'] : 'test',
				'connected_payment_mode' => $connected_payment_mode,
				'connected'              => $connected,
				'account_name'           => sanitize_text_field( $square['account_name'] ?? '' ),
				'merchant_currency'      => sanitize_text_field( $square['merchant_currency'] ?? '' ),
				'location_id'            => sanitize_text_field( $square['location_id'] ?? '' ),
				'business_locations'     => $locations,
			);
		}

		public static function is_square_connected() {
			$credentials = self::get_stored_credentials();

			if ( ! empty( $credentials['access_token'] ) ) {
				return true;
			}

			$settings = get_option( self::SETTINGS_OPTION, array() );
			$square   = is_array( $settings ) && isset( $settings[ self::GATEWAY_ID ] ) && is_array( $settings[ self::GATEWAY_ID ] )
				? $settings[ self::GATEWAY_ID ]
				: array();

			return ! empty( $square['connected'] );
		}

		/**
		 * Build middleware authorization URL (square-mid-server OAuth v2).
		 *
		 * @param string $payment_mode test|live.
		 * @return string|WP_Error
		 */
		public function get_connect_url( $payment_mode = 'test' ) {
			$payment_mode   = in_array( $payment_mode, array( 'live', 'test' ), true ) ? $payment_mode : 'test';
			$sandbox_enabled = 'test' === $payment_mode ? 'yes' : 'no';
			$usf_state       = wp_create_nonce( 'gutena_forms_square_connect' );

			update_option( 'gutena_forms_square_oauth_state', $usf_state, false );
			update_option( 'gutena_forms_square_oauth_mode', $payment_mode, false );

			$callback_url = add_query_arg(
				array(
					'action'        => self::OAUTH_CALLBACK,
					'oauth_version' => '2',
					'wpep_sandbox'  => $sandbox_enabled,
					'payment_mode'  => $payment_mode,
				),
				admin_url( 'admin-post.php' )
			);

			return add_query_arg(
				array(
					'oauth_version'   => '2',
					'request_type'    => 'authorization',
					'app_name'        => self::get_app_name(),
					'sandbox_enabled' => $sandbox_enabled,
					'redirect'        => $callback_url,
					'scope'           => self::get_oauth_scopes(),
					'plug'            => self::get_plug_name(),
					'usf_state'       => $usf_state,
				),
				trailingslashit( self::get_middleware_url() )
			);
		}

		/**
		 * Handle OAuth callback after user approves redirect on middleware.
		 *
		 * @return void
		 */
		public function handle_oauth_callback() {
			if ( ! is_user_logged_in() ) {
				wp_safe_redirect( wp_login_url( $this->get_current_oauth_return_url() ) );
				exit;
			}

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'Unauthorized.', 'gutena-forms' ), '', array( 'response' => 403 ) );
			}

			$this->process_oauth_callback();
		}

		/**
		 * @return void
		 */
		private function process_oauth_callback() {
			static $processed = false;

			if ( $processed ) {
				return;
			}

			if ( ! empty( $this->get_request_param( 'error' ) ) ) {
				$this->fail_oauth_connection( $this->get_oauth_error_message() );
			}

			$access_token  = $this->get_request_param( 'access_token' );
			$refresh_token = $this->get_request_param( 'refresh_token' );
			$merchant_id   = sanitize_text_field( $this->get_request_param( 'merchant_id' ) );
			$payment_mode  = $this->resolve_payment_mode_from_request();

			if ( '' === $access_token ) {
				$this->fail_oauth_connection(
					__( 'Square did not return an access token. Try connecting again from Gutena Forms (do not refresh the callback page).', 'gutena-forms' )
				);
			}

			if ( ! $this->should_accept_oauth_tokens( $access_token, $payment_mode ) ) {
				$this->fail_oauth_connection(
					__( 'Square connection could not be verified. Check that Gutena Form credentials exist in the middleware, then try again.', 'gutena-forms' )
				);
			}

			$verified_mode = $this->resolve_verified_payment_mode( $access_token, $payment_mode );
			if ( '' !== $verified_mode ) {
				$payment_mode = $verified_mode;
			}

			$merchant_details = $this->fetch_merchant_details( $access_token, $payment_mode, $merchant_id );

			$processed = true;

			$this->complete_connection(
				array(
					'merchant_id'        => $merchant_id,
					'access_token'       => trim( (string) $access_token ),
					'refresh_token'      => trim( (string) $refresh_token ),
					'payment_mode'       => $payment_mode,
					'account_name'       => $merchant_details['account_name'],
					'merchant_currency'  => $merchant_details['merchant_currency'],
					'business_locations' => $merchant_details['business_locations'],
				)
			);
		}

		private function sync_connection_from_credentials( $credentials, $square ) {
			$payment_mode = in_array( $credentials['payment_mode'] ?? 'test', array( 'live', 'test' ), true )
				? $credentials['payment_mode']
				: 'test';

			$this->update_gateway_settings(
				array(
					'connected'    => true,
					'payment_mode' => $payment_mode,
				)
			);
		}

		private function repair_merchant_display( $credentials, $square ) {
			$access_token = sanitize_text_field( $credentials['access_token'] ?? '' );

			if ( '' === $access_token ) {
				return;
			}

			$payment_mode = in_array( $credentials['payment_mode'] ?? 'test', array( 'live', 'test' ), true )
				? $credentials['payment_mode']
				: 'test';
			$merchant_id  = sanitize_text_field( $credentials['merchant_id'] ?? '' );
			$details      = $this->fetch_merchant_details( $access_token, $payment_mode, $merchant_id );
			$locations    = self::sanitize_locations( $details['business_locations'] ?? array() );
			$updates      = array(
				'connected' => true,
			);

			if ( empty( $square['account_name'] ) && ! empty( $details['account_name'] ) ) {
				$updates['account_name'] = $details['account_name'];
			} elseif ( empty( $square['account_name'] ) ) {
				$updates['account_name'] = __( 'Square Account', 'gutena-forms' );
			}

			if ( empty( $square['merchant_currency'] ) && ! empty( $details['merchant_currency'] ) ) {
				$updates['merchant_currency'] = $details['merchant_currency'];
				$updates['currency']          = $details['merchant_currency'];
			}

			if ( empty( $square['business_locations'] ) && ! empty( $locations ) ) {
				$updates['business_locations'] = $locations;
			}

			if ( empty( $square['location_id'] ) && ! empty( $locations[0]['id'] ) ) {
				$updates['location_id'] = $locations[0]['id'];
			}

			$this->update_gateway_settings( $updates );
		}

		private function complete_connection( $data ) {
			$payment_mode      = in_array( $data['payment_mode'] ?? 'test', array( 'live', 'test' ), true ) ? $data['payment_mode'] : 'test';
			$locations         = self::sanitize_locations( $data['business_locations'] ?? array() );
			$default_location  = ! empty( $locations[0]['id'] ) ? $locations[0]['id'] : '';
			$merchant_currency = sanitize_text_field( $data['merchant_currency'] ?? '' );

			$this->store_credentials(
				array(
					'merchant_id'   => $data['merchant_id'],
					'access_token'  => $data['access_token'],
					'refresh_token' => $data['refresh_token'],
					'payment_mode'  => $payment_mode,
				)
			);

			$this->update_gateway_settings(
				array(
					'connected'          => true,
					'account_name'       => $data['account_name'] ? $data['account_name'] : __( 'Square Account', 'gutena-forms' ),
					'payment_mode'       => $payment_mode,
					'merchant_currency'  => $merchant_currency,
					'business_locations' => $locations,
					'location_id'        => $default_location,
					'currency'           => $merchant_currency ? $merchant_currency : 'USD',
				)
			);

			delete_option( 'gutena_forms_square_oauth_state' );
			delete_option( 'gutena_forms_square_oauth_mode' );
			delete_option( 'gutena_forms_square_oauth_last_error' );

			$this->set_connect_notice(
				'success',
				__( 'Your Square account has been successfully connected.', 'gutena-forms' )
			);

			$this->redirect_to_square_settings();
		}

		public function disconnect() {
			$all = get_option( self::CREDENTIALS_OPTION, array() );

			if ( is_array( $all ) && isset( $all[ self::GATEWAY_ID ] ) && is_array( $all[ self::GATEWAY_ID ] ) ) {
				$credentials   = $all[ self::GATEWAY_ID ];
				$access_token  = sanitize_text_field( $credentials['access_token'] ?? '' );
				$payment_mode  = in_array( $credentials['payment_mode'] ?? 'test', array( 'live', 'test' ), true )
					? $credentials['payment_mode']
					: 'test';
				$sandbox_enabled = 'test' === $payment_mode ? 'yes' : 'no';

				if ( '' !== $access_token ) {
					wp_remote_get(
						add_query_arg(
							array(
								'oauth_version'   => '2',
								'request_type'    => 'revoke_token',
								'app_name'        => self::get_app_name(),
								'sandbox_enabled' => $sandbox_enabled,
								'access_token'    => $access_token,
							),
							trailingslashit( self::get_middleware_url() )
						),
						array(
							'timeout' => 15,
						)
					);
				}

				unset( $all[ self::GATEWAY_ID ] );
				update_option( self::CREDENTIALS_OPTION, $all, false );
			}

			$this->update_gateway_settings(
				array(
					'connected'          => false,
					'account_name'       => '',
					'merchant_currency'  => '',
					'business_locations' => array(),
					'location_id'        => '',
				)
			);

			return true;
		}

		public function is_connected() {
			return self::is_square_connected();
		}

		/**
		 * Renew access token via middleware (square-mid-server renew_token).
		 *
		 * @return array|WP_Error
		 */
		public function renew_access_token() {
			$all = get_option( self::CREDENTIALS_OPTION, array() );

			if ( ! is_array( $all ) || empty( $all[ self::GATEWAY_ID ] ) || ! is_array( $all[ self::GATEWAY_ID ] ) ) {
				return new WP_Error( 'square_not_connected', __( 'Square is not connected.', 'gutena-forms' ) );
			}

			$credentials     = $all[ self::GATEWAY_ID ];
			$refresh_token   = sanitize_text_field( $credentials['refresh_token'] ?? '' );
			$payment_mode    = in_array( $credentials['payment_mode'] ?? 'test', array( 'live', 'test' ), true )
				? $credentials['payment_mode']
				: 'test';
			$sandbox_enabled = 'test' === $payment_mode ? 'yes' : 'no';

			if ( '' === $refresh_token ) {
				return new WP_Error( 'square_no_refresh_token', __( 'Square refresh token is missing.', 'gutena-forms' ) );
			}

			$response = wp_remote_get(
				add_query_arg(
					array(
						'oauth_version'   => '2',
						'request_type'    => 'renew_token',
						'app_name'        => self::get_app_name(),
						'sandbox_enabled' => $sandbox_enabled,
						'refresh_token'   => $refresh_token,
					),
					trailingslashit( self::get_middleware_url() )
				),
				array(
					'timeout' => 20,
				)
			);

			if ( is_wp_error( $response ) ) {
				return $response;
			}

			$body = json_decode( wp_remote_retrieve_body( $response ), true );

			if ( ! is_array( $body ) || empty( $body['access_token'] ) ) {
				return new WP_Error( 'square_token_renewal_failed', __( 'Failed to renew Square access token.', 'gutena-forms' ) );
			}

			$credentials['access_token']  = sanitize_text_field( $body['access_token'] );
			$credentials['refresh_token'] = sanitize_text_field( $body['refresh_token'] ?? $refresh_token );
			$all[ self::GATEWAY_ID ]      = $credentials;
			update_option( self::CREDENTIALS_OPTION, $all, false );

			return $credentials;
		}

		/**
		 * Fetch merchant profile and locations from Square API.
		 *
		 * @param string $access_token Access token.
		 * @param string $payment_mode test|live.
		 * @param string $merchant_id  Merchant ID.
		 * @return array
		 */
		private function fetch_merchant_details( $access_token, $payment_mode, $merchant_id ) {
			$base = 'test' === $payment_mode
				? 'https://connect.squareupsandbox.com'
				: 'https://connect.squareup.com';

			$headers = array(
				'Authorization'  => 'Bearer ' . $access_token,
				'Square-Version' => self::SQUARE_API_VERSION,
				'Content-Type'   => 'application/json',
			);

			$account_name      = '';
			$merchant_currency = '';
			$locations         = array();

			if ( '' !== $merchant_id ) {
				$merchant_response = wp_remote_get(
					$base . '/v2/merchants/' . rawurlencode( $merchant_id ),
					array(
						'timeout' => 15,
						'headers' => $headers,
					)
				);

				if ( ! is_wp_error( $merchant_response ) ) {
					$merchant_body = json_decode( wp_remote_retrieve_body( $merchant_response ), true );
					if ( is_array( $merchant_body ) && ! empty( $merchant_body['merchant'] ) && is_array( $merchant_body['merchant'] ) ) {
						$merchant     = $merchant_body['merchant'];
						$account_name = sanitize_text_field( $merchant['business_name'] ?? $merchant['name'] ?? '' );
					}
				}
			}

			$locations_response = wp_remote_get(
				$base . '/v2/locations',
				array(
					'timeout' => 15,
					'headers' => $headers,
				)
			);

			if ( ! is_wp_error( $locations_response ) ) {
				$locations_body = json_decode( wp_remote_retrieve_body( $locations_response ), true );
				if ( is_array( $locations_body ) && ! empty( $locations_body['locations'] ) && is_array( $locations_body['locations'] ) ) {
					foreach ( $locations_body['locations'] as $location ) {
						if ( ! is_array( $location ) || empty( $location['id'] ) ) {
							continue;
						}

						if ( '' === $merchant_currency && ! empty( $location['currency'] ) ) {
							$merchant_currency = sanitize_text_field( $location['currency'] );
						}

						$locations[] = array(
							'id'   => sanitize_text_field( $location['id'] ),
							'name' => sanitize_text_field( $location['name'] ?? $location['business_name'] ?? $location['id'] ),
						);
					}
				}
			}

			return array(
				'account_name'        => $account_name,
				'merchant_currency'   => $merchant_currency,
				'business_locations'  => $locations,
			);
		}

		private function store_credentials( $credentials ) {
			$all = get_option( self::CREDENTIALS_OPTION, array() );

			if ( ! is_array( $all ) ) {
				$all = array();
			}

			$all[ self::GATEWAY_ID ] = array(
				'merchant_id'   => sanitize_text_field( $credentials['merchant_id'] ?? '' ),
				'access_token'  => trim( (string) ( $credentials['access_token'] ?? '' ) ),
				'refresh_token' => trim( (string) ( $credentials['refresh_token'] ?? '' ) ),
				'payment_mode'  => in_array( $credentials['payment_mode'] ?? 'test', array( 'live', 'test' ), true )
					? $credentials['payment_mode']
					: 'test',
			);

			update_option( self::CREDENTIALS_OPTION, $all, false );
		}

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

		public static function sanitize_locations( $locations ) {
			if ( ! is_array( $locations ) ) {
				return array();
			}

			$sanitized = array();

			foreach ( $locations as $location ) {
				if ( ! is_array( $location ) ) {
					continue;
				}

				$id = sanitize_text_field( $location['id'] ?? '' );
				if ( '' === $id ) {
					continue;
				}

				$sanitized[] = array(
					'id'   => $id,
					'name' => sanitize_text_field( $location['name'] ?? $location['business_name'] ?? $id ),
				);
			}

			return $sanitized;
		}

		private function get_notice_transient_key() {
			$user_id = get_current_user_id();

			return $user_id
				? 'gutena_forms_square_connect_notice_' . $user_id
				: 'gutena_forms_square_connect_notice';
		}

		private function set_connect_notice( $type, $message ) {
			set_transient(
				$this->get_notice_transient_key(),
				array(
					'type'    => $type,
					'message' => $message,
				),
				60
			);
		}

		private function redirect_to_square_settings() {
			wp_safe_redirect( admin_url( 'admin.php?page=gutena-forms#/settings/settings/payment/square' ) );
			exit;
		}

		public static function consume_connect_notice() {
			$user_id = get_current_user_id();
			$key     = $user_id
				? 'gutena_forms_square_connect_notice_' . $user_id
				: 'gutena_forms_square_connect_notice';
			$notice  = get_transient( $key );
			delete_transient( $key );

			if ( is_array( $notice ) ) {
				return $notice;
			}

			return null;
		}
	}

	Gutena_Forms_Square_Connect::get_instance();
endif;
