<?php
/**
 * Gutena Forms — AI middleware client for Gutenberg block generation.
 *
 * wp-config.php (staging example):
 *   define( 'GUTENA_FORMS_AI_MIDDLEWARE_BASE_URL', 'https://laravel.test' );
 *   define( 'GUTENA_FORMS_AI_MIDDLEWARE_OPERATION', 'gutena-forms' );
 *   define( 'GUTENA_FORMS_AI_MIDDLEWARE_LICENSE_KEY', '...' ); // optional
 *   define( 'GUTENA_FORMS_AI_PROVISION_SECRET', '...' ); // optional
 *
 * Production default base URL: https://ai-api.wpexperts.io
 *
 * @package Gutena Forms
 * @since   1.9.2
 */

defined( 'ABSPATH' ) || exit;

/**
 * Extract a balanced JSON object starting at $start_pos (handles nested braces in strings).
 *
 * @param string $string    Haystack.
 * @param int    $start_pos Index of opening `{`.
 * @return string|null
 */
function gutena_forms_ai_extract_balanced_json( $string, $start_pos ) {
	$len = strlen( $string );
	if ( $start_pos < 0 || $start_pos >= $len || '{' !== $string[ $start_pos ] ) {
		return null;
	}

	$depth     = 0;
	$in_string = false;
	$escape    = false;

	for ( $i = $start_pos; $i < $len; $i++ ) {
		$ch = $string[ $i ];

		if ( $in_string ) {
			if ( $escape ) {
				$escape = false;
				continue;
			}
			if ( '\\' === $ch ) {
				$escape = true;
				continue;
			}
			if ( '"' === $ch ) {
				$in_string = false;
			}
			continue;
		}

		if ( '"' === $ch ) {
			$in_string = true;
			continue;
		}
		if ( '{' === $ch ) {
			++$depth;
		} elseif ( '}' === $ch ) {
			--$depth;
			if ( 0 === $depth ) {
				return substr( $string, $start_pos, $i - $start_pos + 1 );
			}
		}
	}

	return null;
}

/**
 * Parse gutena/forms block comment attrs from raw markup.
 *
 * @param string $markup Block markup.
 * @return array{attrs:array,json:string,json_start:int,json_end:int}|null
 */
function gutena_forms_ai_extract_forms_block_attrs( $markup ) {
	if ( ! preg_match( '/<!--\s*wp:gutena\/forms\s+/', $markup, $match, PREG_OFFSET_CAPTURE ) ) {
		return null;
	}

	$json_start = strpos( $markup, '{', $match[0][1] );
	if ( false === $json_start ) {
		return null;
	}

	$json = gutena_forms_ai_extract_balanced_json( $markup, $json_start );
	if ( null === $json ) {
		return null;
	}

	$attrs = json_decode( $json, true );
	if ( ! is_array( $attrs ) ) {
		return null;
	}

	return array(
		'attrs'      => $attrs,
		'json'       => $json,
		'json_start' => $json_start,
		'json_end'   => $json_start + strlen( $json ) - 1,
	);
}

/**
 * Derive a human-readable form name from the first formClasses token.
 *
 * @param string $form_classes formClasses attribute value.
 * @return string
 */
function gutena_forms_ai_form_name_from_classes( $form_classes ) {
	$parts = preg_split( '/\s+/', trim( (string) $form_classes ) );
	if ( empty( $parts[0] ) ) {
		return '';
	}

	$slug = $parts[0];
	if ( 0 === strpos( $slug, 'gutena-forms-' ) ) {
		$slug = substr( $slug, strlen( 'gutena-forms-' ) );
	}

	$slug = str_replace( '-', ' ', $slug );

	return ucwords( $slug );
}

/**
 * Pull Gutenberg block markup from middleware JSON.
 *
 * @param array|string $payload      Decoded JSON or empty.
 * @param string       $fallback_raw Raw response if JSON has no markup.
 * @return string
 */
function gutena_forms_ai_parse_block_markup( $payload, $fallback_raw = '' ) {
	if ( ! is_array( $payload ) ) {
		return is_string( $fallback_raw ) ? gutena_forms_ai_sanitize_block_markup( $fallback_raw ) : '';
	}

	$data = isset( $payload['data'] ) && is_array( $payload['data'] ) ? $payload['data'] : $payload;

	// Prefer save-ready Gutenberg block_markup over JSON field spec in content.
	foreach ( array( 'block_markup', 'form_code', 'form_template' ) as $key ) {
		if ( empty( $data[ $key ] ) || ! is_string( $data[ $key ] ) ) {
			continue;
		}

		$out = gutena_forms_ai_sanitize_block_markup( $data[ $key ] );
		if ( '' !== $out && gutena_forms_ai_is_save_ready_form_markup( $out ) ) {
			return $out;
		}
	}

	// Fall back to JSON field spec when no save-ready block markup exists.
	foreach ( array( 'content', 'block_markup' ) as $key ) {
		if ( empty( $data[ $key ] ) || ! is_string( $data[ $key ] ) ) {
			continue;
		}

		$raw = gutena_forms_ai_sanitize_block_markup( $data[ $key ] );
		if ( gutena_forms_ai_is_json_field_spec( $raw ) ) {
			return $raw;
		}
	}

	foreach ( array( 'block_markup', 'form_code', 'form_template', 'content' ) as $key ) {
		if ( empty( $data[ $key ] ) || ! is_string( $data[ $key ] ) ) {
			continue;
		}

		$out = gutena_forms_ai_sanitize_block_markup( $data[ $key ] );
		if ( '' === $out ) {
			continue;
		}

		if ( gutena_forms_ai_is_save_ready_form_markup( $out ) ) {
			return $out;
		}
	}

	foreach ( array( 'block_markup', 'content' ) as $key ) {
		if ( empty( $data[ $key ] ) ) {
			continue;
		}

		$raw = gutena_forms_ai_sanitize_block_markup( $data[ $key ] );
		if ( '' !== $raw && ( '{' === $raw[0] || '[' === $raw[0] ) ) {
			return $raw;
		}
	}

	return is_string( $fallback_raw ) ? gutena_forms_ai_sanitize_block_markup( $fallback_raw ) : '';
}

