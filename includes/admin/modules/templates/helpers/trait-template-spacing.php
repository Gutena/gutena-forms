<?php
/**
 * Shared spacing presets for form templates.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! trait_exists( 'Gutena_Forms_Template_Spacing_Trait' ) ) :
	/**
	 * Provides reusable form block spacing presets.
	 *
	 * @since 2.2.0
	 */
	trait Gutena_Forms_Template_Spacing_Trait {

		/**
		 * Single-column spacing preset.
		 *
		 * @return array
		 */
		protected function get_basic_spacing() {
			return array(
				'spacing' => array(
					'blockGap' => '2rem',
					'padding'  => array(
						'top'    => '2rem',
						'bottom' => '5rem',
					),
				),
			);
		}

		/**
		 * Two-column spacing preset.
		 *
		 * @return array
		 */
		protected function get_two_col_spacing() {
			return array(
				'spacing' => array(
					'blockGap' => '2rem',
					'padding'  => array(
						'top'    => '2rem',
						'bottom' => '5rem',
						'right'  => '1.25rem',
						'left'   => '1.25rem',
					),
				),
			);
		}
	}
endif;
