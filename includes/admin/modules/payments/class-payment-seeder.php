<?php
/**
 * Seeds demo payment entries for local design testing.
 *
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Payment_Seeder' ) ) :
	class Gutena_Forms_Payment_Seeder {

		public static function register() {
			add_action( 'admin_init', array( __CLASS__, 'maybe_seed_demo_payments' ), 20 );
		}

		public static function maybe_seed_demo_payments() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			if ( ! class_exists( 'Gutena_Forms_Entry_Payment' ) ) {
				return;
			}

			self::maybe_seed_stripe_demo();
			self::maybe_seed_square_demo();
		}

		/**
		 * Seed a demo Stripe payment when the payments table is empty.
		 *
		 * @return void
		 */
		private static function maybe_seed_stripe_demo() {
			if ( get_option( 'gutena_forms_payment_demo_seeded' ) ) {
				return;
			}

			$payment_model = Gutena_Forms_Entry_Payment::get_instance();

			if ( ! empty( $payment_model->get_all_list_items() ) ) {
				update_option( 'gutena_forms_payment_demo_seeded', 1 );
				return;
			}

			$entry_id = self::create_demo_entry(
				array(
					'name'  => 'John Doe',
					'email' => 'john.doe@example.com',
				)
			);

			if ( ! $entry_id ) {
				return;
			}

			$payment_model->save_for_entry(
				$entry_id,
				self::get_stripe_demo_payment_payload( $entry_id )
			);

			update_option( 'gutena_forms_payment_demo_seeded', 1 );
		}

		/**
		 * Seed a demo Square payment for UI testing (Square icons, payment tab, etc.).
		 *
		 * @return void
		 */
		private static function maybe_seed_square_demo() {
			if ( get_option( 'gutena_forms_square_payment_demo_seeded' ) ) {
				return;
			}

			if ( self::has_gateway_payment( 'square' ) ) {
				update_option( 'gutena_forms_square_payment_demo_seeded', 1 );
				return;
			}

			$entry_id = self::create_demo_entry(
				array(
					'name'  => 'Jane Smith',
					'email' => 'jane.smith@example.com',
				)
			);

			if ( ! $entry_id ) {
				return;
			}

			Gutena_Forms_Entry_Payment::get_instance()->save_for_entry(
				$entry_id,
				self::get_square_demo_payment_payload( $entry_id )
			);

			update_option( 'gutena_forms_square_payment_demo_seeded', 1 );
		}

		/**
		 * @param string $gateway Gateway slug.
		 * @return bool
		 */
		private static function has_gateway_payment( $gateway ) {
			global $wpdb;

			$store = Gutena_Forms_Store::get_instance();

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$count = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$store->table_gutenaforms_payments} WHERE gateway = %s",
					sanitize_key( $gateway )
				)
			);

			return absint( $count ) > 0;
		}

		/**
		 * @param array $customer Optional name/email for the demo entry.
		 * @return int
		 */
		private static function create_demo_entry( $customer = array() ) {
			global $wpdb;

			$store = Gutena_Forms_Store::get_instance();

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$form = $wpdb->get_row(
				"SELECT form_id, form_name FROM {$store->table_gutenaforms} ORDER BY form_id ASC LIMIT 1",
				ARRAY_A
			);

			if ( empty( $form['form_id'] ) ) {
				return 0;
			}

			$form_id    = absint( $form['form_id'] );
			$user_id    = get_current_user_id();
			$name       = sanitize_text_field( $customer['name'] ?? 'John Doe' );
			$email      = sanitize_email( $customer['email'] ?? 'john.doe@example.com' );
			$entry_data = array(
				array(
					'label' => __( 'Name', 'gutena-forms' ),
					'value' => $name,
					'type'  => 'text',
				),
				array(
					'label' => __( 'Email', 'gutena-forms' ),
					'value' => $email,
					'type'  => 'email',
				),
			);

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$inserted = $wpdb->insert(
				$store->table_gutenaforms_entries,
				array(
					'form_id'      => $form_id,
					'user_id'      => 0,
					'modified_by'  => absint( $user_id ),
					'entry_data'   => maybe_serialize( $entry_data ),
					'entry_status' => 'read',
					'trash'        => 0,
				),
				array( '%d', '%d', '%d', '%s', '%s', '%d' )
			);

			if ( ! $inserted ) {
				return 0;
			}

			return absint( $wpdb->insert_id );
		}

		/**
		 * @param int $entry_id Entry ID.
		 * @return array
		 */
		private static function get_form_context( $entry_id ) {
			global $wpdb;

			$store   = Gutena_Forms_Store::get_instance();
			$form_id = Gutena_Forms_Entries_Model::get_instance()->get_form_id_by_entry_id( $entry_id );

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$form_name = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT form_name FROM {$store->table_gutenaforms} WHERE form_id = %d LIMIT 1",
					absint( $form_id )
				)
			);

			return array(
				'form_id'   => absint( $form_id ),
				'form_name' => $form_name ? sanitize_text_field( $form_name ) : __( 'Demo Form', 'gutena-forms' ),
			);
		}

		/**
		 * @param int $entry_id Entry ID.
		 * @return array
		 */
		private static function get_stripe_demo_payment_payload( $entry_id ) {
			$form    = self::get_form_context( $entry_id );
			$now     = gmdate( 'F j, Y h:i A' );
			$txn_id  = 'pi_demo_3NqExampleStripe01';

			return array(
				'gateway'               => 'stripe',
				'gateway_label'         => 'Stripe',
				'payment_id'            => 'PAY-DEMO-1001',
				'payment_mode'          => 'test',
				'payment_method'        => 'Stripe',
				'payment_type'          => 'one_time',
				'transaction_id'        => $txn_id,
				'amount'                => 4999,
				'currency'              => 'USD',
				'status'                => 'succeeded',
				'customer_name'         => 'John Doe',
				'customer_email'        => 'john.doe@example.com',
				'transaction_date'      => $now,
				'received_on'           => $now,
				'form_id'               => $form['form_id'],
				'form_name'             => $form['form_name'],
				'stripe_dashboard_url'  => 'https://dashboard.stripe.com/test/payments/' . $txn_id,
				'gateway_dashboard_url' => 'https://dashboard.stripe.com/test/payments/' . $txn_id,
				'logs'                  => array(
					array(
						'event'          => 'payment_verification',
						'transaction_id' => $txn_id,
						'gateway'        => 'stripe',
						'amount'         => '$49.99',
						'status'         => 'Succeeded',
						'user_id'        => get_current_user_id(),
						'mode'           => 'test',
						'created_at'     => $now,
					),
				),
			);
		}

		/**
		 * @param int $entry_id Entry ID.
		 * @return array
		 */
		private static function get_square_demo_payment_payload( $entry_id ) {
			$form    = self::get_form_context( $entry_id );
			$now     = gmdate( 'F j, Y h:i A' );
			$txn_id  = 'sq0cgp-DEMO1002SquarePay01';
			$dash_url = class_exists( 'Gutena_Forms_Square_Payment_Service' )
				? Gutena_Forms_Square_Payment_Service::get_dashboard_url( $txn_id, 'test' )
				: 'https://squareupsandbox.com/dashboard/sales/transactions/' . rawurlencode( $txn_id );

			return array(
				'gateway'               => 'square',
				'gateway_label'         => 'Square',
				'payment_id'            => 'PAY-DEMO-2001',
				'payment_mode'          => 'test',
				'payment_method'        => 'Square',
				'payment_type'          => 'one_time',
				'transaction_id'        => $txn_id,
				'amount'                => 7500,
				'currency'              => 'USD',
				'status'                => 'succeeded',
				'customer_name'         => 'Jane Smith',
				'customer_email'        => 'jane.smith@example.com',
				'transaction_date'      => $now,
				'received_on'           => $now,
				'form_id'               => $form['form_id'],
				'form_name'             => $form['form_name'],
				'gateway_dashboard_url' => esc_url_raw( $dash_url ),
				'logs'                  => array(
					array(
						'event'          => 'payment_verification',
						'transaction_id' => $txn_id,
						'gateway'        => 'square',
						'amount'         => '$75.00',
						'status'         => 'Succeeded',
						'user_id'        => get_current_user_id(),
						'mode'           => 'test',
						'created_at'     => $now,
					),
				),
			);
		}
	}

	Gutena_Forms_Payment_Seeder::register();
endif;