/**
 * Whether a string is middleware JSON with a fields array.
 *
 * @param string $raw Raw string.
 * @return bool
 */
function gutena_forms_ai_is_json_field_spec( $raw ) {
	if ( '' === $raw || ( '{' !== $raw[0] && '[' !== $raw[0] ) ) {
		return false;
	}

	$spec = json_decode( $raw, true );

	return is_array( $spec ) && ! empty( $spec['fields'] ) && is_array( $spec['fields'] );
}

/**
 * Build formClasses string matching the block editor.
 *
 * @param string $form_id   Form ID.
 * @param string $form_name Form name.
 * @return string
 */
function gutena_forms_ai_build_form_classes( $form_id, $form_name ) {
	$form_slug = sanitize_title( (string) $form_name );

	return trim( $form_slug . ' ' . (string) $form_id . ' after_submit_message' );
}

/**
 * Form element class string as produced by gutena/forms save() + useBlockProps.save().
 *
 * @param string $form_classes Value of the formClasses block attribute (no wp-block prefix).
 * @return string
 */
function gutena_forms_ai_form_save_html_class( $form_classes ) {
	$form_classes = trim( (string) $form_classes );
	if ( '' === $form_classes ) {
		return 'wp-block-gutena-forms';
	}
	if ( preg_match( '/(?:^|\s)wp-block-gutena-forms(?:\s|$)/', $form_classes ) ) {
		return $form_classes;
	}

	return 'wp-block-gutena-forms ' . $form_classes;
}

/**
 * Whether middleware markup already contains save()-compatible Gutena form HTML.
 *
 * @param string $markup Block markup string.
 * @return bool
 */
function gutena_forms_ai_is_save_ready_form_markup( $markup ) {
	if ( false === stripos( $markup, 'gutena/forms' ) ) {
		return false;
	}

	if ( false === stripos( $markup, '<form' ) ) {
		return false;
	}

	return (bool) preg_match( '/name=["\']formid["\']/i', $markup );
}

/**
 * Inject formID, formName, and formClasses into save-ready block markup without re-serializing.
 *
 * Preserves middleware HTML exactly (avoids parse_blocks/serialize_blocks validation drift).
 *
 * @param string $markup         Save-ready block markup.
 * @param string $form_id        New form ID.
 * @param string $form_name      Fallback form display name.
 * @param string $resolved_name  Optional. Resolved form name after injection.
 * @return string
 */
function gutena_forms_ai_inject_identity_into_markup( $markup, $form_id, $form_name, &$resolved_name = '' ) {
	$parsed = gutena_forms_ai_extract_forms_block_attrs( $markup );

	if ( null === $parsed ) {
		$resolved_name = $form_name;
		return $markup;
	}

	$attrs       = $parsed['attrs'];
	$old_form_id = isset( $attrs['formID'] ) ? (string) $attrs['formID'] : '';

	if ( ! empty( $attrs['formClasses'] ) && is_string( $attrs['formClasses'] ) ) {
		$form_classes = '' !== $old_form_id
			? str_replace( $old_form_id, $form_id, $attrs['formClasses'] )
			: $attrs['formClasses'];
	} else {
		$form_classes = gutena_forms_ai_build_form_classes( $form_id, $form_name );
	}

	if ( ! empty( $attrs['formName'] ) && is_string( $attrs['formName'] ) ) {
		$resolved_name = sanitize_text_field( $attrs['formName'] );
	} else {
		$derived = gutena_forms_ai_form_name_from_classes( $form_classes );
		$resolved_name = '' !== $derived ? $derived : $form_name;
	}

	$attrs['formID']      = $form_id;
	$attrs['formName']    = $resolved_name;
	$attrs['formClasses'] = $form_classes;

	$new_json = wp_json_encode( $attrs );
	$markup   = substr( $markup, 0, $parsed['json_start'] ) . $new_json . substr( $markup, $parsed['json_end'] + 1 );

	$markup = preg_replace(
		'/(<input[^>]*name=["\']formid["\'][^>]*value=["\'])([^"\']*)(["\'])/i',
		'${1}' . esc_attr( $form_id ) . '${3}',
		(string) $markup,
		1
	);

	if ( preg_match( '/<form[^>]*\sclass=["\']([^"\']*)["\']/i', $markup, $form_match ) ) {
		$html_class = $form_match[1];
		if ( '' !== $old_form_id ) {
			$html_class = str_replace( $old_form_id, $form_id, $html_class );
		}
		$html_class = gutena_forms_ai_form_save_html_class(
			trim( preg_replace( '/\bwp-block-gutena-forms\b/', '', $html_class ) )
		);

		$markup = preg_replace(
			'/(<form[^>]*\sclass=["\'])([^"\']*)(["\'])/i',
			'${1}' . esc_attr( $html_class ) . '${3}',
			(string) $markup,
			1
		);
	}

	return $markup;
}

