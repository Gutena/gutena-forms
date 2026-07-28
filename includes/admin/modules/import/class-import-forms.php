<?php
/**
 * Import Gutena Forms from an exported JSON file.
 *
 * @since 2.1.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Import_Forms' ) ) :
	/**
	 * Creates gutena_forms CPT posts from an export payload.
	 *
	 * @since 2.1.0
	 */
	class Gutena_Forms_Import_Forms {
		/**
		 * Import forms from a decoded export payload.
		 *
		 * @since 2.1.0
		 * @param array $payload Export JSON decoded as array.
		 * @return array|WP_Error { imported: array, count: int } or error.
		 */
		public function import( $payload ) {
			$validated = $this->validate_payload( $payload );
			if ( is_wp_error( $validated ) ) {
				return $validated;
			}

			if ( ! function_exists( 'serialize_block' ) || ! function_exists( 'parse_blocks' ) ) {
				return new WP_Error(
					'blocks_unavailable',
					__( 'WordPress block APIs are not available.', 'gutena-forms' )
				);
			}

			$imported = array();
			$errors   = array();

			foreach ( $payload['forms'] as $index => $form ) {
				$result = $this->import_single_form( $form );
				if ( is_wp_error( $result ) ) {
					$errors[] = sprintf(
						/* translators: 1: form index, 2: error message */
						__( 'Form #%1$d: %2$s', 'gutena-forms' ),
						$index + 1,
						$result->get_error_message()
					);
					continue;
				}
				$imported[] = $result;
			}

			if ( empty( $imported ) ) {
				return new WP_Error(
					'import_failed',
					! empty( $errors )
						? implode( ' ', $errors )
						: __( 'No forms could be imported from this file.', 'gutena-forms' )
				);
			}

			return array(
				'imported' => $imported,
				'count'    => count( $imported ),
				'errors'   => $errors,
			);
		}

		/**
		 * Validate export payload structure.
		 *
		 * @since 2.1.0
		 * @param mixed $payload Decoded JSON.
		 * @return true|WP_Error
		 */
		private function validate_payload( $payload ) {
			if ( ! is_array( $payload ) ) {
				return new WP_Error(
					'invalid_payload',
					__( 'Unable to import the file. Please upload a valid Gutena Forms export (.json) file.', 'gutena-forms' )
				);
			}

			$plugin = isset( $payload['plugin'] ) ? (string) $payload['plugin'] : '';
			$generator = isset( $payload['generator'] ) ? (string) $payload['generator'] : '';

			if ( 'gutena-forms' !== $plugin && 'gutena-forms' !== $generator ) {
				return new WP_Error(
					'invalid_plugin',
					__( 'Unable to import the file. Please upload a valid Gutena Forms export (.json) file.', 'gutena-forms' )
				);
			}

			if ( empty( $payload['forms'] ) || ! is_array( $payload['forms'] ) ) {
				return new WP_Error(
					'missing_forms',
					__( 'The import file does not contain any forms.', 'gutena-forms' )
				);
			}

			return true;
		}

		/**
		 * Import one form entry from the export file.
		 *
		 * @since 2.1.0
		 * @param array $form Single form export entry.
		 * @return array|WP_Error
		 */
		private function import_single_form( $form ) {
			if ( ! is_array( $form ) ) {
				return new WP_Error( 'invalid_form', __( 'Invalid form data in import file.', 'gutena-forms' ) );
			}

			$block = null;
			if ( ! empty( $form['block'] ) && is_array( $form['block'] ) ) {
				$block = $form['block'];
			} elseif ( ! empty( $form['content'] ) && is_string( $form['content'] ) ) {
				$parsed = parse_blocks( $form['content'] );
				foreach ( $parsed as $candidate ) {
					if ( ! empty( $candidate['blockName'] ) && 'gutena/forms' === $candidate['blockName'] ) {
						$block = $candidate;
						break;
					}
				}
			}

			if ( empty( $block ) || empty( $block['blockName'] ) || 'gutena/forms' !== $block['blockName'] ) {
				return new WP_Error(
					'missing_form_block',
					__( 'No Gutena form block found in the import data.', 'gutena-forms' )
				);
			}

			// Prefer exact casing from block attrs / content. sanitize_key() lowercases and
			// breaks str_replace against markup that uses gutena_forms_ID_*.
			$old_form_id = '';
			if ( ! empty( $block['attrs']['formID'] ) ) {
				$old_form_id = (string) $block['attrs']['formID'];
			} elseif ( ! empty( $form['form_id'] ) ) {
				$old_form_id = (string) $form['form_id'];
			}
			if ( empty( $old_form_id ) && ! empty( $form['content'] ) && is_string( $form['content'] ) ) {
				if ( preg_match( '/"formID"\s*:\s*"([^"]+)"/', $form['content'], $m ) ) {
					$old_form_id = $m[1];
				}
			}

			$new_form_id = $this->generate_form_id();
			$title       = ! empty( $form['title'] )
				? sanitize_text_field( $form['title'] )
				: ( ! empty( $block['attrs']['formName'] ) ? sanitize_text_field( $block['attrs']['formName'] ) : __( 'Contact Form', 'gutena-forms' ) );

			// Prefer original serialized content for fidelity (avoids block recovery).
			if ( ! empty( $form['content'] ) && is_string( $form['content'] ) ) {
				$content = $form['content'];
				if ( '' !== $old_form_id ) {
					$content = str_replace( $old_form_id, $new_form_id, $content );
				}
				$parsed = parse_blocks( $content );
				foreach ( $parsed as $idx => $candidate ) {
					if ( empty( $candidate['blockName'] ) || 'gutena/forms' !== $candidate['blockName'] ) {
						continue;
					}
					if ( ! isset( $parsed[ $idx ]['attrs'] ) || ! is_array( $parsed[ $idx ]['attrs'] ) ) {
						$parsed[ $idx ]['attrs'] = array();
					}
					$parsed[ $idx ]['attrs']['formID']   = $new_form_id;
					$parsed[ $idx ]['attrs']['formName'] = $title;
					if ( ! empty( $parsed[ $idx ]['attrs']['formClasses'] ) && is_string( $parsed[ $idx ]['attrs']['formClasses'] ) && '' !== $old_form_id ) {
						$parsed[ $idx ]['attrs']['formClasses'] = str_replace( $old_form_id, $new_form_id, $parsed[ $idx ]['attrs']['formClasses'] );
					}
					$content = serialize_block( $parsed[ $idx ] );
					break;
				}
			} else {
				$block = $this->remap_form_id_in_block( $block, $old_form_id, $new_form_id );
				if ( ! isset( $block['attrs'] ) || ! is_array( $block['attrs'] ) ) {
					$block['attrs'] = array();
				}
				$block['attrs']['formID']   = $new_form_id;
				$block['attrs']['formName'] = $title;
				if ( ! empty( $block['attrs']['formClasses'] ) && is_string( $block['attrs']['formClasses'] ) && '' !== $old_form_id ) {
					$block['attrs']['formClasses'] = str_replace( $old_form_id, $new_form_id, $block['attrs']['formClasses'] );
				}
				$content = serialize_block( $block );
			}

			if ( '' !== $old_form_id && false !== strpos( $content, $old_form_id ) ) {
				$content = str_replace( $old_form_id, $new_form_id, $content );
			}

			$post_id = wp_insert_post(
				array(
					'post_type'    => 'gutena_forms',
					'post_title'   => $title,
					'post_content' => $content,
					'post_status'  => 'publish',
				),
				true
			);

			if ( is_wp_error( $post_id ) ) {
				return $post_id;
			}

			if ( empty( $post_id ) ) {
				return new WP_Error(
					'insert_failed',
					__( 'Could not create the imported form.', 'gutena-forms' )
				);
			}

			update_post_meta( $post_id, 'gutena_form_id', $new_form_id );
			update_post_meta( $post_id, '_gutena_connected_posts', array() );

			// Ensure schema + store row exist even if save_post skipped rebuilding.
			$this->ensure_form_schema( $post_id, $new_form_id, $form );

			return array(
				'post_id'      => (int) $post_id,
				'form_id'      => $new_form_id,
				'title'        => $title,
				'source_form_id' => $old_form_id,
			);
		}

		/**
		 * Ensure schema option (and store row via save hooks) exist for the imported form.
		 *
		 * @since 2.1.0
		 * @param int    $post_id     New CPT ID.
		 * @param string $new_form_id New block form ID.
		 * @param array  $form        Original export entry.
		 * @return void
		 */
		private function ensure_form_schema( $post_id, $new_form_id, $form ) {
			$existing = function_exists( 'gutena_forms_get_form_schema_option' )
				? gutena_forms_get_form_schema_option( $new_form_id, null )
				: get_option( GUTENA_FORMS_SCHEMA_OPTION_PREFIX . $new_form_id, null );

			if ( ! empty( $existing ) && is_array( $existing ) ) {
				return;
			}

			$post = get_post( $post_id );
			if ( $post && class_exists( 'Gutena_Forms' ) ) {
				Gutena_Forms::get_instance()->save_gutena_forms_schema( $post_id, $post, false );
				$existing = function_exists( 'gutena_forms_get_form_schema_option' )
					? gutena_forms_get_form_schema_option( $new_form_id, null )
					: null;
				if ( ! empty( $existing ) ) {
					return;
				}
			}

			// Fallback: remap and persist exported schema.
			if ( empty( $form['schema'] ) || ! is_array( $form['schema'] ) ) {
				return;
			}

			$schema = $form['schema'];
			$old_id = ! empty( $form['form_id'] ) ? sanitize_key( $form['form_id'] ) : '';
			$schema = $this->remap_schema_form_id( $schema, $old_id, $new_form_id );

			if ( class_exists( 'Gutena_Forms_Helper' ) ) {
				$schema = Gutena_Forms_Helper::sanitize_array( $schema, true );
			}

			update_option( GUTENA_FORMS_SCHEMA_OPTION_PREFIX . $new_form_id, $schema );

			$gutena_form_ids = get_option( 'gutena_form_ids', array() );
			if ( ! is_array( $gutena_form_ids ) ) {
				$gutena_form_ids = array();
			}
			$gutena_form_ids[] = $new_form_id;
			$gutena_form_ids   = array_values( array_unique( array_map( 'sanitize_key', $gutena_form_ids ) ) );
			update_option( 'gutena_form_ids', $gutena_form_ids );

			if ( class_exists( 'Gutena_Forms_Admin' ) || class_exists( 'Gutena_Forms_Manage_Store' ) ) {
				/**
				 * Allow store layer to create a forms table row for the imported schema.
				 *
				 * @param array  $schema      Form schema.
				 * @param string $new_form_id Block form ID.
				 * @param array  $ids         Existing form IDs.
				 */
				apply_filters( 'gutena_forms_save_form_schema', $schema, $new_form_id, $gutena_form_ids );
			}
		}

		/**
		 * Remap formID inside an exported schema array.
		 *
		 * @since 2.1.0
		 * @param array  $schema      Schema.
		 * @param string $old_form_id Old ID.
		 * @param string $new_form_id New ID.
		 * @return array
		 */
		private function remap_schema_form_id( $schema, $old_form_id, $new_form_id ) {
			$encoded = wp_json_encode( $schema );
			if ( false === $encoded ) {
				return $schema;
			}
			if ( ! empty( $old_form_id ) ) {
				$encoded = str_replace( $old_form_id, $new_form_id, $encoded );
			}
			$decoded = json_decode( $encoded, true );
			if ( ! is_array( $decoded ) ) {
				return $schema;
			}
			if ( isset( $decoded['form_attrs'] ) && is_array( $decoded['form_attrs'] ) ) {
				$decoded['form_attrs']['formID'] = $new_form_id;
			}
			return $decoded;
		}

		/**
		 * Recursively replace form ID tokens inside a block tree.
		 *
		 * @since 2.1.0
		 * @param array  $block       Block.
		 * @param string $old_form_id Old ID.
		 * @param string $new_form_id New ID.
		 * @return array
		 */
		private function remap_form_id_in_block( $block, $old_form_id, $new_form_id ) {
			if ( ! is_array( $block ) ) {
				return $block;
			}

			if ( ! empty( $block['attrs'] ) && is_array( $block['attrs'] ) ) {
				if ( ! empty( $block['attrs']['formID'] ) && ( empty( $old_form_id ) || $block['attrs']['formID'] === $old_form_id ) ) {
					$block['attrs']['formID'] = $new_form_id;
				}
			}

			if ( ! empty( $old_form_id ) ) {
				if ( isset( $block['innerHTML'] ) && is_string( $block['innerHTML'] ) ) {
					$block['innerHTML'] = str_replace( $old_form_id, $new_form_id, $block['innerHTML'] );
				}
				if ( ! empty( $block['innerContent'] ) && is_array( $block['innerContent'] ) ) {
					foreach ( $block['innerContent'] as $i => $piece ) {
						if ( is_string( $piece ) ) {
							$block['innerContent'][ $i ] = str_replace( $old_form_id, $new_form_id, $piece );
						}
					}
				}
			}

			if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
				foreach ( $block['innerBlocks'] as $i => $inner ) {
					$block['innerBlocks'][ $i ] = $this->remap_form_id_in_block( $inner, $old_form_id, $new_form_id );
				}
			}

			return $block;
		}

		/**
		 * Generate a unique gutena form ID (matches editor pattern).
		 *
		 * @since 2.1.0
		 * @return string
		 */
		private function generate_form_id() {
			try {
				$random = bin2hex( random_bytes( 4 ) );
			} catch ( \Exception $e ) {
				$random = substr( md5( uniqid( (string) wp_rand(), true ) ), 0, 8 );
			}

			$stamp = gmdate( 'j' ) . gmdate( 'n' ) . gmdate( 'Y' ) . gmdate( 'G' ) . gmdate( 'i' ) . gmdate( 's' );

			return 'gutena_forms_ID_' . $random . '_' . $stamp;
		}
	}
endif;
