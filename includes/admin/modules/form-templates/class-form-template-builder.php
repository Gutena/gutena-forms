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
		 * Build and serialize a gutena/forms post from a template definition.
		 *
		 * @since 2.2.0
		 * @param array  $template  Resolved template from the catalog.
		 * @param string $form_id   Unique Gutena form block ID.
		 * @param string $form_name Form title / formName attribute.
		 * @return string|null Serialized block markup, or null on failure.
		 */
		public static function serialize_form_block( $template, $form_id, $form_name ) {
			$markup = self::build_form_markup( $template, $form_id, $form_name );

			if ( '' === $markup || ! function_exists( 'parse_blocks' ) ) {
				return null;
			}

			$blocks = parse_blocks( $markup );

			if (
				empty( $blocks )
				|| empty( $blocks[0]['blockName'] )
				|| 'gutena/forms' !== $blocks[0]['blockName']
				|| empty( $blocks[0]['innerBlocks'] )
			) {
				return null;
			}

			// Persist the authored markup directly so static field blocks keep their saved HTML.
			return $markup;
		}

		/**
		 * Build full block-editor markup for a template-based form.
		 *
		 * @since 2.2.0
		 * @param array  $template  Resolved template from the catalog.
		 * @param string $form_id   Unique Gutena form block ID.
		 * @param string $form_name Form title / formName attribute.
		 * @return string
		 */
		public static function build_form_markup( $template, $form_id, $form_name ) {
			if ( empty( $template['fields'] ) || ! is_array( $template['fields'] ) ) {
				return '';
			}

			$form_attrs = array(
				'formID'   => $form_id,
				'formName' => $form_name,
			);

			$parts   = array();
			$parts[] = Gutena_Forms_Form_Template_Field_Renderer::wrap_block_markup(
				'gutena/forms',
				$form_attrs,
				self::build_form_inner_markup( $template, $form_id )
			);

			return implode( '', $parts );
		}

		/**
		 * Build the inner form markup (fields + submit + messages).
		 *
		 * @since 2.2.0
		 * @param array  $template Resolved template from the catalog.
		 * @param string $form_id  Unique Gutena form block ID.
		 * @return string
		 */
		private static function build_form_inner_markup( $template, $form_id ) {
			$parts   = array();
			$parts[] = sprintf(
				'<form method="post" enctype="multipart/form-data" class="wp-block-gutena-forms"><input type="hidden" name="formid" value="%s"/>',
				esc_attr( $form_id )
			);

			foreach ( $template['fields'] as $field ) {
				$field_markup = Gutena_Forms_Form_Template_Field_Renderer::render_field_markup( $field );

				if ( '' === $field_markup ) {
					return '';
				}

				$parts[] = $field_markup;
			}

			$submit_markup = self::build_submit_button_markup( $template['submit_label'] );
			$confirm_markup = self::build_confirm_message_markup( $template['success_message'] );
			$error_markup   = self::build_error_message_markup();

			if ( '' === $submit_markup || '' === $confirm_markup || '' === $error_markup ) {
				return '';
			}

			$parts[] = $submit_markup;
			$parts[] = $confirm_markup;
			$parts[] = $error_markup;
			$parts[] = '</form>';

			return implode( '', $parts );
		}

		/**
		 * Build submit button block markup.
		 *
		 * @since 2.2.0
		 * @param string $submit_label Submit button label.
		 * @return string
		 */
		private static function build_submit_button_markup( $submit_label ) {
			$submit_label = wp_strip_all_tags( $submit_label );

			if ( '' === $submit_label ) {
				$submit_label = __( 'Submit', 'gutena-forms' );
			}

			$markup = sprintf(
				'<!-- wp:buttons {"className":"gutena-forms-submit-buttons"} --><div class="wp-block-buttons gutena-forms-submit-buttons"><!-- wp:button {"className":"gutena-forms-submit-button"} --><div class="wp-block-button gutena-forms-submit-button"><a class="wp-block-button__link wp-element-button">%s</a></div><!-- /wp:button --></div><!-- /wp:buttons -->',
				esc_html( $submit_label )
			);

			return $markup;
		}

		/**
		 * Build confirmation message block markup.
		 *
		 * @since 2.2.0
		 * @param string $success_message Success message text.
		 * @return string
		 */
		private static function build_confirm_message_markup( $success_message ) {
			$success_message = wp_strip_all_tags( $success_message );

			if ( '' === $success_message ) {
				$success_message = __( 'Your form submitted successfully!', 'gutena-forms' );
			}

			$icon_markup = self::build_message_icon_markup(
				'success-tick.svg',
				__( 'Success', 'gutena-forms' )
			);

			return sprintf(
				'<!-- wp:gutena/form-confirm-msg --><div class="wp-block-gutena-form-confirm-msg"><!-- wp:group {"style":{"spacing":{"blockGap":"8px","padding":{"top":"12px","right":"12px","bottom":"12px","left":"12px"}},"color":{"background":"#d8eacc"},"border":{"radius":"5px"}},"layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"center","justifyContent":"left"}} --><div class="wp-block-group has-background" style="border-radius:5px;background-color:#d8eacc;padding-top:12px;padding-right:12px;padding-bottom:12px;padding-left:12px">%1$s<!-- wp:paragraph {"style":{"typography":{"lineHeight":"1.2","fontStyle":"normal","fontWeight":"500","fontSize":"12px"}},"textColor":"black","fontSize":"tiny"} --><p class="has-black-color has-text-color has-tiny-font-size">%2$s</p><!-- /wp:paragraph --></div><!-- /wp:group --></div><!-- /wp:gutena/form-confirm-msg -->',
				$icon_markup,
				esc_html( $success_message )
			);
		}

		/**
		 * Build error message block markup.
		 *
		 * @since 2.2.0
		 * @return string
		 */
		private static function build_error_message_markup() {
			$error_message = __( 'Sorry! your form was not submitted properly, Please check the errors above.', 'gutena-forms' );

			$icon_markup = self::build_message_icon_markup(
				'error.svg',
				__( 'Error', 'gutena-forms' )
			);

			return sprintf(
				'<!-- wp:gutena/form-error-msg --><div class="wp-block-gutena-form-error-msg"><!-- wp:group {"style":{"spacing":{"blockGap":"8px","padding":{"top":"12px","right":"12px","bottom":"12px","left":"12px"}},"color":{"background":"#ffd3d3"},"border":{"radius":"5px"}},"layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"center","justifyContent":"left"}} --><div class="wp-block-group has-background" style="border-radius:5px;background-color:#ffd3d3;padding-top:12px;padding-right:12px;padding-bottom:12px;padding-left:12px">%1$s<!-- wp:paragraph {"style":{"typography":{"lineHeight":"1.2","fontStyle":"normal","fontWeight":"500","fontSize":"12px"}},"textColor":"black","className":"gutena-forms-error-text","fontSize":"tiny"} --><p class="gutena-forms-error-text has-black-color has-text-color has-tiny-font-size">%2$s</p><!-- /wp:paragraph --></div><!-- /wp:group --></div><!-- /wp:gutena/form-error-msg -->',
				$icon_markup,
				esc_html( $error_message )
			);
		}

		/**
		 * Build a core/image block for form message icons.
		 *
		 * @since 2.2.0
		 * @param string $filename Asset filename.
		 * @param string $alt      Image alt text.
		 * @return string
		 */
		private static function build_message_icon_markup( $filename, $alt ) {
			$url = self::get_message_asset_url( $filename );

			if ( '' === $url ) {
				return '';
			}

			$attrs = array(
				'url'             => $url,
				'alt'             => $alt,
				'sizeSlug'        => 'large',
				'linkDestination' => 'none',
				'className'       => 'form-message-icon',
			);

			$html = sprintf(
				'<figure class="wp-block-image size-large form-message-icon"><img src="%s" alt="%s"/></figure>',
				esc_url( $url ),
				esc_attr( $alt )
			);

			return Gutena_Forms_Form_Template_Field_Renderer::wrap_block_markup( 'core/image', $attrs, $html );
		}

		/**
		 * Resolve a form message icon URL from plugin assets.
		 *
		 * @since 2.2.0
		 * @param string $filename Asset filename.
		 * @return string
		 */
		private static function get_message_asset_url( $filename ) {
			if ( ! defined( 'GUTENA_FORMS_PLUGIN_URL' ) ) {
				return '';
			}

			return esc_url_raw(
				trailingslashit( GUTENA_FORMS_PLUGIN_URL ) . 'src/blocks/form/variations/assets/' . ltrim( $filename, '/' )
			);
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
