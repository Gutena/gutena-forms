<?php
/**
 * Abstract payment gateway settings.
 *
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Payment_Gateway_Settings' ) && class_exists( 'Gutena_Forms_Forms_Settings' ) ) :
	abstract class Gutena_Forms_Payment_Gateway_Settings extends Gutena_Forms_Forms_Settings {

		public $id          = '';
		public $title       = '';
		public $description = '';
		public $is_enabled  = false;
		public $settings    = array();

		public function __construct() {
			$settings = get_option( 'gutena_forms__payment_settings', array() );

			if ( is_array( $settings ) && isset( $settings[ $this->id ] ) ) {
				$this->settings = $settings[ $this->id ];
			}

			$this->is_enabled = ! empty( $this->settings['enable'] );
		}

		public function save_settings( $settings ) {
			$all_settings = get_option( 'gutena_forms__payment_settings', array() );

			if ( ! is_array( $all_settings ) ) {
				$all_settings = array();
			}

			$previous = isset( $all_settings[ $this->id ] ) && is_array( $all_settings[ $this->id ] )
				? $all_settings[ $this->id ]
				: array();

			$incoming = is_array( $settings ) ? $settings : array();
			$merged   = array_merge( $previous, $incoming );

			$allowed_keys = array(
				'enable',
				'payment_mode',
				'currency',
				'currency_sign_position',
				'account_name',
				'connected',
				'webhook_connected',
				'webhook_slots_exceeded',
			);
			$sanitized    = array();

			foreach ( $allowed_keys as $key ) {
				if ( array_key_exists( $key, $merged ) ) {
					$sanitized[ $key ] = $merged[ $key ];
				}
			}

			$sanitized['payment_mode'] = in_array( $sanitized['payment_mode'] ?? 'test', array( 'live', 'test' ), true )
				? $sanitized['payment_mode']
				: 'test';

			$sanitized['currency']               = sanitize_text_field( $sanitized['currency'] ?? 'USD' );
			$sanitized['currency_sign_position'] = class_exists( 'Gutena_Forms_Stripe_Connect' )
				? Gutena_Forms_Stripe_Connect::sanitize_sign_position( $sanitized['currency_sign_position'] ?? 'left' )
				: 'left';
			$sanitized['account_name']           = sanitize_text_field( $sanitized['account_name'] ?? '' );
			$sanitized['enable']                 = ! empty( $sanitized['enable'] );
			$sanitized['connected']              = ! empty( $sanitized['connected'] );
			$sanitized['webhook_connected']      = ! empty( $sanitized['webhook_connected'] );
			$sanitized['webhook_slots_exceeded'] = ! empty( $sanitized['webhook_slots_exceeded'] );

			$this->is_enabled    = ! empty( $sanitized['enable'] );
			$sanitized['enable'] = $this->is_enabled;

			$all_settings[ $this->id ] = $sanitized;
			update_option( 'gutena_forms__payment_settings', $all_settings );

			$this->settings = $sanitized;

			return true;
		}
	}
endif;
