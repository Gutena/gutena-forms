<?php

/**

 * Entry payment storage and helpers.

 *

 * @package Gutena Forms

 */



defined( 'ABSPATH' ) || exit;



if ( ! class_exists( 'Gutena_Forms_Entry_Payment' ) ) :

	class Gutena_Forms_Entry_Payment {



		const META_TYPE = 'payment_stripe';



		private static $instance;



		/** @var Gutena_Forms_Store */

		private $store;



		public static function get_instance() {

			if ( is_null( self::$instance ) ) {

				self::$instance = new self();

			}

			return self::$instance;

		}



		private function __construct() {

			$this->store = Gutena_Forms_Store::get_instance();

		}



		/**

		 * @param int $entry_id Entry ID.

		 * @return array|null

		 */

		public function get_by_entry_id( $entry_id ) {

			$row = $this->get_table_row_by_entry_id( $entry_id );



			if ( is_array( $row ) ) {

				return $this->row_to_payment_array( $row );

			}



			return $this->get_meta_payment( $entry_id );

		}

		/**
		 * Find an entry ID by Stripe PaymentIntent / transaction ID.
		 *
		 * @param string $transaction_id PaymentIntent or transaction ID.
		 * @return int
		 */
		public function get_entry_id_by_transaction_id( $transaction_id ) {
			global $wpdb;

			$transaction_id = sanitize_text_field( $transaction_id );
			if ( empty( $transaction_id ) ) {
				return 0;
			}

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$entry_id = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT entry_id FROM {$this->store->table_gutenaforms_payments} WHERE transaction_id = %s OR external_payment_id = %s LIMIT 1",
					$transaction_id,
					$transaction_id
				)
			);

			return absint( $entry_id );
		}



		/**

		 * @param int   $entry_id Entry ID.

		 * @param array $payment  Payment payload.

		 * @return bool

		 */

		public function save_for_entry( $entry_id, $payment ) {

			$entry_id = absint( $entry_id );

			if ( ! $entry_id || ! is_array( $payment ) ) {

				return false;

			}

			$this->ensure_payments_table();



			$form_id = isset( $payment['form_id'] ) ? absint( $payment['form_id'] ) : Gutena_Forms_Entries_Model::get_instance()->get_form_id_by_entry_id( $entry_id );



			if ( $form_id && empty( $payment['form_id'] ) ) {

				$payment['form_id'] = $form_id;

			}



			$table_saved = $this->upsert_table_row( $entry_id, $payment );

			$meta_saved  = $this->save_meta_payment( $entry_id, $payment );



			return $table_saved || $meta_saved;

		}



		/**

		 * @param int   $entry_id Entry ID.

		 * @param array $partial  Partial payment update.

		 * @return bool

		 */

		public function update_for_entry( $entry_id, $partial ) {

			$current = $this->get_by_entry_id( $entry_id );

			if ( ! is_array( $current ) ) {

				return false;

			}



			return $this->save_for_entry( $entry_id, array_merge( $current, $partial ) );

		}



		/**

		 * @param int   $entry_id Entry ID.

		 * @param array $log      Log row.

		 * @return bool

		 */

		public function append_log( $entry_id, $log ) {

			$current = $this->get_by_entry_id( $entry_id );

			if ( ! is_array( $current ) ) {

				return false;

			}



			if ( empty( $current['logs'] ) || ! is_array( $current['logs'] ) ) {

				$current['logs'] = array();

			}



			$current['logs'][] = $log;



			return $this->save_for_entry( $entry_id, $current );

		}



		/**

		 * List payment rows for the dashboard payments screen.

		 *

		 * @return array

		 */

		public function get_all_list_items() {

			global $wpdb;



			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$rows = $wpdb->get_results(
				"SELECT p.*, e.added_time AS entry_added_time
				FROM {$this->store->table_gutenaforms_payments} p
				LEFT JOIN {$this->store->table_gutenaforms_entries} e ON e.entry_id = p.entry_id
				WHERE e.entry_id IS NULL OR e.trash = 0
				ORDER BY p.added_time DESC",
				ARRAY_A
			);



			if ( empty( $rows ) || ! is_array( $rows ) ) {

				return $this->get_all_list_items_from_meta();

			}



			$items = array();



			foreach ( $rows as $row ) {

				$payment = $this->row_to_payment_array( $row );
				$payment = $this->maybe_backfill_payment_type( $payment, absint( $row['entry_id'] ) );

				$items[] = $this->format_list_item( $payment, absint( $row['entry_id'] ), $row['entry_added_time'] ?? '' );

			}



			return $items;

		}



		/**

		 * Format payment for REST (no secrets).

		 *

		 * @param int $entry_id Entry ID.

		 * @return array

		 */

		public function get_public_details( $entry_id ) {

			$payment = $this->get_by_entry_id( $entry_id );



			if ( ! is_array( $payment ) ) {

				return array(

					'has_payment' => false,

				);

			}

			$payment = $this->maybe_backfill_payment_type( $payment, absint( $entry_id ) );

			$form_id   = isset( $payment['form_id'] ) ? absint( $payment['form_id'] ) : 0;
			$form_name = isset( $payment['form_name'] ) ? sanitize_text_field( $payment['form_name'] ) : '';

			if ( ( ! $form_name || 0 === strpos( $form_name, 'gutena_forms_id_' ) ) && $form_id ) {
				$model_name = Gutena_Forms_Forms_Model::get_instance()->get_name_by_id( $form_id );
				if ( ! empty( $model_name ) && 0 !== strpos( $model_name, 'gutena_forms_id_' ) ) {
					$form_name = $model_name;
				}
			}

			if ( ! $form_name || 0 === strpos( $form_name, 'gutena_forms_id_' ) ) {
				$form_name = __( 'Contact Form', 'gutena-forms' );
			}



			$amount_cents = isset( $payment['amount'] ) ? absint( $payment['amount'] ) : 0;

			$refunded     = isset( $payment['refunded_amount'] ) ? absint( $payment['refunded_amount'] ) : 0;

			$currency     = sanitize_text_field( $payment['currency'] ?? 'USD' );

			$payment_type = sanitize_text_field( $payment['payment_type'] ?? 'one_time' );
			$gateway      = sanitize_text_field( $payment['gateway'] ?? 'stripe' );
			$dashboard_url = esc_url_raw(
				$payment['gateway_dashboard_url']
					?? $payment['square_dashboard_url']
					?? $payment['stripe_dashboard_url']
					?? ''
			);

			if ( '' === $dashboard_url && class_exists( 'Gutena_Forms_Square_Payment_Service' ) && 'square' === $gateway ) {
				$dashboard_url = Gutena_Forms_Square_Payment_Service::get_dashboard_url(
					sanitize_text_field( $payment['transaction_id'] ?? $payment['payment_id'] ?? '' ),
					sanitize_text_field( $payment['payment_mode'] ?? 'test' )
				);
			}

			return array(

				'has_payment'          => true,

				'gateway'              => $gateway,

				'gateway_label'        => self::gateway_label( $gateway, $payment['gateway_label'] ?? '' ),

				'payment_id'           => sanitize_text_field( $payment['payment_id'] ?? '' ),

				'payment_mode'         => sanitize_text_field( $payment['payment_mode'] ?? 'test' ),

				'payment_method'       => sanitize_text_field( $payment['payment_method'] ?? 'Stripe' ),

				'payment_type'         => $payment_type,

				'payment_type_label'   => self::payment_type_label( $payment_type ),

				'is_subscription'    => 'subscription' === $payment_type,

				'transaction_id'     => sanitize_text_field( $payment['transaction_id'] ?? '' ),

				'amount'               => $amount_cents,

				'amount_formatted'   => self::format_amount( $amount_cents, $currency ),

				'currency'             => $currency,

				'status'               => sanitize_text_field( $payment['status'] ?? 'pending' ),

				'status_label'         => self::status_label( $payment['status'] ?? 'pending' ),

				'customer_name'        => sanitize_text_field( $payment['customer_name'] ?? '' ),

				'customer_email'       => sanitize_email( $payment['customer_email'] ?? '' ),

				'received_on'          => sanitize_text_field( $payment['received_on'] ?? '' ),

				'transaction_date'     => sanitize_text_field( $payment['transaction_date'] ?? '' ),

				'stripe_dashboard_url' => esc_url_raw( $payment['stripe_dashboard_url'] ?? '' ),

				'gateway_dashboard_url' => $dashboard_url,

				'form_id'              => $form_id,

				'form_name'            => $form_name ? $form_name : __( 'Unknown Form', 'gutena-forms' ),

				'form_edit_url'        => $form_id ? admin_url( 'admin.php?page=gutena-forms#/settings/entries/' . $form_id ) : '',

				'user_id'              => isset( $payment['user_id'] ) ? absint( $payment['user_id'] ) : 0,

				'refundable_amount'    => max( 0, $amount_cents - $refunded ),

				'refundable_formatted' => self::format_amount( max( 0, $amount_cents - $refunded ), $currency ),

				'can_refund'           => self::can_refund( $payment ),

				'logs'                 => self::sanitize_logs( $payment['logs'] ?? array() ),

				'billing'              => array(

					'amount'           => self::format_amount( $amount_cents, $currency ),

					'status'           => self::status_label( $payment['status'] ?? 'pending' ),

					'transaction_date' => sanitize_text_field( $payment['transaction_date'] ?? '' ),

					'can_refund'       => self::can_refund( $payment ),

				),

			);

		}



		/**

		 * Copy legacy meta rows into the payments table.

		 *

		 * @return void

		 */

		public function migrate_meta_to_table() {

			global $wpdb;



			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

			$rows = $wpdb->get_results(

				$wpdb->prepare(

					"SELECT entry_id, metadata FROM {$this->store->table_gutenaforms_meta} WHERE data_type = %s",

					self::META_TYPE

				),

				ARRAY_A

			);



			if ( empty( $rows ) || ! is_array( $rows ) ) {

				return;

			}



			foreach ( $rows as $row ) {

				$entry_id = absint( $row['entry_id'] ?? 0 );

				$data     = json_decode( $row['metadata'] ?? '', true );



				if ( ! $entry_id || ! is_array( $data ) ) {

					continue;

				}



				if ( $this->get_table_row_by_entry_id( $entry_id ) ) {

					continue;

				}



				$this->upsert_table_row( $entry_id, $data );

			}

		}



		/**

		 * @param array $payment Payment row.

		 * @return bool

		 */

		public static function can_refund( $payment ) {

			if ( ! is_array( $payment ) ) {

				return false;

			}



			$status = sanitize_text_field( $payment['status'] ?? '' );

			if ( in_array( $status, array( 'refunded', 'failed', 'pending' ), true ) ) {

				return false;

			}



			$amount   = isset( $payment['amount'] ) ? absint( $payment['amount'] ) : 0;

			$refunded = isset( $payment['refunded_amount'] ) ? absint( $payment['refunded_amount'] ) : 0;



			return ( $amount - $refunded ) > 0 && ! empty( $payment['transaction_id'] );

		}



		/**

		 * @param int    $cents    Amount in cents.

		 * @param string $currency Currency code.

		 * @return string

		 */

		public static function format_amount( $cents, $currency = 'USD' ) {

			$amount = number_format( absint( $cents ) / 100, 2 );

			$symbol = '$';



			if ( 'EUR' === $currency ) {

				$symbol = '€';

			} elseif ( 'GBP' === $currency ) {

				$symbol = '£';

			}



			return $symbol . $amount;

		}



		/**

		 * @param string $status Payment status key.

		 * @return string

		 */

		public static function status_label( $status ) {

			$labels = array(

				'succeeded'  => __( 'Succeeded', 'gutena-forms' ),

				'pending'    => __( 'Pending', 'gutena-forms' ),

				'failed'     => __( 'Failed', 'gutena-forms' ),

				'refunded'   => __( 'Refunded', 'gutena-forms' ),

				'processing' => __( 'Processing', 'gutena-forms' ),

				'completed'  => __( 'Succeeded', 'gutena-forms' ),

				'approved'   => __( 'Succeeded', 'gutena-forms' ),

				'canceled'   => __( 'Canceled', 'gutena-forms' ),

			);



			return $labels[ $status ] ?? ucfirst( sanitize_text_field( $status ) );

		}



		/**
		 * Human-readable gateway label.
		 *
		 * @param string $gateway Gateway slug.
		 * @param string $fallback Stored label.
		 * @return string
		 */
		public static function gateway_label( $gateway, $fallback = '' ) {
			if ( '' !== $fallback ) {
				return sanitize_text_field( $fallback );
			}

			$labels = array(
				'stripe' => 'Stripe',
				'square' => 'Square',
			);

			return $labels[ $gateway ] ?? ucfirst( sanitize_text_field( $gateway ) );

		}



		/**
		 * Normalize Square payment status keys for Gutena records.
		 *
		 * @param string $status Square or internal status.
		 * @return string
		 */
		public static function normalize_square_status( $status ) {
			$status = strtolower( sanitize_text_field( $status ) );

			$map = array(
				'completed' => 'succeeded',
				'approved'  => 'succeeded',
				'pending'   => 'pending',
				'failed'    => 'failed',
				'canceled'  => 'failed',
				'refunded'  => 'refunded',
			);

			return $map[ $status ] ?? $status;
		}



		/**

		 * @param string $type Payment type key.

		 * @return string

		 */

		public static function payment_type_label( $type ) {

			$labels = array(

				'one_time'     => __( 'One Time', 'gutena-forms' ),

				'subscription' => __( 'Subscription', 'gutena-forms' ),

			);



			return $labels[ $type ] ?? ucfirst( sanitize_text_field( str_replace( '_', ' ', $type ) ) );

		}



		/**

		 * @param int $entry_id Entry ID.

		 * @return array|null

		 */

		private function get_table_row_by_entry_id( $entry_id ) {

			global $wpdb;



			$entry_id = absint( $entry_id );

			if ( ! $entry_id ) {

				return null;

			}



			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

			$row = $wpdb->get_row(

				$wpdb->prepare(

					"SELECT * FROM {$this->store->table_gutenaforms_payments} WHERE entry_id = %d LIMIT 1",

					$entry_id

				),

				ARRAY_A

			);



			return is_array( $row ) ? $row : null;

		}



		/**

		 * @param int   $entry_id Entry ID.

		 * @param array $payment  Payment payload.

		 * @return bool

		 */

		private function upsert_table_row( $entry_id, $payment ) {

			global $wpdb;



			$entry_id = absint( $entry_id );

			$row      = $this->payment_array_to_row( $entry_id, $payment );

			$existing = $this->get_table_row_by_entry_id( $entry_id );

			$formats = array(
				'%d', // entry_id
				'%d', // form_id
				'%s', // gateway
				'%s', // external_payment_id
				'%s', // transaction_id
				'%s', // payment_type
				'%s', // payment_mode
				'%s', // payment_method
				'%d', // amount
				'%d', // refunded_amount
				'%s', // currency
				'%s', // status
				'%s', // customer_name
				'%s', // customer_email
				'%s', // transaction_date
				'%s', // received_on
				'%s', // stripe_dashboard_url
				'%s', // metadata
			);



			if ( $existing ) {

				unset( $row['added_time'] );

				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

				return false !== $wpdb->update(

					$this->store->table_gutenaforms_payments,

					$row,

					array( 'entry_id' => $entry_id ),

					$formats,

					array( '%d' )

				);

			}



			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery

			return false !== $wpdb->insert( $this->store->table_gutenaforms_payments, $row, $formats );

		}

		/**
		 * Ensure the payments table exists before writing payment rows.
		 *
		 * @return void
		 */
		private function ensure_payments_table() {
			if ( class_exists( 'Gutena_Forms_Create_Store' ) ) {
				Gutena_Forms_Create_Store::get_instance()->maybe_upgrade_tables();
			}
		}



		/**
		 * Resolve stored payment type from row data and metadata.
		 *
		 * @param array $row      Database row.
		 * @param array $metadata Decoded metadata.
		 * @return string
		 */
		private function resolve_stored_payment_type( $row, $metadata ) {
			$payment_type = sanitize_text_field( $row['payment_type'] ?? 'one_time' );

			if ( 'subscription' === $payment_type ) {
				return $payment_type;
			}

			if ( ! empty( $metadata['payment_type'] ) && 'subscription' === sanitize_key( $metadata['payment_type'] ) ) {
				return 'subscription';
			}

			if ( ! empty( $metadata['subscription_id'] ) ) {
				return 'subscription';
			}

			return $payment_type;
		}

		/**
		 * Backfill legacy rows that were saved with the wrong payment type.
		 *
		 * @param array $payment  Payment payload.
		 * @param int   $entry_id Entry ID.
		 * @return array
		 */
		private function maybe_backfill_payment_type( $payment, $entry_id ) {
			if ( ! is_array( $payment ) ) {
				return $payment;
			}

			$entry_id = absint( $entry_id );
			$type     = sanitize_text_field( $payment['payment_type'] ?? 'one_time' );

			if ( 'subscription' === $type || ! empty( $payment['subscription_id'] ) ) {
				if ( 'subscription' !== $type && ! empty( $payment['subscription_id'] ) ) {
					$payment['payment_type'] = 'subscription';
				}

				return $payment;
			}

			$payment_id = sanitize_text_field( $payment['payment_id'] ?? $payment['transaction_id'] ?? '' );
			if ( empty( $payment_id ) || ! class_exists( 'Gutena_Forms_Stripe_Intent_Service' ) ) {
				return $payment;
			}

			$intent = Gutena_Forms_Stripe_Intent_Service::get_instance()->retrieve_payment_intent( $payment_id );
			if ( is_wp_error( $intent ) || ! is_array( $intent ) ) {
				return $payment;
			}

			$resolved = 'one_time';
			if ( ! empty( $intent['metadata']['payment_type'] ) && 'subscription' === sanitize_key( $intent['metadata']['payment_type'] ) ) {
				$resolved = 'subscription';
			} elseif ( ! empty( $intent['metadata']['subscription_id'] ) || ! empty( $intent['invoice'] ) ) {
				$resolved = 'subscription';
			}

			if ( 'subscription' !== $resolved ) {
				return $payment;
			}

			$payment['payment_type'] = 'subscription';
			$payment['subscription_id'] = sanitize_text_field( $intent['metadata']['subscription_id'] ?? '' );
			if ( empty( $payment['subscription_plan_name'] ) && ! empty( $intent['metadata']['subscription_plan_name'] ) ) {
				$payment['subscription_plan_name'] = sanitize_text_field( $intent['metadata']['subscription_plan_name'] );
			}

			if ( $entry_id ) {
				$this->update_for_entry(
					$entry_id,
					array(
						'payment_type'           => 'subscription',
						'subscription_id'        => $payment['subscription_id'],
						'subscription_plan_name' => sanitize_text_field( $payment['subscription_plan_name'] ?? '' ),
					)
				);
			}

			return $payment;
		}

		/**

		 * @param array $row Database row.

		 * @return array

		 */

		private function row_to_payment_array( $row ) {

			$metadata = array();



			if ( ! empty( $row['metadata'] ) ) {

				$decoded = json_decode( $row['metadata'], true );

				if ( is_array( $decoded ) ) {

					$metadata = $decoded;

				}

			}



			return array_merge(

				array(

					'gateway'              => sanitize_text_field( $row['gateway'] ?? 'stripe' ),

					'gateway_label'        => self::gateway_label( $row['gateway'] ?? 'stripe', $metadata['gateway_label'] ?? '' ),

					'payment_id'           => sanitize_text_field( $row['external_payment_id'] ?? '' ),

					'payment_mode'         => sanitize_text_field( $row['payment_mode'] ?? 'test' ),

					'payment_method'       => sanitize_text_field( $row['payment_method'] ?? 'Stripe' ),

					'payment_type'         => $this->resolve_stored_payment_type( $row, $metadata ),

					'transaction_id'       => sanitize_text_field( $row['transaction_id'] ?? '' ),

					'amount'               => absint( $row['amount'] ?? 0 ),

					'refunded_amount'      => absint( $row['refunded_amount'] ?? 0 ),

					'currency'             => sanitize_text_field( $row['currency'] ?? 'USD' ),

					'status'               => sanitize_text_field( $row['status'] ?? 'pending' ),

					'customer_name'        => sanitize_text_field( $row['customer_name'] ?? '' ),

					'customer_email'       => sanitize_email( $row['customer_email'] ?? '' ),

					'transaction_date'     => sanitize_text_field( $row['transaction_date'] ?? '' ),

					'received_on'          => sanitize_text_field( $row['received_on'] ?? '' ),

					'stripe_dashboard_url' => esc_url_raw( $row['stripe_dashboard_url'] ?? '' ),

					'gateway_dashboard_url' => esc_url_raw(
						$metadata['gateway_dashboard_url']
							?? $metadata['square_dashboard_url']
							?? $row['stripe_dashboard_url']
							?? ''
					),

					'form_id'              => absint( $row['form_id'] ?? 0 ),

					'logs'                 => isset( $metadata['logs'] ) && is_array( $metadata['logs'] ) ? $metadata['logs'] : array(),

				),

				$metadata

			);

		}



		/**

		 * @param int   $entry_id Entry ID.

		 * @param array $payment  Payment payload.

		 * @return array

		 */

		private function payment_array_to_row( $entry_id, $payment ) {

			$logs = isset( $payment['logs'] ) && is_array( $payment['logs'] ) ? $payment['logs'] : array();



			return array(

				'entry_id'             => absint( $entry_id ),

				'form_id'              => absint( $payment['form_id'] ?? 0 ),

				'gateway'              => sanitize_text_field( $payment['gateway'] ?? 'stripe' ),

				'external_payment_id'  => sanitize_text_field( $payment['payment_id'] ?? '' ),

				'transaction_id'       => sanitize_text_field( $payment['transaction_id'] ?? '' ),

				'payment_type'         => sanitize_text_field( $payment['payment_type'] ?? 'one_time' ),

				'payment_mode'         => sanitize_text_field( $payment['payment_mode'] ?? 'test' ),

				'payment_method'       => sanitize_text_field( $payment['payment_method'] ?? 'Stripe' ),

				'amount'               => absint( $payment['amount'] ?? 0 ),

				'refunded_amount'      => absint( $payment['refunded_amount'] ?? 0 ),

				'currency'             => sanitize_text_field( $payment['currency'] ?? 'USD' ),

				'status'               => sanitize_text_field( $payment['status'] ?? 'pending' ),

				'customer_name'        => sanitize_text_field( $payment['customer_name'] ?? '' ),

				'customer_email'       => sanitize_email( $payment['customer_email'] ?? '' ),

				'transaction_date'     => sanitize_text_field( $payment['transaction_date'] ?? '' ),

				'received_on'          => sanitize_text_field( $payment['received_on'] ?? '' ),

				'stripe_dashboard_url' => esc_url_raw( $payment['stripe_dashboard_url'] ?? '' ),

				'metadata'             => wp_json_encode(
					array(
						'logs'                   => $logs,
						'form_name'              => sanitize_text_field( $payment['form_name'] ?? '' ),
						'payment_type'           => sanitize_text_field( $payment['payment_type'] ?? 'one_time' ),
						'subscription_id'        => sanitize_text_field( $payment['subscription_id'] ?? '' ),
						'subscription_plan_name' => sanitize_text_field( $payment['subscription_plan_name'] ?? '' ),
						'gateway_dashboard_url'  => esc_url_raw(
							$payment['gateway_dashboard_url']
								?? $payment['square_dashboard_url']
								?? $payment['stripe_dashboard_url']
								?? ''
						),
						'refund_notes'           => sanitize_text_field( $payment['refund_notes'] ?? '' ),
					)
				),

			);

		}



		/**

		 * @param array  $payment    Payment payload.

		 * @param int    $entry_id   Entry ID.

		 * @param string $added_time Entry added time.

		 * @return array

		 */

		private function format_list_item( $payment, $entry_id, $added_time = '' ) {

			$form_id      = isset( $payment['form_id'] ) ? absint( $payment['form_id'] ) : 0;

			$form_name    = isset( $payment['form_name'] ) ? sanitize_text_field( $payment['form_name'] ) : '';

			$amount_cents = isset( $payment['amount'] ) ? absint( $payment['amount'] ) : 0;

			$currency     = sanitize_text_field( $payment['currency'] ?? 'USD' );

			$status       = sanitize_text_field( $payment['status'] ?? 'pending' );

			$payment_type = sanitize_text_field( $payment['payment_type'] ?? 'one_time' );



			if ( ( ! $form_name || 0 === strpos( $form_name, 'gutena_forms_id_' ) ) && $form_id ) {
				$model_name = Gutena_Forms_Forms_Model::get_instance()->get_name_by_id( $form_id );
				if ( ! empty( $model_name ) && 0 !== strpos( $model_name, 'gutena_forms_id_' ) ) {
					$form_name = $model_name;
				}
			}

			if ( ! $form_name || 0 === strpos( $form_name, 'gutena_forms_id_' ) ) {
				$form_name = __( 'Contact Form', 'gutena-forms' );
			}



			return array(

				'entry_id'            => absint( $entry_id ),

				'payment_id'          => sanitize_text_field( $payment['payment_id'] ?? '' ),

				'form_id'             => $form_id,

				'form_name'           => $form_name ? $form_name : __( 'Unknown Form', 'gutena-forms' ),

				'amount_formatted'    => self::format_amount( $amount_cents, $currency ),

				'payment_type'        => $payment_type,

				'payment_type_label'  => self::payment_type_label( $payment_type ),

				'is_subscription'     => 'subscription' === $payment_type,

				'status'              => $status,

				'status_label'        => self::status_label( $status ),

				'transaction_date'    => sanitize_text_field( $payment['transaction_date'] ?? '' ),

				'customer_name'       => sanitize_text_field( $payment['customer_name'] ?? '' ),

				'customer_email'      => sanitize_email( $payment['customer_email'] ?? '' ),

				'gateway'             => sanitize_text_field( $payment['gateway'] ?? 'stripe' ),

				'gateway_label'       => sanitize_text_field( $payment['gateway_label'] ?? 'Stripe' ),

				'added_time'          => ! empty( $added_time ) ? gmdate( 'F j, Y h:i A', strtotime( $added_time ) ) : '',

				'datetime'            => sanitize_text_field( $payment['transaction_date'] ?? '' ) ?: ( ! empty( $added_time ) ? gmdate( 'Y-m-d h:i:s A', strtotime( $added_time ) ) : '' ),

			);

		}



		/**

		 * @return array

		 */

		private function get_all_list_items_from_meta() {

			global $wpdb;



			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

			$rows = $wpdb->get_results(

				$wpdb->prepare(

					"SELECT m.entry_id, m.metadata, e.form_id, e.added_time

					FROM {$this->store->table_gutenaforms_meta} m

					INNER JOIN {$this->store->table_gutenaforms_entries} e ON e.entry_id = m.entry_id

					WHERE m.data_type = %s AND e.trash = 0

					ORDER BY e.added_time DESC",

					self::META_TYPE

				),

				ARRAY_A

			);



			if ( empty( $rows ) || ! is_array( $rows ) ) {

				return array();

			}



			$items = array();



			foreach ( $rows as $row ) {

				$data = json_decode( $row['metadata'] ?? '', true );

				if ( ! is_array( $data ) ) {

					continue;

				}



				if ( empty( $data['form_id'] ) && ! empty( $row['form_id'] ) ) {

					$data['form_id'] = absint( $row['form_id'] );

				}



				$items[] = $this->format_list_item( $data, absint( $row['entry_id'] ), $row['added_time'] ?? '' );

			}



			return $items;

		}



		/**

		 * @param int $entry_id Entry ID.

		 * @return array|null

		 */

		private function get_meta_payment( $entry_id ) {

			global $wpdb;



			$entry_id = absint( $entry_id );

			if ( ! $entry_id ) {

				return null;

			}



			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

			$row = $wpdb->get_row(

				$wpdb->prepare(

					"SELECT metadata FROM {$this->store->table_gutenaforms_meta} WHERE entry_id = %d AND data_type = %s LIMIT 1",

					$entry_id,

					self::META_TYPE

				),

				ARRAY_A

			);



			if ( empty( $row['metadata'] ) ) {

				return null;

			}



			$data = json_decode( $row['metadata'], true );

			return is_array( $data ) ? $data : null;

		}



		/**

		 * @param int   $entry_id Entry ID.

		 * @param array $payment  Payment payload.

		 * @return bool

		 */

		private function save_meta_payment( $entry_id, $payment ) {

			global $wpdb;



			$entry_id = absint( $entry_id );

			$form_id  = isset( $payment['form_id'] ) ? absint( $payment['form_id'] ) : Gutena_Forms_Entries_Model::get_instance()->get_form_id_by_entry_id( $entry_id );

			$user_id  = get_current_user_id();

			$encoded  = wp_json_encode( $payment );



			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

			$existing_id = $wpdb->get_var(

				$wpdb->prepare(

					"SELECT id FROM {$this->store->table_gutenaforms_meta} WHERE entry_id = %d AND data_type = %s LIMIT 1",

					$entry_id,

					self::META_TYPE

				)

			);



			if ( $existing_id ) {

				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

				return false !== $wpdb->update(

					$this->store->table_gutenaforms_meta,

					array(

						'metadata'      => $encoded,

						'modified_time' => current_time( 'mysql', true ),

					),

					array( 'id' => (int) $existing_id ),

					array( '%s', '%s' ),

					array( '%d' )

				);

			}



			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery

			return false !== $wpdb->insert(

				$this->store->table_gutenaforms_meta,

				array(

					'form_id'   => absint( $form_id ),

					'entry_id'  => $entry_id,

					'user_id'   => absint( $user_id ),

					'data_type' => self::META_TYPE,

					'metadata'  => $encoded,

				),

				array( '%d', '%d', '%d', '%s', '%s' )

			);

		}



		/**

		 * @param array $logs Raw logs.

		 * @return array

		 */

		private static function sanitize_logs( $logs ) {

			if ( ! is_array( $logs ) ) {

				return array();

			}



			$clean = array();



			foreach ( $logs as $log ) {

				if ( ! is_array( $log ) ) {

					continue;

				}



				$clean[] = array(

					'event'          => sanitize_text_field( $log['event'] ?? '' ),

					'transaction_id' => sanitize_text_field( $log['transaction_id'] ?? '' ),

					'gateway'        => sanitize_text_field( $log['gateway'] ?? 'stripe' ),

					'amount'         => sanitize_text_field( $log['amount'] ?? '' ),

					'status'         => sanitize_text_field( $log['status'] ?? '' ),

					'user_id'        => isset( $log['user_id'] ) ? absint( $log['user_id'] ) : 0,

					'mode'           => sanitize_text_field( $log['mode'] ?? '' ),

					'created_at'     => sanitize_text_field( $log['created_at'] ?? '' ),

				);

			}



			return $clean;

		}

	}



	Gutena_Forms_Entry_Payment::get_instance();

endif;


