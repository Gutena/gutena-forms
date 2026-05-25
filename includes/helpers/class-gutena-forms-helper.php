<?php
/**
 * Gutena Forms Helper
 *
 * @since 1.8.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Helper' ) ) :
	/**
	 * Gutena Forms helper
	 *
	 * @since 1.8.0
	 */
	class Gutena_Forms_Helper {
		/**
		 * Sanitize array
		 *
		 * @since 1.8.0
		 * @param array $array_to_sanitize Array to sanitize.
		 * @param bool  $textarea_sanitize Is textarea.
		 *
		 * @return array
		 */
		public static function sanitize_array( $array_to_sanitize, $textarea_sanitize = false ) {
			if ( ! empty( $array_to_sanitize ) && is_array( $array_to_sanitize ) ) {
				foreach ( (array) $array_to_sanitize as $key => $value ) {
					if ( is_array( $value ) ) {
						$array_to_sanitize[ $key ] = self::sanitize_array( $value );
					} elseif ( 'block_markup' === $key && function_exists( 'wp_kses' ) ) {

						$array_to_sanitize[ $key ] = wp_kses(
							$value,
							array_merge(
								wp_kses_allowed_html( 'post' ),
								array(
									'form'  => array(
										'method' => 1,
										'class'  => 1,
										'style'  => 1,
									),
									'input' => array(
										'type'  => 1,
										'name'  => 1,
										'class' => 1,
										'value' => 1,
									),
								)
							)
						);
					} else {
						$array_to_sanitize[ $key ] = true === $textarea_sanitize ? sanitize_textarea_field( $value ) : sanitize_text_field( $value );
					}
				}
			}
			return $array_to_sanitize;
		}
	}
endif;
