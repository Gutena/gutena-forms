<?php
/**
 * Square payment gateway settings.
 *
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Square' ) && class_exists( 'Gutena_Forms_Payment_Gateway_Settings' ) ) :
	class Gutena_Forms_Square extends Gutena_Forms_Payment_Gateway_Settings {

		private static $instance;

		public function __construct() {
			$this->id          = 'square';
			$this->title       = 'Square';
			$this->description = __( 'Accept payments via Square in your WordPress forms, and collect online payments securely.', 'gutena-forms' );

			parent::__construct();
		}

		public static function register_module() {
			add_filter(
				'gutena_forms__payment_gateways',
				function ( $gateways ) {
					$gateways['square'] = __CLASS__;
					return $gateways;
				}
			);

			self::get_instance();
		}

		public function get_settings() {
			$public = class_exists( 'Gutena_Forms_Square_Connect' )
				? Gutena_Forms_Square_Connect::get_public_settings()
				: array();

			$locations = $public['business_locations'] ?? ( $this->settings['business_locations'] ?? array() );

			return array(
				'id'          => $this->id,
				'title'       => __( 'Square Account Settings', 'gutena-forms' ),
				'description' => __( 'Connect your Square account to start accepting payments through your forms.', 'gutena-forms' ),
				'back'        => '/settings/settings/payment-methods',
				'fields'      => array(
					array(
						'type' => 'template',
						'name' => 'square-settings',
					),
				),
				'values'      => array(
					'payment_mode'           => $public['payment_mode'] ?? ( $this->settings['payment_mode'] ?? 'test' ),
					'connected_payment_mode' => $public['connected_payment_mode'] ?? ( $public['payment_mode'] ?? ( $this->settings['payment_mode'] ?? 'test' ) ),
					'connected'              => ! empty( $public['connected'] ) || ! empty( $this->settings['connected'] ),
					'account_name'           => $public['account_name'] ?? ( $this->settings['account_name'] ?? '' ),
					'merchant_currency'      => $public['merchant_currency'] ?? ( $this->settings['merchant_currency'] ?? '' ),
					'location_id'            => $public['location_id'] ?? ( $this->settings['location_id'] ?? '' ),
					'business_locations'     => is_array( $locations ) ? $locations : array(),
				),
			);
		}

		public function save_settings( $settings ) {
			if ( is_array( $settings ) ) {
				unset(
					$settings['connected'],
					$settings['account_name'],
					$settings['merchant_currency'],
					$settings['business_locations']
				);
			}

			$previous_global = class_exists( 'Gutena_Forms_Square_Connect' )
				? Gutena_Forms_Square_Connect::get_public_settings()
				: array();

			$all = get_option( 'gutena_forms__payment_settings', array() );
			if ( ! is_array( $all ) ) {
				$all = array();
			}

			$previous = isset( $all[ $this->id ] ) && is_array( $all[ $this->id ] )
				? $all[ $this->id ]
				: array();

			$incoming = is_array( $settings ) ? $settings : array();
			$merged   = array_merge( $previous, $incoming );

			$sanitized = array(
				'enable'       => ! empty( $merged['enable'] ),
				'payment_mode' => in_array( $merged['payment_mode'] ?? 'test', array( 'live', 'test' ), true )
					? $merged['payment_mode']
					: 'test',
				'location_id'  => sanitize_text_field( $merged['location_id'] ?? ( $previous['location_id'] ?? '' ) ),
			);

			foreach ( array( 'connected', 'account_name', 'merchant_currency', 'currency', 'business_locations' ) as $preserve_key ) {
				if ( array_key_exists( $preserve_key, $previous ) ) {
					$sanitized[ $preserve_key ] = $previous[ $preserve_key ];
				}
			}

			$all[ $this->id ]     = $sanitized;
			$this->settings       = $sanitized;
			$this->is_enabled     = ! empty( $sanitized['enable'] );
			$sanitized['enable']  = $this->is_enabled;
			$all[ $this->id ]     = $sanitized;

			update_option( 'gutena_forms__payment_settings', $all );

			if ( class_exists( 'Gutena_Forms_Settings_Migrator' ) ) {
				$next_global = class_exists( 'Gutena_Forms_Square_Connect' )
					? Gutena_Forms_Square_Connect::get_public_settings()
					: array();
				Gutena_Forms_Settings_Migrator::sync_global_module_to_forms(
					'paymentSquare',
					$previous_global,
					$next_global
				);
			}

			return true;
		}

		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}
	}

	Gutena_Forms_Square::register_module();
endif;
