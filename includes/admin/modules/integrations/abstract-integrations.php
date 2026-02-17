<?php
/**
 * Abstract Integrations Class
 *
 * @since 1.8.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Integration_Settings' ) && class_exists( 'Gutena_Forms_Forms_Settings' ) ) :
	abstract class Gutena_Forms_Integration_Settings extends Gutena_Forms_Forms_Settings {
		public $id = '';
		public $title = '';
		public $description = '';
		public $is_enabled = false;
		public $settings = array();

		public function __construct() {
			$settings = get_option( 'gutena_forms__integration_settings', array() );
			if ( is_array( $settings ) && isset( $settings[ $this->id ] ) ) {
				$this->settings = $settings[ $this->id ];
			}

			$this->is_enabled = isset( $this->settings['is_enabled'] ) && $this->settings['is_enabled'];
		}

		public function save_settings( $settings ) {
			$all_settings = get_option( 'gutena_forms__integration_settings', array() );
			if ( ! is_array( $all_settings ) ) {
				$all_settings = array();
			}

			$all_settings[ $this->id ]               = $settings;
			$all_settings[ $this->id ]['is_enabled'] = $this->is_enabled;

			update_option( 'gutena_forms__integration_settings', $all_settings );

			return true;
		}
	}
endif;