/**
 * Convert middleware JSON field spec into serialized Gutena block markup.
 *
 * @param string $raw       JSON string or markdown-fenced JSON.
 * @param string $form_id   Gutena form block ID.
 * @param string $form_name Form display name.
 * @return string
 */
function gutena_forms_ai_markup_from_json_spec( $raw, $form_id = '', $form_name = '' ) {
	$raw = gutena_forms_ai_sanitize_block_markup( $raw );
	if ( '' === $raw || ( '{' !== $raw[0] && '[' !== $raw[0] ) ) {
		return '';
	}

	$spec = json_decode( $raw, true );
	if ( ! is_array( $spec ) || empty( $spec['fields'] ) || ! is_array( $spec['fields'] ) ) {
		return '';
	}

	if ( ! function_exists( 'serialize_blocks' ) ) {
		return '';
	}

	if ( '' === $form_name && ! empty( $spec['formName'] ) && is_string( $spec['formName'] ) ) {
		$form_name = $spec['formName'];
	}
	if ( '' === $form_name ) {
		$form_name = __( 'Contact Form', 'gutena-forms' );
	}

	$inner_blocks = array();
	$index        = 0;

	foreach ( $spec['fields'] as $field ) {
		if ( ! is_array( $field ) ) {
			continue;
		}

		$block = gutena_forms_ai_map_json_field_to_block( $field, $index );
		if ( empty( $block ) ) {
			continue;
		}

		gutena_forms_ai_apply_field_block_html( $block );
		$inner_blocks[] = $block;
		++$index;
	}

	if ( empty( $inner_blocks ) ) {
		return '';
	}

	$inner_blocks[] = gutena_forms_ai_build_submit_buttons_block();

	$form_block = gutena_forms_ai_build_form_block( $inner_blocks, $form_id, $form_name );
	$markup     = serialize_blocks( array( $form_block ) );

	return $markup;
}

/**
 * Build gutena/forms block with valid save() HTML and innerContent.
 *
 * @param array  $inner_blocks Inner blocks.
 * @param string $form_id      Form ID.
 * @param string $form_name    Form name.
 * @return array
 */
function gutena_forms_ai_build_form_block( $inner_blocks, $form_id, $form_name ) {
	$form_id       = (string) $form_id;
	$form_name     = sanitize_text_field( (string) $form_name );
	$form_classes  = gutena_forms_ai_build_form_classes( $form_id, $form_name );
	$form_class_attr = esc_attr( $form_classes );

	$inner_content = array(
		'<form method="post" enctype="multipart/form-data" class="' . $form_class_attr . '">',
		'<input type="hidden" name="formid" value="' . esc_attr( $form_id ) . '" />',
	);
	foreach ( $inner_blocks as $ignored ) {
		$inner_content[] = null;
	}
	$inner_content[] = '</form>';

	$inner_html = '<form method="post" enctype="multipart/form-data" class="' . $form_class_attr . '">';
	$inner_html .= '<input type="hidden" name="formid" value="' . esc_attr( $form_id ) . '" />';
	foreach ( $inner_blocks as $inner_block ) {
		$inner_html .= serialize_block( $inner_block );
	}
	$inner_html .= '</form>';

	return array(
		'blockName'    => 'gutena/forms',
		'attrs'        => array(
			'formID'      => $form_id,
			'formName'    => $form_name,
			'formClasses' => $form_classes,
		),
		'innerBlocks'  => $inner_blocks,
		'innerHTML'    => $inner_html,
		'innerContent' => $inner_content,
	);
}

/**
 * Build submit buttons block with valid core/button HTML.
 *
 * @return array
 */
function gutena_forms_ai_build_submit_buttons_block() {
	$submit_label = __( 'Submit', 'gutena-forms' );
	$button_html  = '<div class="wp-block-button gutena-forms-submit-button"><a class="wp-block-button__link wp-element-button">' . esc_html( $submit_label ) . '</a></div>';

	$button_block = array(
		'blockName'    => 'core/button',
		'attrs'        => array(
			'text'      => $submit_label,
			'className' => 'gutena-forms-submit-button',
		),
		'innerBlocks'  => array(),
		'innerHTML'    => $button_html,
		'innerContent' => array( $button_html ),
	);

	$buttons_html = '<div class="wp-block-buttons gutena-forms-submit-buttons">' . $button_html . '</div>';

	return array(
		'blockName'    => 'core/buttons',
		'attrs'        => array(
			'className' => 'gutena-forms-submit-buttons',
		),
		'innerBlocks'  => array( $button_block ),
		'innerHTML'    => $buttons_html,
		'innerContent' => array( '<div class="wp-block-buttons gutena-forms-submit-buttons">', null, '</div>' ),
	);
}

