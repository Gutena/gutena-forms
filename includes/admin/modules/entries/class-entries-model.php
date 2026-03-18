<?php
/**
 * Gutena Forms Entries Model
 *
 * @since 1.7.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Entries_Model' ) ) :
	/**
	 * Data access for form entries: get details, data, related entries, headers, delete.
	 *
	 * @since 1.7.0
	 * @package Gutena Forms
	 */
	class Gutena_Forms_Entries_Model {
		/**
		 * Singleton instance
		 *
		 * @since 1.7.0
		 * @var Gutena_Forms_Entries_Model Singleton instance of the class.
		 */
		private static $instance;

		/**
		 * WordPress database global object
		 *
		 * @since 1.7.0
		 * @var WPDB $wpdb WordPress database global object.
		 */
		private $wpdb;

		/**
		 * Store instance
		 *
		 * @since 1.7.0
		 * @var Gutena_Forms_Store $store Store instance.
		 */
		private $store;

		/**
		 * Set WordPress database and store instances.
		 *
		 * @since 1.7.0
		 */
		public function __construct() {
			global $wpdb;

			$this->wpdb  = $wpdb;
			$this->store = new Gutena_Forms_Store();
		}

		/**
		 * Get singleton instance
		 *
		 * @since 1.7.0
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
		 * @since 1.7.0
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

			$details = $this->wpdb->get_row( $sql, ARRAY_A );

			if ( ! empty( $details ) && ! empty( $details['entry_status'] ) && 'unread' === $details['entry_status'] ) {
				$this->store->update_entries_status( 'read', $entry_id );
			}

			return $details;
		}

		/**
		 * Get entry data
		 *
		 * @since 1.7.0
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
		 * @since 1.7.0
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
		 * @since 1.7.0
		 * @param int|string $form_id Form ID.
		 *
		 * @return string|null
		 */
		public function get_count_by_form_id( $form_id ) {
			$block_form_id = get_post_meta( $form_id, 'gutena_form_id', true );
			$sql           = 'SELECT COUNT( gutenaFormsEntries.entry_id ) FROM %i gutenaForms LEFT JOIN %i gutenaFormsEntries ON gutenaForms.form_id = gutenaFormsEntries.form_id WHERE gutenaForms.block_form_id = %s AND gutenaFormsEntries.trash = 0';
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
		 * @since 1.7.0
		 * @param int $form_id Form ID.
		 *
		 * @return array|object|stdClass[]|null
		 */
		public function get_all( $form_id = 0 ) {
			$sql = "SELECT e.entry_id, e.form_id, f.form_name, e.added_time, e.entry_data, e.entry_status AS status, m.metadata AS starred FROM %i e LEFT JOIN %i f ON e.form_id = f.form_id LEFT JOIN %i m ON e.entry_id = m.entry_id AND m.data_type = 'starred' WHERE e.trash = 0 ";
			$sql = $this->wpdb->prepare(
				$sql,
				$this->store->table_gutenaforms_entries,
				$this->store->table_gutenaforms,
				$this->store->table_gutenaforms_meta
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
						'entry_id'    => absint( $entry['entry_id'] ),
						'form_id'     => absint( $entry['form_id'] ),
						'form_name'   => ! empty( $entry['form_name'] ) ? $entry['form_name'] : __( 'Unknown Form', 'gutena-forms' ),
						'datetime'    => ! empty( $entry['added_time'] ) ? gmdate( 'Y-m-d h:i:s A', strtotime( $entry['added_time'] ) ) : '',
						'value'       => $value,
						'first_value' => is_array( $value ) && isset( $value[ array_key_first( $value ) ]['value'] ) ? $value[ array_key_first( $value ) ]['value'] : '',
						'status'      => ! empty( $entry['status'] ) ? $entry['status'] : 'unknown',
						'starred'     => ! empty( $entry['starred'] ) && '1' == $entry['starred'],
					);
				},
				$results
			);
		}

		/**
		 * Get table headers for entries list by form ID (block/post ID).
		 *
		 * @since 1.7.0
		 * @param int|string $form_id Form block/post ID.
		 * @return array[] Array of header definitions (key, value, width).
		 */
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
				'key'   => 'status',
				'value' => __( 'Status', 'gutena-forms' ),
				'width' => '150px',
			);

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

		/**
		 * Get entry rows (data, entry_id, added_time) for a form by form block/post ID.
		 *
		 * @since 1.7.0
		 * @param int|string $form_id Form block/post ID.
		 * @return array[] List of entry records with entry_data unserialized.
		 */
		public function get_entry_data( $form_id ) {
			$form_id = get_post_meta( $form_id, 'gutena_form_id', true );

			$sql    = "SELECT entries.entry_data, entries.entry_id, entries.added_time, entries.entry_status, metadata.metadata AS starred FROM %i forms LEFT JOIN %i entries ON forms.form_id = entries.form_id LEFT JOIN %i metadata ON entries.entry_id = metadata.entry_id AND metadata.data_type = 'starred' WHERE forms.block_form_id = %s AND entries.trash = 0 ORDER BY entries.entry_id DESC";
			$sql    = $this->wpdb->prepare(
				$sql,
				$this->store->table_gutenaforms,
				$this->store->table_gutenaforms_entries,
				$this->store->table_gutenaforms_meta,
				$form_id
			);
			$result = $this->wpdb->get_results( $sql, ARRAY_A );

			$result = array_map(
				function ( $result ) {
					$result['entry_data']   = maybe_unserialize( $result['entry_data'] );
					$result['entry_status'] = ! empty( $result['entry_status'] ) ? $result['entry_status'] : 'Unknown';

					foreach ( $result['entry_data'] as $k => $v ) {
						$v['value'] = substr( $v['value'], 0, 30 );
						$result['entry_data'][ $k ] = $v;
					}

					return $result;
				},
				$result
			);

			return $result;
		}

		/**
		 * Fetch current entry context: total count, previous/next entry IDs, and serial number.
		 *
		 * @since 1.7.0
		 * @param int|string $entry_id   Current entry ID.
		 * @param int        $serial_no  Unused; serial number is computed from results.
		 * @return array{total_count: int, previous_entry: int|null, next_entry: int|null, serial_no: int}
		 */
		public function fetch_current_prev_details( $entry_id, $serial_no ) {
			$sql = 'SELECT form_id FROM %i WHERE entry_id = %d';
			$sql = $this->wpdb->prepare(
				$sql,
				$this->store->table_gutenaforms_entries,
				$entry_id
			);

			$form_id = $this->wpdb->get_var( $sql );

			$sql = 'SELECT entry_id FROM %i WHERE form_id = %d AND trash = 0 GROUP BY entry_id ORDER BY entry_id';
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

		/**
		 * Delete entries (move to trash) by entry ID(s).
		 *
		 * @since 1.7.0
		 * @param int|int[] $entry_ids Single entry ID or array of entry IDs.
		 * @return bool True on success, false on failure.
		 */
		public function delete_entries( $entry_ids ) {
			return $this->store->update_entries_status( 'trash', $entry_ids );
		}

		/**
		 * Get entry status by entry ID.
		 *
		 * @since 1.7.0
		 * @param string $entry_id Entry id.
		 */
		public function get_status_by_id( $entry_id ) {
			$sql = 'SELECT entry_status FROM %i WHERE entry_id = %d';
			$sql = $this->wpdb->prepare(
				$sql,
				$this->store->table_gutenaforms_entries,
				$entry_id
			);

			return $this->wpdb->get_var( $sql );
		}

		public function get_form_id_by_entry_id( $entry_id ) {
			$sql = 'SELECT form_id FROM %i WHERE entry_id = %d';
			$sql = $this->wpdb->prepare(
				$sql,
				$this->store->table_gutenaforms_entries,
				$entry_id
			);

			$form_id = $this->wpdb->get_var( $sql );

			return is_null( $form_id ) ? 0 : $form_id;
		}
	}

	Gutena_Forms_Entries_Model::get_instance();
endif;
