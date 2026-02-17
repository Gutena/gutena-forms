<?php
/**
 * Active Campaign Integration Class
 *
 * @since 1.8.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Active_Campaign' ) && class_exists( 'Gutena_Forms_Integration_Settings' ) ) :
	class Gutena_Forms_Active_Campaign extends Gutena_Forms_Integration_Settings {

		public function __construct() {

			$this->id          = 'active-campaign';
			$this->title       = __( 'Active Campaign', 'gutena-forms' );
			$this->description = __( 'This module allows you to send form submissions to your ActiveCampaign list to manage leads and automate email campaigns.', 'gutena-forms' );

			parent::__construct();
		}

		public static function register_module() {
			add_filter(
				'gutena_forms__integrations',
				function ( $integrations ) {

					$integrations['active-campaign'] = __CLASS__;

					return $integrations;
				}
			);
		}

		public function get_settings() {
		}

		public function save_settings( $settings ) {
		}
	}

	Gutena_Forms_Active_Campaign::register_module();
endif;