/**
 * Apply save()-compatible innerHTML to a field block array.
 *
 * @param array $block Block array (by reference).
 */
function gutena_forms_ai_apply_field_block_html( &$block ) {
	$attrs      = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();
	$block_name = isset( $block['blockName'] ) ? (string) $block['blockName'] : '';
	$html       = gutena_forms_ai_render_field_save_html( $block_name, $attrs );

	$block['innerHTML']    = $html;
	$block['innerContent'] = array( $html );
}

/**
 * Render static field HTML matching Gutena field block save() output.
 *
 * @param string $block_name Block name.
 * @param array  $attrs        Block attributes.
 * @return string
 */
function gutena_forms_ai_render_field_save_html( $block_name, $attrs ) {
	$name        = esc_attr( (string) ( $attrs['nameAttr'] ?? 'f_0' ) );
	$label       = esc_html( (string) ( $attrs['fieldName'] ?? 'Field' ) );
	$required    = ! empty( $attrs['isRequired'] );
	$placeholder = esc_attr( (string) ( $attrs['placeholder'] ?? '' ) );
	$default     = esc_attr( (string) ( $attrs['defaultValue'] ?? '' ) );
	$req_attr    = $required ? ' required' : '';
	$req_star    = $required ? ' *' : '';

	$label_html = '<label for="' . $name . '" class="heading-input-label-gutena">' . $label . $req_star . '</label>';
	$error_html = '<p class="gutena-forms-field-error-msg"></p>';

	if ( 'gutena/textarea-field' === $block_name ) {
		$rows = isset( $attrs['textAreaRows'] ) ? (int) $attrs['textAreaRows'] : 5;
		if ( $rows < 1 ) {
			$rows = 5;
		}
		$input = '<textarea id="' . $name . '" name="' . $name . '" class="gutena-forms-field textarea-field' . ( $required ? ' required-field' : '' ) . ' " placeholder="' . $placeholder . '" rows="' . $rows . '"' . $req_attr . '>' . esc_html( (string) ( $attrs['defaultValue'] ?? '' ) ) . '</textarea>';

		return '<div class="wp-block-gutena-field-group wp-block-gutena-textarea-field field-group-type-textarea standalone-textarea-field">' .
			$label_html .
			'<div class="wp-block-gutena-form-field">' . $input . '</div>' .
			$error_html .
			'</div>';
	}

	if ( 'gutena/number-field' === $block_name ) {
		$input = '<input id="' . $name . '" name="' . $name . '" type="number" class="gutena-forms-field number-field' . ( $required ? ' required-field' : '' ) . ' " placeholder="' . $placeholder . '" value="' . $default . '"' . $req_attr . ' />';

		return '<div class="wp-block-gutena-field-group wp-block-gutena-number-field field-group-type-number standalone-number-field">' .
			$label_html .
			'<div class="wp-block-gutena-form-field">' . $input . '</div>' .
			$error_html .
			'</div>';
	}

	if ( 'gutena/email-field' === $block_name ) {
		$input = '<input id="' . $name . '" name="' . $name . '" type="email" class="gutena-forms-field email-field' . ( $required ? ' required-field' : '' ) . ' " placeholder="' . $placeholder . '" value="' . $default . '"' . $req_attr . ' />';

		return '<div class="wp-block-gutena-field-group wp-block-gutena-email-field field-group-type-email standalone-email-field">' .
			$label_html .
			'<div class="wp-block-gutena-form-field">' . $input . '</div>' .
			$error_html .
			'</div>';
	}

	$input = '<input id="' . $name . '" name="' . $name . '" type="text" class="gutena-forms-field text-field' . ( $required ? ' required-field' : '' ) . ' " placeholder="' . $placeholder . '" value="' . $default . '"' . $req_attr . ' />';

	return '<div class="wp-block-gutena-field-group wp-block-gutena-text-field field-group-type-text standalone-text-field">' .
		$label_html .
		'<div class="wp-block-gutena-form-field">' . $input . '</div>' .
		$error_html .
		'</div>';
}

/**
 * Map one JSON field definition to a Gutena inner block.
 *
 * @param array $field Field definition from middleware JSON.
 * @param int   $index Field index.
 * @return array|null
 */
