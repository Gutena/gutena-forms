<?php
/**
 * Renders saved block markup for template field definitions.
 *
 * Output must match the React block `save` functions so Gutenberg validation passes.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Form_Template_Field_Renderer' ) ) :
	/**
	 * PHP mirror of Gutena field block save markup.
	 *
	 * @since 2.2.0
	 * @package Gutena Forms
	 */
	class Gutena_Forms_Form_Template_Field_Renderer {
		/**
		 * Render a field definition as serialized block markup.
		 *
		 * @since 2.2.0
		 * @param array $field Template field definition.
		 * @return string
		 */
		public static function render_field_markup( $field ) {
			if ( empty( $field['block'] ) || empty( $field['attributes'] ) || ! is_array( $field['attributes'] ) ) {
				return '';
			}

			$block_name = $field['block'];
			$attrs      = $field['attributes'];
			$html       = '';

			switch ( $block_name ) {
				case 'gutena/text-field':
					$html = self::render_text_field( $attrs, 'text' );
					break;
				case 'gutena/email-field':
					$html = self::render_email_field( $attrs );
					break;
				case 'gutena/textarea-field':
					$html = self::render_textarea_field( $attrs );
					break;
				case 'gutena/number-field':
					$html = self::render_number_field( $attrs );
					break;
				case 'gutena/dropdown-field':
					$html = self::render_dropdown_field( $attrs );
					break;
				case 'gutena/radio-field':
					$html = self::render_radio_field( $attrs );
					break;
				case 'gutena/checkbox-field':
					$html = self::render_checkbox_field( $attrs );
					break;
				case 'gutena/range-field':
					$html = self::render_range_field( $attrs );
					break;
				case 'gutena/optin-field':
					$html = self::render_optin_field( $attrs );
					break;
				default:
					return '';
			}

			if ( '' === $html ) {
				return '';
			}

			return self::wrap_block_markup( $block_name, $attrs, $html );
		}

		/**
		 * Wrap HTML in Gutenberg block comments.
		 *
		 * @since 2.2.0
		 * @param string $block_name Block name.
		 * @param array  $attrs      Block attributes.
		 * @param string $html       Saved HTML content.
		 * @return string
		 */
		public static function wrap_block_markup( $block_name, $attrs, $html ) {
			if ( function_exists( 'get_comment_delimited_block_content' ) ) {
				return get_comment_delimited_block_content( $block_name, $attrs, $html );
			}

			return sprintf(
				'<!-- wp:%1$s %2$s -->%3$s<!-- /wp:%1$s -->',
				$block_name,
				wp_json_encode( $attrs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ),
				$html
			);
		}

		/**
		 * @param array  $attrs Field attributes.
		 * @param string $type  Input type attribute.
		 * @return string
		 */
		private static function render_text_field( $attrs, $type = 'text' ) {
			$name_attr       = $attrs['nameAttr'];
			$field_name      = $attrs['fieldName'];
			$placeholder     = isset( $attrs['placeholder'] ) ? $attrs['placeholder'] : '';
			$is_required     = ! empty( $attrs['isRequired'] );
			$default_value   = isset( $attrs['defaultValue'] ) ? $attrs['defaultValue'] : '';
			$maxlength       = isset( $attrs['maxlength'] ) ? (int) $attrs['maxlength'] : 0;
			$autocomplete    = ! empty( $attrs['autocomplete'] );
			$description     = isset( $attrs['description'] ) ? $attrs['description'] : '';
			$wrapper_class   = 'wp-block-gutena-text-field wp-block-gutena-field-group field-group-type-text standalone-text-field';
			$input_classes   = self::input_classes( 'text', $is_required, $autocomplete );
			$autocomplete_at = self::text_autocomplete_attr( $autocomplete, $name_attr, $field_name );
			$required_attr   = $is_required ? ' required' : '';
			$maxlength_attr  = $maxlength > 0 ? sprintf( ' maxLength="%d"', $maxlength ) : '';

			$html  = sprintf( '<div class="%s">', esc_attr( $wrapper_class ) );
			$html .= sprintf(
				'<label for="%s" class="heading-input-label-gutena">%s%s</label>',
				esc_attr( $name_attr ),
				esc_html( $field_name ),
				$is_required ? ' *' : ''
			);
			$html .= '<div class="wp-block-gutena-form-field">';
			$html .= sprintf(
				'<input id="%1$s" name="%1$s" type="%2$s" class="%3$s" placeholder="%4$s" value="%5$s" autocomplete="%6$s"%7$s%8$s/>',
				esc_attr( $name_attr ),
				esc_attr( $type ),
				esc_attr( $input_classes ),
				esc_attr( $placeholder ),
				esc_attr( $default_value ),
				esc_attr( $autocomplete_at ),
				$maxlength_attr,
				$required_attr
			);
			$html .= '</div>';
			if ( '' !== $description ) {
				$html .= sprintf( '<p class="gutena-forms-text-field-description">%s</p>', esc_html( $description ) );
			}
			$html .= '<p class="gutena-forms-field-error-msg"></p></div>';

			return $html;
		}

		/**
		 * @param array $attrs Field attributes.
		 * @return string
		 */
		private static function render_email_field( $attrs ) {
			$name_attr       = $attrs['nameAttr'];
			$field_name      = $attrs['fieldName'];
			$placeholder     = isset( $attrs['placeholder'] ) ? $attrs['placeholder'] : '';
			$is_required     = ! empty( $attrs['isRequired'] );
			$default_value   = isset( $attrs['defaultValue'] ) ? $attrs['defaultValue'] : '';
			$maxlength       = isset( $attrs['maxlength'] ) ? (int) $attrs['maxlength'] : 0;
			$autocomplete    = ! empty( $attrs['autocomplete'] );
			$description     = isset( $attrs['description'] ) ? $attrs['description'] : '';
			$wrapper_class   = 'wp-block-gutena-email-field wp-block-gutena-field-group field-group-type-email standalone-email-field';
			$input_classes   = self::input_classes( 'email', $is_required, $autocomplete );
			$autocomplete_at = self::autocomplete_attr( $autocomplete, 'email' );
			$required_attr   = $is_required ? ' required' : '';
			$maxlength_attr  = $maxlength > 0 ? sprintf( ' maxLength="%d"', $maxlength ) : '';

			$html  = sprintf( '<div class="%s">', esc_attr( $wrapper_class ) );
			$html .= sprintf(
				'<label for="%s" class="heading-input-label-gutena">%s%s</label>',
				esc_attr( $name_attr ),
				esc_html( $field_name ),
				$is_required ? ' *' : ''
			);
			$html .= '<div class="wp-block-gutena-form-field">';
			$html .= sprintf(
				'<input id="%1$s" name="%1$s" type="email" class="%2$s" placeholder="%3$s" value="%4$s" autocomplete="%5$s"%6$s%7$s/>',
				esc_attr( $name_attr ),
				esc_attr( $input_classes ),
				esc_attr( $placeholder ),
				esc_attr( $default_value ),
				esc_attr( $autocomplete_at ),
				$maxlength_attr,
				$required_attr
			);
			$html .= '</div>';
			if ( '' !== $description ) {
				$html .= sprintf( '<p class="gutena-forms-email-field-description">%s</p>', esc_html( $description ) );
			}
			$html .= '<p class="gutena-forms-field-error-msg"></p></div>';

			return $html;
		}

		/**
		 * @param array $attrs Field attributes.
		 * @return string
		 */
		private static function render_textarea_field( $attrs ) {
			$name_attr     = $attrs['nameAttr'];
			$field_name    = $attrs['fieldName'];
			$placeholder   = isset( $attrs['placeholder'] ) ? $attrs['placeholder'] : '';
			$is_required   = ! empty( $attrs['isRequired'] );
			$default_value = isset( $attrs['defaultValue'] ) ? $attrs['defaultValue'] : '';
			$rows          = isset( $attrs['textAreaRows'] ) ? (int) $attrs['textAreaRows'] : 5;
			$maxlength     = isset( $attrs['maxlength'] ) ? (int) $attrs['maxlength'] : 0;
			$description   = isset( $attrs['description'] ) ? $attrs['description'] : '';
			$wrapper_class = 'wp-block-gutena-textarea-field wp-block-gutena-field-group field-group-type-textarea standalone-textarea-field';
			$input_classes = self::input_classes( 'textarea', $is_required, false );
			$required_attr = $is_required ? ' required' : '';
			$maxlength_attr = $maxlength > 0 ? sprintf( ' maxLength="%d"', $maxlength ) : '';

			if ( $rows <= 0 ) {
				$rows = 5;
			}

			$html  = sprintf( '<div class="%s">', esc_attr( $wrapper_class ) );
			$html .= sprintf(
				'<label for="%s" class="heading-input-label-gutena">%s%s</label>',
				esc_attr( $name_attr ),
				esc_html( $field_name ),
				$is_required ? ' *' : ''
			);
			$html .= '<div class="wp-block-gutena-form-field">';
			$html .= sprintf(
				'<textarea id="%1$s" name="%1$s" class="%2$s" placeholder="%3$s" rows="%4$d"%5$s%6$s>%7$s</textarea>',
				esc_attr( $name_attr ),
				esc_attr( $input_classes ),
				esc_attr( $placeholder ),
				$rows,
				$maxlength_attr,
				$required_attr,
				esc_html( $default_value )
			);
			$html .= '</div>';
			if ( '' !== $description ) {
				$html .= sprintf( '<p class="gutena-forms-textarea-field-description">%s</p>', esc_html( $description ) );
			}
			$html .= '<p class="gutena-forms-field-error-msg"></p></div>';

			return $html;
		}

		/**
		 * @param array $attrs Field attributes.
		 * @return string
		 */
		private static function render_number_field( $attrs ) {
			$name_attr     = $attrs['nameAttr'];
			$field_name    = $attrs['fieldName'];
			$placeholder   = isset( $attrs['placeholder'] ) ? $attrs['placeholder'] : '';
			$is_required   = ! empty( $attrs['isRequired'] );
			$default_value = isset( $attrs['defaultValue'] ) ? $attrs['defaultValue'] : '';
			$autocomplete  = ! empty( $attrs['autocomplete'] );
			$description   = isset( $attrs['description'] ) ? $attrs['description'] : '';
			$min_max_step  = isset( $attrs['minMaxStep'] ) && is_array( $attrs['minMaxStep'] ) ? $attrs['minMaxStep'] : array();
			$wrapper_class = 'wp-block-gutena-number-field wp-block-gutena-field-group field-group-type-number standalone-number-field';
			$input_classes = self::input_classes( 'number', $is_required, $autocomplete );
			$required_attr = $is_required ? ' required' : '';
			$min_attr      = self::numeric_attr( 'min', $min_max_step );
			$max_attr      = self::numeric_attr( 'max', $min_max_step );
			$step_attr     = self::numeric_attr( 'step', $min_max_step );

			$html  = sprintf( '<div class="%s">', esc_attr( $wrapper_class ) );
			$html .= sprintf(
				'<label for="%s" class="heading-input-label-gutena">%s%s</label>',
				esc_attr( $name_attr ),
				esc_html( $field_name ),
				$is_required ? ' *' : ''
			);
			$html .= '<div class="wp-block-gutena-form-field">';
			$html .= sprintf(
				'<input id="%1$s" name="%1$s" type="number" class="%2$s" placeholder="%3$s" value="%4$s"%5$s%6$s%7$s autocomplete="%8$s"%9$s/>',
				esc_attr( $name_attr ),
				esc_attr( $input_classes ),
				esc_attr( $placeholder ),
				esc_attr( $default_value ),
				$min_attr,
				$max_attr,
				$step_attr,
				esc_attr( self::autocomplete_attr( $autocomplete, 'on' ) ),
				$required_attr
			);
			$html .= '</div>';
			if ( '' !== $description ) {
				$html .= sprintf( '<p class="gutena-forms-number-field-description">%s</p>', esc_html( $description ) );
			}
			$html .= '<p class="gutena-forms-field-error-msg"></p></div>';

			return $html;
		}

		/**
		 * @param array $attrs Field attributes.
		 * @return string
		 */
		private static function render_dropdown_field( $attrs ) {
			$name_attr      = $attrs['nameAttr'];
			$field_name     = $attrs['fieldName'];
			$is_required    = ! empty( $attrs['isRequired'] );
			$select_options = isset( $attrs['selectOptions'] ) && is_array( $attrs['selectOptions'] ) ? $attrs['selectOptions'] : array();
			$autocomplete   = ! empty( $attrs['autocomplete'] );
			$description    = isset( $attrs['description'] ) ? $attrs['description'] : '';
			$label_id       = $name_attr . '-gf-label';
			$native_id      = $name_attr . '__gf-native';
			$listbox_id     = $name_attr . '__gf-listbox';
			$wrapper_class  = 'wp-block-gutena-dropdown-field wp-block-gutena-field-group field-group-type-select standalone-dropdown-field';
			$field_classes  = trim( 'gutena-forms-field select-field' . ( $is_required ? ' required-field' : '' ) . ( $autocomplete ? ' autocomplete' : '' ) );
			$required_attr  = $is_required ? ' required' : '';
			$initial_label  = $is_required ? __( 'Select an Option', 'gutena-forms' ) : '';

			if ( ! $is_required ) {
				foreach ( $select_options as $option ) {
					if ( '' !== (string) $option ) {
						$initial_label = $option;
						break;
					}
				}
			}

			$html  = sprintf( '<div class="%s">', esc_attr( $wrapper_class ) );
			$html .= sprintf(
				'<label id="%1$s" for="%2$s" class="heading-input-label-gutena">%3$s%4$s</label>',
				esc_attr( $label_id ),
				esc_attr( $name_attr ),
				esc_html( $field_name ),
				$is_required ? ' *' : ''
			);
			$html .= '<div class="wp-block-gutena-form-field">';
			$html .= sprintf(
				'<div class="gf-dropdown-custom" data-gf-dropdown-custom="1" data-gf-field-label="%s">',
				esc_attr( $field_name )
			);
			$html .= sprintf(
				'<select id="%1$s" name="%2$s" class="gf-dropdown-custom__native %3$s" tabindex="-1" aria-hidden="true" aria-labelledby="%4$s" autocomplete="%5$s"%6$s>',
				esc_attr( $native_id ),
				esc_attr( $name_attr ),
				esc_attr( $field_classes ),
				esc_attr( $label_id ),
				esc_attr( self::autocomplete_attr( $autocomplete, 'on' ) ),
				$required_attr
			);
			if ( $is_required ) {
				$html .= sprintf(
					'<option value="select">%s</option>',
					esc_html( __( 'Select an Option', 'gutena-forms' ) )
				);
			}
			foreach ( $select_options as $option ) {
				if ( '' === (string) $option ) {
					continue;
				}
				$html .= sprintf( '<option value="%s">%s</option>', esc_attr( $option ), esc_html( $option ) );
			}
			$html .= '</select>';
			$html .= sprintf(
				'<button type="button" id="%1$s" class="gf-dropdown-custom__trigger" aria-haspopup="listbox" aria-expanded="false" aria-controls="%2$s" aria-labelledby="%3$s"><span class="gf-dropdown-custom__value" aria-hidden="true">%4$s</span><span class="gf-dropdown-custom__icon" aria-hidden="true"></span></button>',
				esc_attr( $name_attr ),
				esc_attr( $listbox_id ),
				esc_attr( $label_id ),
				esc_html( $initial_label )
			);
			$html .= sprintf(
				'<div class="gf-dropdown-custom__popover" hidden><ul id="%1$s" class="gf-dropdown-custom__list" role="listbox" tabindex="-1" aria-labelledby="%2$s"></ul></div>',
				esc_attr( $listbox_id ),
				esc_attr( $label_id )
			);
			$html .= '</div></div>';
			if ( '' !== $description ) {
				$html .= sprintf( '<p class="gutena-forms-dropdown-field-description">%s</p>', esc_html( $description ) );
			}
			$html .= '<p class="gutena-forms-field-error-msg"></p></div>';

			return $html;
		}

		/**
		 * @param array $attrs Field attributes.
		 * @return string
		 */
		private static function render_radio_field( $attrs ) {
			$name_attr       = $attrs['nameAttr'];
			$field_name      = $attrs['fieldName'];
			$is_required     = ! empty( $attrs['isRequired'] );
			$select_options  = isset( $attrs['selectOptions'] ) && is_array( $attrs['selectOptions'] ) ? $attrs['selectOptions'] : array();
			$options_inline  = ! empty( $attrs['optionsInline'] );
			$options_columns = isset( $attrs['optionsColumns'] ) ? (int) $attrs['optionsColumns'] : 0;
			$autocomplete    = ! empty( $attrs['autocomplete'] );
			$description     = isset( $attrs['description'] ) ? $attrs['description'] : '';
			$wrapper_class   = 'wp-block-gutena-radio-field wp-block-gutena-field-group field-group-type-radio standalone-radio-field';
			$field_classes   = self::option_field_classes( 'radio', $is_required, $options_inline, $options_columns, $autocomplete );

			$html  = sprintf( '<div class="%s"><fieldset><legend><span class="heading-input-label-gutena">%s%s</span></legend><div class="%s">',
				esc_attr( $wrapper_class ),
				esc_html( $field_name ),
				$is_required ? ' *' : '',
				esc_attr( $field_classes )
			);

			foreach ( $select_options as $key => $option ) {
				if ( '' === (string) $option ) {
					continue;
				}
				$opt_id = $name_attr . '_' . $key;
				$html  .= sprintf(
					'<label class="radio-container" for="%1$s"><div>%2$s</div><div><input id="%1$s" type="radio" name="%3$s" value="%4$s" autocomplete="%5$s"/><span class="checkmark"></span></div></label>',
					esc_attr( $opt_id ),
					esc_html( $option ),
					esc_attr( $name_attr ),
					esc_attr( $option ),
					esc_attr( self::autocomplete_attr( $autocomplete, 'on' ) )
				);
			}

			$html .= '</div></fieldset>';
			if ( '' !== $description ) {
				$html .= sprintf( '<p class="gutena-forms-radio-field-description">%s</p>', esc_html( $description ) );
			}
			$html .= '<p class="gutena-forms-field-error-msg"></p></div>';

			return $html;
		}

		/**
		 * @param array $attrs Field attributes.
		 * @return string
		 */
		private static function render_checkbox_field( $attrs ) {
			$name_attr       = $attrs['nameAttr'];
			$field_name      = $attrs['fieldName'];
			$is_required     = ! empty( $attrs['isRequired'] );
			$select_options  = isset( $attrs['selectOptions'] ) && is_array( $attrs['selectOptions'] ) ? $attrs['selectOptions'] : array();
			$options_inline  = ! empty( $attrs['optionsInline'] );
			$options_columns = isset( $attrs['optionsColumns'] ) ? (int) $attrs['optionsColumns'] : 0;
			$description     = isset( $attrs['description'] ) ? $attrs['description'] : '';
			$wrapper_class   = 'wp-block-gutena-checkbox-field wp-block-gutena-field-group field-group-type-checkbox standalone-checkbox-field';
			$field_classes   = self::option_field_classes( 'checkbox', $is_required, $options_inline, $options_columns, false );
			$name_brackets   = $name_attr . '[]';

			$html  = sprintf( '<div class="%s"><fieldset><legend><span class="heading-input-label-gutena">%s%s</span></legend><div class="%s">',
				esc_attr( $wrapper_class ),
				esc_html( $field_name ),
				$is_required ? ' *' : '',
				esc_attr( $field_classes )
			);

			foreach ( $select_options as $index => $option ) {
				if ( '' === (string) $option ) {
					continue;
				}
				$opt_id = $name_attr . '_' . $index;
				$html  .= sprintf(
					'<label class="checkbox-container" for="%1$s">%2$s<input id="%1$s" type="checkbox" name="%3$s" value="%4$s"/><span class="checkmark"></span></label>',
					esc_attr( $opt_id ),
					esc_html( $option ),
					esc_attr( $name_brackets ),
					esc_attr( $option )
				);
			}

			$html .= '</div></fieldset>';
			if ( '' !== $description ) {
				$html .= sprintf( '<p class="gutena-forms-checkbox-field-description">%s</p>', esc_html( $description ) );
			}
			$html .= '<p class="gutena-forms-field-error-msg"></p></div>';

			return $html;
		}

		/**
		 * @param array $attrs Field attributes.
		 * @return string
		 */
		private static function render_range_field( $attrs ) {
			$name_attr     = $attrs['nameAttr'];
			$field_name    = $attrs['fieldName'];
			$is_required   = ! empty( $attrs['isRequired'] );
			$default_value = isset( $attrs['defaultValue'] ) ? $attrs['defaultValue'] : '';
			$min_max_step  = isset( $attrs['minMaxStep'] ) && is_array( $attrs['minMaxStep'] ) ? $attrs['minMaxStep'] : array();
			$description   = isset( $attrs['description'] ) ? $attrs['description'] : '';
			$wrapper_class = 'wp-block-gutena-range-field wp-block-gutena-field-group field-group-type-range standalone-range-field';
			$field_class   = trim( 'gutena-forms-field range-field' . ( $is_required ? ' required-field' : '' ) );
			$range_value   = self::range_value( $default_value, $min_max_step );
			$required_attr = $is_required ? ' required' : '';

			$html  = sprintf( '<div class="%s">', esc_attr( $wrapper_class ) );
			$html .= sprintf(
				'<label for="%s" class="heading-input-label-gutena">%s%s</label>',
				esc_attr( $name_attr ),
				esc_html( $field_name ),
				$is_required ? ' *' : ''
			);
			$html .= '<div class="wp-block-gutena-form-field gutena-forms-range-field"><div class="gf-range-container">';
			$html .= sprintf(
				'<input id="%1$s" name="%1$s" type="range" class="%2$s" value="%3$s"%4$s%5$s%6$s%7$s/>',
				esc_attr( $name_attr ),
				esc_attr( $field_class ),
				esc_attr( $range_value ),
				self::numeric_attr( 'min', $min_max_step ),
				self::numeric_attr( 'max', $min_max_step ),
				self::numeric_attr( 'step', $min_max_step ),
				$required_attr
			);
			$html .= '<p class="gf-range-values"><span class="gf-prefix-value-wrapper">';
			if ( self::has_numeric_value( $min_max_step, 'step' ) ) {
				$html .= sprintf(
					'<span>Step: &nbsp;</span><span class="gf-value">%s,</span>&nbsp;',
					esc_html( (string) $min_max_step['step'] )
				);
			}
			if ( self::has_numeric_value( $min_max_step, 'max' ) ) {
				$html .= sprintf(
					'<span>Max: &nbsp;</span><span class="gf-value">%s</span>',
					esc_html( (string) $min_max_step['max'] )
				);
			}
			$html .= '</span><span class="gf-prefix-value-wrapper"><span class="gf-value range-input-value"></span></span></p>';
			$html .= '</div></div>';
			if ( '' !== $description ) {
				$html .= sprintf( '<p class="gutena-forms-range-field-description">%s</p>', esc_html( $description ) );
			}
			$html .= '<p class="gutena-forms-field-error-msg"></p></div>';

			return $html;
		}

		/**
		 * @param array $attrs Field attributes.
		 * @return string
		 */
		private static function render_optin_field( $attrs ) {
			$name_attr     = $attrs['nameAttr'];
			$field_name    = $attrs['fieldName'];
			$is_required   = ! isset( $attrs['isRequired'] ) || ! empty( $attrs['isRequired'] );
			$autocomplete  = ! empty( $attrs['autocomplete'] );
			$description   = isset( $attrs['description'] ) ? $attrs['description'] : '';
			$wrapper_class = 'wp-block-gutena-optin-field wp-block-gutena-field-group field-group-type-optin standalone-optin-field';
			$field_classes = trim( 'gutena-forms-field optin-field' . ( $is_required ? ' required-field' : '' ) . ( $autocomplete ? ' autocomplete' : '' ) );

			$html  = sprintf( '<div class="%s"><div class="%s">', esc_attr( $wrapper_class ), esc_attr( $field_classes ) );
			$html .= sprintf(
				'<label class="optin-container" for="%1$s">%2$s<input id="%1$s" type="checkbox" name="%1$s" value="1" autocomplete="%3$s"/><span class="checkmark"></span></label>',
				esc_attr( $name_attr ),
				esc_html( $field_name ),
				esc_attr( self::autocomplete_attr( $autocomplete, 'on' ) )
			);
			$html .= '</div>';
			if ( '' !== $description ) {
				$html .= sprintf( '<p class="gutena-forms-optin-field-description">%s</p>', esc_html( $description ) );
			}
			$html .= '<p class="gutena-forms-field-error-msg"></p></div>';

			return $html;
		}

		/**
		 * @param string $type         Field type slug.
		 * @param bool   $is_required  Whether the field is required.
		 * @param bool   $autocomplete Whether autocomplete is enabled.
		 * @return string
		 */
		private static function input_classes( $type, $is_required, $autocomplete ) {
			$classes = 'gutena-forms-field ' . $type . '-field';
			if ( $is_required ) {
				$classes .= ' required-field';
			}
			$classes .= ' ';
			if ( $autocomplete ) {
				$classes .= 'autocomplete';
			}

			return $classes;
		}

		/**
		 * @param string $type            Field type slug.
		 * @param bool   $is_required       Whether the field is required.
		 * @param bool   $options_inline    Whether options are inline.
		 * @param int    $options_columns   Number of option columns.
		 * @param bool   $autocomplete      Whether autocomplete is enabled.
		 * @return string
		 */
		private static function option_field_classes( $type, $is_required, $options_inline, $options_columns, $autocomplete ) {
			$classes = 'gutena-forms-field ' . $type . '-field';
			if ( $is_required ) {
				$classes .= ' required-field';
			}
			if ( $autocomplete ) {
				$classes .= ' autocomplete';
			}
			if ( $options_inline ) {
				$classes .= ' inline-options';
			} elseif ( $options_columns > 0 ) {
				$classes .= ' has-' . $options_columns . '-col';
			}

			return $classes;
		}

		/**
		 * @param bool   $enabled Whether autocomplete is enabled.
		 * @param string $token   Token when enabled.
		 * @return string
		 */
		private static function autocomplete_attr( $enabled, $token = 'on' ) {
			return $enabled ? $token : 'off';
		}

		/**
		 * @param bool   $enabled   Whether autocomplete is enabled.
		 * @param string $name_attr Field name attribute.
		 * @param string $field_name Field label.
		 * @return string
		 */
		private static function text_autocomplete_attr( $enabled, $name_attr, $field_name ) {
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
		 * @param string $key         Attribute key.
		 * @param array  $min_max_step Min/max/step values.
		 * @return string
		 */
		private static function numeric_attr( $key, $min_max_step ) {
			if ( ! self::has_numeric_value( $min_max_step, $key ) ) {
				return '';
			}

			return sprintf( ' %s="%s"', esc_attr( $key ), esc_attr( (string) $min_max_step[ $key ] ) );
		}

		/**
		 * @param array  $min_max_step Min/max/step values.
		 * @param string $key          Key to inspect.
		 * @return bool
		 */
		private static function has_numeric_value( $min_max_step, $key ) {
			if ( ! isset( $min_max_step[ $key ] ) ) {
				return false;
			}

			$value = $min_max_step[ $key ];

			return '' !== (string) $value || 0 === $value || '0' === $value;
		}

		/**
		 * @param mixed $default_value Default value.
		 * @param array $min_max_step  Min/max/step values.
		 * @return string
		 */
		private static function range_value( $default_value, $min_max_step ) {
			if ( '' !== (string) $default_value || '0' === (string) $default_value ) {
				return (string) $default_value;
			}

			if ( self::has_numeric_value( $min_max_step, 'min' ) ) {
				return (string) $min_max_step['min'];
			}

			return '';
		}
	}
endif;
