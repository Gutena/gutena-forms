<?php
/**
 * Standalone verification for notification sending behavior.
 */

define( 'ABSPATH', __DIR__ . '/' );

if ( ! function_exists( 'esc_html' ) ) {
	function esc_html( $text ) {
		return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( '__' ) ) {
	function __( $text ) {
		return $text;
	}
}

if ( ! function_exists( 'esc_html__' ) ) {
	function esc_html__( $text ) {
		return $text;
	}
}

if ( ! function_exists( 'esc_url' ) ) {
	function esc_url( $url ) {
		return $url;
	}
}

if ( ! function_exists( 'sanitize_email' ) ) {
	function sanitize_email( $email ) {
		$email = trim( (string) $email );
		return filter_var( $email, FILTER_VALIDATE_EMAIL ) ? $email : '';
	}
}

if ( ! function_exists( 'is_email' ) ) {
	function is_email( $email ) {
		return (bool) sanitize_email( $email );
	}
}

if ( ! function_exists( 'sanitize_text_field' ) ) {
	function sanitize_text_field( $text ) {
		return trim( strip_tags( (string) $text ) );
	}
}

if ( ! function_exists( 'sanitize_key' ) ) {
	function sanitize_key( $key ) {
		return strtolower( preg_replace( '/[^a-z0-9_\-]/', '', (string) $key ) );
	}
}

if ( ! function_exists( 'wp_kses_post' ) ) {
	function wp_kses_post( $content ) {
		return $content;
	}
}

if ( ! function_exists( 'wpautop' ) ) {
	function wpautop( $content ) {
		return $content;
	}
}

if ( ! function_exists( 'get_bloginfo' ) ) {
	function get_bloginfo( $show = '' ) {
		return 'name' === $show ? 'Test Site' : '';
	}
}

if ( ! function_exists( 'get_site_url' ) ) {
	function get_site_url() {
		return 'https://example.com';
	}
}

if ( ! function_exists( 'get_option' ) ) {
	function get_option( $key ) {
		return 'admin_email' === $key ? 'admin@example.com' : '';
	}
}

if ( ! function_exists( 'get_posts' ) ) {
	function get_posts() {
		return array();
	}
}

if ( ! function_exists( 'get_the_title' ) ) {
	function get_the_title() {
		return '';
	}
}

if ( ! function_exists( 'rest_sanitize_boolean' ) ) {
	function rest_sanitize_boolean( $value ) {
		return filter_var( $value, FILTER_VALIDATE_BOOLEAN );
	}
}

if ( ! function_exists( 'apply_filters' ) ) {
	function apply_filters( $tag, $value ) {
		return $value;
	}
}

if ( ! function_exists( 'wp_parse_args' ) ) {
	function wp_parse_args( $args, $defaults = array() ) {
		if ( is_object( $args ) ) {
			$args = get_object_vars( $args );
		}
		return array_merge( $defaults, (array) $args );
	}
}

if ( ! function_exists( 'is_gutena_forms_pro' ) ) {
	function is_gutena_forms_pro() {
		return true;
	}
}

if ( ! function_exists( 'admin_url' ) ) {
	function admin_url() {
		return 'https://example.com/wp-admin/';
	}
}

if ( ! function_exists( 'get_language_attributes' ) ) {
	function get_language_attributes() {
		return 'lang="en"';
	}
}

$GLOBALS['gf_mail_log']   = array();
$GLOBALS['gf_mail_queue']   = array();

if ( ! function_exists( 'wp_mail' ) ) {
	function wp_mail( $to, $subject, $message, $headers = '' ) {
		$should_fail = ! empty( $GLOBALS['gf_mail_queue'] ) ? false === array_shift( $GLOBALS['gf_mail_queue'] ) : false;
		$GLOBALS['gf_mail_log'][] = compact( 'to', 'subject', 'message', 'headers', 'should_fail' );
		return ! $should_fail;
	}
}

require_once dirname( __DIR__ ) . '/includes/helpers/class-gutena-forms-auto-responder-helper.php';
require_once dirname( __DIR__ ) . '/includes/helpers/class-gutena-forms-notification-helper.php';

$form_submit_data = array(
	'formName'      => 'Contact Form',
	'formID'        => 'test_form_1',
	'replyToFname'  => 'Jane',
	'replyToLname'  => 'Doe',
	'raw_data'      => array(
		'first_name'  => array( 'label' => 'First Name', 'value' => 'Jane' ),
		'last_name'   => array( 'label' => 'Last Name', 'value' => 'Doe' ),
		'email_field' => array( 'label' => 'Email', 'value' => 'jane@example.com' ),
	),
);

$field_schema = array(
	'first_name'  => array( 'fieldName' => 'First Name', 'fieldType' => 'text' ),
	'last_name'   => array( 'fieldName' => 'Last Name', 'fieldType' => 'text' ),
	'email_field' => array( 'fieldName' => 'Email', 'fieldType' => 'email' ),
);

$base_schema = array(
	'form_attrs' => array(
		'formID'   => 'test_form_1',
		'settings' => array(
			'emailNotifications' => array(
				'enabled'        => true,
				'hasSavedConfig' => true,
				'notifications'  => array(),
			),
		),
	),
);

function reset_mail() {
	$GLOBALS['gf_mail_log']   = array();
	$GLOBALS['gf_mail_queue'] = array();
}

function assert_true( $condition, $message ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
}

$passed = 0;

try {
	reset_mail();
	$schema = $base_schema;
	$schema['form_attrs']['settings']['emailNotifications']['notifications'] = array(
		array(
			'id' => 'n1', 'enabled' => true, 'send_email_to' => 'admin@example.com', 'subject' => 'Test', 'message' => '<p>Hello {first_name}</p>',
			'from_name' => 'Forms', 'reply_to' => 'jane@example.com', 'reply_to_name' => 'first_name', 'reply_to_last_name' => 'last_name',
		),
	);
	assert_true( Gutena_Forms_Notification_Helper::send_form_notifications( $form_submit_data, $schema, $field_schema, '' ), '1 send true' );
	assert_true( 1 === count( $GLOBALS['gf_mail_log'] ), '1 one email' );
	assert_true( false !== strpos( $GLOBALS['gf_mail_log'][0]['message'], 'Hello Jane' ), '1 merge tag' );
	assert_true( false !== strpos( implode( "\n", (array) $GLOBALS['gf_mail_log'][0]['headers'] ), 'text/html' ), '1 html header' );
	assert_true( false !== strpos( implode( "\n", (array) $GLOBALS['gf_mail_log'][0]['headers'] ), 'admin@example.com' ), '1 from fallback' );
	assert_true( false !== strpos( implode( "\n", (array) $GLOBALS['gf_mail_log'][0]['headers'] ), 'Reply-To: Jane Doe' ), '1 reply name' );
	++$passed;

	reset_mail();
	$schema['form_attrs']['settings']['emailNotifications']['notifications'] = array(
		array( 'id' => 'n1', 'enabled' => true, 'send_email_to' => 'one@example.com', 'subject' => 'One', 'message' => 'One' ),
		array( 'id' => 'n2', 'enabled' => true, 'send_email_to' => 'two@example.com', 'subject' => 'Two', 'message' => 'Two' ),
	);
	assert_true( Gutena_Forms_Notification_Helper::send_form_notifications( $form_submit_data, $schema, $field_schema, '' ), '2 send true' );
	assert_true( 2 === count( $GLOBALS['gf_mail_log'] ), '2 two emails' );
	++$passed;

	reset_mail();
	$schema['form_attrs']['settings']['emailNotifications']['notifications'] = array(
		array( 'id' => 'n1', 'enabled' => false, 'send_email_to' => 'skip@example.com', 'subject' => 'Skip', 'message' => 'Skip' ),
		array( 'id' => 'n2', 'enabled' => true, 'send_email_to' => 'send@example.com', 'subject' => 'Send', 'message' => 'Send' ),
	);
	assert_true( Gutena_Forms_Notification_Helper::send_form_notifications( $form_submit_data, $schema, $field_schema, '' ), '3 send true' );
	assert_true( 1 === count( $GLOBALS['gf_mail_log'] ), '3 one email' );
	++$passed;

	reset_mail();
	$GLOBALS['gf_mail_queue'] = array( false, true );
	$schema['form_attrs']['settings']['emailNotifications']['notifications'] = array(
		array( 'id' => 'n1', 'enabled' => true, 'send_email_to' => 'fail@example.com', 'subject' => 'Fail', 'message' => 'Fail' ),
		array( 'id' => 'n2', 'enabled' => true, 'send_email_to' => 'ok@example.com', 'subject' => 'Ok', 'message' => 'Ok' ),
	);
	assert_true( Gutena_Forms_Notification_Helper::send_form_notifications( $form_submit_data, $schema, $field_schema, '' ), '4 second succeeds' );
	assert_true( 2 === count( $GLOBALS['gf_mail_log'] ), '4 both attempted' );
	++$passed;

	reset_mail();
	$schema['form_attrs']['settings']['emailNotifications']['notifications'] = array(
		array( 'id' => 'n1', 'enabled' => true, 'send_email_to' => '{user_email}', 'subject' => 'Missing', 'message' => 'Missing' ),
		array( 'id' => 'n2', 'enabled' => true, 'send_email_to' => 'valid@example.com', 'subject' => 'Valid', 'message' => 'Valid' ),
	);
	$invalid = $form_submit_data;
	$invalid['raw_data']['email_field']['value'] = 'not-an-email';
	assert_true( Gutena_Forms_Notification_Helper::send_form_notifications( $invalid, $schema, $field_schema, '' ), '5 valid sends' );
	assert_true( 1 === count( $GLOBALS['gf_mail_log'] ), '5 invalid skipped' );
	++$passed;

	reset_mail();
	$schema['form_attrs']['settings']['emailNotifications']['enabled'] = false;
	assert_true( ! Gutena_Forms_Notification_Helper::send_form_notifications( $form_submit_data, $schema, $field_schema, '' ), '6 disabled' );
	assert_true( 0 === count( $GLOBALS['gf_mail_log'] ), '6 no mail' );
	$schema['form_attrs']['settings']['emailNotifications']['enabled'] = true;
	++$passed;

	reset_mail();
	$schema['form_attrs']['settings']['emailNotifications']['notifications'] = array(
		array( 'id' => 'n1', 'enabled' => true, 'send_email_to' => 'admin@example.com', 'subject' => 'From', 'message' => 'Body', 'from_email' => '{admin_email}' ),
	);
	Gutena_Forms_Notification_Helper::send_form_notifications( $form_submit_data, $schema, $field_schema, '' );
	assert_true( false !== strpos( implode( "\n", (array) $GLOBALS['gf_mail_log'][0]['headers'] ), 'admin@example.com' ), '7 from merge tag' );
	assert_true( false !== strpos( $GLOBALS['gf_mail_log'][0]['message'], '<!DOCTYPE html>' ), '8 html wrapper' );
	++$passed;

	echo "All {$passed} notification sending checks passed.\n";
	exit( 0 );
} catch ( Throwable $e ) {
	fwrite( STDERR, 'Verification failed: ' . $e->getMessage() . "\n" );
	exit( 1 );
}
