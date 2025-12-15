<?php
/**
 * Class Gutena_Forms_Submit_Handler
 *
 * @since 1.6.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Submit_Handler' ) ) :
	/**
	 * Gutena Forms Submit Handler Class
	 *
	 * @since 1.6.0
	 */
	class Gutena_Forms_Submit_Handler {
		/**
		 * The single instance of the class.
		 *
		 * @since 1.6.0
		 * @var Gutena_Forms_Submit_Handler $instance The single instance of the class.
		 */
		private static $instance;

		/**
		 * Form ID
		 *
		 * @since 1.6.0
		 * @var string $form_id The form ID.
		 */
		private $form_id;

		/**
		 * Form Schema
		 *
		 * @since 1.6.0
		 * @var array $form_schema The form schema.
		 */
		private $form_schema;

		/**
		 * Get the single instance of the class.
		 *
		 * @since 1.6.0
		 * @return Gutena_Forms_Submit_Handler
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Handle form submission.
		 *
		 * @since 1.6.0
		 */
		public function handle_submit() {
			check_ajax_referer( 'gutena_Forms', 'nonce' );

			if ( empty( $_POST['formid'] ) ) {
				wp_send_json(
					array(
						'status'  => 'error',
						'message' => __( 'Missing form identity', 'gutena-forms' ),
					)
				);
			}

			$this->form_id     = sanitize_key( wp_unslash( $_POST['formid'] ) );
			$this->form_schema = get_option( $this->form_id );

			if ( empty( $this->form_schema ) || empty( $this->form_schema['form_attrs'] ) || empty( $this->form_schema['form_fields'] ) ) {
				wp_send_json(
					array(
						'status'  => 'error',
						'message' => __( 'Missing form details', 'gutena-forms' ),
					)
				);
			}

			// Verify all security features using the security manager
			$security_verification = Gutena_Forms_Security_Manager::verify_all( $this->form_schema );

			if ( true !== $security_verification ) {
				wp_send_json( $security_verification );
			}

			$blog_title  = get_bloginfo( 'name' );
			$from_name =  empty( $this->form_schema['form_attrs']['emailFromName'] ) ? $blog_title : $this->form_schema['form_attrs']['emailFromName'];
			$from_name = sanitize_text_field( $from_name );

			$admin_email = sanitize_email( get_option( 'admin_email' ) );

			// Email To
			$to = empty( $this->form_schema['form_attrs']['adminEmails'] ) ? $admin_email : $this->form_schema['form_attrs']['adminEmails'];

			if ( ! is_array( $to ) ) {
				$to = explode( ',', $to );
			}

			foreach ( $to as $key => $toEmail ) {
				$to[ $key ] = sanitize_email( wp_unslash( $toEmail ) );
			}

			$reply_to = empty( $this->form_schema['form_attrs']['replyToEmail'] ) ? '' : $this->form_schema['form_attrs']['replyToEmail'];

			$reply_to = ( empty( $reply_to ) || empty( $_POST[ $reply_to ] ) ) ? '' : sanitize_email( wp_unslash( $_POST[ $reply_to ] ) );

			//First name field
			$reply_to_name = empty( $this->form_schema['form_attrs']['replyToName'] ) ? '' : $this->form_schema['form_attrs']['replyToName'];

			//Last name field
			$reply_to_lname = empty( $this->form_schema['form_attrs']['replyToLastName'] ) ? '' : $this->form_schema['form_attrs']['replyToLastName'];


			$reply_to_name = ( empty( $reply_to_name ) || empty( $_POST[ $reply_to_name ] ) ) ? sanitize_key( $reply_to ) : sanitize_text_field( wp_unslash( $_POST[ $reply_to_name ] ) );

			$reply_to_lname = ( empty( $reply_to_lname ) || empty( $_POST[ $reply_to_lname ] ) ) ? '' : sanitize_text_field( wp_unslash( $_POST[ $reply_to_lname ] ) );

			//Form submit Data for filter
			$form_submit_data = array(
				'formName' => empty( $this->form_schema['form_attrs']['formName'] ) ? '': $this->form_schema['form_attrs']['formName'],
				'formID' => $this->form_schema['form_attrs']['formID'],
				'emailFromName' => $from_name,
				'replyToEmail' => $reply_to,
				'replyToFname' => $reply_to_name,
				'replyToLname' => $reply_to_lname
			);

			$reply_to_name = $reply_to_name .' '.$reply_to_lname;


			// Email Subject
			$subject = sanitize_text_field( empty( $this->form_schema['form_attrs']['adminEmailSubject'] ) ? __( 'Form received', 'gutena-forms' ) . '- ' . $blog_title : $this->form_schema['form_attrs']['adminEmailSubject'] );

			$fieldSchema = $this->form_schema['form_fields'];
			$body        = '';

			foreach ( $_POST as $name_attr => $field_value ) {
				$name_attr   = sanitize_key( wp_unslash( $name_attr ) );

				if ( empty( $fieldSchema[ $name_attr ] ) || ( ! empty( $fieldSchema[ $name_attr ][ 'fieldType' ] ) && 'optin' == $fieldSchema[ $name_attr ][ 'fieldType' ] ) ) {
					continue;
				}

				$field_value = apply_filters( 'gutena_forms_field_value_for_email', $field_value, $fieldSchema[ $name_attr ], $this->form_id );

				if ( is_array( $field_value ) ) {
					$field_value =	Gutena_Forms_Helper::sanitize_array( wp_unslash( $field_value ), true );
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
							'formID' => $this->form_id,
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
						'formID' => $this->form_id,
					)
				);

				$body .= $field_email_html;

			}
			//submitted form raw data
			do_action( 'gutena_forms_submitted_data', $form_submit_data['raw_data'], $this->form_id, $fieldSchema );
			do_action( 'gutena_forms_submission', $form_submit_data, $this->form_schema );

			// If admin don't want to get Email notification
			if ( isset( $this->form_schema['form_attrs']['emailNotifyAdmin'] ) && ( '' === $this->form_schema['form_attrs']['emailNotifyAdmin'] || false === $this->form_schema['form_attrs']['emailNotifyAdmin'] || '0' == $this->form_schema['form_attrs']['emailNotifyAdmin'] ) ) {
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
				array_push(
					$headers,
					'Reply-To: ' . esc_html( $reply_to_name ) . ' <' . $reply_to . '>'
				);
			}

			//Apply filter for admin email notification
			$body    = apply_filters( 'gutena_forms_submit_admin_notification', $body, $form_submit_data );

			if ( ! Gutena_Forms_Helper::has_pro() ) {
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

		private function email_html_body ( $body, $subject ) {
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

	}
endif;
