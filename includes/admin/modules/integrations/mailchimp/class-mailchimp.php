<?php
/**
 * Mailchimp Integration Class
 *
 * @since 1.8.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Mailchimp' ) && class_exists( 'Gutena_Forms_Integration_Settings' ) ) :
	class Gutena_Forms_Mailchimp extends Gutena_Forms_Integration_Settings {

		public function __construct() {
			$this->id          = 'mailchimp';
			$this->title       = __( 'Mailchimp', 'gutena-forms' );
			$this->description = __( 'This module allows you to automatically add form submissions to your Mailchimp audience to grow your email list.', 'gutena-forms' );

			parent::__construct();
		}

		public static function register_module() {
			add_filter(
				'gutena_forms__integrations',
				function ( $integrations ) {

					$integrations['mailchimp'] = __CLASS__;

					return $integrations;
				}
			);
		}

		public function get_settings() {
		}

		public function save_settings( $settings ) {
		}
	}

	Gutena_Forms_Mailchimp::register_module();
endif;
