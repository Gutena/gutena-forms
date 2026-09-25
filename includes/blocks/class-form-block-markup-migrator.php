<?php
/**
 * One-time migration for gutena/forms block save markup.
 *
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Form_Block_Markup_Migrator' ) ) :
	/**
	 * Migrates legacy form block HTML to the content-wrapper save markup.
	 *
	 * @since 2.0.0
	 */
	class Gutena_Forms_Form_Block_Markup_Migrator {
		const MIGRATION_FLAG = 'gutena_forms_content_wrapper_migration_v1';

		/**
		 * Boot migration hook.
		 *
		 * @since 2.0.0
		 */
		public static function init() {
			add_action( 'admin_init', array( __CLASS__, 'maybe_migrate' ), 5 );
		}

		/**
		 * Run migration once for all stored forms.
		 *
		 * @since 2.0.0
		 */
		public static function maybe_migrate() {
			if ( get_option( self::MIGRATION_FLAG ) ) {
				return;
			}

			if ( ! function_exists( 'update_option' ) ) {
				return;
			}

			global $wpdb;

			if ( empty( $wpdb ) ) {
				return;
			}

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$post_ids = $wpdb->get_col(
				"SELECT ID FROM {$wpdb->posts}
				WHERE post_content LIKE '%wp:gutena/forms%'
				AND post_status NOT IN ( 'trash', 'auto-draft', 'inherit' )"
			);

			if ( ! empty( $post_ids ) ) {
				foreach ( $post_ids as $post_id ) {
					$post = get_post( (int) $post_id );
					if ( empty( $post ) || empty( $post->post_content ) ) {
						continue;
					}

					$migrated_content = self::migrate_content( $post->post_content );
					if ( $migrated_content !== $post->post_content ) {
						wp_update_post(
							array(
								'ID'           => (int) $post_id,
								'post_content' => $migrated_content,
							)
						);
					}
				}
			}

			self::migrate_form_schema_options();

			update_option( self::MIGRATION_FLAG, GUTENA_FORMS_VERSION );
		}

		/**
		 * Migrate serialized gutena/forms blocks inside post content.
		 *
		 * @since 2.0.0
		 * @param string $content Post content.
		 * @return string
		 */
		public static function migrate_content( $content ) {
			if ( empty( $content ) || false === strpos( $content, 'wp:gutena/forms' ) ) {
				return $content;
			}

			$parts = explode( '<!-- wp:gutena/forms', $content );
			if ( count( $parts ) < 2 ) {
				return $content;
			}

			$migrated = array_shift( $parts );
			foreach ( $parts as $part ) {
				$migrated .= '<!-- wp:gutena/forms' . self::migrate_single_form_block_part( $part );
			}

			return $migrated;
		}

		/**
		 * Migrate one gutena/forms block fragment.
		 *
		 * @since 2.0.0
		 * @param string $part Block fragment beginning after `<!-- wp:gutena/forms`.
		 * @return string
		 */
		private static function migrate_single_form_block_part( $part ) {
			if ( false !== strpos( $part, 'gutena-forms-content-wrapper' ) ) {
				return self::strip_form_inline_padding_from_part( $part );
			}

			$migrated = preg_replace_callback(
				'/(<form\b[^>]*>)(\s*<input\b[^>]*\bname=[\'"]formid[\'"][^>]*\/?>)([\s\S]*?)(<\/form>\s*<!-- \/wp:gutena\/forms -->)/i',
				static function ( $matches ) {
					$form_open = self::strip_form_inline_padding_from_tag( $matches[1] );

					return $form_open .
						$matches[2] .
						'<div class="gutena-forms-content-wrapper">' .
						$matches[3] .
						'</div>' .
						$matches[4];
				},
				$part,
				1
			);

			if ( null === $migrated ) {
				return $part;
			}

			return $migrated;
		}

		/**
		 * Remove inline padding styles from a saved form opening tag.
		 *
		 * @since 2.0.0
		 * @param string $form_tag Opening form tag markup.
		 * @return string
		 */
		private static function strip_form_inline_padding_from_tag( $form_tag ) {
			return preg_replace_callback(
				'/\sstyle=(["\'])([^"\']*)\1/i',
				static function ( $matches ) {
					$style = preg_replace( '/padding-(top|right|bottom|left)\s*:\s*[^;"]+;?\s*/i', '', $matches[2] );
					$style = trim( $style, '; ' );

					if ( '' === $style ) {
						return '';
					}

					return ' style="' . $style . '"';
				},
				$form_tag
			);
		}

		/**
		 * Strip inline padding from forms that already contain the content wrapper.
		 *
		 * @since 2.0.0
		 * @param string $part Block fragment.
		 * @return string
		 */
		private static function strip_form_inline_padding_from_part( $part ) {
			return preg_replace_callback(
				'/<form\b[^>]*>/i',
				static function ( $matches ) {
					return self::strip_form_inline_padding_from_tag( $matches[0] );
				},
				$part,
				1
			);
		}

		/**
		 * Migrate cached block markup stored in form schema options.
		 *
		 * @since 2.0.0
		 */
		private static function migrate_form_schema_options() {
			global $wpdb;

			if ( empty( $wpdb ) ) {
				return;
			}

			$like = $wpdb->esc_like( GUTENA_FORMS_SCHEMA_OPTION_PREFIX ) . '%';
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$option_names = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",
					$like
				)
			);

			if ( empty( $option_names ) ) {
				return;
			}

			foreach ( $option_names as $option_name ) {
				$schema = get_option( $option_name );
				if ( empty( $schema['block_markup'] ) || ! is_string( $schema['block_markup'] ) ) {
					continue;
				}

				$migrated_markup = self::migrate_content( $schema['block_markup'] );
				if ( $migrated_markup === $schema['block_markup'] ) {
					continue;
				}

				$schema['block_markup'] = $migrated_markup;
				update_option( $option_name, $schema );
			}
		}
	}

	Gutena_Forms_Form_Block_Markup_Migrator::init();
endif;
