<?php
/**
 * Hydrates template block arrays with save markup before serialization.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Templates_Block_Serializer' ) ) :
	/**
	 * Prepares programmatic block trees for serialize_block().
	 *
	 * @since 2.2.0
	 */
	class Gutena_Forms_Templates_Block_Serializer {

		/**
		 * Blocks that use save: () => null (dynamic).
		 *
		 * @since 2.2.0
		 * @var string[]
		 */
		private static $dynamic_blocks = array(
			'gutena/date-field',
			'gutena/phone-field',
			'gutena/file-upload-field',
			'gutena/rating-field',
			'gutena/url-field',
			'gutena/time-field',
			'gutena/state-field',
			'gutena/country-field',
			'gutena/password-field',
			'gutena/hidden-field',
			'gutena/step-break-field',
		);

		/**
		 * Recursively hydrate a block tree for post_content serialization.
		 *
		 * @since 2.2.0
		 * @param array $block Parsed block array.
		 * @return array
		 */
		public static function prepare_for_save( $block ) {
			if ( empty( $block['blockName'] ) ) {
				return $block;
			}

			$inner_blocks = isset( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ? $block['innerBlocks'] : array();

			foreach ( $inner_blocks as $index => $child ) {
				$inner_blocks[ $index ] = self::prepare_for_save( $child );
			}

			$block['innerBlocks'] = $inner_blocks;

			if ( self::is_dynamic_block( $block['blockName'] ) ) {
				$block['innerContent'] = ! empty( $inner_blocks ) ? self::build_inner_content( array( '' ), $inner_blocks, array( '' ) ) : array();
				$block['innerHTML']    = self::inner_content_to_html( $block['innerContent'], $inner_blocks );
				return $block;
			}

			if ( ! empty( $inner_blocks ) ) {
				return self::prepare_parent_block( $block );
			}

			return self::prepare_leaf_block( $block );
		}

		/**
		 * Whether a block is dynamic (no static save HTML).
		 *
		 * @since 2.2.0
		 * @param string $block_name Block name.
		 * @return bool
		 */
		private static function is_dynamic_block( $block_name ) {
			if ( ! in_array( $block_name, self::$dynamic_blocks, true ) ) {
				return false;
			}

			// Pro registers static save() markup for premium fields; only free stubs are dynamic.
			if ( function_exists( 'is_gutena_forms_pro' ) && is_gutena_forms_pro() ) {
				return false;
			}

			return true;
		}

		/**
		 * Hydrate a parent block.
		 *
		 * @since 2.2.0
		 * @param array $block Block array.
		 * @return array
		 */
		private static function prepare_parent_block( $block ) {
			$wrapper_parts = self::get_parent_wrapper_parts( $block['blockName'], $block['attrs'] ?? array() );
			$inner_blocks  = $block['innerBlocks'];

			$block['innerContent'] = self::build_inner_content( $wrapper_parts['before'], $inner_blocks, $wrapper_parts['after'] );
			$block['innerHTML']    = self::inner_content_to_html( $block['innerContent'], $inner_blocks );

			return $block;
		}

		/**
		 * Hydrate a static leaf block.
		 *
		 * @since 2.2.0
		 * @param array $block Block array.
		 * @return array
		 */
		private static function prepare_leaf_block( $block ) {
			$html = self::get_leaf_html( $block['blockName'], $block['attrs'] ?? array() );

			$block['innerContent'] = array( $html );
			$block['innerHTML']    = $html;
			$block['innerBlocks']  = array();

			return $block;
		}

		/**
		 * Build innerContent array with null placeholders for inner blocks.
		 *
		 * @since 2.2.0
		 * @param string[] $before       Opening HTML chunks.
		 * @param array    $inner_blocks Inner blocks.
		 * @param string[] $after        Closing HTML chunks.
		 * @return array
		 */
		private static function build_inner_content( $before, $inner_blocks, $after ) {
			$inner_content = array();

			foreach ( $before as $chunk ) {
				$inner_content[] = $chunk;
			}

			foreach ( $inner_blocks as $ignored ) {
				$inner_content[] = null;
			}

			foreach ( $after as $chunk ) {
				$inner_content[] = $chunk;
			}

			return $inner_content;
		}

		/**
		 * Convert innerContent + innerBlocks to innerHTML string.
		 *
		 * @since 2.2.0
		 * @param array $inner_content Inner content array.
		 * @param array $inner_blocks  Inner blocks.
		 * @return string
		 */
		private static function inner_content_to_html( $inner_content, $inner_blocks ) {
			$html  = '';
			$index = 0;

			foreach ( $inner_content as $chunk ) {
				if ( is_string( $chunk ) ) {
					$html .= $chunk;
					continue;
				}

				if ( isset( $inner_blocks[ $index ] ) ) {
					$html .= serialize_block( $inner_blocks[ $index ] );
				}
				++$index;
			}

			return $html;
		}

		/**
		 * Opening/closing HTML for blocks with inner blocks.
		 *
		 * @since 2.2.0
		 * @param string $block_name Block name.
		 * @param array  $attrs      Block attributes.
		 * @return array{before:string[],after:string[]}
		 */
		private static function get_parent_wrapper_parts( $block_name, $attrs ) {
			switch ( $block_name ) {
				case 'gutena/forms':
					$form_id      = isset( $attrs['formID'] ) ? $attrs['formID'] : '';
					$form_classes = isset( $attrs['formClasses'] ) ? $attrs['formClasses'] : '';
					$wrapper      = self::wrapper_attributes( $block_name, $attrs, $form_classes );
					return array(
						'before' => array( '<form method="post" enctype="multipart/form-data" ' . $wrapper . '><input type="hidden" name="formid" value="' . esc_attr( $form_id ) . '"/>' ),
						'after'  => array( '</form>' ),
					);

				case 'gutena/form-confirm-msg':
				case 'gutena/form-error-msg':
					$wrapper = self::wrapper_attributes( $block_name, $attrs );
					return array(
						'before' => array( '<div ' . $wrapper . '>' ),
						'after'  => array( '</div>' ),
					);

				case 'core/buttons':
					$wrapper = self::wrapper_attributes( $block_name, $attrs );
					return array(
						'before' => array( '<div ' . $wrapper . '>' ),
						'after'  => array( '</div>' ),
					);

				case 'core/columns':
					$wrapper = self::wrapper_attributes( $block_name, $attrs );
					return array(
						'before' => array( '<div ' . $wrapper . '>' ),
						'after'  => array( '</div>' ),
					);

				case 'core/column':
					$wrapper = self::wrapper_attributes( $block_name, $attrs );
					return array(
						'before' => array( '<div ' . $wrapper . '>' ),
						'after'  => array( '</div>' ),
					);

				case 'core/group':
					$wrapper = self::wrapper_attributes( $block_name, $attrs );
					return array(
						'before' => array( '<div ' . $wrapper . '>' ),
						'after'  => array( '</div>' ),
					);

				default:
					$wrapper = self::wrapper_attributes( $block_name, $attrs );
					return array(
						'before' => array( '<div ' . $wrapper . '>' ),
						'after'  => array( '</div>' ),
					);
			}
		}

		/**
		 * Generate static save HTML for leaf blocks.
		 *
		 * @since 2.2.0
		 * @param string $block_name Block name.
		 * @param array  $attrs      Block attributes.
		 * @return string
		 */
		private static function get_leaf_html( $block_name, $attrs ) {
			switch ( $block_name ) {
				case 'gutena/text-field':
					return self::text_field_html( $attrs );
				case 'gutena/email-field':
					return self::email_field_html( $attrs );
				case 'gutena/textarea-field':
					return self::textarea_field_html( $attrs );
				case 'gutena/number-field':
					return self::number_field_html( $attrs );
				case 'gutena/dropdown-field':
					return self::dropdown_field_html( $attrs );
				case 'gutena/radio-field':
					return self::radio_field_html( $attrs );
				case 'gutena/checkbox-field':
					return self::checkbox_field_html( $attrs );
				case 'gutena/optin-field':
					return self::optin_field_html( $attrs );
				case 'gutena/phone-field':
					return self::phone_field_html( $attrs );
				case 'gutena/url-field':
					return self::url_field_html( $attrs );
				case 'core/button':
					return self::core_button_html( $attrs );
				case 'core/paragraph':
					return self::core_paragraph_html( $attrs );
				case 'core/image':
					return self::core_image_html( $attrs );
				default:
					$wrapper = self::wrapper_attributes( $block_name, $attrs );
					return '<div ' . $wrapper . '></div>';
			}
		}

		/**
		 * Build block wrapper attributes using WP block supports.
		 *
		 * @since 2.2.0
		 * @param string $block_name  Block name.
		 * @param array  $attrs       Block attributes.
		 * @param string $extra_class Additional classes.
		 * @return string
		 */
		private static function wrapper_attributes( $block_name, $attrs, $extra_class = '' ) {
			$parsed = array(
				'blockName'    => $block_name,
				'attrs'        => $attrs,
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			);

			$previous                           = WP_Block_Supports::$block_to_render;
			WP_Block_Supports::$block_to_render = $parsed;

			$extra = array();
			if ( '' !== $extra_class ) {
				$extra['class'] = $extra_class;
			}

			$attributes = get_block_wrapper_attributes( $extra );

			WP_Block_Supports::$block_to_render = $previous;

			return $attributes;
		}

		/**
		 * Build a field CSS class list matching save.js templates.
		 *
		 * @since 2.2.0
		 * @param string   $base      Base field classes.
		 * @param bool     $required  Whether field is required.
		 * @param bool     $autocomplete Whether autocomplete is enabled.
		 * @param string[] $extra     Extra classes.
		 * @return string
		 */
		private static function field_classes( $base, $required, $autocomplete = false, $extra = array() ) {
			$parts = array_merge( preg_split( '/\s+/', trim( $base ), -1, PREG_SPLIT_NO_EMPTY ), $extra );

			if ( $required ) {
				$parts[] = 'required-field';
			}

			if ( $autocomplete ) {
				$parts[] = 'autocomplete';
			}

			return implode( ' ', $parts ) . ' ';
		}

		/**
		 * Text autocomplete attribute helper.
		 *
		 * @since 2.2.0
		 * @param bool   $enabled   Whether enabled.
		 * @param string $name_attr Field name attr.
		 * @param string $field_name Field label.
		 * @return string
		 */
		private static function text_autocomplete_attr( $enabled, $name_attr = '', $field_name = '' ) {
			if ( ! $enabled ) {
				return 'off';
			}

			$haystack = strtolower( $name_attr . ' ' . $field_name );

			if ( preg_match( '/user|login|account/', $haystack ) ) {
				return 'username';
			}

			if ( preg_match( '/\bname\b|first|last|full/', $haystack ) ) {
				return 'name';
			}

			return 'on';
		}

		/**
		 * Generic autocomplete attribute helper.
		 *
		 * @since 2.2.0
		 * @param bool   $enabled Whether enabled.
		 * @param string $token   Token when enabled.
		 * @return string
		 */
		private static function autocomplete_attr( $enabled, $token = 'on' ) {
			return $enabled ? $token : 'off';
		}

		/**
		 * Build a void HTML element.
		 *
		 * @since 2.2.0
		 * @param string $tag    Tag name.
		 * @param array  $attrs  Attributes.
		 * @return string
		 */
		private static function void_element( $tag, $attrs ) {
			$parts = array( '<' . $tag );

			foreach ( $attrs as $name => $value ) {
				if ( true === $value ) {
					$parts[] = $name;
					continue;
				}

				if ( false === $value || null === $value || '' === $value ) {
					continue;
				}

				$parts[] = $name . '="' . esc_attr( $value ) . '"';
			}

			$parts[] = '/>';

			return implode( ' ', $parts );
		}

		/**
		 * Text field save HTML.
		 *
		 * @since 2.2.0
		 * @param array $attrs Attributes.
		 * @return string
		 */
		private static function text_field_html( $attrs ) {
			$name_attr    = $attrs['nameAttr'] ?? '';
			$field_name   = $attrs['fieldName'] ?? '';
			$placeholder  = $attrs['placeholder'] ?? '';
			$is_required  = ! empty( $attrs['isRequired'] );
			$default      = $attrs['defaultValue'] ?? '';
			$maxlength    = isset( $attrs['maxlength'] ) ? (int) $attrs['maxlength'] : 0;
			$autocomplete = ! empty( $attrs['autocomplete'] );
			$description  = $attrs['description'] ?? '';

			$wrapper = self::wrapper_attributes(
				'gutena/text-field',
				$attrs,
				'wp-block-gutena-field-group field-group-type-text standalone-text-field'
			);

			$input_attrs = array(
				'id'           => $name_attr,
				'name'         => $name_attr,
				'type'         => 'text',
				'class'        => self::field_classes( 'gutena-forms-field text-field', $is_required, $autocomplete ),
				'placeholder'  => $placeholder,
				'value'        => $default,
				'autocomplete' => self::text_autocomplete_attr( $autocomplete, $name_attr, $field_name ),
			);

			if ( $maxlength > 0 ) {
				$input_attrs['maxlength'] = $maxlength;
			}

			if ( $is_required ) {
				$input_attrs['required'] = true;
			}

			$html  = '<div ' . $wrapper . '>';
			$html .= '<label for="' . esc_attr( $name_attr ) . '" class="heading-input-label-gutena">' . esc_html( $field_name ) . ( $is_required ? ' *' : '' ) . '</label>';
			$html .= '<div class="wp-block-gutena-form-field">' . self::void_element( 'input', $input_attrs ) . '</div>';
			if ( '' !== $description ) {
				$html .= '<p class="gutena-forms-text-field-description">' . esc_html( $description ) . '</p>';
			}
			$html .= '<p class="gutena-forms-field-error-msg"></p>';
			$html .= '</div>';

			return $html;
		}

		/**
		 * Email field save HTML.
		 *
		 * @since 2.2.0
		 * @param array $attrs Attributes.
		 * @return string
		 */
		private static function email_field_html( $attrs ) {
			$name_attr    = $attrs['nameAttr'] ?? '';
			$field_name   = $attrs['fieldName'] ?? '';
			$placeholder  = $attrs['placeholder'] ?? '';
			$is_required  = ! empty( $attrs['isRequired'] );
			$default      = $attrs['defaultValue'] ?? '';
			$maxlength    = isset( $attrs['maxlength'] ) ? (int) $attrs['maxlength'] : 0;
			$autocomplete = ! empty( $attrs['autocomplete'] );
			$description  = $attrs['description'] ?? '';

			$wrapper = self::wrapper_attributes(
				'gutena/email-field',
				$attrs,
				'wp-block-gutena-field-group field-group-type-email standalone-email-field'
			);

			$input_attrs = array(
				'id'           => $name_attr,
				'name'         => $name_attr,
				'type'         => 'email',
				'class'        => self::field_classes( 'gutena-forms-field email-field', $is_required, $autocomplete ),
				'placeholder'  => $placeholder,
				'value'        => $default,
				'autocomplete' => self::autocomplete_attr( $autocomplete, 'email' ),
			);

			if ( $maxlength > 0 ) {
				$input_attrs['maxlength'] = $maxlength;
			}

			if ( $is_required ) {
				$input_attrs['required'] = true;
			}

			$html  = '<div ' . $wrapper . '>';
			$html .= '<label for="' . esc_attr( $name_attr ) . '" class="heading-input-label-gutena">' . esc_html( $field_name ) . ( $is_required ? ' *' : '' ) . '</label>';
			$html .= '<div class="wp-block-gutena-form-field">' . self::void_element( 'input', $input_attrs ) . '</div>';
			if ( '' !== $description ) {
				$html .= '<p class="gutena-forms-email-field-description">' . esc_html( $description ) . '</p>';
			}
			$html .= '<p class="gutena-forms-field-error-msg"></p>';
			$html .= '</div>';

			return $html;
		}

		/**
		 * Textarea field save HTML.
		 *
		 * @since 2.2.0
		 * @param array $attrs Attributes.
		 * @return string
		 */
		private static function textarea_field_html( $attrs ) {
			$name_attr    = $attrs['nameAttr'] ?? '';
			$field_name   = $attrs['fieldName'] ?? '';
			$placeholder  = $attrs['placeholder'] ?? '';
			$is_required  = ! empty( $attrs['isRequired'] );
			$default      = $attrs['defaultValue'] ?? '';
			$rows         = isset( $attrs['textAreaRows'] ) ? (int) $attrs['textAreaRows'] : 5;
			$maxlength    = isset( $attrs['maxlength'] ) ? (int) $attrs['maxlength'] : 0;
			$description  = $attrs['description'] ?? '';

			if ( $rows <= 0 ) {
				$rows = 5;
			}

			$wrapper = self::wrapper_attributes(
				'gutena/textarea-field',
				$attrs,
				'wp-block-gutena-field-group field-group-type-textarea standalone-textarea-field'
			);

			$textarea_attrs = array(
				'id'          => $name_attr,
				'name'        => $name_attr,
				'class'       => self::field_classes( 'gutena-forms-field textarea-field', $is_required ),
				'placeholder' => $placeholder,
				'rows'        => $rows,
			);

			if ( $maxlength > 0 ) {
				$textarea_attrs['maxlength'] = $maxlength;
			}

			if ( $is_required ) {
				$textarea_attrs['required'] = true;
			}

			$attr_string = '';
			foreach ( $textarea_attrs as $name => $value ) {
				if ( true === $value ) {
					$attr_string .= ' ' . $name;
					continue;
				}
				$attr_string .= ' ' . $name . '="' . esc_attr( $value ) . '"';
			}

			$html  = '<div ' . $wrapper . '>';
			$html .= '<label for="' . esc_attr( $name_attr ) . '" class="heading-input-label-gutena">' . esc_html( $field_name ) . ( $is_required ? ' *' : '' ) . '</label>';
			$html .= '<div class="wp-block-gutena-form-field"><textarea' . $attr_string . '>' . esc_html( $default ) . '</textarea></div>';
			if ( '' !== $description ) {
				$html .= '<p class="gutena-forms-textarea-field-description">' . esc_html( $description ) . '</p>';
			}
			$html .= '<p class="gutena-forms-field-error-msg"></p>';
			$html .= '</div>';

			return $html;
		}

		/**
		 * Number field save HTML.
		 *
		 * @since 2.2.0
		 * @param array $attrs Attributes.
		 * @return string
		 */
		private static function number_field_html( $attrs ) {
			$name_attr    = $attrs['nameAttr'] ?? '';
			$field_name   = $attrs['fieldName'] ?? '';
			$placeholder  = $attrs['placeholder'] ?? '';
			$is_required  = ! empty( $attrs['isRequired'] );
			$default      = $attrs['defaultValue'] ?? '';
			$autocomplete = ! empty( $attrs['autocomplete'] );
			$description  = $attrs['description'] ?? '';
			$min_max_step = isset( $attrs['minMaxStep'] ) && is_array( $attrs['minMaxStep'] ) ? $attrs['minMaxStep'] : array();

			$wrapper = self::wrapper_attributes(
				'gutena/number-field',
				$attrs,
				'wp-block-gutena-field-group field-group-type-number standalone-number-field'
			);

			$input_attrs = array(
				'id'           => $name_attr,
				'name'         => $name_attr,
				'type'         => 'number',
				'class'        => self::field_classes( 'gutena-forms-field number-field', $is_required, $autocomplete ),
				'placeholder'  => $placeholder,
				'value'        => $default,
				'autocomplete' => self::autocomplete_attr( $autocomplete, 'on' ),
			);

			foreach ( array( 'min', 'max', 'step' ) as $key ) {
				if ( isset( $min_max_step[ $key ] ) && '' !== $min_max_step[ $key ] && null !== $min_max_step[ $key ] ) {
					$input_attrs[ $key ] = $min_max_step[ $key ];
				}
			}

			if ( $is_required ) {
				$input_attrs['required'] = true;
			}

			$html  = '<div ' . $wrapper . '>';
			$html .= '<label for="' . esc_attr( $name_attr ) . '" class="heading-input-label-gutena">' . esc_html( $field_name ) . ( $is_required ? ' *' : '' ) . '</label>';
			$html .= '<div class="wp-block-gutena-form-field">' . self::void_element( 'input', $input_attrs ) . '</div>';
			if ( '' !== $description ) {
				$html .= '<p class="gutena-forms-number-field-description">' . esc_html( $description ) . '</p>';
			}
			$html .= '<p class="gutena-forms-field-error-msg"></p>';
			$html .= '</div>';

			return $html;
		}

		/**
		 * Dropdown field save HTML.
		 *
		 * @since 2.2.0
		 * @param array $attrs Attributes.
		 * @return string
		 */
		private static function dropdown_field_html( $attrs ) {
			$name_attr      = $attrs['nameAttr'] ?? '';
			$field_name     = $attrs['fieldName'] ?? '';
			$is_required    = ! empty( $attrs['isRequired'] );
			$select_options = isset( $attrs['selectOptions'] ) && is_array( $attrs['selectOptions'] ) ? $attrs['selectOptions'] : array();
			$autocomplete   = ! empty( $attrs['autocomplete'] );
			$description    = $attrs['description'] ?? '';

			$wrapper = self::wrapper_attributes(
				'gutena/dropdown-field',
				$attrs,
				'wp-block-gutena-field-group field-group-type-select standalone-dropdown-field'
			);

			$field_classes = self::field_classes( 'gutena-forms-field select-field', $is_required, $autocomplete );
			$native_id     = $name_attr . '__gf-native';
			$listbox_id    = $name_attr . '__gf-listbox';
			$label_id      = $name_attr . '-gf-label';

			$options_html = '';
			if ( $is_required ) {
				$options_html .= '<option value="select">' . esc_html__( 'Select an Option', 'gutena-forms' ) . '</option>';
			}

			$first_real_option = '';
			foreach ( $select_options as $option ) {
				if ( '' === $option || null === $option ) {
					continue;
				}
				if ( '' === $first_real_option ) {
					$first_real_option = $option;
				}
				$options_html .= '<option value="' . esc_attr( $option ) . '">' . esc_html( $option ) . '</option>';
			}

			$trigger_label = $is_required ? __( 'Select an Option', 'gutena-forms' ) : ( $first_real_option ? $first_real_option : '' );

			$html  = '<div ' . $wrapper . '>';
			$html .= '<label id="' . esc_attr( $label_id ) . '" for="' . esc_attr( $name_attr ) . '" class="heading-input-label-gutena">' . esc_html( $field_name ) . ( $is_required ? ' *' : '' ) . '</label>';
			$html .= '<div class="wp-block-gutena-form-field">';
			$html .= '<div class="gf-dropdown-custom" data-gf-dropdown-custom="1" data-gf-field-label="' . esc_attr( $field_name ) . '">';
			$html .= '<select id="' . esc_attr( $native_id ) . '" name="' . esc_attr( $name_attr ) . '" class="gf-dropdown-custom__native ' . esc_attr( trim( $field_classes ) ) . '" tabindex="-1" aria-hidden="true" aria-labelledby="' . esc_attr( $label_id ) . '" autocomplete="' . esc_attr( self::autocomplete_attr( $autocomplete, 'on' ) ) . '"';
			if ( $is_required ) {
				$html .= ' required';
			}
			$html .= '>' . $options_html . '</select>';
			$html .= '<button type="button" id="' . esc_attr( $name_attr ) . '" class="gf-dropdown-custom__trigger" aria-haspopup="listbox" aria-expanded="false" aria-controls="' . esc_attr( $listbox_id ) . '" aria-labelledby="' . esc_attr( $label_id ) . '">';
			$html .= '<span class="gf-dropdown-custom__value" aria-hidden="true">' . esc_html( $trigger_label ) . '</span>';
			$html .= '<span class="gf-dropdown-custom__icon" aria-hidden="true"></span>';
			$html .= '</button>';
			$html .= '<div class="gf-dropdown-custom__popover" hidden><ul id="' . esc_attr( $listbox_id ) . '" class="gf-dropdown-custom__list" role="listbox" tabindex="-1" aria-labelledby="' . esc_attr( $label_id ) . '"></ul></div>';
			$html .= '</div></div>';
			if ( '' !== $description ) {
				$html .= '<p class="gutena-forms-dropdown-field-description">' . esc_html( $description ) . '</p>';
			}
			$html .= '<p class="gutena-forms-field-error-msg"></p>';
			$html .= '</div>';

			return $html;
		}

		/**
		 * Radio field save HTML.
		 *
		 * @since 2.2.0
		 * @param array $attrs Attributes.
		 * @return string
		 */
		private static function radio_field_html( $attrs ) {
			$name_attr       = $attrs['nameAttr'] ?? '';
			$field_name      = $attrs['fieldName'] ?? '';
			$is_required     = ! empty( $attrs['isRequired'] );
			$select_options  = isset( $attrs['selectOptions'] ) && is_array( $attrs['selectOptions'] ) ? $attrs['selectOptions'] : array();
			$options_inline  = ! empty( $attrs['optionsInline'] );
			$options_columns = isset( $attrs['optionsColumns'] ) ? (int) $attrs['optionsColumns'] : 0;
			$autocomplete    = ! empty( $attrs['autocomplete'] );
			$description     = $attrs['description'] ?? '';

			$extra = array();
			if ( $options_inline ) {
				$extra[] = 'inline-options';
			} elseif ( $options_columns > 0 ) {
				$extra[] = 'has-' . $options_columns . '-col';
			}

			$wrapper = self::wrapper_attributes(
				'gutena/radio-field',
				$attrs,
				'wp-block-gutena-field-group field-group-type-radio standalone-radio-field'
			);

			$field_classes = self::field_classes( 'gutena-forms-field radio-field', $is_required, $autocomplete, $extra );

			$options_html = '';
			foreach ( $select_options as $key => $option ) {
				if ( '' === $option || null === $option ) {
					continue;
				}
				$opt_id        = $name_attr . '_' . $key;
				$options_html .= '<label class="radio-container" for="' . esc_attr( $opt_id ) . '"><div>' . esc_html( $option ) . '</div><div>';
				$options_html .= self::void_element(
					'input',
					array(
						'id'           => $opt_id,
						'type'         => 'radio',
						'name'         => $name_attr,
						'value'        => $option,
						'autocomplete' => self::autocomplete_attr( $autocomplete, 'on' ),
					)
				);
				$options_html .= '<span class="checkmark"></span></div></label>';
			}

			$html  = '<div ' . $wrapper . '>';
			$html .= '<fieldset><legend><span class="heading-input-label-gutena">' . esc_html( $field_name ) . ( $is_required ? ' *' : '' ) . '</span></legend>';
			$html .= '<div class="' . esc_attr( trim( $field_classes ) ) . '">' . $options_html . '</div></fieldset>';
			if ( '' !== $description ) {
				$html .= '<p class="gutena-forms-radio-field-description">' . esc_html( $description ) . '</p>';
			}
			$html .= '<p class="gutena-forms-field-error-msg"></p>';
			$html .= '</div>';

			return $html;
		}

		/**
		 * Checkbox field save HTML.
		 *
		 * @since 2.2.0
		 * @param array $attrs Attributes.
		 * @return string
		 */
		private static function checkbox_field_html( $attrs ) {
			$name_attr       = $attrs['nameAttr'] ?? '';
			$field_name      = $attrs['fieldName'] ?? '';
			$is_required     = ! empty( $attrs['isRequired'] );
			$select_options  = isset( $attrs['selectOptions'] ) && is_array( $attrs['selectOptions'] ) ? $attrs['selectOptions'] : array();
			$options_inline  = ! empty( $attrs['optionsInline'] );
			$options_columns = isset( $attrs['optionsColumns'] ) ? (int) $attrs['optionsColumns'] : 0;
			$description     = $attrs['description'] ?? '';

			$extra = array();
			if ( $options_inline ) {
				$extra[] = 'inline-options';
			} elseif ( $options_columns > 0 ) {
				$extra[] = 'has-' . $options_columns . '-col';
			}

			$wrapper = self::wrapper_attributes(
				'gutena/checkbox-field',
				$attrs,
				'wp-block-gutena-field-group field-group-type-checkbox standalone-checkbox-field'
			);

			$field_classes = self::field_classes( 'gutena-forms-field checkbox-field', $is_required, false, $extra );

			$options_html = '';
			foreach ( $select_options as $index => $option ) {
				if ( '' === $option || null === $option ) {
					continue;
				}
				$opt_id        = $name_attr . '_' . $index;
				$options_html .= '<label class="checkbox-container" for="' . esc_attr( $opt_id ) . '">' . esc_html( $option );
				$options_html .= self::void_element(
					'input',
					array(
						'id'    => $opt_id,
						'type'  => 'checkbox',
						'name'  => $name_attr . '[]',
						'value' => $option,
					)
				);
				$options_html .= '<span class="checkmark"></span></label>';
			}

			$html  = '<div ' . $wrapper . '>';
			$html .= '<fieldset><legend><span class="heading-input-label-gutena">' . esc_html( $field_name ) . ( $is_required ? ' *' : '' ) . '</span></legend>';
			$html .= '<div class="' . esc_attr( trim( $field_classes ) ) . '">' . $options_html . '</div></fieldset>';
			if ( '' !== $description ) {
				$html .= '<p class="gutena-forms-checkbox-field-description">' . esc_html( $description ) . '</p>';
			}
			$html .= '<p class="gutena-forms-field-error-msg"></p>';
			$html .= '</div>';

			return $html;
		}

		/**
		 * Opt-in field save HTML.
		 *
		 * @since 2.2.0
		 * @param array $attrs Attributes.
		 * @return string
		 */
		private static function optin_field_html( $attrs ) {
			$name_attr    = $attrs['nameAttr'] ?? '';
			$field_name   = $attrs['fieldName'] ?? '';
			$autocomplete = ! empty( $attrs['autocomplete'] );
			$description  = $attrs['description'] ?? '';
			$is_required  = ! isset( $attrs['isRequired'] ) || ! empty( $attrs['isRequired'] );

			$wrapper = self::wrapper_attributes(
				'gutena/optin-field',
				$attrs,
				'wp-block-gutena-field-group field-group-type-optin standalone-optin-field'
			);

			$field_classes = self::field_classes( 'gutena-forms-field optin-field', $is_required, $autocomplete );

			$html  = '<div ' . $wrapper . '>';
			$html .= '<div class="' . esc_attr( trim( $field_classes ) ) . '">';
			$html .= '<label class="optin-container" for="' . esc_attr( $name_attr ) . '">' . esc_html( $field_name );
			$html .= self::void_element(
				'input',
				array(
					'id'           => $name_attr,
					'type'         => 'checkbox',
					'name'         => $name_attr,
					'value'        => '1',
					'autocomplete' => self::autocomplete_attr( $autocomplete, 'on' ),
				)
			);
			$html .= '<span class="checkmark"></span></label></div>';
			if ( '' !== $description ) {
				$html .= '<p class="gutena-forms-optin-field-description">' . esc_html( $description ) . '</p>';
			}
			$html .= '<p class="gutena-forms-field-error-msg"></p>';
			$html .= '</div>';

			return $html;
		}

		/**
		 * Core button save HTML.
		 *
		 * @since 2.2.0
		 * @param array $attrs Attributes.
		 * @return string
		 */
		private static function core_button_html( $attrs ) {
			$text    = $attrs['text'] ?? '';
			$wrapper = self::wrapper_attributes( 'core/button', $attrs );

			return '<div ' . $wrapper . '><a class="wp-block-button__link wp-element-button">' . esc_html( $text ) . '</a></div>';
		}

		/**
		 * Core paragraph save HTML.
		 *
		 * @since 2.2.0
		 * @param array $attrs Attributes.
		 * @return string
		 */
		private static function core_paragraph_html( $attrs ) {
			$content = $attrs['content'] ?? '';
			$wrapper = self::wrapper_attributes( 'core/paragraph', $attrs );

			return '<p ' . $wrapper . '>' . esc_html( $content ) . '</p>';
		}

		/**
		 * Core image save HTML.
		 *
		 * @since 2.2.0
		 * @param array $attrs Attributes.
		 * @return string
		 */
		private static function core_image_html( $attrs ) {
			$url         = $attrs['url'] ?? '';
			$alt         = $attrs['alt'] ?? '';
			$size_class  = ! empty( $attrs['sizeSlug'] ) ? 'size-' . sanitize_html_class( $attrs['sizeSlug'] ) : '';
			$wrapper     = self::wrapper_attributes( 'core/image', $attrs, $size_class );

			return '<figure ' . $wrapper . '><img src="' . esc_url( $url ) . '" alt="' . esc_attr( $alt ) . '"/></figure>';
		}

		/**
		 * Pro phone field save HTML (matches phoneCountryPickerSave.js).
		 *
		 * @since 2.2.0
		 * @param array $attrs Block attributes.
		 * @return string
		 */
		private static function phone_field_html( $attrs ) {
			$name_attr       = $attrs['nameAttr'] ?? '';
			$field_name      = $attrs['fieldName'] ?? '';
			$placeholder     = $attrs['placeholder'] ?? '';
			$is_required     = ! empty( $attrs['isRequired'] );
			$default         = $attrs['defaultValue'] ?? '';
			$maxlength       = isset( $attrs['maxlength'] ) ? (int) $attrs['maxlength'] : 0;
			$autocomplete    = ! empty( $attrs['autocomplete'] );
			$description     = $attrs['description'] ?? '';
			$select_options  = isset( $attrs['selectOptions'] ) && is_array( $attrs['selectOptions'] ) ? $attrs['selectOptions'] : array();
			$settings        = isset( $attrs['settings'] ) && is_array( $attrs['settings'] ) ? $attrs['settings'] : array();
			$default_country = isset( $settings['defaultCountry'] ) ? (string) $settings['defaultCountry'] : '';
			$show_selected   = ! empty( $settings['showSelectedCountries'] ) && ! empty( $select_options );

			$wrapper = self::wrapper_attributes(
				'gutena/phone-field',
				$attrs,
				'wp-block-gutena-field-group field-group-type-phone standalone-phone-field'
			);

			$field_classes = trim( self::field_classes( 'gutena-forms-field phone-field', $is_required, $autocomplete ) );

			$html  = '<div ' . $wrapper . '>';
			$html .= '<label for="' . esc_attr( $name_attr ) . '" class="heading-input-label-gutena">' . esc_html( $field_name ) . ( $is_required ? ' *' : '' ) . '</label>';
			$html .= '<div class="wp-block-gutena-form-field gfp-phone-form-field"><div class="gfp-phone-country-code-container"><div class="gfp-country-isd-code" aria-haspopup="true">';

			if ( ! $autocomplete ) {
				$html .= '<div class="gfp-country-flag not-dropdown"';
				if ( '' !== $default_country ) {
					$html .= ' data-selected="' . esc_attr( $default_country ) . '"';
				}
				$html .= '>' . esc_html( $default_country ) . '</div>';
			} else {
				$html .= '<div class="gfp-country-flag"';
				if ( '' !== $default_country ) {
					$html .= ' data-selected="' . esc_attr( $default_country ) . '"';
				}
				$html .= '></div><ul class="gfp-country-list" role="list"';
				if ( $show_selected ) {
					$html .= ' show="' . esc_attr( implode( ',', $select_options ) ) . '"';
				}
				$html .= '></ul>';
			}

			$html .= '</div>';

			$tel_attrs = array(
				'type'        => 'tel',
				'id'          => $name_attr,
				'class'       => $field_classes,
				'placeholder' => $placeholder,
				'value'       => $default,
			);
			if ( $maxlength > 0 ) {
				$tel_attrs['maxlength'] = $maxlength;
			}
			if ( $is_required ) {
				$tel_attrs['required'] = true;
			}

			$html .= self::void_element( 'input', $tel_attrs );
			$html .= self::void_element(
				'input',
				array(
					'type'  => 'hidden',
					'class' => 'gfp-phone-value',
					'id'    => $name_attr,
					'name'  => $name_attr,
				)
			);
			$html .= '</div></div>';

			if ( '' !== $description ) {
				$html .= '<p class="gutena-forms-phone-field-description">' . esc_html( $description ) . '</p>';
			}

			$html .= '<p class="gutena-forms-field-error-msg"></p></div>';

			return $html;
		}

		/**
		 * Pro URL field save HTML.
		 *
		 * @since 2.2.0
		 * @param array $attrs Block attributes.
		 * @return string
		 */
		private static function url_field_html( $attrs ) {
			$name_attr    = $attrs['nameAttr'] ?? '';
			$field_name   = $attrs['fieldName'] ?? '';
			$placeholder  = $attrs['placeholder'] ?? '';
			$is_required  = ! empty( $attrs['isRequired'] );
			$default      = $attrs['defaultValue'] ?? '';
			$maxlength    = isset( $attrs['maxlength'] ) ? (int) $attrs['maxlength'] : 0;
			$autocomplete = ! empty( $attrs['autocomplete'] );
			$description  = $attrs['description'] ?? '';

			$wrapper = self::wrapper_attributes(
				'gutena/url-field',
				$attrs,
				'wp-block-gutena-field-group field-group-type-url standalone-url-field'
			);

			$input_attrs = array(
				'id'           => $name_attr,
				'name'         => $name_attr,
				'type'         => 'text',
				'inputmode'    => 'url',
				'class'        => self::field_classes( 'gutena-forms-field url-field', $is_required, $autocomplete ),
				'placeholder'  => $placeholder,
				'value'        => $default,
				'autocomplete' => self::autocomplete_attr( $autocomplete, 'url' ),
			);

			if ( $maxlength > 0 ) {
				$input_attrs['maxlength'] = $maxlength;
			}
			if ( $is_required ) {
				$input_attrs['required'] = true;
			}

			$html  = '<div ' . $wrapper . '>';
			$html .= '<label for="' . esc_attr( $name_attr ) . '" class="heading-input-label-gutena">' . esc_html( $field_name ) . ( $is_required ? ' *' : '' ) . '</label>';
			$html .= '<div class="wp-block-gutena-form-field">' . self::void_element( 'input', $input_attrs ) . '</div>';
			if ( '' !== $description ) {
				$html .= '<p class="gutena-forms-url-field-description">' . esc_html( $description ) . '</p>';
			}
			$html .= '<p class="gutena-forms-field-error-msg"></p></div>';

			return $html;
		}
	}
endif;
