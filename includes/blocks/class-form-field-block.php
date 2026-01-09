<?php
/**
 * Class Form Field Block
 *
 * @since 1.6.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Form_Field_Block' ) ) :
	/**
	 * Gutena Forms Form Field Block Class.
	 *
	 * @since 1.6.0
	 */
	class Gutena_Forms_Form_Field_Block {
		/**
		 * The single instance of the class.
		 *
		 * @since 1.6.0
		 * @var Gutena_Forms_Form_Field_Block $instance The single instance of the class.
		 */
		private static $instance;

		/**
		 * Register block.
		 *
		 * @since 1.6.0
		 */
		public function register_block() {
			register_block_type(
				GUTENA_FORMS_DIR_PATH . 'build/form-field',
				array(
					'render_callback' => array( $this, 'render' ),
				)
			);
		}

		/**
		 * Render block callback.
		 *
		 * @since 1.6.0
		 * @param array  $attributes Block attributes.
		 * @param string $content Block content.
		 * @param WP_Block $block Block instance.
		 *
		 * @return string
		 */
		public function render( $attributes, $content, $block ) {
			// No changes if fieldType is empty
			if ( empty( $attributes ) || empty( $attributes['fieldType'] ) || empty( $attributes['nameAttr'] ) ) {
				return $content;
			}

			// Get Block Supports like styles or classNames
			$wrapper_attributes = get_block_wrapper_attributes(
				array(
					'class' => 'gutena-forms-' . esc_attr( $attributes['fieldType'] ) . '-field field-name-' . esc_attr( $attributes['nameAttr'] ) .' '. ( empty( $attributes['optionsInline'] ) ? '':'gf-inline-content'  ),
				)
			);
			// Output Html
			$output = '';

			$inputAttr = '';

			//Check for required attribute
			$inputAttr .= empty( $attributes['isRequired'] ) ? '' : ' required';


			// Text type Input
			if ( in_array( $attributes['fieldType'], array( 'text', 'email', 'number' ) ) ) {
				$output = '<input
				id="' . esc_attr( $attributes['nameAttr'] ) . '"
				' . $this->get_field_attribute( $attributes, array(
						'nameAttr' 		=> 'name',
						'fieldType' 	=> 'type',
						'fieldClasses' 	=> 'class',
						'placeholder' 	=> 'placeholder',
						'maxlength' 	=> 'maxlength',
						'defaultValue' 	=> 'value',
						'minMaxStep' 	=> array(
							'min'=>'min',
							'max'=>'max',
							'step'=>'step',
						),
					) ) .
					' ' . esc_attr( $inputAttr ) . '
				/>';
			}

			//Input Range slider
			if ( 'range' === $attributes['fieldType'] ) {
				$output = '<div class="gf-range-container">
				<input
				id="' . esc_attr( $attributes['nameAttr'] ) . '"
				' . $this->get_field_attribute( $attributes, array(
						'nameAttr' 		=> 'name',
						'fieldType' 	=> 'type',
						'fieldClasses' 	=> 'class',
						'minMaxStep' 	=> array(
							'min'=>'min',
							'max'=>'max',
							'step'=>'step',
						),
					) ) .
					' ' . esc_attr( $inputAttr ) . '
				/><p class="gf-range-values"> ';

				//Range min value
				if ( ! empty( $attributes['minMaxStep'] ) && isset( $attributes['minMaxStep']['min'] ) ) {
					$output .= '
					<span class="gf-prefix-value-wrapper">
						<span class="gf-prefix">' . $this->check_esc_attr( $attributes, 'preFix' ) . '</span>
						<span class="gf-value">' . $this->check_esc_attr( $attributes['minMaxStep'], 'min' ) . '</span>
						<span class="gf-suffix">' . $this->check_esc_attr( $attributes, 'sufFix' ) . '</span>
					</span>';
				}

				//range input value
				$output .= '<span class="gf-prefix-value-wrapper">
					<span class="gf-prefix">' . $this->check_esc_attr( $attributes, 'preFix' ) . '</span>
					<span class="gf-value range-input-value"></span>
					<span class="gf-suffix">' . $this->check_esc_attr( $attributes, 'sufFix' ) . '</span>
				</span>';

				//Range max value
				if ( ! empty( $attributes['minMaxStep'] ) && ! empty( $attributes['minMaxStep']['max'] ) ) {
					$output .= '<span class="gf-prefix-value-wrapper">
						<span class="gf-prefix">' . $this->check_esc_attr( $attributes, 'preFix' ) . '</span>
						<span class="gf-value">' . $this->check_esc_attr( $attributes['minMaxStep'], 'max' ) . '</span>
						<span class="gf-suffix">' . $this->check_esc_attr( $attributes, 'sufFix' ) . '</span>
					</span>
					';
				}

				$output .='</p></div>';
			}

			// Textarea type Input
			if ( 'textarea' === $attributes['fieldType'] ) {
				$output = '<textarea
				id="' . esc_attr( $attributes['nameAttr'] ) . '"
				' . $this->get_field_attribute( $attributes, array(
						'nameAttr' 		=> 'name',
						'textAreaRows' 	=> 'rows',
						'fieldClasses' 	=> 'class',
						'placeholder'	=> 'placeholder',
						'maxlength' 	=> 'maxlength',
					) ) .
					' ' . esc_attr( $inputAttr ) . '
				>'. esc_html( empty( $attributes['defaultValue'] ) ? '' : $attributes['defaultValue'] ).'</textarea>';
			}

			// Select type Input
			if ( 'select' === $attributes['fieldType'] ) {
				$output = '<select
				id="' . esc_attr( $attributes['nameAttr'] ) . '"
				' . $this->get_field_attribute( $attributes, array(
						'nameAttr' 		=> 'name',
						'fieldClasses' 	=> 'class',
					) ) .
					' ' . esc_attr( $inputAttr ) . '
				 >';
				if ( ! empty( $attributes['selectOptions'] ) && is_array( $attributes['selectOptions'] ) ) {
					foreach ( $attributes['selectOptions'] as $option ) {
						$output .= '<option value="' . esc_attr( $option ) . '" >' . esc_attr( $option ) . '</option>';
					}
				}
				$output .= '</select>';
			}

			// radio type Input
			if ( in_array( $attributes['fieldType'], array( 'radio', 'checkbox', 'optin' ) ) ) {
				$output = '<div
				' . $this->get_field_attribute( $attributes, array(
						'fieldClasses' 	=> 'class',
					) ) .
					'
				>';
				if ( 'optin' == $attributes['fieldType'] ) {
					$output .= '<label for="' . esc_attr( $attributes['nameAttr'] ) . '" class="' . esc_attr( $attributes['fieldType'] ) . '-container">
						<input
						id="' . esc_attr( $attributes['nameAttr'] ) . '"
						 type="checkbox" name="' . esc_attr( $attributes['nameAttr']  ) .
						'" value="1" >
						<span class="checkmark"></span>
					  </label>';
				} else if ( ! empty( $attributes['selectOptions'] ) && is_array( $attributes['selectOptions'] ) ) {
					foreach ( $attributes['selectOptions'] as $option ) {
						$output .= '<label class="' . esc_attr( $attributes['fieldType'] ) . '-container">' . esc_attr( $option ) . '
						<input type="' . esc_attr( $attributes['fieldType'] ) . '" name="' . esc_attr( 'radio' === $attributes['fieldType'] ? $attributes['nameAttr'] : $attributes['nameAttr'].'[]'  ) .
							'" value="' . esc_attr( $option ) . '" >
						<span class="checkmark"></span>
					  </label>';
					}
				}
				$output .= '</div>';
			}

			//filter output field
			$output = apply_filters( 'gutena_forms_render_field', $output, $attributes, $inputAttr, $block );

			//render field styles
			if ( ! empty( $attributes['fieldStyle'] ) && ! empty( $block->context['gutena-forms/formID'] ) && function_exists( 'wp_add_inline_style' ) ) {
				wp_add_inline_style(
					'gutena-forms-style',
					'.wp-block-gutena-forms.' . esc_attr( $block->context['gutena-forms/formID'] ) . ' .gutena-forms-' . esc_attr( $attributes['fieldType'] ) . '-field {
					' . esc_attr( $attributes['fieldStyle'] ) . '
					}'
				);
			}

			// output
			return sprintf(
				'<div %1$s>%2$s</div>',
				$wrapper_attributes,
				$output
			);
		}

		/**
		 * Check esc attr
		 *
		 * @since 1.0.0
		 * @param array  $attributes Array of attributes.
		 * @param string $key attribute key.
		 *
		 * @return string|null
		 */
		private function check_esc_attr( $attributes, $key = '' ) {

			if ( empty( $key ) ) {
				return ( isset( $attributes ) && ! is_array( $attributes ) ) ? esc_attr( $attributes ): '';
			}

			return isset( $attributes[ $key ] ) ? esc_attr( $attributes[ $key ] ): '';
		}

		/**
		 * Getting field attributes
		 *
		 * @since 1.0.0
		 * @param array $attributes Attributes.
		 * @param array $check_attr Attributes to check.
		 *
		 * @return string
		 */
		public function get_field_attribute( $attributes , $check_attr = array() ) {

			//field_attr to render inside field
			$field_attr = '';

			//check if values are empty
			if ( empty( $attributes ) || empty( $check_attr ) ) {
				return $field_attr;
			}

			foreach ( $check_attr as $check => $input_attr ) {
				//continue loop if empty except zero value
				if ( ! isset( $attributes[$check] ) || ( empty( $attributes[$check] ) && '0' != $attributes[$check]  ) || empty( $input_attr ) ) {
					continue;
				}

				//if input attr is also an array then check recursively
				if ( is_array( $input_attr ) ) {
					$field_attr .= $this->get_field_attribute( $attributes[$check], $input_attr );
					continue;
				}

				$field_attr .= ' ' . sanitize_key( $input_attr ) . '="' . esc_attr( $attributes[$check] ) .'"';
			}

			return $field_attr;
		}

		/**
		 * Get the single instance of the class.
		 *
		 * @since 1.6.0
		 * @return Gutena_Forms_Form_Field_Block
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}
	}
endif;