function gutena_forms_ai_map_json_field_to_block( $field, $index ) {
	$type  = strtolower( sanitize_key( (string) ( $field['type'] ?? 'text' ) ) );
	$label = isset( $field['label'] ) ? sanitize_text_field( (string) $field['label'] ) : sprintf( /* translators: %d field number */ __( 'Field %d', 'gutena-forms' ), $index + 1 );

	$block_map = array(
		'text'     => 'gutena/text-field',
		'email'    => 'gutena/email-field',
		'textarea' => 'gutena/textarea-field',
		'number'   => 'gutena/number-field',
		'select'   => 'gutena/dropdown-field',
		'dropdown' => 'gutena/dropdown-field',
		'checkbox' => 'gutena/checkbox-field',
		'radio'    => 'gutena/radio-field',
		'range'    => 'gutena/range-field',
		'optin'    => 'gutena/optin-field',
		'tel'      => 'gutena/text-field',
		'phone'    => 'gutena/text-field',
		'date'     => 'gutena/text-field',
	);

	$field_type_map = array(
		'tel'   => 'tel',
		'phone' => 'tel',
		'date'  => 'date',
	);

	$block_name = isset( $block_map[ $type ] ) ? $block_map[ $type ] : 'gutena/text-field';
	$field_type = isset( $field_type_map[ $type ] ) ? $field_type_map[ $type ] : $type;
	if ( 'dropdown' === $type ) {
		$field_type = 'select';
	}

	$attrs = array(
		'nameAttr'    => 'f_' . $index,
		'fieldName'   => $label,
		'fieldType'   => $field_type,
		'isRequired'  => ! empty( $field['required'] ),
		'placeholder' => isset( $field['placeholder'] ) ? sanitize_text_field( (string) $field['placeholder'] ) : '',
	);

	if ( ! empty( $field['options'] ) && is_array( $field['options'] ) ) {
		$attrs['selectOptions'] = array_values(
			array_map(
				static function ( $option ) {
					return sanitize_text_field( (string) $option );
				},
				$field['options']
			)
		);
	}

	if ( 'textarea' === $type ) {
		$attrs['textAreaRows'] = 5;
	}

	return array(
		'blockName'    => $block_name,
		'attrs'        => $attrs,
		'innerBlocks'  => array(),
		'innerHTML'    => '',
		'innerContent' => array(),
	);
}

/**
 * Clean AI text into Gutenberg block markup (strip fences, normalize newlines).
 *
 * @param string $content Raw model output.
 * @return string
 */
function gutena_forms_ai_sanitize_block_markup( $content ) {
	$content = preg_replace( '/(\\\\+n|\\\\+r)/', "\n", (string) $content );
	$content = str_replace( array( "\r\n", "\r" ), "\n", $content );
	$content = str_replace( '\\"', '"', $content );
	$content = trim( $content );

	if ( preg_match( '/```(?:php|html|text|plaintext|json)?\s*\n(.*?)\n```/s', $content, $matches ) ) {
		$content = trim( $matches[1] );
	}

	if ( preg_match( '/^```json\s*/i', $content ) ) {
		$content = trim( preg_replace( '/^```json\s*/i', '', $content ) );
		$content = trim( preg_replace( '/\s*```$/', '', $content ) );
	}

	return trim( $content );
}

/**
 * HTTP client: provision-free, process-ai, license storage.
 */
class Gutena_Forms_Ai_Middleware_Client {

	const DEFAULT_OPERATION = 'gutena-forms';

	const DEFAULT_BASE_URL = 'https://ai-api.wpexperts.io';

	const EMBEDDED_PROVISION_SECRET = 'gutena-forms-embedded-provision-2026-04-K9mNp2sLqR';

	const LICENSE_OPTION = 'gutena_forms_ai_middleware_license_key';

	/**
	 * Whether outbound middleware HTTP requests should verify SSL certificates.
	 *
	 * @return bool
	 */
	public static function should_verify_ssl() {
		if ( defined( 'GUTENA_FORMS_AI_MIDDLEWARE_SSL_VERIFY' ) ) {
			return (bool) GUTENA_FORMS_AI_MIDDLEWARE_SSL_VERIFY;
		}

		if ( function_exists( 'wp_get_environment_type' ) && 'local' === wp_get_environment_type() ) {
			return false;
		}

		return (bool) apply_filters( 'gutena_forms_ai_middleware_sslverify', true );
	}

	/**
	 * Normalize site URL for middleware (scheme, host, path).
	 *
	 * @param string $url Site URL.
	 * @return string
	 */
	public static function normalize_site_url( $url ) {
		$url = untrailingslashit( trim( (string) $url ) );
		if ( '' === $url ) {
			return '';
		}
		$parts = wp_parse_url( $url );
		if ( ! is_array( $parts ) || empty( $parts['host'] ) ) {
			return $url;
		}
		$scheme = isset( $parts['scheme'] ) ? strtolower( (string) $parts['scheme'] ) : 'http';
		$host   = strtolower( (string) $parts['host'] );
		$port   = ! empty( $parts['port'] ) ? ':' . (int) $parts['port'] : '';
		$path   = isset( $parts['path'] ) ? trim( (string) $parts['path'] ) : '';
		$path   = untrailingslashit( $path );
		$out    = $scheme . '://' . $host . $port;
		if ( '' !== $path && '/' !== $path ) {
			$out .= $path;
		}

		return $out;
	}

