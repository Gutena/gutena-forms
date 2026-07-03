<?php
/**
 * Gutena Forms — AI form generation service (middleware → CPT).
 *
 * @package Gutena Forms
 * @since   1.9.2
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Ai_Form_Service' ) ) :
	/**
	 * Generates forms via AI middleware and creates gutena_forms posts.
	 */
	class Gutena_Forms_Ai_Form_Service {

		const PROMPT_MAX_LENGTH = 300;

		const CATEGORY_LABELS = array(
			'contact'          => 'Contact',
			'booking'          => 'Booking',
			'lead-generation'  => 'Lead Generation',
			'survey'           => 'Survey',
		);

		/**
		 * Generate block markup via middleware and create a gutena_forms post.
		 *
		 * @param string $prompt   User prompt.
		 * @param string $category Optional category slug.
		 * @return array|WP_Error
		 */
		public static function generate_and_create( $prompt, $category = '' ) {
			if ( ! current_user_can( 'edit_posts' ) ) {
				return new WP_Error(
					'gutena_forms_ai_forbidden',
					__( 'You do not have permission to create forms.', 'gutena-forms' ),
					array( 'status' => 403 )
				);
			}

			$prompt = sanitize_textarea_field( (string) $prompt );
			$prompt = trim( $prompt );

			if ( '' === $prompt ) {
				return new WP_Error(
					'gutena_forms_ai_empty_prompt',
					__( 'Please describe the form you want to create.', 'gutena-forms' ),
					array( 'status' => 400 )
				);
			}

			if ( function_exists( 'mb_strlen' ) && mb_strlen( $prompt, 'UTF-8' ) > self::PROMPT_MAX_LENGTH ) {
				$prompt = mb_substr( $prompt, 0, self::PROMPT_MAX_LENGTH, 'UTF-8' );
			} elseif ( strlen( $prompt ) > self::PROMPT_MAX_LENGTH ) {
				$prompt = substr( $prompt, 0, self::PROMPT_MAX_LENGTH );
			}

			$combined_prompt = self::build_combined_prompt( $prompt, $category );
			$markup          = Gutena_Forms_Ai_Middleware_Client::block_markup_from_ai( $combined_prompt );

			if ( is_wp_error( $markup ) ) {
				return $markup;
			}

			$prepared = self::prepare_post_content( $markup, $prompt );
			if ( is_wp_error( $prepared ) ) {
				return $prepared;
			}

			$post_args = apply_filters(
				'gutena_forms_ai_generated_post_args',
				array(
					'post_type'    => 'gutena_forms',
					'post_title'   => $prepared['form_name'],
					'post_status'  => 'publish',
					'post_content' => $prepared['post_content'],
				),
				$prepared,
				$prompt,
				$category
			);

			$post_id = wp_insert_post( $post_args, true );

			if ( is_wp_error( $post_id ) ) {
				return $post_id;
			}

			if ( empty( $post_id ) ) {
				return new WP_Error(
					'gutena_forms_ai_create_failed',
					__( 'Could not create the form.', 'gutena-forms' ),
					array( 'status' => 500 )
				);
			}

			update_post_meta( $post_id, 'gutena_form_id', $prepared['form_id'] );

			$post = get_post( $post_id );
			if ( $post instanceof WP_Post && class_exists( 'Gutena_Forms' ) ) {
				Gutena_Forms::get_instance()->save_gutena_forms_schema( $post_id, $post, false );
			}

			$edit_url = get_edit_post_link( $post_id, 'raw' );
			if ( ! is_string( $edit_url ) || '' === $edit_url ) {
				$edit_url = admin_url( 'post.php?post=' . absint( $post_id ) . '&action=edit' );
			}

			return array(
				'post_id'  => (int) $post_id,
				'form_id'  => $prepared['form_id'],
				'edit_url' => $edit_url,
			);
		}

		/**
		 * Append optional category context to the prompt.
		 *
		 * @param string $prompt   User prompt.
		 * @param string $category Category slug.
		 * @return string
		 */
		private static function build_combined_prompt( $prompt, $category ) {
			$category = sanitize_key( (string) $category );
			if ( '' === $category || ! isset( self::CATEGORY_LABELS[ $category ] ) ) {
				return $prompt;
			}

			return sprintf(
				/* translators: 1: category label, 2: user prompt */
				__( 'Category: %1$s. %2$s', 'gutena-forms' ),
				self::CATEGORY_LABELS[ $category ],
				$prompt
			);
		}

		/**
		 * Sanitize markup, inject formID/formName, serialize blocks.
		 *
		 * @param string $markup Raw block markup from AI.
		 * @param string $prompt Original user prompt (for title).
		 * @return array|WP_Error
		 */
		private static function prepare_post_content( $markup, $prompt ) {
			$markup = gutena_forms_ai_sanitize_block_markup( $markup );

			$form_id   = self::generate_form_id();
			$form_name = self::derive_form_name( $prompt );

			if ( gutena_forms_ai_is_json_field_spec( $markup ) ) {
				$spec = json_decode( $markup, true );
				if ( is_array( $spec ) && ! empty( $spec['formName'] ) && is_string( $spec['formName'] ) ) {
					$form_name = sanitize_text_field( $spec['formName'] );
				}
			}

			if ( gutena_forms_ai_is_save_ready_form_markup( $markup ) && ! gutena_forms_ai_is_json_field_spec( $markup ) ) {
				$resolved_name = $form_name;
				$post_content  = gutena_forms_ai_inject_identity_into_markup( $markup, $form_id, $form_name, $resolved_name );
				return array(
					'form_id'      => $form_id,
					'form_name'    => $resolved_name,
					'post_content' => $post_content,
				);
			}

			if ( false === stripos( $markup, 'gutena/forms' ) || false === stripos( $markup, '<form' ) || gutena_forms_ai_is_json_field_spec( $markup ) ) {
				$from_json = gutena_forms_ai_markup_from_json_spec( $markup, $form_id, $form_name );
				if ( '' !== $from_json ) {
					return array(
						'form_id'      => $form_id,
						'form_name'    => $form_name,
						'post_content' => $from_json,
					);
				}
			}

			if ( false === stripos( $markup, 'gutena/forms' ) ) {
				if ( preg_match( '/\[(?:text\*?|email\*?|textarea|tel|select|checkbox|radio)/i', $markup ) ) {
					return new WP_Error(
						'gutena_forms_ai_invalid_markup',
						__( 'AI returned CF7-style tags instead of Gutena Gutenberg blocks. Ask the middleware team to register the gutena-forms operation with block markup output.', 'gutena-forms' ),
						array( 'status' => 400 )
					);
				}

				return new WP_Error(
					'gutena_forms_ai_invalid_markup',
					__( 'AI returned invalid form. Try again.', 'gutena-forms' ),
					array( 'status' => 400 )
				);
			}

			if ( ! function_exists( 'parse_blocks' ) || ! function_exists( 'serialize_blocks' ) ) {
				return new WP_Error(
					'gutena_forms_ai_blocks_unavailable',
					__( 'Block editor functions are not available.', 'gutena-forms' ),
					array( 'status' => 500 )
				);
			}

			$blocks = parse_blocks( $markup );
			if ( empty( $blocks ) || ! self::has_form_block( $blocks ) ) {
				return new WP_Error(
					'gutena_forms_ai_invalid_markup',
					__( 'AI returned malformed Gutena block markup. Try again or check middleware logs.', 'gutena-forms' ),
					array( 'status' => 400 )
				);
			}

			self::inject_form_attrs( $blocks, $form_id, $form_name );

			$post_content = serialize_blocks( $blocks );

			return array(
				'form_id'      => $form_id,
				'form_name'    => $form_name,
				'post_content' => $post_content,
			);
		}

		/**
		 * Generate a unique Gutena form block ID.
		 *
		 * @return string
		 */
		private static function generate_form_id() {
			$random = function_exists( 'wp_generate_password' )
				? wp_generate_password( 8, false, false )
				: substr( md5( uniqid( (string) wp_rand(), true ) ), 0, 8 );
			$random = preg_replace( '/\W/', '', $random );
			$date   = gmdate( 'j' ) . gmdate( 'n' ) . gmdate( 'Y' ) . gmdate( 'G' ) . gmdate( 'i' ) . gmdate( 's' );

			return 'gutena_forms_ID_' . $random . '_' . $date;
		}

		/**
		 * Derive a post title from the user prompt.
		 *
		 * @param string $prompt User prompt.
		 * @return string
		 */
		private static function derive_form_name( $prompt ) {
			$name = wp_strip_all_tags( $prompt );
			$name = preg_replace( '/\s+/', ' ', trim( $name ) );

			if ( function_exists( 'mb_strlen' ) && function_exists( 'mb_substr' ) && mb_strlen( $name, 'UTF-8' ) > 80 ) {
				$name = mb_substr( $name, 0, 80, 'UTF-8' );
			} elseif ( strlen( $name ) > 80 ) {
				$name = substr( $name, 0, 80 );
			}

			if ( '' === $name ) {
				return __( 'AI Generated Form', 'gutena-forms' );
			}

			return $name;
		}

		/**
		 * Whether blocks contain a gutena/forms block.
		 *
		 * @param array $blocks Parsed blocks.
		 * @return bool
		 */
		private static function has_form_block( $blocks ) {
			foreach ( $blocks as $block ) {
				if ( isset( $block['blockName'] ) && 'gutena/forms' === $block['blockName'] ) {
					return true;
				}
				if ( ! empty( $block['innerBlocks'] ) && self::has_form_block( $block['innerBlocks'] ) ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Inject formID and formName into gutena/forms block attrs.
		 *
		 * @param array  $blocks    Parsed blocks (by reference).
		 * @param string $form_id   Form ID.
		 * @param string $form_name Form name.
		 */
		private static function inject_form_attrs( &$blocks, $form_id, $form_name ) {
			$form_classes = gutena_forms_ai_build_form_classes( $form_id, $form_name );

			foreach ( $blocks as &$block ) {
				if ( isset( $block['blockName'] ) && 'gutena/forms' === $block['blockName'] ) {
					if ( ! isset( $block['attrs'] ) || ! is_array( $block['attrs'] ) ) {
						$block['attrs'] = array();
					}
					$block['attrs']['formID']      = $form_id;
					$block['attrs']['formName']    = $form_name;
					$block['attrs']['formClasses'] = $form_classes;

					if ( ! empty( $block['innerHTML'] ) && is_string( $block['innerHTML'] ) ) {
						$block['innerHTML'] = preg_replace(
							'/(<input[^>]*name=["\']formid["\'][^>]*value=["\'])([^"\']*)(["\'])/i',
							'${1}' . esc_attr( $form_id ) . '${3}',
							$block['innerHTML'],
							1
						);
						$block['innerHTML'] = preg_replace(
							'/(<form[^>]*\sclass=["\'])([^"\']*)(["\'])/i',
							'${1}' . esc_attr( $form_classes ) . '${3}',
							$block['innerHTML'],
							1
						);
					}
				}
				if ( ! empty( $block['innerBlocks'] ) ) {
					self::inject_form_attrs( $block['innerBlocks'], $form_id, $form_name );
				}
			}
		}
	}
endif;
