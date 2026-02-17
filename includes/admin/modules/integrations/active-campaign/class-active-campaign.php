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

			return array(
				'id'          => $this->id,
				'title'       => sprintf( __( '%1$s Settings', 'gutena-froms' ), $this->title ),
				'description' => $this->description,
				'fields'      => array(
					array(
						'id'      => 'api_url',
						'type'    => 'text',
						'name'    => __( 'ActiveCampaign API URL', 'gutena-forms' ),
						'desc'    => sprintf(
							__( 'Enter your ActiveCampaign API URL. You can find this in your ActiveCampaign account under %1$s. %2$s', 'gutena-forms' ),
							'<a href="https://help.activecampaign.com/hc/en-us/articles/115000309370-How-to-find-your-API-URL-and-Key" target="_blank">' . __( 'Settings > Developer', 'gutena-forms' ) . '</a>',
							'<br><br>' . __( 'Note: This API URL is used to connect your forms to ActiveCampaign and will allow us to send form submissions to your account. It does not have access to any other parts of your ActiveCampaign account.', 'gutena-forms' ),
						),
						'default' => '',
						'value'   => $this->settings['api_url'] ?? '',
					),
					array(
						'id'      => 'api_key',
						'type'    => 'text',
						'name'    => __( 'ActiveCampaign API Key', 'gutena-forms' ),
						'desc'    => sprintf(
							__( 'Enter your ActiveCampaign API key. You can find this in your ActiveCampaign account under %1$s. %2$s', 'gutena-forms' ),
							'<a href="https://help.activecampaign.com/hc/en-us/articles/115000309370-How-to-find-your-API-URL-and-Key" target="_blank">' . __( 'Settings > Developer', 'gutena-forms' ) . '</a>',
							'<br><br>' . __( 'Note: This API key is used to connect your forms to ActiveCampaign and will allow us to send form submissions to your account. It does not have access to any other parts of your ActiveCampaign account.', 'gutena-forms' ),
						),
						'default' => '',
						'value'   => $this->settings['api_key'] ?? '',
					),
					array(
						'id'      => 'list_id',
						'type'    => 'text',
						'name'    => __( 'ActiveCampaign List ID', 'gutena-forms' ),
						'desc'    => sprintf(
							__( 'Enter the List ID for the ActiveCampaign list you want to add contacts to. You can find this in your ActiveCampaign account under %1$s. %2$s', 'gutena-forms' ),
							'<a href="https://help.activecampaign.com/hc/en-us/articles/115000309370-How-to-find-your-API-URL-and-Key" target="_blank">' . __( 'Settings > Developer', 'gutena-forms' ) . '</a>',
							'<br><br>' . __( 'Note: This List ID is used to specify which list new contacts will be added to when they submit your forms.', 'gutena-forms' ),
						),
						'default' => '',
						'value'   => $this->settings['list_id'] ?? '',
					),
					array(
						'id'   => 'submit_button',
						'type' => 'submit',
						'name' => __( 'Save Settings', 'gutena-forms' ),
					),
				),
			);
		}
	}

	Gutena_Forms_Active_Campaign::register_module();
endif;
