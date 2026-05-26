<?php
/**
 * Legacy block names (deprecated; hidden from inserter).
 *
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'gutena_forms_get_legacy_hidden_block_names' ) ) :
	/**
	 * Block names that must not appear in the block inserter (backward compatibility only).
	 *
	 * @return string[]
	 */
	function gutena_forms_get_legacy_hidden_block_names() {
		return array(
			'gutena/form-field',
			'gutena/field-group',
			'gutena/text-field-group',
			'gutena/email-field-group',
			'gutena/textarea-field-group',
			'gutena/range-field-group',
			'gutena/radio-field-group',
			'gutena/checkbox-field-group',
			'gutena/dropdown-field-group',
			'gutena/optin-field-group',
			'gutena/number-field-group',
			'gutena/date-field-group',
			'gutena/time-field-group',
			'gutena/phone-field-group',
			'gutena/country-field-group',
			'gutena/state-field-group',
			'gutena/file-upload-field-group',
			'gutena/url-field-group',
			'gutena/hidden-field-group',
			'gutena/rating-field-group',
			'gutena/password-field-group',
		);
	}
endif;

if ( ! function_exists( 'gutena_forms_legacy_block_registration_args' ) ) :
	/**
	 * Registration args for legacy blocks (keep registered, hide from inserter).
	 *
	 * @param array $extra Optional extra registration args merged on top.
	 * @return array
	 */
	function gutena_forms_legacy_block_registration_args( $extra = array() ) {
		return array_merge(
			array(
				'supports' => array(
					'inserter' => false,
				),
			),
			$extra
		);
	}
endif;
