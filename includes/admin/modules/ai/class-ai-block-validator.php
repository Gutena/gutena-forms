<?php
/**
 * Block validator for AI-generated form markup.
 *
 * Parses serialized block markup returned by the AI middle server,
 * validates every block against an allowlist, degrades Pro-only
 * field blocks to plain text fields when Pro is not active, and
 * injects a generated formID into the root gutena/forms block.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_AI_Block_Validator' ) ) :
	/**
	 * Validates and prepares AI-generated block markup for insertion.
	 *
	 * @since 2.2.0
	 */
	class Gutena_Forms_AI_Block_Validator {

		/**
		 * Block names that are allowed in AI-generated form markup.
		 *
		 * @since 2.2.0
		 * @var string[]
		 */
		const ALLOWED_BLOCKS = array(
			// Structural / container blocks.
			'gutena/forms',
			'gutena/layout-field',
			'gutena/layout-column',
			'gutena/form-confirm-msg',
			'gutena/form-error-msg',
			// Free field blocks.
			'gutena/text-field',
			'gutena/email-field',
			'gutena/textarea-field',
			'gutena/number-field',
			'gutena/dropdown-field',
			'gutena/radio-field',
			'gutena/checkbox-field',
			'gutena/optin-field',
			'gutena/range-field',
			// Pro field blocks (degraded to text when Pro is not active).
			'gutena/date-field',
			'gutena/time-field',
			'gutena/phone-field',
			'gutena/country-field',
			'gutena/state-field',
			'gutena/file-upload-field',
			'gutena/url-field',
			'gutena/hidden-field',
			'gutena/rating-field',
			'gutena/password-field',
			'gutena/page-break-field',
			// Core blocks used in form footers (submit button, messages).
			'core/buttons',
			'core/button',
			'core/group',
			'core/image',
			'core/paragraph',
			'core/columns',
			'core/column',
		);

		/**
		 * Pro field block names mapped to their fieldType token.
		 *
		 * Mirrors the standalone block map in
		 * Gutena_Forms_Pro_Fields::map_standalone_field_schema() and the
		 * dynamic block list in Gutena_Forms_Templates_Block_Serializer.
		 *
		 * @since 2.2.0
		 * @var array<string,string>
		 */
		const PRO_FIELD_BLOCKS = array(
			'gutena/date-field'        => 'date',
			'gutena/time-field'        => 'time',
			'gutena/phone-field'       => 'phone',
			'gutena/country-field'     => 'country',
			'gutena/state-field'       => 'state',
			'gutena/file-upload-field' => 'file',
			'gutena/url-field'         => 'url',
			'gutena/hidden-field'      => 'hidden',
			'gutena/rating-field'      => 'rating',
			'gutena/password-field'   => 'password',
			'gutena/page-break-field'  => 'page-break',
		);

		/**
		 * Validate serialized block markup.
		 *
		 * Parses the markup and rejects the whole response if any
		 * block name is not in the allowlist. Free-form HTML between
		 * blocks is discarded so the AI cannot inject arbitrary markup.
		 *
		 * @since 2.2.0
		 * @param string $markup Serialized block markup from the middle server.
		 * @return array|\WP_Error Parsed block array on success, WP_Error on failure.
		 */
		public static function validate( $markup ) {
			if ( ! is_string( $markup ) || '' === trim( $markup ) ) {
				return new \WP_Error(
					'gutena_forms_ai_empty_markup',
					__( 'The AI server returned an empty form.', 'gutena-forms' )
				);
			}

			if ( ! function_exists( 'parse_blocks' ) ) {
				return new \WP_Error(
					'gutena_forms_ai_parse_unavailable',
					__( 'Unable to parse the generated form.', 'gutena-forms' )
				);
			}

			$blocks = parse_blocks( $markup );

			// parse_blocks() always returns an array; drop empty freeform entries.
			$blocks = array_values(
				array_filter(
					$blocks,
					function ( $block ) {
						return ! empty( $block['blockName'] );
					}
				)
			);

			if ( empty( $blocks ) ) {
				return new \WP_Error(
					'gutena_forms_ai_no_blocks',
					__( 'The AI server did not return any blocks.', 'gutena-forms' )
				);
			}

			$invalid = self::find_invalid_block( $blocks );
			if ( is_string( $invalid ) ) {
				return new \WP_Error(
					'gutena_forms_ai_invalid_block',
					sprintf(
						/* translators: %s: the rejected block name. */
						__( 'The generated form contains an unsupported block: %s', 'gutena-forms' ),
						$invalid
					)
				);
			}

			// Ensure exactly one root gutena/forms block exists.
			$root_forms = array_filter(
				$blocks,
				function ( $block ) {
					return 'gutena/forms' === $block['blockName'];
				}
			);

			if ( empty( $root_forms ) ) {
				return new \WP_Error(
					'gutena_forms_ai_no_form_block',
					__( 'The generated form is missing its root form block.', 'gutena-forms' )
				);
			}

			return $blocks;
		}

		/**
		 * Recursively find the first block name that is not allowed.
		 *
		 * @since 2.2.0
		 * @param array $blocks Parsed blocks.
		 * @return string|null The invalid block name, or null when all are allowed.
		 */
		private static function find_invalid_block( $blocks ) {
			foreach ( $blocks as $block ) {
				if ( empty( $block['blockName'] ) ) {
					continue;
				}

				if ( ! in_array( $block['blockName'], self::ALLOWED_BLOCKS, true ) ) {
					return $block['blockName'];
				}

				if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
					$inner_invalid = self::find_invalid_block( $block['innerBlocks'] );
					if ( is_string( $inner_invalid ) ) {
						return $inner_invalid;
					}
				}
			}

			return null;
		}

		/**
		 * Degrade Pro-only field blocks to plain text fields when Pro is not active.
		 *
		 * Preserves nameAttr, fieldName, isRequired and placeholder so the
		 * generated form stays usable on the free tier. Mirrors the
		 * *_field_for_template() fallback pattern in the Block Builder.
		 *
		 * @since 2.2.0
		 * @param array $blocks Parsed block array (passed by reference via return).
		 * @return array Modified block array.
		 */
		public static function degrade_pro_fields( $blocks ) {
			if ( function_exists( 'is_gutena_forms_pro' ) && is_gutena_forms_pro() ) {
				return $blocks;
			}

			foreach ( $blocks as $index => $block ) {
				if ( empty( $block['blockName'] ) ) {
					continue;
				}

				if ( isset( self::PRO_FIELD_BLOCKS[ $block['blockName'] ] ) ) {
					$blocks[ $index ] = self::degrade_single_field( $block );
				}

				if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
					$blocks[ $index ]['innerBlocks'] = self::degrade_pro_fields( $block['innerBlocks'] );
				}
			}

			return $blocks;
		}

		/**
		 * Convert a single Pro field block into a free text-field block.
		 *
		 * @since 2.2.0
		 * @param array $block Pro field block.
		 * @return array Text field block.
		 */
		private static function degrade_single_field( $block ) {
			$attrs = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();

			$text_attrs = array(
				'nameAttr'    => isset( $attrs['nameAttr'] ) ? $attrs['nameAttr'] : '',
				'fieldName'   => isset( $attrs['fieldName'] ) ? $attrs['fieldName'] : '',
				'fieldType'   => 'text',
				'isRequired'  => ! empty( $attrs['isRequired'] ),
				'placeholder' => isset( $attrs['placeholder'] ) ? $attrs['placeholder'] : '',
			);

			return array(
				'blockName'    => 'gutena/text-field',
				'attrs'        => $text_attrs,
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			);
		}

		/**
		 * Inject a generated formID into the root gutena/forms block attrs.
		 *
		 * @since 2.2.0
		 * @param array  $blocks  Parsed block array.
		 * @param string $form_id Form ID (e.g. gutena_forms_ID_xxx_timestamp).
		 * @return array Modified block array.
		 */
		public static function inject_form_id( $blocks, $form_id ) {
			foreach ( $blocks as $index => $block ) {
				if ( 'gutena/forms' === $block['blockName'] ) {
					if ( ! isset( $blocks[ $index ]['attrs'] ) || ! is_array( $blocks[ $index ]['attrs'] ) ) {
						$blocks[ $index ]['attrs'] = array();
					}
					$blocks[ $index ]['attrs']['formID'] = $form_id;
				}

				if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
					$blocks[ $index ]['innerBlocks'] = self::inject_form_id( $block['innerBlocks'], $form_id );
				}
			}

			return $blocks;
		}
	}
endif;
