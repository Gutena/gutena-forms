<?php
/**
 * Class Form submit handler
 *
 * @since 1.6.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Submit_Form_Handler' ) ) :
	/**
	 * Gutena Forms Submit Form Handler Class
	 *
	 * @since 1.6.0
	 */
	class Gutena_Forms_Submit_Form_Handler {
		/**
		 * The single instance of the class.
		 *
		 * @since 1.6.0
		 * @var Gutena_Forms_Submit_Form_Handler The single instance of the class.
		 */
		private static $instance;

		/**
		 * Form ID.
		 *
		 * @since 1.6.0
		 * @var string $id Form ID.
		 */
		private $id;

		/**
		 * Form schema.
		 *
		 * @since 1.6.0
		 * @var array $schema Form schema.
		 */
		private $schema;

		/**
		 * Constructor.
		 *
		 * @since 1.6.0
		 */
		private function __construct() {
		}

		/**
		 * Get the single instance of the class.
		 *
		 * @since 1.6.0
		 * @return Gutena_Forms_Submit_Form_Handler
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Handle form submit
		 *
		 * @since 1.6.0
		 */
		public function handle_submit() {
			$this->verify_nonce();

			$this->validate_form_id_and_scehma();

			$this->validate_captcha();

			$blog_title  = get_bloginfo( 'name' );
			$from_name =  empty( $this->schema['form_attrs']['emailFromName'] ) ? $blog_title : $this->schema['form_attrs']['emailFromName'];
			$from_name = sanitize_text_field( $from_name );

			$admin_email = sanitize_email( get_option( 'admin_email' ) );

			// Email To
			$to = empty( $this->schema['form_attrs']['adminEmails'] ) ? $admin_email : $this->schema['form_attrs']['adminEmails'];

			if ( ! is_array( $to ) ) {
				$to = explode( ',', $to );
			}

			foreach ( $to as $key => $toEmail ) {
				$to[ $key ] = sanitize_email( wp_unslash( $toEmail ) );
			}

			$reply_to = empty( $this->schema['form_attrs']['replyToEmail'] ) ? '' : $this->schema['form_attrs']['replyToEmail'];

			$reply_to = ( empty( $reply_to ) || empty( $_POST[ $reply_to ] ) ) ? '' : sanitize_email( wp_unslash( $_POST[ $reply_to ] ) );

			//First name field
			$reply_to_name = empty( $this->schema['form_attrs']['replyToName'] ) ? '' : $this->schema['form_attrs']['replyToName'];

			//Last name field
			$reply_to_lname = empty( $this->schema['form_attrs']['replyToLastName'] ) ? '' : $this->schema['form_attrs']['replyToLastName'];


			$reply_to_name = ( empty( $reply_to_name ) || empty( $_POST[ $reply_to_name ] ) ) ? sanitize_key( $reply_to ) : sanitize_text_field( wp_unslash( $_POST[ $reply_to_name ] ) );

			$reply_to_lname = ( empty( $reply_to_lname ) || empty( $_POST[ $reply_to_lname ] ) ) ? '' : sanitize_text_field( wp_unslash( $_POST[ $reply_to_lname ] ) );

			//Form submit Data for filter
			$form_submit_data = array(
				'formName' => empty( $this->schema['form_attrs']['formName'] ) ? '': $this->schema['form_attrs']['formName'],
				'formID' => $this->schema['form_attrs']['formID'],
				'emailFromName' => $from_name,
				'replyToEmail' => $reply_to,
				'replyToFname' => $reply_to_name,
				'replyToLname' => $reply_to_lname
			);

			$reply_to_name = $reply_to_name .' '.$reply_to_lname;


			// Email Subject
			$subject = sanitize_text_field( empty( $this->schema['form_attrs']['adminEmailSubject'] ) ? __( 'Form received', 'gutena-forms' ) . '- ' . $blog_title : $this->schema['form_attrs']['adminEmailSubject'] );

			$fieldSchema = $this->schema['form_fields'];
			$body        = '';

			foreach ( $_POST as $name_attr => $field_value ) {
				$name_attr   = sanitize_key( wp_unslash( $name_attr ) );

				if ( empty( $fieldSchema[ $name_attr ] ) || ( ! empty( $fieldSchema[ $name_attr ][ 'fieldType' ] ) && 'optin' == $fieldSchema[ $name_attr ][ 'fieldType' ] ) ) {
					continue;
				}

				$field_value = apply_filters( 'gutena_forms_field_value_for_email', $field_value, $fieldSchema[ $name_attr ], $this->id );

				if ( is_array( $field_value ) ) {
					$field_value =	Gutena_Forms::get_instance()->sanitize_array( wp_unslash( $field_value ), true );
					$field_value = implode(", ", $field_value );
				} else {
					$field_value = sanitize_textarea_field( wp_unslash( $field_value ) );
				}

				//Add prefix in value if set
				if ( ! empty( $fieldSchema[ $name_attr ][ 'preFix' ] ) ) {
					$field_value = sanitize_text_field( $fieldSchema[ $name_attr ][ 'preFix' ] ).' '.$field_value;
				}

				//Add suffix in value if set
				if ( ! empty( $fieldSchema[ $name_attr ][ 'sufFix' ] ) ) {
					$field_value =  $field_value . ' ' . sanitize_text_field( $fieldSchema[ $name_attr ][ 'sufFix' ] );
				}

				$field_name = sanitize_text_field( empty( $fieldSchema[ $name_attr ]['fieldName'] ) ? str_ireplace( '_', ' ', $name_attr ) : $fieldSchema[ $name_attr ]['fieldName'] );

				//Form submit Data for filter
				$form_submit_data['submit_data'][ $field_name ] = $field_value;
				$form_submit_data['raw_data'][ $name_attr ] = array(
					'label' => $field_name,
					'value'	=> $field_value,
					'fieldType' =>  empty( $fieldSchema[ $name_attr ][ 'fieldType' ] ) ? 'text': $fieldSchema[ $name_attr ][ 'fieldType' ],
					'raw_value' => apply_filters(
						'gutena_forms_field_raw_value',
						wp_unslash( $_POST[ $name_attr ] ),
						array(
							'field_name' => $field_name,
							'field_value' => $field_value,
							'fieldSchema' => $fieldSchema[ $name_attr ],
							'formID' => $this->id,
						)
					)
				);

				$field_email_html = '<p><strong>' . esc_html( $field_name ) . '</strong> <br />' . esc_html( $field_value ) . ' </p>';

				$field_email_html = apply_filters(
					'gutena_forms_field_email_html',
					$field_email_html,
					array(
						'field_name' => $field_name,
						'field_value' => $field_value,
						'fieldSchema' => $fieldSchema[ $name_attr ],
						'formID' => $this->id,
					)
				);

				$body .= $field_email_html;

			}
			//submitted form raw data
			do_action( 'gutena_forms_submitted_data', $form_submit_data['raw_data'], $this->id, $fieldSchema );
			do_action( 'gutena_forms_submission', $form_submit_data, $this->schema );

			// If admin don't want to get Email notification
			if ( isset( $this->schema['form_attrs']['emailNotifyAdmin'] ) && ( '' === $this->schema['form_attrs']['emailNotifyAdmin'] || false === $this->schema['form_attrs']['emailNotifyAdmin'] || '0' == $this->schema['form_attrs']['emailNotifyAdmin'] ) ) {
				wp_send_json(
					array(
						'status'  => 'Success',
						'message' => __( 'success', 'gutena-forms' ),
						'detail'  => __( 'admin email notification off', 'gutena-forms' ),
					)
				);
			}

			//Email headers
			$headers = array(
				'Content-Type: text/html; charset=UTF-8',
				'From: ' . esc_html( $from_name ) . ' <' . $admin_email . '>',
			);
			//Add reply to header
			if ( ! empty( $reply_to ) ) {
				$headers[] = 'Reply-To: ' . esc_html( $reply_to_name ) . ' <' . $reply_to . '>';
			}

			//Apply filter for admin email notification
			$body    = apply_filters( 'gutena_forms_submit_admin_notification', $body, $form_submit_data );

			if ( ! is_gutena_forms_pro( false ) ) {
				/**
				 * https://stackoverflow.com/questions/17602400/html-email-in-gmail-css-style-attribute-removed
				 */
				$body .= '<div style="background-color: #fffbeb; width: fit-content; margin-top: 50px; padding: 14px 15px 12px 15px; border-radius: 10px;" > <span style="font-size: 13px; line-height: 1; display: flex;" > <span style="margin-right: 5px;" > </span> <span style="margin-right: 3px;" ><strong>' . __( 'Exciting News!', 'gutena-forms' ) . ' </strong></span> '. __( 'Now, you can view and manage all your form submissions right from the Gutena Forms Dashboard.', 'gutena-forms' ) . '<strong><a href="'.esc_url( admin_url( 'admin.php?page=gutena-forms' ) ).'" style="color: #E35D3F; margin-left: 1rem;" target="_blank" > ' . __( 'See all Entries', 'gutena-forms' ) . ' </a></strong></span></div>';
			}

			$body    = wpautop( $body, true );
			$body 	 = $this->email_html_body( $body, $subject );
			$subject = esc_html( $subject );
			$res     = wp_mail( $to, $subject, $body, $headers );

			if ( $res ) {
				wp_send_json(
					array(
						'status'  => 'Success',
						'message' => __( 'success', 'gutena-forms' ),
					)
				);
			} else {
				wp_send_json(
					array(
						'status'  => 'error',
						'message' => __( 'Sorry! your form was submitted, but the email could not be sent. The site admin may need to review the email settings.', 'gutena-forms' ),
						'details' => __( 'Failed to send email', 'gutena-forms' ),
					)
				);
			}
		}

		/**
		 * Verify nonce
		 *
		 * @since 1.6.0
		 */
		private function verify_nonce() {
			check_ajax_referer( 'gutena_Forms', 'nonce' );
		}

		/**
		 * Validate form ID and schema
		 *
		 * @since 1.6.0
		 */
		private function validate_form_id_and_scehma() {
			if ( empty( $_POST['formid'] ) ) {
				wp_send_json(
					array(
						'status'  => 'error',
						'message' => __( 'Missing form identity', 'gutena-forms' ),
					)
				);
			}

			$this->id     = sanitize_key( wp_unslash( $_POST['formid'] ) );
			$this->schema = gutena_forms_get_form_schema_option( $this->id );

			if ( empty( $this->schema ) || empty( $this->schema['form_attrs'] ) || empty( $this->schema['form_fields'] ) ) {
				wp_send_json(
					array(
						'status'  => 'error',
						'message' => __( 'Missing form details', 'gutena-forms' ),
					)
				);
			}
		}

		/**
		 * Validate Captcha
		 *
		 * @since 1.6.0
		 */
		private function validate_captcha() {
			if ( ! empty( $this->schema['form_attrs']['recaptcha'] ) && ! empty( $this->schema['form_attrs']['recaptcha']['enable'] ) && ! $this->recaptcha_verify() ) {
				wp_send_json(
					array(
						'status'  => 'error',
						'message' => __( 'Invalid reCAPTCHA', 'gutena-forms' ),
						'recaptcha_error'	  => isset( $_POST['recaptcha_error'] ) ? sanitize_text_field( $_POST['recaptcha_error'] ) : ''
					)
				);
			}

			if ( ! empty( $this->schema['form_attrs']['cloudflareTurnstile'] ) && ! empty( $this->schema['form_attrs']['cloudflareTurnstile']['enable'] ) && ! $this->cloudflare_turnstile_verify() ) {
				wp_send_json(
					array(
						'status'  => 'error',
						'message' => __( 'Invalid Cloudflare Turnstile', 'gutena-forms' ),
					)
				);
			}

			if ( ! empty( $this->schema['form_attrs']['honeypot'] ) && ! empty( $this->schema['form_attrs']['honeypot']['enable'] ) && ! $this->honeypot_verify() ) {
				wp_send_json(
					array(
						'status'  => 'error',
						'message' => __( 'Spam detected', 'gutena-forms' ),
					)
				);
			}
		}

		/**
		 * Wrap email body in HTML structure
		 *
		 * @since 1.6.0
		 * @param string $body Email body.
		 * @param string $subject Email subject.
		 *
		 * @return string
		 */
		private function email_html_body( $body, $subject ) {
			$lang = function_exists( 'get_language_attributes' ) ? get_language_attributes('html') : 'lang="en"';
			return '
			<!DOCTYPE html>
			<html '. $lang .'>
				<head>
				<meta http-equiv="X-UA-Compatible" content="IE=edge">
				<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
				<meta name="viewport" content="width=device-width, initial-scale=1">
				<title>'.$subject.'</title>
				</head>
				<body style="margin:0;padding:0;background:#ffffff;">
				'.$body.'
				</body>
			</html>
			';
		}

		/**
		 * Verify Google reCAPTCHA
		 *
		 * @since 1.6.0
		 * @return bool
		 */
		private function recaptcha_verify(){
			//check if reCAPTCHA not embedded in the form
			if ( empty( $_POST['recaptcha_enable'] ) && empty( $_POST['g-recaptcha-response'] ) ) {
				return true;
			}
			//default recaptcha failed is considered as spam
			$_POST['recaptcha_error'] = 'spam';

			if ( empty( $_POST['g-recaptcha-response'] ) ) {
				$_POST['recaptcha_error'] = 'Recaptcha input missing';
				return false;
			} else {
				//get reCAPTCHA settings
				$recaptcha_settings= get_option( 'gutena_forms_grecaptcha', false );

				if ( empty( $recaptcha_settings ) ) {
					return false;
				}
				//verify reCAPTCHA
				$response = wp_remote_post( 'https://www.google.com/recaptcha/api/siteverify', array(
					'body'        => array(
						'secret' => $recaptcha_settings['secret_key'],
						'response' => sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ) )
					)
				));

				if ( 200 != wp_remote_retrieve_response_code( $response ) ) {
					$_POST['recaptcha_error'] = 'No response from api';
					return false;//fail to verify
				}

				$api_response = json_decode( wp_remote_retrieve_body( $response ), true );

				if ( ! empty($api_response) && $api_response['success'] ) {

					$threshold_score = apply_filters( 'gutena_forms_recaptcha_threshold_score', ( empty( $recaptcha_settings['thresholdScore'] ) || $recaptcha_settings['thresholdScore'] < 0.5 ) ? 0.5 : $recaptcha_settings['thresholdScore'] );

					// check the hostname of the site where the reCAPTCHA was solved
					if ( ! empty( $api_response['hostname'] ) && function_exists( 'get_site_url' ) ) {
						$site_url = explode( "?", get_site_url() );
						if ( 5 < strlen( $site_url[0] ) && false === stripos( $site_url[0], $api_response['hostname'] ) ) {
							$_POST['recaptcha_error'] = 'different hostname';
							return false;//fail to verify hostname
						}
					}

					if ( 'v2' === $recaptcha_settings['type'] ) {
						return true;//for v2
					} else if ( isset( $api_response['score'] ) && $api_response['score'] > $threshold_score ) {
						return	apply_filters( 'gutena_forms_recaptcha_verify', true, $response );
					} else {
						return false;//spam
					}
				}else{
					return false;
				}
			}
		}

		/**
		 * Verify Cloudflare Turnstile
		 *
		 * @since 1.3.0
		 * @return boolean
		 */
		private function cloudflare_turnstile_verify() {
			if ( isset( $_POST['cf-turnstile-response'] ) && ! empty( $_POST['cf-turnstile-response'] ) ) {
				$token 				  = sanitize_text_field( wp_unslash( $_POST['cf-turnstile-response'] ) );
				$cloudflare_turnstile = get_option( 'gutena_forms_cloudflare_turnstile', false );

				if ( empty( $cloudflare_turnstile ) ) {
					return false;
				}

				$response = wp_remote_post(
					'https://challenges.cloudflare.com/turnstile/v0/siteverify',
					array(
						'body' => array(
							'secret' => $cloudflare_turnstile['secret_key'],
							'response' => $token,
						),
					)
				);

				if ( 200 != wp_remote_retrieve_response_code( $response ) ) {
					return false;
				}

				$api_response = json_decode( wp_remote_retrieve_body( $response ), true );

				if ( ! empty( $api_response ) && $api_response['success'] ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Verify Honeypot
		 *
		 * @since 1.6.0
		 * @return bool
		 */
		private function honeypot_verify() {
			$honeypot_field_name   = 'gf_hp_' . sanitize_text_field( wp_unslash( $_POST['formid'] ) );
			$time_check_field_name = 'gf_time_check_' . sanitize_text_field( wp_unslash( $_POST['formid'] ) );

			if ( isset( $_POST[ $honeypot_field_name ] ) && ! empty( $_POST[ $honeypot_field_name ] ) ) {
				return false;
			}

			if ( isset( $_POST[ $time_check_field_name ] ) && ! empty( $_POST[ $time_check_field_name ] ) ) {
				$time_check_value = intval( $_POST[ $time_check_field_name ] );

				return time() > ( $time_check_value );
			} else {
				return false;
			}
		}
	}
endif;
