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

			return array(
				'id'          => $this->id,
				'title'       => sprintf( __( '%1$s Settings', 'gutena-froms' ), $this->title ),
				'description' => $this->description,
				'fields'      => array(
					array(
						'id'      => 'api_key',
						'type'    => 'text',
						'name'    => __( 'Brevo API Key', 'gutena-forms' ),
						'desc'    => sprintf(
							__( 'Enter your Brevo API key. You can find this in your Brevo account under %1$s. %2$s', 'gutena-forms' ),
							'<a href="https://help.brevo.com/hc/en-us/articles/360002078479-How-to-find-your-API-key" target="_blank">' . __( 'SMTP & API > API', 'gutena-forms' ) . '</a>',
							'<br><br>' . __( 'Note: This API key is used to connect your forms to Brevo and will allow us to add contacts to your list. It does not have access to any other parts of your Brevo account.', 'gutena-forms' ),
						),
						'default' => '',
						'value'   => $this->settings['api_key'] ?? '',
					),
					array(
						'id'      => 'list_id',
						'type'    => 'text',
						'name'    => __( 'Brevo List ID', 'gutena-forms' ),
						'desc'    => sprintf(
							__( 'Enter the List ID for the Brevo list you want to add contacts to. You can find this in your Brevo account under %1$s. %2$s', 'gutena-forms' ),
							'<a href="https://help.brevo.com/hc/en-us/articles/360002078479-How-to-find-your-API-key" target="_blank">' . __( 'SMTP & API > API', 'gutena-forms' ) . '</a>',
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

	Gutena_Forms_Brevo::register_module();
endif;
