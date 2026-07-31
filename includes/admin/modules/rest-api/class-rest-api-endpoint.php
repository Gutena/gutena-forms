<?php
/**
 * Gutena Forms REST API Endpoint
 *
 * Registers the public /submit-entry REST route (only when the REST API
 * toggle is enabled) and handles submissions by reusing the existing
 * gutena_forms_submitted_data action so entries are stored by the same
 * Gutena_Forms_Manage_Store::save_form_entry() listener used by AJAX.
 *
 * @since 2.1.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Rest_API_Endpoint' ) ) :
	/**
	 * Class Gutena_Forms_Rest_API_Endpoint
	 *
	 * @since 2.1.0
	 */
	class Gutena_Forms_Rest_API_Endpoint {

		/**
		 * Register the submit-entry route via the gutena_forms__rest_routs filter.
		 * The route is only registered when the REST API toggle is enabled, so a
		 * disabled installation returns a 404 for the endpoint.
		 *
		 * @since 2.1.0
		 */
		public static function register_routes() {
			add_filter(
				'gutena_forms__rest_routs',
				function ( $routes, $server ) {
					$settings = get_option( 'gutena_forms_rest_api_settings', array() );
					if ( empty( $settings['rest_api_enabled'] ) ) {
						return $routes;
					}

					$routes[] = array(
						'route'    => '/submit-entry',
						'methods'  => 'POST',
						'callback' => array( __CLASS__, 'submit' ),
						'auth'     => false, // public; API key verified inside the callback.
					);

					return $routes;
				},
				10,
				2
			);
		}

		/**
		 * Handle a REST submission.
		 *
		 * @since 2.1.0
		 * @param WP_REST_Request $request Full request object.
		 * @return WP_REST_Response
		 */
		public static function submit( WP_REST_Request $request ) {
			$settings = get_option( 'gutena_forms_rest_api_settings', array() );
			if ( ! is_array( $settings ) ) {
				$settings = array();
			}

			// Verify API key when required.
			if ( ! empty( $settings['require_api_key'] ) ) {
				$provided = $request->get_header( 'X_Gutena_API_Key' );
				$stored   = isset( $settings['api_key'] ) ? (string) $settings['api_key'] : '';
				if ( empty( $stored ) || ! hash_equals( $stored, (string) $provided ) ) {
					return new WP_REST_Response(
						array(
							'status'  => 'error',
							'message' => __( 'Invalid API key', 'gutena-forms' ),
						),
						401
					);
				}
			}

		// Validate form_id and fields payload.
		// Accepts either the Gutena Form post ID (numeric, e.g. 123) or the
		// block formID string (e.g. gutena_forms_ID_...). A numeric value is
		// resolved to the block formID via post meta, matching the pattern used
		// by the entries module (class-entries-model.php).
		$submitted_form_id = sanitize_key( wp_unslash( $request->get_param( 'form_id' ) ) );
		$fields            = $request->get_param( 'fields' );

		if ( empty( $submitted_form_id ) ) {
			return new WP_REST_Response(
				array(
					'status'  => 'error',
					'message' => __( 'Missing form_id', 'gutena-forms' ),
				),
				400
			);
		}

		if ( ! is_array( $fields ) || empty( $fields ) ) {
			return new WP_REST_Response(
				array(
					'status'  => 'error',
					'message' => __( 'Missing or invalid fields payload', 'gutena-forms' ),
				),
				400
			);
		}

		// Resolve to the block formID that the schema lookup expects.
		$block_form_id = self::resolve_block_form_id( $submitted_form_id );
		if ( empty( $block_form_id ) ) {
			return new WP_REST_Response(
				array(
					'status'  => 'error',
					'message' => __( 'Invalid form_id', 'gutena-forms' ),
				),
				404
			);
		}

		// Load the same form schema the AJAX handler uses.
		$schema = gutena_forms_get_form_schema_option( $block_form_id );
		if ( empty( $schema ) || empty( $schema['form_attrs'] ) || empty( $schema['form_fields'] ) ) {
			return new WP_REST_Response(
				array(
					'status'  => 'error',
					'message' => __( 'Invalid form_id', 'gutena-forms' ),
				),
				404
			);
		}

		$field_schema = Gutena_Forms_Helper::resolve_form_fields_schema( $block_form_id, $schema['form_fields'] );

			// Build the same raw_data structure the AJAX handler builds so the
			// existing save_form_entry() listener stores it correctly.
			$raw_data = array();

			foreach ( $fields as $name_attr => $value ) {
				$name_attr = sanitize_key( wp_unslash( $name_attr ) );

				// Skip unknown fields — matches the AJAX handler behavior.
				if ( empty( $field_schema[ $name_attr ] ) ) {
					continue;
				}

				// Skip optin fields (consent checkboxes) — matches AJAX handler.
				if ( ! empty( $field_schema[ $name_attr ]['fieldType'] ) && 'optin' === $field_schema[ $name_attr ]['fieldType'] ) {
					continue;
				}

				$field_name = sanitize_text_field(
					empty( $field_schema[ $name_attr ]['fieldName'] )
						? str_ireplace( '_', ' ', $name_attr )
						: $field_schema[ $name_attr ]['fieldName']
				);

				if ( is_array( $value ) ) {
					$sanitized = Gutena_Forms_Helper::sanitize_array( wp_unslash( $value ), true );
					$display_value = implode( ', ', $sanitized );
				} else {
					$display_value = sanitize_textarea_field( wp_unslash( $value ) );
				}

				$raw_data[ $name_attr ] = array(
					'label'     => $field_name,
					'value'     => $display_value,
					'fieldType' => empty( $field_schema[ $name_attr ]['fieldType'] ) ? 'text' : $field_schema[ $name_attr ]['fieldType'],
					'raw_value' => $value, // save_form_entry re-sanitizes this.
				);
			}

			if ( empty( $raw_data ) ) {
				return new WP_REST_Response(
					array(
						'status'  => 'error',
						'message' => __( 'No valid fields matched the form schema', 'gutena-forms' ),
					),
					422
				);
			}

			// Reuse ALL existing storage logic — no DB code duplicated.
			do_action( 'gutena_forms_submitted_data', $raw_data, $block_form_id, $field_schema );

			return new WP_REST_Response(
				array(
					'status'  => 'Success',
					'message' => __( 'Entry stored', 'gutena-forms' ),
				),
				201
			);
		}

		/**
		 * Resolve a submitted form_id to the block formID used by the schema
		 * lookup. Accepts either:
		 *   - the Gutena Form post ID (numeric), resolved via the
		 *     `gutena_form_id` post meta; or
		 *   - the block formID string itself, returned as-is.
		 *
		 * Mirrors the resolution pattern in class-entries-model.php.
		 *
		 * @since 2.1.0
		 * @param string $submitted_form_id Raw form_id from the request.
		 * @return string The block formID, or empty string on failure.
		 */
		private static function resolve_block_form_id( $submitted_form_id ) {
			$submitted_form_id = sanitize_key( wp_unslash( $submitted_form_id ) );
			if ( empty( $submitted_form_id ) ) {
				return '';
			}

			// If numeric, treat as a Gutena Form post ID and resolve via meta.
			if ( is_numeric( $submitted_form_id ) ) {
				$post_id = (int) $submitted_form_id;
				if ( 'gutena_forms' !== get_post_type( $post_id ) ) {
					return '';
				}
				$block_form_id = get_post_meta( $post_id, 'gutena_form_id', true );
				return ! empty( $block_form_id ) ? (string) $block_form_id : '';
			}

			// Otherwise assume the caller already passed the block formID.
			return $submitted_form_id;
		}
	}

	Gutena_Forms_Rest_API_Endpoint::register_routes();
endif;
