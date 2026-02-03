<?php
/**
 * Gutena Forms Entries Model
 *
 * @since 1.6.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Entries_Model' ) ) :
	/**
	 * Gutena Forms Entries Model
	 *
	 * @since 1.6.0
	 */
	class Gutena_Forms_Entries_Model {
		/**
		 * Singleton instance
		 *
		 * @since 1.6.0
		 * @var Gutena_Forms_Entries_Model Singleton instance of the class.
		 */
		private static $instance;

		/**
		 * WordPress database global object
		 *
		 * @since 1.6.0
		 * @var WPDB $wpdb WordPress database global object.
		 */
		private $wpdb;

		/**
		 * Store instance
		 *
		 * @since 1.6.0
		 * @var Gutena_Forms_Store $store Store instance.
		 */
		private $store;

		/**
		 * Constructor
		 *
		 * @since 1.6.0
		 */
		public function __construct() {
			global $wpdb;

			$this->wpdb  = $wpdb;
			$this->store = new Gutena_Forms_Store();
		}

		/**
		 * Get singleton instance
		 *
		 * @since 1.6.0
		 * @return Gutena_Forms_Entries_Model
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Get entry details
		 *
		 * @since 1.6.0
		 * @param int|string $entry_id Entry ID.
		 *
		 * @return array|object|stdClass|null
		 */
		public function get_details( $entry_id ) {
			$sql = 'SELECT entry_id, form_id, user_id, added_time, entry_status FROM %i WHERE entry_id = %d AND trash = 0';
			$sql = $this->wpdb->prepare(
				$sql,
				$this->store->table_gutenaforms_entries,
				$entry_id
			);

			return $this->wpdb->get_row( $sql, ARRAY_A );
		}

		/**
		 * Get entry data
		 *
		 * @since 1.6.0
		 * @param int|string $entry_id Entry ID.
		 *
		 * @return string|null
		 */
		public function get_data( $entry_id ) {
			$sql    = 'SELECT entry_data FROM %i WHERE entry_id = %d AND trash = 0';
			$sql    = $this->wpdb->prepare(
				$sql,
				$this->store->table_gutenaforms_entries,
				$entry_id
			);

			return $this->wpdb->get_var( $sql );
		}

		/**
		 * Get related entries
		 *
		 * @since 1.6.0
		 * @param int|string $entry_id Entry ID.
		 *
		 * @return array|object|stdClass[]|null
		 */
		public function get_related( $entry_id ) {
			$sql = 'SELECT related.entry_id, related.added_time FROM %i main LEFT JOIN %i related ON main.user_id = related.user_id AND main.entry_id != related.entry_id WHERE main.entry_id = %d';
			$sql = $this->wpdb->prepare(
				$sql,
				$this->store->table_gutenaforms_entries,
				$this->store->table_gutenaforms_entries,
				$entry_id
			);

			return $this->wpdb->get_results( $sql, ARRAY_A );
		}

		/**
		 * Get count of entries by form ID
		 *
		 * @since 1.6.0
		 * @param int|string $form_id Form ID.
		 *
		 * @return string|null
		 */
		public function get_count_by_form_id( $form_id ) {
			$block_form_id = get_post_meta( $form_id, 'gutena_form_id', true );
			$sql           = 'SELECT COUNT( gutenaFormsEntries.entry_id ) FROM %i gutenaForms LEFT JOIN %i gutenaFormsEntries ON gutenaForms.form_id = gutenaFormsEntries.form_id WHERE gutenaForms.block_form_id = %s';
			$sql           = $this->wpdb->prepare(
				$sql,
				$this->store->table_gutenaforms,
				$this->store->table_gutenaforms_entries,
				$block_form_id
			);

			return $this->wpdb->get_var( $sql );
		}

		/**
		 * Get all entries, optionally by form ID
		 *
		 * @since 1.6.0
		 * @param int $form_id Form ID.
		 *
		 * @return array|object|stdClass[]|null
		 */
		public function get_all( $form_id = 0 ) {
			$sql = 'SELECT e.entry_id, e.form_id, f.form_name, e.added_time, e.entry_data FROM %i e LEFT JOIN %i f ON e.form_id = f.form_id WHERE e.trash = 0';
			$sql = $this->wpdb->prepare(
				$sql,
				$this->store->table_gutenaforms_entries,
				$this->store->table_gutenaforms
			);

			if ( ! empty( $form_id ) ) {
				$sql .= ' AND f.block_form_id = %s ';
				$sql = $this->wpdb->prepare( $sql, $form_id );
			}

			$sql .= ' ORDER BY e.entry_id DESC ';

			$results = $this->wpdb->get_results( $sql, ARRAY_A );

			return array_map(
				function ( $entry ) {
					$value = array();
					if ( is_serialized( $entry['entry_data'] ) ) {
						$value = maybe_unserialize( $entry['entry_data'] );
					}

					return array(
						'entry_id'  => absint( $entry['entry_id'] ),
						'form_id'   => absint( $entry['form_id'] ),
						'form_name' => ! empty( $entry['form_name'] ) ? $entry['form_name'] : __( 'Unknown Form', 'gutena-forms' ),
						'datetime'  => ! empty( $entry['added_time'] ) ? gmdate( 'Y-m-d h:i:s A', strtotime( $entry['added_time'] ) ) : '',
						'value' => $value,
					);
				},
				$results
			);
		}

		public function get_entries_header( $form_id ) {
			$form_id = get_post_meta( $form_id, 'gutena_form_id', true );

			$sql    = 'SELECT entries.entry_data FROM %i forms LEFT JOIN %i entries ON forms.form_id = entries.form_id WHERE forms.block_form_id = %s LIMIT 1';
			$sql    = $this->wpdb->prepare(
				$sql,
				$this->store->table_gutenaforms,
				$this->store->table_gutenaforms_entries,
				$form_id
			);
			$result = $this->wpdb->get_var( $sql );

			$headers = array(
				array(
					'key'   => 'checkbox',
					'value' => 'entry_id',
					'width' => '25px',
				),
				array(
					'key'   => 'entry_id',
					'value' => __( 'Entry ID', 'gutena-forms' ),
					'width' => '100px',
				),
			);

			$entry_data = maybe_unserialize( $result );

			$i = 0;
			foreach ( $entry_data as $entry_key => $entry ) {
				if ( $i >= 5 )
					break;

				$headers[] = array(
					'key'   => $entry_key,
					'value' => $entry['label'],
					'width' => '150px',
				);

				$i++;
			}
			$headers[] = array(
				'key'   => 'datetime',
				'value' => __( 'Submitted On', 'gutena-forms' ),
				'width' => '150px',
			);
			$headers[] = array(
				'key'   => 'actions',
				'value' => __( 'Actions', 'gutena-forms' ),
				'width' => '110px',
			);

			return $headers;
		}

		public function get_entry_data( $form_id ) {
			$form_id = get_post_meta( $form_id, 'gutena_form_id', true );

			$sql    = 'SELECT entries.entry_data, entries.entry_id, entries.added_time FROM %i forms LEFT JOIN %i entries ON forms.form_id = entries.form_id WHERE forms.block_form_id = %s';
			$sql    = $this->wpdb->prepare(
				$sql,
				$this->store->table_gutenaforms,
				$this->store->table_gutenaforms_entries,
				$form_id
			);
			$result = $this->wpdb->get_results( $sql, ARRAY_A );

			$result = array_map(
				function ( $result ) {
					$result['entry_data'] = maybe_unserialize( $result['entry_data'] );
					return $result;
				},
				$result
			);

			return $result;
		}

		public function fetch_current_prev_details( $entry_id, $serial_no ) {
			$sql = 'SELECT form_id FROM %i WHERE entry_id = %d';
			$sql = $this->wpdb->prepare(
				$sql,
				$this->store->table_gutenaforms_entries,
				$entry_id
			);

			$form_id = $this->wpdb->get_var( $sql );

			$sql = 'SELECT entry_id FROM %i WHERE form_id = %d AND trash = 0 GROUP BY entry_id ORDER BY entry_id ASC';
			$sql = $this->wpdb->prepare(
				$sql,
				$this->store->table_gutenaforms_entries,
				$form_id
			);

			$results = $this->wpdb->get_col( $sql );

			$total_entries = count( $results );

			$previous = null;
			$next     = null;
			foreach ( $results as $k => $entry ) {
				if ( absint( $entry ) > absint( $entry_id ) ) {
					$next = $entry;
					break;
				}
			}

			for ( $i = $total_entries - 1; $i >= 0; $i-- ) {
				if ( absint( $results[ $i ] ) < absint( $entry_id ) ) {
					$previous = $results[ $i ];
					break;
				}
			}

			$serial_no = 0;
			foreach ( $results as $k => $entry ) {
				$serial_no++;
				if ( absint( $entry ) === absint( $entry_id ) ) {
					break;
				}
			}


			return array(
				'total_count' => $total_entries,
				'previous_entry' => $previous,
				'next_entry'     => $next,
				'serial_no'   => $serial_no,
			);
		}
	}

	Gutena_Forms_Entries_Model::get_instance();
endif;
