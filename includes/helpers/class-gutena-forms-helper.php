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
		 * Merge missing block attributes with registered block.json defaults.
		 *
		 * @since 1.9.1
		 * @param string $block_name Block name.
		 * @param array  $attrs      Parsed block attributes.
		 * @return array
		 */
		public static function merge_block_default_attributes( $block_name, $attrs ) {
			$attrs = is_array( $attrs ) ? $attrs : array();

			if ( empty( $block_name ) || ! class_exists( 'WP_Block_Type_Registry' ) ) {
				return $attrs;
			}

			$block_type = WP_Block_Type_Registry::get_instance()->get_registered( $block_name );
			if ( empty( $block_type ) || empty( $block_type->attributes ) ) {
				return $attrs;
			}

			foreach ( $block_type->attributes as $attribute_name => $attribute_def ) {
				if ( ! array_key_exists( $attribute_name, $attrs ) && array_key_exists( 'default', $attribute_def ) ) {
					$attrs[ $attribute_name ] = $attribute_def['default'];
				}
			}

			return $attrs;
		}

		/**
		 * Resolve stored form field schema with block defaults and post content.
		 *
		 * @since 1.9.1
		 * @param string $form_id            Form ID.
		 * @param array  $stored_form_fields Stored form field schema.
		 * @return array
		 */
		public static function resolve_form_fields_schema( $form_id, $stored_form_fields ) {
			$stored_form_fields = is_array( $stored_form_fields ) ? $stored_form_fields : array();
			$parsed_form_fields = self::parse_form_fields_from_post( $form_id );

			if ( empty( $parsed_form_fields ) && empty( $stored_form_fields ) ) {
				return $stored_form_fields;
			}

			$resolved = array();
			$name_attrs = array_unique(
				array_merge(
					array_keys( $stored_form_fields ),
					array_keys( $parsed_form_fields )
				)
			);

			foreach ( $name_attrs as $name_attr ) {
				$stored = isset( $stored_form_fields[ $name_attr ] ) ? $stored_form_fields[ $name_attr ] : array();
				$parsed = isset( $parsed_form_fields[ $name_attr ] ) ? $parsed_form_fields[ $name_attr ] : array();
				$block_name = ! empty( $parsed['blockName'] ) ? $parsed['blockName'] : ( ! empty( $stored['blockName'] ) ? $stored['blockName'] : '' );
				$merged     = array_merge( $parsed, $stored );
				$resolved[ $name_attr ] = self::merge_block_default_attributes( $block_name, $merged );
			}

			return $resolved;
		}

		/**
		 * Parse form fields for a form ID from the Gutena Forms post content.
		 *
		 * @since 1.9.1
		 * @param string $form_id Form ID.
		 * @return array
		 */
		public static function parse_form_fields_from_post( $form_id ) {
			$post_id = self::find_post_id_by_form_id( $form_id );
			if ( empty( $post_id ) ) {
				return array();
			}

			$post = get_post( $post_id );
			if ( empty( $post ) || empty( $post->post_content ) ) {
				return array();
			}

			$form_fields = array();
			self::walk_blocks_for_form_fields( parse_blocks( $post->post_content ), $form_id, $form_fields );

			return $form_fields;
		}

		/**
		 * Find Gutena Forms post ID by form ID.
		 *
		 * @since 1.9.1
		 * @param string $form_id Form ID.
		 * @return int
		 */
		private static function find_post_id_by_form_id( $form_id ) {
			$posts = get_posts(
				array(
					'post_type'              => 'gutena_forms',
					'post_status'            => array( 'publish', 'draft', 'private', 'pending' ),
					'posts_per_page'         => 1,
					'meta_key'               => 'gutena_form_id',
					'meta_value'             => $form_id,
					'fields'                 => 'ids',
					'no_found_rows'          => true,
					'update_post_meta_cache' => false,
					'update_post_term_cache' => false,
				)
			);

			return ! empty( $posts[0] ) ? (int) $posts[0] : 0;
		}

		/**
		 * Walk parsed blocks and collect form field attributes.
		 *
		 * @since 1.9.1
		 * @param array  $blocks          Parsed blocks.
		 * @param string $target_form_id  Target form ID.
		 * @param array  $form_fields     Collected fields (by reference).
		 * @param string $current_form_id Current form ID while walking.
		 */
		private static function walk_blocks_for_form_fields( $blocks, $target_form_id, &$form_fields, $current_form_id = '' ) {
			if ( empty( $blocks ) || ! is_array( $blocks ) ) {
				return;
			}

			foreach ( $blocks as $block ) {
				if ( ! empty( $block['blockName'] ) && 'gutena/forms' === $block['blockName'] && ! empty( $block['attrs']['formID'] ) ) {
					$current_form_id = $block['attrs']['formID'];
				}

				if ( $current_form_id === $target_form_id && ! empty( $block['blockName'] ) && ! empty( $block['attrs']['nameAttr'] ) && self::is_form_field_block( $block['blockName'] ) ) {
					$attrs = self::merge_block_default_attributes( $block['blockName'], $block['attrs'] );
					$attrs['blockName'] = $block['blockName'];
					$form_fields[ $block['attrs']['nameAttr'] ] = $attrs;
				}

				if ( ! empty( $block['innerBlocks'] ) ) {
					self::walk_blocks_for_form_fields( $block['innerBlocks'], $target_form_id, $form_fields, $current_form_id );
				}
			}
		}

		/**
		 * Whether a block name represents a form input field.
		 *
		 * @since 1.9.1
		 * @param string $block_name Block name.
		 * @return bool
		 */
		private static function is_form_field_block( $block_name ) {
			if ( 'gutena/form-field' === $block_name ) {
				return true;
			}

			if ( 0 === strpos( $block_name, 'gutena/' ) && preg_match( '/-field(-group)?$/', $block_name ) ) {
				return true;
			}

			return (bool) apply_filters( 'gutena_forms_is_form_field_block', false, $block_name );
		}

		/**
		 * Remove reCAPTCHA secret keys from settings intended for the frontend.
		 *
		 * @since 1.9.2
		 * @param array $settings Raw reCAPTCHA settings.
		 * @return array
		 */
		public static function strip_recaptcha_secret_keys( $settings ) {
			$settings = is_array( $settings ) ? $settings : array();

			unset( $settings['secret_key'], $settings['v2_secret_key'], $settings['v3_secret_key'] );

			return $settings;
		}

		/**
		 * Remove Cloudflare Turnstile secret keys from settings intended for the frontend.
		 *
		 * @since 1.9.2
		 * @param array $settings Raw Turnstile settings.
		 * @return array
		 */
		public static function strip_turnstile_secret_keys( $settings ) {
			$settings = is_array( $settings ) ? $settings : array();

			unset( $settings['secret_key'] );

			return $settings;
		}

		/**
		 * Strip captcha secret keys from form block attrs when global defaults are used.
		 *
		 * @since 1.9.2
		 * @param array $form_attrs Form block attributes.
		 * @return array
		 */
		public static function strip_captcha_secrets_from_form_attrs( $form_attrs ) {
			$form_attrs = is_array( $form_attrs ) ? $form_attrs : array();

			if ( ! empty( $form_attrs['recaptcha'] ) && is_array( $form_attrs['recaptcha'] ) ) {
				$use_global = ! isset( $form_attrs['recaptcha']['defaultSettings'] )
					|| false !== $form_attrs['recaptcha']['defaultSettings'];

				if ( $use_global ) {
					$form_attrs['recaptcha'] = self::strip_recaptcha_secret_keys( $form_attrs['recaptcha'] );
				}
			}

			if ( ! empty( $form_attrs['cloudflareTurnstile'] ) && is_array( $form_attrs['cloudflareTurnstile'] ) ) {
				$use_global = ! isset( $form_attrs['cloudflareTurnstile']['defaultSettings'] )
					|| false !== $form_attrs['cloudflareTurnstile']['defaultSettings'];

				if ( $use_global ) {
					$form_attrs['cloudflareTurnstile'] = self::strip_turnstile_secret_keys( $form_attrs['cloudflareTurnstile'] );
				}
			}

			return $form_attrs;
		}

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
