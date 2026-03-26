<?php
/**
 * Handle Save Form
 *
 * @since 1.8.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_handle_Save_Form' ) ) :
	class Gutena_Forms_handle_Save_Form {
		private static $instance;

		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		private function __construct() {
			add_action( 'save_post', array( $this, 'save_post' ), 10, 3 );
			add_action( 'admin_init', array( $this, 'save_form_settings_env' ) );
		}

		public function save_form_settings_env() {

			$gutena_form_settings = apply_filters( 'gutena_forms__settings', array() );

			$settings_to_verify = apply_filters(
				'gutena_forms__settings_to_verify',
				array(
					'honeypot',
					'google-recaptcha',
					'cloudflare-turnstile',
					'validation-messages',
				)
			);
			foreach ( $gutena_form_settings as $k => $v ) {
				if ( in_array( $k, $settings_to_verify ) ) {
					$settings = new $v();
					if ( $settings instanceof Gutena_Forms_Forms_Settings ) {
						if ( ! empty( $settings->settings ) ) {
							return;
						}
					}
				}
			}

			$post = get_posts(
				array(
					'post_type' => 'gutena_forms',
					'numberposts' => 1,
					'post_status' => 'publish',
				)
			);

			if ( empty( $post ) ) {
				return;
			}

			$post = $post[0];
//			$this->update_settings( $post );
		}

		/**
		 * @param int     $id
		 * @param WP_Post $post
		 * @param bool    $update
		 */
		public function save_post( $id, $post, $update ) {
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE || wp_is_post_revision( $id ) ) {
				return;
			}

			$this->update_settings( $post );
		}

		/**
		 * @param WP_Post $post
		 */
		private function update_settings( $post ) {
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE || wp_is_post_revision( $post->ID ) ) {
				return;
			}

			$content = $post->post_content;

			if ( empty( $content ) || ! has_block( 'gutena/forms', $content ) ) {
				return;
			}

			$blocks = parse_blocks( $content );
			foreach ( $blocks as $block ) {
				if ( 'gutena/forms' === $block['blockName'] ) {
					$attributes = $block['attrs'];

					do_action( 'gutena_forms__saving_block', $attributes, $block, $post );
				}
			}
		}
	}

	Gutena_Forms_handle_Save_Form::get_instance();
endif;