	/**
	 * License key, operation slug, site URL, middleware base URL.
	 *
	 * @return array{license_key:string,operation:string,site_url:string,base_url:string}
	 */
	public static function mw_config() {
		$license = get_option( self::LICENSE_OPTION, '' );
		$license = is_string( $license ) ? trim( $license ) : '';

		if ( '' === $license && defined( 'GUTENA_FORMS_AI_MIDDLEWARE_LICENSE_KEY' ) && GUTENA_FORMS_AI_MIDDLEWARE_LICENSE_KEY !== '' && GUTENA_FORMS_AI_MIDDLEWARE_LICENSE_KEY !== null ) {
			$license = trim( (string) GUTENA_FORMS_AI_MIDDLEWARE_LICENSE_KEY );
		}

		$op = self::DEFAULT_OPERATION;
		if ( defined( 'GUTENA_FORMS_AI_MIDDLEWARE_OPERATION' ) && GUTENA_FORMS_AI_MIDDLEWARE_OPERATION !== '' && GUTENA_FORMS_AI_MIDDLEWARE_OPERATION !== null ) {
			$op = trim( (string) GUTENA_FORMS_AI_MIDDLEWARE_OPERATION );
		}
		if ( '' === $op ) {
			$op = self::DEFAULT_OPERATION;
		}
		$op = (string) apply_filters( 'gutena_forms_ai_middleware_operation', $op );

		$site = untrailingslashit( home_url( '/' ) );
		if ( '' === $site ) {
			$site = untrailingslashit( site_url( '/' ) );
		}
		$site = self::normalize_site_url( $site );
		$site = (string) apply_filters( 'gutena_forms_ai_middleware_site_url', $site );

		if ( defined( 'GUTENA_FORMS_AI_MIDDLEWARE_BASE_URL' ) && GUTENA_FORMS_AI_MIDDLEWARE_BASE_URL !== '' && GUTENA_FORMS_AI_MIDDLEWARE_BASE_URL !== null ) {
			$base = (string) GUTENA_FORMS_AI_MIDDLEWARE_BASE_URL;
		} else {
			$base = self::DEFAULT_BASE_URL;
		}
		$base = (string) apply_filters( 'gutena_forms_ai_middleware_base_url', $base );

		return array(
			'license_key' => $license,
			'operation'   => $op,
			'site_url'    => $site,
			'base_url'    => untrailingslashit( $base ),
		);
	}

	/**
	 * Whether middleware base URL is non-empty.
	 *
	 * @return bool
	 */
	public static function mw_base_ok() {
		return '' !== self::mw_config()['base_url'];
	}

	/**
	 * Full URL for path under /api/.
	 *
	 * @param string $base_url Middleware base URL.
	 * @param string $endpoint API endpoint slug.
	 * @return string
	 */
	public static function mw_url( $base_url, $endpoint ) {
		$base     = untrailingslashit( (string) $base_url );
		$endpoint = trim( (string) $endpoint, '/' );
		if ( '' === $base || '' === $endpoint ) {
			return '';
		}
		$base_lower = strtolower( $base );
		if ( strlen( $base_lower ) >= 4 && substr( $base_lower, -4 ) === '/api' ) {
			$url = $base . '/' . $endpoint;
		} else {
			$url = $base . '/api/' . $endpoint;
		}

		return (string) apply_filters( 'gutena_forms_ai_middleware_api_url', $url, $base_url, $endpoint );
	}

	/**
	 * Stored or constant license key present.
	 *
	 * @return bool
	 */
	public static function has_license() {
		return '' !== trim( self::mw_config()['license_key'] );
	}

	/**
	 * Non-empty getenv / $_ENV value or empty string.
	 *
	 * @param string $name Environment variable name.
	 * @return string
	 */
	private static function env_str( $name ) {
		$v = getenv( $name );
		if ( is_string( $v ) && $v !== '' ) {
			return $v;
		}
		if ( isset( $_ENV[ $name ] ) && is_string( $_ENV[ $name ] ) && $_ENV[ $name ] !== '' ) {
			return (string) $_ENV[ $name ];
		}

		return '';
	}

	/**
	 * HTTP header name for provision-free auth (shared middleware uses CF7 header).
	 *
	 * @return string
	 */
	public static function provision_header_name() {
		$header = 'X-CF7Apps-Provision-Secret';

		return (string) apply_filters( 'gutena_forms_ai_provision_header_name', $header );
	}

	/**
	 * Secret for POST provision-free (must match Laravel).
	 *
	 * @return string
	 */
	public static function provision_secret() {
		if ( defined( 'GUTENA_FORMS_PROVISION_SECRET' ) && GUTENA_FORMS_PROVISION_SECRET !== '' && GUTENA_FORMS_PROVISION_SECRET !== null ) {
			return (string) apply_filters( 'gutena_forms_ai_provision_secret', (string) GUTENA_FORMS_PROVISION_SECRET );
		}
		if ( defined( 'GUTENA_FORMS_AI_PROVISION_SECRET' ) && GUTENA_FORMS_AI_PROVISION_SECRET !== '' && GUTENA_FORMS_AI_PROVISION_SECRET !== null ) {
			return (string) apply_filters( 'gutena_forms_ai_provision_secret', (string) GUTENA_FORMS_AI_PROVISION_SECRET );
		}
		foreach ( array( 'GUTENA_FORMS_PROVISION_SECRET', 'GUTENA_FORMS_AI_PROVISION_SECRET' ) as $key ) {
			$v = self::env_str( $key );
			if ( $v !== '' ) {
				return (string) apply_filters( 'gutena_forms_ai_provision_secret', $v );
			}
		}
		$opt = get_option( 'gutena_forms_ai_provision_secret', '' );
		$opt = is_string( $opt ) ? trim( $opt ) : '';
		if ( '' !== $opt ) {
			return (string) apply_filters( 'gutena_forms_ai_provision_secret', $opt );
		}

		// Shared local/staging middleware (same Laravel app as CF7 Apps).
		if ( function_exists( 'wp_get_environment_type' ) && 'local' === wp_get_environment_type() ) {
			if ( defined( 'CF7APPS_PROVISION_SECRET' ) && CF7APPS_PROVISION_SECRET !== '' && CF7APPS_PROVISION_SECRET !== null ) {
				return (string) apply_filters( 'gutena_forms_ai_provision_secret', (string) CF7APPS_PROVISION_SECRET );
			}
			if ( defined( 'CF7APPS_AI_PROVISION_SECRET' ) && CF7APPS_AI_PROVISION_SECRET !== '' && CF7APPS_AI_PROVISION_SECRET !== null ) {
				return (string) apply_filters( 'gutena_forms_ai_provision_secret', (string) CF7APPS_AI_PROVISION_SECRET );
			}
			foreach ( array( 'CF7APPS_PROVISION_SECRET', 'CF7APPS_AI_PROVISION_SECRET' ) as $key ) {
				$v = self::env_str( $key );
				if ( $v !== '' ) {
					return (string) apply_filters( 'gutena_forms_ai_provision_secret', $v );
				}
			}
			return (string) apply_filters( 'gutena_forms_ai_provision_secret', 'cf7apps-hfcf7-embedded-provision-2026-04-K9mNp2sLqR' );
		}

		return (string) apply_filters( 'gutena_forms_ai_provision_secret', self::EMBEDDED_PROVISION_SECRET );
	}

