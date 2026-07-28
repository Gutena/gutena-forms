<?php
/**
 * Export form entries to CSV, XLSX, or PDF.
 *
 * @since 2.1.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Export_Entries' ) ) :
	/**
	 * Builds and formats entry export files.
	 *
	 * @since 2.1.0
	 */
	class Gutena_Forms_Export_Entries {
		/**
		 * Supported export formats.
		 *
		 * @since 2.1.0
		 * @var string[]
		 */
		const FORMATS = array( 'csv', 'xlsx', 'pdf' );

		/**
		 * WordPress DB.
		 *
		 * @since 2.1.0
		 * @var wpdb
		 */
		private $wpdb;

		/**
		 * Store instance.
		 *
		 * @since 2.1.0
		 * @var Gutena_Forms_Store
		 */
		private $store;

		/**
		 * Constructor.
		 *
		 * @since 2.1.0
		 */
		public function __construct() {
			global $wpdb;
			$this->wpdb  = $wpdb;
			$this->store = new Gutena_Forms_Store();
		}

		/**
		 * Export entries for a form CPT post.
		 *
		 * @since 2.1.0
		 * @param int      $post_id Form CPT post ID.
		 * @param string[] $field_ids Selected field nameAttr ids.
		 * @param string   $format    csv|xlsx|pdf.
		 * @return array|WP_Error { file, filename, mime } or error.
		 */
		public function export( $post_id, $field_ids, $format ) {
			$post_id = absint( $post_id );
			$format  = sanitize_key( $format );

			if ( empty( $post_id ) || 'gutena_forms' !== get_post_type( $post_id ) ) {
				return new WP_Error( 'invalid_form', __( 'Invalid form selected.', 'gutena-forms' ) );
			}

			if ( ! in_array( $format, self::FORMATS, true ) ) {
				return new WP_Error( 'invalid_format', __( 'Invalid export format.', 'gutena-forms' ) );
			}

			$field_ids = array_values(
				array_filter(
					array_map( 'sanitize_key', (array) $field_ids )
				)
			);

			if ( empty( $field_ids ) ) {
				return new WP_Error( 'missing_fields', __( 'Please select at least one field.', 'gutena-forms' ) );
			}

			$available_fields = Gutena_Forms_Forms_Model::get_instance()->get_fields_by_post_id( $post_id );
			$field_map        = array();
			foreach ( $available_fields as $field ) {
				$field_map[ $field['id'] ] = $field;
			}

			$columns = array();
			foreach ( $field_ids as $field_id ) {
				if ( isset( $field_map[ $field_id ] ) ) {
					$columns[] = $field_map[ $field_id ];
				}
			}

			if ( empty( $columns ) ) {
				return new WP_Error( 'missing_fields', __( 'No valid fields selected for export.', 'gutena-forms' ) );
			}

			$entries = $this->get_entries_by_post_id( $post_id );
			$rows    = $this->build_rows( $entries, $columns );
			$form    = get_post( $post_id );
			$slug    = sanitize_title( ! empty( $form->post_title ) ? $form->post_title : 'gutena-form' );
			$stamp   = gmdate( 'Y-m-d' );

			switch ( $format ) {
				case 'csv':
					$binary   = $this->build_csv( $columns, $rows );
					$filename = $slug . '-entries-' . $stamp . '.csv';
					$mime     = 'text/csv';
					break;
				case 'xlsx':
					$binary = $this->build_xlsx( $columns, $rows );
					if ( is_wp_error( $binary ) ) {
						return $binary;
					}
					$filename = $slug . '-entries-' . $stamp . '.xlsx';
					$mime     = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
					break;
				case 'pdf':
				default:
					$binary = $this->build_pdf( $columns, $rows, ! empty( $form->post_title ) ? $form->post_title : __( 'Form Entries', 'gutena-forms' ) );
					if ( is_wp_error( $binary ) ) {
						return $binary;
					}
					$filename = $slug . '-entries-' . $stamp . '.pdf';
					$mime     = 'application/pdf';
					break;
			}

			return array(
				'file'     => base64_encode( $binary ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
				'filename' => $filename,
				'mime'     => $mime,
			);
		}

		/**
		 * Fetch all non-trashed entries for a CPT form post (full values, no truncation).
		 *
		 * @since 2.1.0
		 * @param int $post_id CPT post ID.
		 * @return array[]
		 */
		private function get_entries_by_post_id( $post_id ) {
			$block_form_id = get_post_meta( $post_id, 'gutena_form_id', true );
			if ( empty( $block_form_id ) ) {
				return array();
			}

			$sql = 'SELECT entries.entry_id, entries.entry_data, entries.added_time, entries.entry_status
				FROM %i forms
				INNER JOIN %i entries ON forms.form_id = entries.form_id
				WHERE forms.block_form_id = %s
				AND entries.trash = 0
				ORDER BY entries.entry_id DESC';

			$sql = $this->wpdb->prepare(
				$sql,
				$this->store->table_gutenaforms,
				$this->store->table_gutenaforms_entries,
				sanitize_key( $block_form_id )
			);

			$results = $this->wpdb->get_results( $sql, ARRAY_A );
			if ( empty( $results ) || ! is_array( $results ) ) {
				return array();
			}

			return array_map(
				function ( $row ) {
					$data = maybe_unserialize( $row['entry_data'] );
					return array(
						'entry_id'     => absint( $row['entry_id'] ),
						'added_time'   => $row['added_time'],
						'entry_status' => ! empty( $row['entry_status'] ) ? $row['entry_status'] : '',
						'entry_data'   => is_array( $data ) ? $data : array(),
					);
				},
				$results
			);
		}

		/**
		 * Build tabular rows for selected columns.
		 *
		 * @since 2.1.0
		 * @param array[] $entries Entries.
		 * @param array[] $columns Field column defs.
		 * @return array[] Each row is list of string cell values matching column order.
		 */
		private function build_rows( $entries, $columns ) {
			$rows = array();

			foreach ( $entries as $entry ) {
				$row = array();
				foreach ( $columns as $column ) {
					$row[] = $this->get_entry_field_value( $entry['entry_data'], $column['id'] );
				}
				$rows[] = $row;
			}

			return $rows;
		}

		/**
		 * Get a single field value from entry_data by nameAttr.
		 *
		 * @since 2.1.0
		 * @param array  $entry_data Entry data keyed by nameAttr.
		 * @param string $field_id   Field nameAttr.
		 * @return string
		 */
		private function get_entry_field_value( $entry_data, $field_id ) {
			if ( empty( $entry_data[ $field_id ] ) || ! is_array( $entry_data[ $field_id ] ) ) {
				return '';
			}

			$value = isset( $entry_data[ $field_id ]['value'] ) ? $entry_data[ $field_id ]['value'] : '';
			if ( is_array( $value ) ) {
				$value = implode( ', ', $value );
			}

			return (string) $value;
		}

		/**
		 * Build CSV binary string (UTF-8 with BOM for Excel).
		 *
		 * @since 2.1.0
		 * @param array[] $columns Columns.
		 * @param array[] $rows    Rows.
		 * @return string
		 */
		private function build_csv( $columns, $rows ) {
			$handle = fopen( 'php://temp', 'r+' );
			if ( false === $handle ) {
				return '';
			}

			$headers = array_map(
				function ( $column ) {
					return $column['label'];
				},
				$columns
			);
			fputcsv( $handle, $headers );

			foreach ( $rows as $row ) {
				fputcsv( $handle, $row );
			}

			rewind( $handle );
			$csv = stream_get_contents( $handle );
			fclose( $handle );

			// BOM helps Excel open UTF-8 correctly.
			return "\xEF\xBB\xBF" . $csv;
		}

		/**
		 * Lazily load Composer autoload for export libraries.
		 *
		 * @since 2.1.0
		 * @return bool
		 */
		private function load_composer_autoload() {
			static $loaded = null;

			if ( null !== $loaded ) {
				return $loaded;
			}

			$autoload = GUTENA_FORMS_DIR_PATH . 'vendor/autoload.php';
			if ( ! file_exists( $autoload ) ) {
				$loaded = false;
				return false;
			}

			require_once $autoload;
			$loaded = true;
			return true;
		}

		/**
		 * Build an XLSX file via SimpleXLSXGen.
		 *
		 * @since 2.1.0
		 * @param array[] $columns Columns.
		 * @param array[] $rows    Rows.
		 * @return string|WP_Error
		 */
		private function build_xlsx( $columns, $rows ) {
			if ( ! $this->load_composer_autoload() || ! class_exists( '\Shuchkin\SimpleXLSXGen' ) ) {
				return new WP_Error(
					'xlsx_unavailable',
					__( 'Excel export library is not available.', 'gutena-forms' )
				);
			}

			$data   = array();
			$header = array();
			foreach ( $columns as $column ) {
				$header[] = (string) $column['label'];
			}
			$data[] = $header;

			foreach ( $rows as $row ) {
				$data[] = array_map( 'strval', $row );
			}

			try {
				$xlsx   = \Shuchkin\SimpleXLSXGen::fromArray( $data );
				$binary = (string) $xlsx;
			} catch ( \Exception $e ) {
				return new WP_Error(
					'xlsx_write',
					__( 'Could not create Excel file.', 'gutena-forms' )
				);
			}

			if ( '' === $binary ) {
				return new WP_Error(
					'xlsx_write',
					__( 'Could not create Excel file.', 'gutena-forms' )
				);
			}

			return $binary;
		}

		/**
		 * Build a landscape PDF table via TCPDF.
		 *
		 * @since 2.1.0
		 * @param array[] $columns Columns.
		 * @param array[] $rows    Rows.
		 * @param string  $title   Document title.
		 * @return string|WP_Error
		 */
		private function build_pdf( $columns, $rows, $title ) {
			if ( ! $this->load_composer_autoload() || ! class_exists( '\TCPDF' ) ) {
				return new WP_Error(
					'pdf_unavailable',
					__( 'PDF export library is not available.', 'gutena-forms' )
				);
			}

			try {
				$pdf = new \TCPDF( 'L', 'pt', 'A4', true, 'UTF-8', false );
				$pdf->SetCreator( 'Gutena Forms' );
				$pdf->SetAuthor( 'Gutena Forms' );
				$pdf->SetTitle( $title );
				$pdf->setPrintHeader( false );
				$pdf->setPrintFooter( false );
				$pdf->SetMargins( 30, 30, 30 );
				$pdf->SetAutoPageBreak( true, 30 );
				$pdf->AddPage();
				$pdf->SetFont( 'dejavusans', '', 9 );

				$html  = '<h2>' . esc_html( $title ) . '</h2>';
				$html .= '<table border="1" cellpadding="4" cellspacing="0" width="100%">';
				$html .= '<thead><tr style="background-color:#f0f0f0;">';
				foreach ( $columns as $column ) {
					$html .= '<th><b>' . esc_html( (string) $column['label'] ) . '</b></th>';
				}
				$html .= '</tr></thead><tbody>';

				foreach ( $rows as $row ) {
					$html .= '<tr>';
					foreach ( $row as $cell ) {
						$html .= '<td>' . esc_html( (string) $cell ) . '</td>';
					}
					$html .= '</tr>';
				}
				$html .= '</tbody></table>';

				$pdf->writeHTML( $html, true, false, true, false, '' );
				$binary = $pdf->Output( '', 'S' );
			} catch ( \Exception $e ) {
				return new WP_Error(
					'pdf_write',
					__( 'Could not create PDF file.', 'gutena-forms' )
				);
			}

			if ( empty( $binary ) ) {
				return new WP_Error(
					'pdf_write',
					__( 'Could not create PDF file.', 'gutena-forms' )
				);
			}

			return $binary;
		}
	}
endif;
