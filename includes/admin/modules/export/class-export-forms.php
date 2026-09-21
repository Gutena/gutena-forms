<?php
/**
 * Export Gutena Forms as JSON (block tree).
 *
 * @since 2.1.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Export_Forms' ) ) :
	/**
	 * Builds a JSON export of selected gutena_forms CPT posts.
	 *
	 * @since 2.1.0
	 */
	class Gutena_Forms_Export_Forms {
		/**
		 * Export selected forms to a downloadable JSON payload.
		 *
		 * @since 2.1.0
		 * @param int[] $post_ids Form CPT post IDs.
		 * @return array|WP_Error { file, filename, mime } or error.
		 */
		public function export( $post_ids ) {
			$post_ids = array_values(
				array_unique(
					array_filter(
						array_map( 'absint', (array) $post_ids )
					)
				)
			);

			if ( empty( $post_ids ) ) {
				return new WP_Error(
					'missing_forms',
					__( 'Please select at least one form to export.', 'gutena-forms' )
				);
			}

			$forms = array();
			foreach ( $post_ids as $post_id ) {
				$exported = $this->export_single_form( $post_id );
				if ( is_wp_error( $exported ) ) {
					return $exported;
				}
				if ( ! empty( $exported ) ) {
					$forms[] = $exported;
				}
			}

			if ( empty( $forms ) ) {
				return new WP_Error(
					'no_forms_exported',
					__( 'No valid Gutena forms found to export.', 'gutena-forms' )
				);
			}

			$payload = array(
				'plugin'      => 'gutena-forms',
				'generator'   => 'gutena-forms',
				'version'     => defined( 'GUTENA_FORMS_VERSION' ) ? GUTENA_FORMS_VERSION : '2.0.0',
				'exported_at' => gmdate( 'c' ),
				'forms'       => $forms,
			);

			$json = wp_json_encode( $payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
			if ( false === $json ) {
				return new WP_Error(
					'json_encode_failed',
					__( 'Could not encode forms export as JSON.', 'gutena-forms' )
				);
			}

			$stamp    = gmdate( 'Y-m-d' );
			$filename = ( 1 === count( $forms ) )
				? sanitize_file_name( $forms[0]['title'] ) . '-form-' . $stamp . '.json'
				: 'gutena-forms-export-' . $stamp . '.json';

			return array(
				'file'     => base64_encode( $json ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
				'filename' => $filename,
				'mime'     => 'application/json',
			);
		}

		/**
		 * Build one form export entry from a CPT post.
		 *
		 * @since 2.1.0
		 * @param int $post_id CPT post ID.
		 * @return array|WP_Error|null
		 */
		private function export_single_form( $post_id ) {
			$post = get_post( $post_id );
			if ( ! $post || 'gutena_forms' !== $post->post_type ) {
				return new WP_Error(
					'invalid_form',
					/* translators: %d: post ID */
					sprintf( __( 'Invalid form selected (ID %d).', 'gutena-forms' ), $post_id )
				);
			}

			if ( ! function_exists( 'parse_blocks' ) || ! function_exists( 'serialize_block' ) ) {
				return new WP_Error(
					'blocks_unavailable',
					__( 'WordPress block APIs are not available.', 'gutena-forms' )
				);
			}

			$form_block = $this->find_form_block( parse_blocks( $post->post_content ) );
			if ( empty( $form_block ) ) {
				return new WP_Error(
					'missing_form_block',
					/* translators: %s: form title */
					sprintf( __( 'No Gutena form block found in “%s”.', 'gutena-forms' ), $post->post_title )
				);
			}

			$form_block = $this->sanitize_block_tree( $form_block );

			$block_form_id = '';
			if ( ! empty( $form_block['attrs']['formID'] ) ) {
				// Preserve original casing — sanitize_key() lowercases and breaks import remapping.
				$block_form_id = (string) $form_block['attrs']['formID'];
			}
			if ( empty( $block_form_id ) ) {
				$block_form_id = (string) get_post_meta( $post_id, 'gutena_form_id', true );
			}

			$schema = array();
			if ( ! empty( $block_form_id ) && function_exists( 'gutena_forms_get_form_schema_option' ) ) {
				$schema_raw = gutena_forms_get_form_schema_option( $block_form_id, array() );
				if ( is_array( $schema_raw ) ) {
					$schema = $schema_raw;
				}
			}

			$title = ! empty( $post->post_title )
				? $post->post_title
				: ( ! empty( $form_block['attrs']['formName'] ) ? $form_block['attrs']['formName'] : __( 'Contact Form', 'gutena-forms' ) );

			return array(
				'title'       => $title,
				'form_id'     => $block_form_id,
				'post_status' => $post->post_status,
				'block'       => $form_block,
				'content'     => serialize_block( $form_block ),
				'schema'      => $schema,
			);
		}

		/**
		 * Find the first gutena/forms block in a parsed block list (depth-first).
		 *
		 * @since 2.1.0
		 * @param array[] $blocks Parsed blocks.
		 * @return array|null
		 */
		private function find_form_block( $blocks ) {
			foreach ( (array) $blocks as $block ) {
				if ( empty( $block ) || ! is_array( $block ) ) {
					continue;
				}

				if ( ! empty( $block['blockName'] ) && 'gutena/forms' === $block['blockName'] ) {
					return $block;
				}

				if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
					$found = $this->find_form_block( $block['innerBlocks'] );
					if ( ! empty( $found ) ) {
						return $found;
					}
				}
			}

			return null;
		}

		/**
		 * Normalize a block tree for JSON export (recursive).
		 *
		 * Keeps attrs + nested innerBlocks so child field blocks are preserved.
		 *
		 * @since 2.1.0
		 * @param array $block Parsed block.
		 * @return array
		 */
		private function sanitize_block_tree( $block ) {
			$clean = array(
				'blockName'    => isset( $block['blockName'] ) ? $block['blockName'] : null,
				'attrs'        => ( ! empty( $block['attrs'] ) && is_array( $block['attrs'] ) ) ? $block['attrs'] : array(),
				'innerBlocks'  => array(),
				'innerHTML'    => isset( $block['innerHTML'] ) ? (string) $block['innerHTML'] : '',
				'innerContent' => isset( $block['innerContent'] ) && is_array( $block['innerContent'] ) ? $block['innerContent'] : array(),
			);

			if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
				foreach ( $block['innerBlocks'] as $inner ) {
					if ( empty( $inner ) || ! is_array( $inner ) ) {
						continue;
					}
					// Skip empty whitespace-only freeform nodes without children.
					if ( empty( $inner['blockName'] ) && empty( $inner['innerBlocks'] ) && empty( trim( (string) ( $inner['innerHTML'] ?? '' ) ) ) ) {
						continue;
					}
					$clean['innerBlocks'][] = $this->sanitize_block_tree( $inner );
				}
			}

			return $clean;
		}
	}
endif;