	/**
	 * Admin email + display name for Laravel user on first provision.
	 *
	 * @return array{admin_email:string,admin_name:string}
	 */
	public static function wp_owner() {
		$admin_email = sanitize_email( (string) get_option( 'admin_email' ) );

		$admin_name = '';
		$user       = wp_get_current_user();
		if ( $user && $user->ID ) {
			$admin_name = trim( (string) $user->display_name );
		}
		if ( '' === $admin_name ) {
			$admin_name = trim( (string) get_bloginfo( 'name' ) );
		}
		if ( function_exists( 'mb_strlen' ) && function_exists( 'mb_substr' ) && mb_strlen( $admin_name, 'UTF-8' ) > 255 ) {
			$admin_name = mb_substr( $admin_name, 0, 255, 'UTF-8' );
		} elseif ( strlen( $admin_name ) > 255 ) {
			$admin_name = substr( $admin_name, 0, 255 );
		}

		return array(
			'admin_email' => $admin_email,
			'admin_name'  => $admin_name,
		);
	}

	/**
	 * Call provision-free once if no license key.
	 *
	 * @return true|WP_Error
	 */
	public static function ensure_license() {
		if ( self::has_license() ) {
			return true;
		}

		$secret       = self::provision_secret();
		$header_name  = self::provision_header_name();
		if ( '' === trim( $secret ) ) {
			return new WP_Error(
				'gutena_forms_ai_license_or_secret_required',
				__( 'The middleware requires a license key, and this site could not obtain one. Set GUTENA_FORMS_AI_MIDDLEWARE_LICENSE_KEY or GUTENA_FORMS_AI_PROVISION_SECRET in wp-config.php.', 'gutena-forms' )
			);
		}

		$config = self::mw_config();
		if ( '' === $config['base_url'] ) {
			return new WP_Error(
				'gutena_forms_ai_no_base',
				__( 'AI middleware base URL is not set.', 'gutena-forms' )
			);
		}

		$site_url = self::normalize_site_url( $config['site_url'] );
		$url      = self::mw_url( $config['base_url'], 'licenses/provision-free' );
		$identity = self::wp_owner();
		$body     = array(
			'site_url'    => $site_url,
			'plugin_slug' => $config['operation'],
		);
		if ( '' !== $identity['admin_email'] ) {
			$body['admin_email'] = $identity['admin_email'];
		}
		if ( '' !== $identity['admin_name'] ) {
			$body['admin_name'] = $identity['admin_name'];
		}

		$args = apply_filters(
			'gutena_forms_ai_provision_request_args',
			array(
				'timeout'   => 45,
				'sslverify' => self::should_verify_ssl(),
				'headers'   => array(
					'Content-Type' => 'application/json; charset=utf-8',
					'Accept'       => 'application/json',
					$header_name   => $secret,
				),
				'body'      => wp_json_encode( $body ),
			),
			$body
		);

		$response = wp_remote_post( $url, $args );

		if ( is_wp_error( $response ) ) {
			return new WP_Error(
				'gutena_forms_ai_provision_http',
				sprintf(
					/* translators: %s error message */
					__( 'Could not provision license: %s', 'gutena-forms' ),
					$response->get_error_message()
				)
			);
		}

		$code = wp_remote_retrieve_response_code( $response );
		$raw  = wp_remote_retrieve_body( $response );
		$json = json_decode( $raw, true );

		$license_from_response = self::license_from_json( $json );
		if ( $code >= 200 && $code < 300 && '' !== $license_from_response ) {
			self::save_license( $license_from_response );

			return true;
		}

		$msg = self::api_msg( $json, __( 'License provisioning failed.', 'gutena-forms' ) );

		return new WP_Error( 'gutena_forms_ai_provision_api', $msg, array( 'status' => $code, 'raw' => $raw ) );
	}

