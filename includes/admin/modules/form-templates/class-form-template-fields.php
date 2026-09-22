<?php
/**
 * Helper for building Gutena Forms template field definitions.
 *
 * Field definitions use the same block names and attribute keys as standalone
 * Gutena field blocks (see src/blocks/form-field-blocks).
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Form_Template_Fields' ) ) :
	/**
	 * Factory helpers for template field definitions.
	 *
	 * @since 2.2.0
	 * @package Gutena Forms
	 */
	class Gutena_Forms_Form_Template_Fields {
		/**
		 * Build a field definition array.
		 *
		 * @since 2.2.0
		 * @param string $block_name Registered block name (e.g. gutena/text-field).
		 * @param int    $index      Field index used to build nameAttr (f_0, f_1, ...).
		 * @param string $field_name Display label for the field.
		 * @param string $field_type Field type attribute value.
		 * @param array  $attributes Additional or overriding block attributes.
		 * @return array
		 */
		private static function make_field( $block_name, $index, $field_name, $field_type, $attributes = array() ) {
			return array(
				'block'      => $block_name,
				'attributes' => array_merge(
					array(
						'nameAttr'   => 'f_' . absint( $index ),
						'fieldName'  => $field_name,
						'fieldType'  => $field_type,
						'isRequired' => false,
					),
					$attributes
				),
			);
		}

		/**
		 * Text field.
		 *
		 * @since 2.2.0
		 * @param int    $index      Field index.
		 * @param string $field_name Field label.
		 * @param array  $attributes Optional attribute overrides.
		 * @return array
		 */
		public static function text( $index, $field_name, $attributes = array() ) {
			return self::make_field( 'gutena/text-field', $index, $field_name, 'text', $attributes );
		}

		/**
		 * Email field.
		 *
		 * @since 2.2.0
		 * @param int    $index      Field index.
		 * @param string $field_name Field label.
		 * @param array  $attributes Optional attribute overrides.
		 * @return array
		 */
		public static function email( $index, $field_name, $attributes = array() ) {
			return self::make_field( 'gutena/email-field', $index, $field_name, 'email', $attributes );
		}

		/**
		 * Textarea field.
		 *
		 * @since 2.2.0
		 * @param int    $index      Field index.
		 * @param string $field_name Field label.
		 * @param array  $attributes Optional attribute overrides.
		 * @return array
		 */
		public static function textarea( $index, $field_name, $attributes = array() ) {
			$attributes = array_merge(
				array(
					'textAreaRows' => 5,
				),
				$attributes
			);

			return self::make_field( 'gutena/textarea-field', $index, $field_name, 'textarea', $attributes );
		}

		/**
		 * Number field.
		 *
		 * @since 2.2.0
		 * @param int    $index      Field index.
		 * @param string $field_name Field label.
		 * @param array  $attributes Optional attribute overrides.
		 * @return array
		 */
		public static function number( $index, $field_name, $attributes = array() ) {
			return self::make_field( 'gutena/number-field', $index, $field_name, 'number', $attributes );
		}

		/**
		 * Dropdown (select) field.
		 *
		 * @since 2.2.0
		 * @param int    $index      Field index.
		 * @param string $field_name Field label.
		 * @param array  $options    Select option labels.
		 * @param array  $attributes Optional attribute overrides.
		 * @return array
		 */
		public static function dropdown( $index, $field_name, $options, $attributes = array() ) {
			$attributes = array_merge(
				array(
					'selectOptions' => $options,
				),
				$attributes
			);

			return self::make_field( 'gutena/dropdown-field', $index, $field_name, 'select', $attributes );
		}

		/**
		 * Radio field.
		 *
		 * @since 2.2.0
		 * @param int    $index      Field index.
		 * @param string $field_name Field label.
		 * @param array  $options    Radio option labels.
		 * @param array  $attributes Optional attribute overrides.
		 * @return array
		 */
		public static function radio( $index, $field_name, $options, $attributes = array() ) {
			$attributes = array_merge(
				array(
					'selectOptions' => $options,
				),
				$attributes
			);

			return self::make_field( 'gutena/radio-field', $index, $field_name, 'radio', $attributes );
		}

		/**
		 * Checkbox field.
		 *
		 * @since 2.2.0
		 * @param int    $index      Field index.
		 * @param string $field_name Field label.
		 * @param array  $options    Checkbox option labels.
		 * @param array  $attributes Optional attribute overrides.
		 * @return array
		 */
		public static function checkbox( $index, $field_name, $options, $attributes = array() ) {
			$attributes = array_merge(
				array(
					'selectOptions' => $options,
				),
				$attributes
			);

			return self::make_field( 'gutena/checkbox-field', $index, $field_name, 'checkbox', $attributes );
		}

		/**
		 * Range field.
		 *
		 * @since 2.2.0
		 * @param int    $index      Field index.
		 * @param string $field_name Field label.
		 * @param array  $attributes Optional attribute overrides.
		 * @return array
		 */
		public static function range( $index, $field_name, $attributes = array() ) {
			return self::make_field( 'gutena/range-field', $index, $field_name, 'range', $attributes );
		}

		/**
		 * Opt-in (consent) field.
		 *
		 * @since 2.2.0
		 * @param int    $index      Field index.
		 * @param string $field_name Field label.
		 * @param array  $attributes Optional attribute overrides.
		 * @return array
		 */
		public static function optin( $index, $field_name, $attributes = array() ) {
			$attributes = array_merge(
				array(
					'isRequired' => true,
				),
				$attributes
			);

			return self::make_field( 'gutena/optin-field', $index, $field_name, 'optin', $attributes );
		}
	}
endif;
