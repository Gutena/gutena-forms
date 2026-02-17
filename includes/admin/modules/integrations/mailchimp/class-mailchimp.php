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
			return array(
				'id'          => $this->id,
				'title'       => sprintf( __( '%1$s Settings', 'gutena-froms' ), $this->title ),
				'description' => $this->description,
				'back'        => '/settings/settings/integrations',
				'fields'      => array(
					array(
						'id'      => 'api_key',
						'type'    => 'text',
						'name'    => __( 'Mailchimp API Key', 'gutena-forms' ),
						'desc'    => sprintf(
							__( 'Enter your Mailchimp API key. You can find this in your Mailchimp account under %1$s. %2$s', 'gutena-forms' ),
							'<a href="https://mailchimp.com/help/about-api-keys/" target="_blank">' . __( 'Account > Extras > API keys', 'gutena-forms' ) . '</a>',
							'<br><br>' . __( 'Note: This API key is used to connect your forms to Mailchimp and will allow us to add subscribers to your audience. It does not have access to any other parts of your Mailchimp account.', 'gutena-forms' ),
						),
						'default' => '',
						'value'   => $this->settings['api_key'] ?? '',
					),
					array(
						'id'      => 'audience_id',
						'type'    => 'text',
						'name'    => __( 'Mailchimp Audience ID', 'gutena-forms' ),
						'desc'    => sprintf(
							__( 'Enter the Audience ID for the Mailchimp audience you want to add subscribers to. You can find this in your Mailchimp account under %1$s. %2$s', 'gutena-forms' ),
							'<a href="https://mailchimp.com/help/find-audience-id/" target="_blank">' . __( 'Audience > Settings > Audience name and defaults', 'gutena-forms' ) . '</a>',
							'<br><br>' . __( 'Note: This Audience ID is used to specify which audience new subscribers will be added to when they submit your forms.', 'gutena-forms' ),
						),
						'default' => '',
						'value'   => $this->settings['audience_id'] ?? '',
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

	Gutena_Forms_Mailchimp::register_module();
endif;