	/**
	 * License key from provision-free JSON data.
	 *
	 * @param mixed $json Decoded JSON.
	 * @return string
	 */
	private static function license_from_json( $json ) {
		if ( is_array( $json ) && ! empty( $json['data']['license_key'] ) && is_string( $json['data']['license_key'] ) ) {
			return trim( $json['data']['license_key'] );
		}

		return '';
	}

	/**
	 * User-facing string from API error JSON.
	 *
	 * @param mixed  $json    Decoded JSON.
	 * @param string $default Default message.
	 * @return string
	 */
	private static function api_msg( $json, $default ) {
		if ( ! is_array( $json ) ) {
			return $default;
		}
		if ( ! empty( $json['message'] ) && is_string( $json['message'] ) ) {
			return $json['message'];
		}
		if ( ! empty( $json['errors'] ) && is_array( $json['errors'] ) ) {
			$parts = array();
			foreach ( $json['errors'] as $messages ) {
				if ( is_array( $messages ) ) {
					foreach ( $messages as $m ) {
						if ( is_string( $m ) ) {
							$parts[] = $m;
						}
					}
				} elseif ( is_string( $messages ) ) {
					$parts[] = $messages;
				}
			}
			if ( $parts ) {
				return implode( ' ', $parts );
			}
		}

		return $default;
	}

	/**
	 * Persist license key to option.
	 *
	 * @param string $license_key License key.
	 * @return bool
	 */
	public static function save_license( $license_key ) {
		$license_key = sanitize_text_field( $license_key );
		if ( '' === $license_key ) {
			return false;
		}

		return (bool) update_option( self::LICENSE_OPTION, $license_key, false );
	}

	/**
	 * Run process-ai; returns block markup or WP_Error.
	 *
	 * @param string $user_prompt Combined user prompt.
	 * @return string|WP_Error
	 */
	public static function block_markup_from_ai( $user_prompt ) {
		$config = self::mw_config();

		if ( '' === $config['base_url'] ) {
			return new WP_Error(
				'gutena_forms_ai_no_base',
				__( 'AI middleware base URL is not set.', 'gutena-forms' )
			);
		}

		$ensured = self::ensure_license();
		if ( is_wp_error( $ensured ) ) {
			return $ensured;
		}

		$config = self::mw_config();
		if ( '' === trim( $config['license_key'] ) ) {
			return new WP_Error(
				'gutena_forms_ai_no_license',
				__( 'No license key is available for the middleware. Check provision-free response or set GUTENA_FORMS_AI_MIDDLEWARE_LICENSE_KEY.', 'gutena-forms' )
			);
		}

		$url = self::mw_url( $config['base_url'], 'process-ai' );

		$body = array(
			'site_url'  => untrailingslashit( $config['site_url'] ),
			'prompt'    => $user_prompt,
			'operation' => $config['operation'],
		);

		$body = apply_filters( 'gutena_forms_ai_middleware_request_body', $body, $user_prompt );

		$args = apply_filters(
			'gutena_forms_ai_middleware_request_args',
			array(
				'timeout'   => 90,
				'sslverify' => self::should_verify_ssl(),
				'headers'   => array(
					'Content-Type'  => 'application/json; charset=utf-8',
					'Accept'        => 'application/json',
					'X-License-Key' => $config['license_key'],
				),
				'body'      => wp_json_encode( $body ),
			),
			$body
		);

		$response = wp_remote_post( $url, $args );
		if ( is_wp_error( $response ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				error_log( 'Gutena Forms AI HTTP error: ' . $response->get_error_message() );
			}
			return new WP_Error(
				'gutena_forms_ai_http',
				sprintf(
					/* translators: %s error message */
					__( 'Could not reach AI server: %s', 'gutena-forms' ),
					$response->get_error_message()
				)
			);
		}

		$code = wp_remote_retrieve_response_code( $response );
		$raw  = wp_remote_retrieve_body( $response );
		$json = json_decode( $raw, true );

		if ( ! is_array( $json ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				error_log( 'Gutena Forms AI invalid JSON response: ' . substr( $raw, 0, 500 ) );
			}
			return new WP_Error(
				'gutena_forms_ai_bad_response',
				__( 'AI server returned an invalid response.', 'gutena-forms' )
			);
		}

		if ( $code >= 400 || ( isset( $json['status'] ) && 'failed' === $json['status'] ) ) {
			$msg      = $json['message'] ?? $json['error'] ?? __( 'AI request failed.', 'gutena-forms' );
			$err_code = ( 402 === (int) $code ) ? 'no_middleware_credits' : 'gutena_forms_ai_api';

			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				error_log( 'Gutena Forms AI API error (' . $code . '): ' . ( is_string( $msg ) ? $msg : wp_json_encode( $json ) ) );
			}

			return new WP_Error(
				$err_code,
				is_string( $msg ) ? $msg : __( 'AI request failed.', 'gutena-forms' ),
				array( 'status' => $code )
			);
		}

		$markup = gutena_forms_ai_parse_block_markup( $json, $raw );

		if ( '' === trim( $markup ) ) {
			return new WP_Error(
				'gutena_forms_ai_empty',
				__( 'AI returned no form code. Try a clearer description or check middleware logs.', 'gutena-forms' )
			);
		}

		return $markup;
	}
}
