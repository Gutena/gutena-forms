<?php
/**
 * Class Gutena_Forms_Helper
 *
 * @since 1.6.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Helper' ) ) :
	/**
	 * Gutena Forms Helper Class
	 *
	 * @since 1.6.0
	 */
	class Gutena_Forms_Helper {
		/**
		 * Check if Pro version is active.
		 *
		 * @since 1.6.0
		 * @return bool
		 */
		public static function has_pro() {
			if ( did_action( 'plugins_loaded' ) ) {
				if ( defined( 'GUTENA_FORMS__PRO_LOADED' ) && GUTENA_FORMS__PRO_LOADED ) {
					return class_exists( 'Gutena_Forms_Pro' );
				}
			}

			return class_exists( 'Gutena_Forms_Pro' );
		}

		/**
		 * Sanitize array.
		 *
		 * @since 1.6.0
		 * @param array $array Array to sanitize.
		 * @param bool  $textarea is textarea.
		 *
		 * @return array
		 */
		public static function sanitize_array( $array, $textarea = false ) {
			if ( ! empty( $array ) && is_array( $array ) ) {
				foreach ( (array) $array as $key => $value ) {
					if ( is_array( $value ) ) {
						$array[ $key ] = self::sanitize_array( $value );
					} else if ( 'block_markup' === $key && function_exists( 'wp_kses' ) ) {

						$array[ $key ] = wp_kses(
							$value,
							array_merge(
								wp_kses_allowed_html( 'post' ),
								array(
									'form' => array(
										'method'=> 1,
										'class'	=> 1,
										'style'	=> 1,
									),
									'input' => array(
										'type'=> 1,
										'name'	=> 1,
										'class'	=> 1,
										'value'	=> 1,
									),
								)
							)
						);
						//$array[ $key ] = wp_kses_post( $value );
					} else {
						$array[ $key ] = true === $textarea ? sanitize_textarea_field( $value )  : sanitize_text_field( $value );
					}
				}
			}
			return $array;
		}

		/**
		 * Check and escape attribute.
		 *
		 * @since 1.6.0
		 * @param array  $attributes The attributes array.
		 * @param string $key The key to check in the attributes array.
		 *
		 * @return string|null
		 */
		public static function check_esc_attr( $attributes, $key = '' ) {
			if ( empty( $key ) ) {
				return ( isset( $attributes ) && ! is_array( $attributes ) ) ? esc_attr( $attributes ): '';
			}

			return isset( $attributes[ $key ] ) ? esc_attr( $attributes[ $key ] ): '';
		}

		/**
		 * Get field attribute string from attributes array.
		 *
		 * @since 1.6.0
		 * @param array $attributes The attributes array.
		 * @param array $check_attr The attributes to check and their corresponding HTML attribute names.
		 * @return string
		 */
		public static function get_field_attribute( $attributes, $check_attr = array() ) {
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
					$field_attr .= self::get_field_attribute( $attributes[$check], $input_attr );
					continue;
				}

				$field_attr .= ' ' . sanitize_key( $input_attr ) . '="' . esc_attr( $attributes[$check] ) .'"';
			}

			return $field_attr;
		}

		/**
		 * Replace last occurrence of a string.
		 *
		 * @since 1.6.0
		 * @param string $search Search string.
		 * @param string $replace Replace string.
		 * @param string $str The original string.
		 *
		 * @return string
		 */
		public static function str_last_replace( $search, $replace, $str ) {
			$pos = strripos( $str, $search );

			if ( $pos !== false ) {
				$str = substr_replace( $str, $replace, $pos, strlen( $search ) );
			}

			return $str;
		}
	}
endif;
