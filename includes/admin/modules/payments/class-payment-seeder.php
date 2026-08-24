<?php
/**
 * Seeds a demo Stripe payment entry for local design testing.
 *
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Payment_Seeder' ) ) :
	class Gutena_Forms_Payment_Seeder {

		public static function register() {
			add_action( 'admin_init', array( __CLASS__, 'maybe_seed_demo_payment' ), 20 );
		}

		public static function maybe_seed_demo_payment() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			if ( get_option( 'gutena_forms_payment_demo_seeded' ) ) {
				return;
			}

			if ( ! class_exists( 'Gutena_Forms_Entry_Payment' ) ) {
				return;
			}

			$payment_model = Gutena_Forms_Entry_Payment::get_instance();

			if ( ! empty( $payment_model->get_all_list_items() ) ) {
				update_option( 'gutena_forms_payment_demo_seeded', 1 );
				return;
			}

			$entry_id = self::create_demo_entry();

			if ( ! $entry_id ) {
				return;
			}

			$payment_model->save_for_entry(
				$entry_id,
				self::get_demo_payment_payload( $entry_id )
			);

			update_option( 'gutena_forms_payment_demo_seeded', 1 );
		}

		private static function create_demo_entry() {
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

			$form_id   = absint( $form['form_id'] );
			$user_id   = get_current_user_id();
			$entry_data = array(
				array(
					'label' => __( 'Name', 'gutena-forms' ),
					'value' => 'John Doe',
					'type'  => 'text',
				),
				array(
					'label' => __( 'Email', 'gutena-forms' ),
					'value' => 'john.doe@example.com',
					'type'  => 'email',
				),
			);

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$inserted = $wpdb->insert(
				$store->table_gutenaforms_entries,
				array(
					'form_id'       => $form_id,
					'user_id'       => 0,
					'modified_by'   => absint( $user_id ),
					'entry_data'    => maybe_serialize( $entry_data ),
					'entry_status'  => 'read',
					'trash'         => 0,
				),
				array( '%d', '%d', '%d', '%s', '%s', '%d' )
			);

			if ( ! $inserted ) {
				return 0;
			}

			return absint( $wpdb->insert_id );
		}

		private static function get_demo_payment_payload( $entry_id ) {
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

			$now = gmdate( 'F j, Y h:i A' );

			return array(
				'gateway'              => 'stripe',
				'gateway_label'        => 'Stripe',
				'payment_id'           => 'PAY-DEMO-1001',
				'payment_mode'         => 'test',
				'payment_method'       => 'Stripe',
				'payment_type'         => 'one_time',
				'transaction_id'       => 'pi_demo_3NqExampleStripe01',
				'amount'               => 4999,
				'currency'             => 'USD',
				'status'               => 'succeeded',
				'customer_name'        => 'John Doe',
				'customer_email'       => 'john.doe@example.com',
				'transaction_date'     => $now,
				'received_on'          => $now,
				'form_id'              => absint( $form_id ),
				'form_name'            => $form_name ? sanitize_text_field( $form_name ) : __( 'Demo Form', 'gutena-forms' ),
				'stripe_dashboard_url' => 'https://dashboard.stripe.com/test/payments/pi_demo_3NqExampleStripe01',
				'logs'                 => array(
					array(
						'event'          => 'payment_verification',
						'transaction_id' => 'pi_demo_3NqExampleStripe01',
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
	}

	Gutena_Forms_Payment_Seeder::register();
endif;
