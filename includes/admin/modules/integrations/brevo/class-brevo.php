<?php
/**
 * Brevo Integration Class
 *
 * @since 1.8.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Brevo' ) && class_exists( 'Gutena_Forms_Integration_Settings' ) ) :
	class Gutena_Forms_Brevo extends Gutena_Forms_Integration_Settings {
		public function __construct() {
			 $this->id          = 'brevo';
			 $this->title       = __( 'Brevo', 'gutena-forms' );
			 $this->description = __( 'This module allows you to add form contacts to your Brevo list to grow and manage your email subscribers.', 'gutena-forms' );

			 parent::__construct();
		}
		public static function register_module() {
			add_filter(
				'gutena_forms__integrations',
				function ( $integrations ) {

					$integrations['brevo'] = __CLASS__;

					return $integrations;
				}
			);
		}

		public function get_settings() {
		}

		public function save_settings( $settings ) {
		}
	}

	Gutena_Forms_Brevo::register_module();
endif;
