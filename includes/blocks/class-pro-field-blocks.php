<?php
/**
 * Pro Field Blocks
 *
 * @since 2.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Pro_Field_Blocks' ) ) :
	class Gutena_Forms_Pro_Field_Blocks {
		private static $instance;

		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		public function register_blocks() {
			if ( is_gutena_forms_pro() ) {
				return;
			}

			if ( ! function_exists( 'register_block_type' ) ) {
				return;
			}

			$blocks = array(
				array(
					'title' => 'Country Field',
					'name'  => 'gutena/country-field',
					'dir'   => 'country-field',
				),
				array(
					'title' => 'Date Field',
					'name'  => 'gutena/date-field',
					'dir'   => 'date-field',
				),
				array(
					'title' => 'File Upload Field',
					'name'  => 'gutena/file-upload-field',
					'dir'   => 'file-upload-field',
				),
				array(
					'title' => 'Hidden Field',
					'name'  => 'gutena/hidden-field',
					'dir'   => 'hidden-field',
				),
				array(
					'title' => 'Password Field',
					'name'  => 'gutena/password-field',
					'dir'   => 'password-field',
				),
				array(
					'title' => 'Phone Field',
					'name'  => 'gutena/phone-field',
					'dir'   => 'phone-field',
				),
				array(
					'title' => 'Rating Field',
					'name'  => 'gutena/rating-field',
					'dir'   => 'rating-field',
				),
				array(
					'title' => 'State Field',
					'name'  => 'gutena/state-field',
					'dir'   => 'state-field',
				),
				array(
					'title' => 'Time Field',
					'name'  => 'gutena/time-field',
					'dir'   => 'time-field',
				),
				array(
					'title' => 'URL Field',
					'name'  => 'gutena/url-field',
					'dir'   => 'url-field',
				),
			);

			usort(
				$blocks,
				function ( $a, $b ) {
					return strcmp( $a['title'], $b['title'] );
				}
			);

			foreach ( $blocks as $block ) {
				$path = GUTENA_FORMS_DIR_PATH . 'build/blocks/form-field-blocks/pro/' . $block['dir'];
				if ( file_exists( $path . '/block.json' ) ) {
					register_block_type( $path );
				}
			}
		}
	}
endif;
