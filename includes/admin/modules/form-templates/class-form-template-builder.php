<?php
/**
 * Builds Gutena Forms block structures from template definitions.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Form_Template_Builder' ) ) :
	/**
	 * Converts catalog template definitions into serialized form block content.
	 *
	 * @since 2.2.0
	 * @package Gutena Forms
	 */
	class Gutena_Forms_Form_Template_Builder {
		/**
		 * Build a gutena/forms block array from a template definition.
		 *
		 * @since 2.2.0
		 * @param array  $template  Resolved template from the catalog.
		 * @param string $form_id   Unique Gutena form block ID.
		 * @param string $form_name Form title / formName attribute.
		 * @return array|null Block array suitable for serialize_block(), or null on failure.
		 */
		public static function build_form_block( $template, $form_id, $form_name ) {
			if ( empty( $template['fields'] ) || ! is_array( $template['fields'] ) ) {
				return null;
			}

			$inner_blocks = array();

			foreach ( $template['fields'] as $field ) {
				$field_block = self::build_field_block( $field );

				if ( null === $field_block ) {
					return null;
				}

				$inner_blocks[] = $field_block;
			}

			$submit_block = self::build_submit_button_block( $template['submit_label'] );

			if ( null === $submit_block ) {
				return null;
			}

			$inner_blocks[] = $submit_block;

			$confirm_block = self::build_confirm_message_block( $template['success_message'] );

			if ( null === $confirm_block ) {
				return null;
			}

			$inner_blocks[] = $confirm_block;

			$error_block = self::build_error_message_block();

			if ( null === $error_block ) {
				return null;
			}

			$inner_blocks[] = $error_block;

			return array(
				'blockName'    => 'gutena/forms',
				'attrs'        => array(
					'formID'   => $form_id,
					'formName' => $form_name,
				),
				'innerBlocks'  => $inner_blocks,
				'innerHTML'    => '',
				'innerContent' => array(),
			);
		}

		/**
		 * Build a field block from a template field definition.
		 *
		 * @since 2.2.0
		 * @param array $field Template field definition.
		 * @return array|null
		 */
		private static function build_field_block( $field ) {
			if ( empty( $field['block'] ) || empty( $field['attributes'] ) || ! is_array( $field['attributes'] ) ) {
				return null;
			}

			if ( ! self::is_registered_field_block( $field['block'] ) ) {
				return null;
			}

			return array(
				'blockName'    => $field['block'],
				'attrs'        => $field['attributes'],
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			);
		}

		/**
		 * Verify the block is registered in the block editor.
		 *
		 * @since 2.2.0
		 * @param string $block_name Block name.
		 * @return bool
		 */
		private static function is_registered_field_block( $block_name ) {
			if ( ! function_exists( 'WP_Block_Type_Registry' ) ) {
				return false;
			}

			$registry = WP_Block_Type_Registry::get_instance();

			return $registry->is_registered( $block_name );
		}

		/**
		 * Build the submit button block using block markup parsing.
		 *
		 * @since 2.2.0
		 * @param string $submit_label Submit button label.
		 * @return array|null
		 */
		private static function build_submit_button_block( $submit_label ) {
			$submit_label = wp_strip_all_tags( $submit_label );

			if ( '' === $submit_label ) {
				$submit_label = __( 'Submit', 'gutena-forms' );
			}

			$markup = sprintf(
				'<!-- wp:buttons {"className":"gutena-forms-submit-buttons"} --><div class="wp-block-buttons gutena-forms-submit-buttons"><!-- wp:button {"className":"gutena-forms-submit-button"} --><div class="wp-block-button gutena-forms-submit-button"><a class="wp-block-button__link wp-element-button">%s</a></div><!-- /wp:button --></div><!-- /wp:buttons -->',
				esc_html( $submit_label )
			);

			return self::parse_first_block( $markup );
		}

		/**
		 * Build the confirmation message block.
		 *
		 * @since 2.2.0
		 * @param string $success_message Success message text.
		 * @return array|null
		 */
		private static function build_confirm_message_block( $success_message ) {
			$success_message = wp_strip_all_tags( $success_message );

			if ( '' === $success_message ) {
				$success_message = __( 'Your form submitted successfully!', 'gutena-forms' );
			}

			$markup = sprintf(
				'<!-- wp:gutena/form-confirm-msg --><div class="wp-block-gutena-form-confirm-msg"><!-- wp:group {"style":{"spacing":{"padding":{"top":"12px","right":"12px","bottom":"12px","left":"12px"}},"color":{"background":"#d8eacc"},"border":{"radius":"5px"}},"layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"center","justifyContent":"left"}} --><div class="wp-block-group"><!-- wp:paragraph {"fontSize":"tiny"} --><p class="has-tiny-font-size">%s</p><!-- /wp:paragraph --></div><!-- /wp:group --></div><!-- /wp:gutena/form-confirm-msg -->',
				esc_html( $success_message )
			);

			return self::parse_first_block( $markup );
		}

		/**
		 * Build the default error message block.
		 *
		 * @since 2.2.0
		 * @return array|null
		 */
		private static function build_error_message_block() {
			$error_message = __( 'Sorry! your form was not submitted properly, Please check the errors above.', 'gutena-forms' );

			$markup = sprintf(
				'<!-- wp:gutena/form-error-msg --><div class="wp-block-gutena-form-error-msg"><!-- wp:group {"style":{"spacing":{"padding":{"top":"12px","right":"12px","bottom":"12px","left":"12px"}},"color":{"background":"#ffd3d3"},"border":{"radius":"5px"}},"layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"center","justifyContent":"left"}} --><div class="wp-block-group"><!-- wp:paragraph {"className":"gutena-forms-error-text","fontSize":"tiny"} --><p class="gutena-forms-error-text has-tiny-font-size">%s</p><!-- /wp:paragraph --></div><!-- /wp:group --></div><!-- /wp:gutena/form-error-msg -->',
				esc_html( $error_message )
			);

			return self::parse_first_block( $markup );
		}

		/**
		 * Parse block markup and return the first parsed block.
		 *
		 * @since 2.2.0
		 * @param string $markup Serialized block markup.
		 * @return array|null
		 */
		private static function parse_first_block( $markup ) {
			if ( ! function_exists( 'parse_blocks' ) ) {
				return null;
			}

			$blocks = parse_blocks( $markup );

			if ( empty( $blocks[0]['blockName'] ) ) {
				return null;
			}

			return $blocks[0];
		}

		/**
		 * Generate a unique Gutena form block ID.
		 *
		 * @since 2.2.0
		 * @return string
		 */
		public static function generate_form_id() {
			return 'gutena_forms_ID_' . wp_generate_password( 8, false, false ) . '_' . time();
		}
	}
endif;
