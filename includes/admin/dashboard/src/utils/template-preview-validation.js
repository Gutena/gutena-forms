/**
 * Local-only validation for template form previews.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

import { __ } from '@wordpress/i18n';

const EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

/**
 * Build initial controlled values for preview fields.
 *
 * @param {Array} fields Template fields from API.
 * @returns {Object}
 */
export function getInitialPreviewValues( fields = [] ) {
	const values = {};

	fields.forEach( ( field, index ) => {
		const nameAttr = field?.attributes?.nameAttr || `field-${ index }`;
		const fieldType = field?.attributes?.fieldType || '';
		const block = field?.block || '';

		if ( 'checkbox' === fieldType || 'gutena/checkbox-field' === block ) {
			values[ nameAttr ] = [];
			return;
		}

		if ( 'radio' === fieldType ) {
			values[ nameAttr ] = '';
			return;
		}

		if ( 'optin' === fieldType || 'gutena/optin-field' === block ) {
			values[ nameAttr ] = false;
			return;
		}

		if ( 'file' === fieldType || 'gutena/file-upload-field' === block ) {
			values[ nameAttr ] = null;
			return;
		}

		if ( 'range' === fieldType || 'gutena/range-field' === block ) {
			const attrs = field.attributes || {};
			values[ nameAttr ] = String( attrs.defaultValue ?? attrs.min ?? 0 );
			return;
		}

		values[ nameAttr ] = field?.attributes?.defaultValue ?? '';
	} );

	return values;
}

/**
 * Validate a single preview field value.
 *
 * @param {Object} field Normalized field.
 * @param {*}      value Current value.
 * @returns {string} Error message or empty string.
 */
export function validatePreviewField( field, value ) {
	if ( ! field.required ) {
		if ( 'email' === field.type && value && ! EMAIL_PATTERN.test( String( value ).trim() ) ) {
			return __( 'Please enter a valid email address.', 'gutena-forms' );
		}
		return '';
	}

	switch ( field.type ) {
		case 'checkbox':
			return Array.isArray( value ) && value.length > 0
				? ''
				: __( 'This field is required.', 'gutena-forms' );

		case 'radio':
			return value
				? ''
				: __( 'This field is required.', 'gutena-forms' );

		case 'optin':
			return value
				? ''
				: __( 'This field is required.', 'gutena-forms' );

		case 'file':
			return value
				? ''
				: __( 'This field is required.', 'gutena-forms' );

		case 'email':
			if ( ! String( value || '' ).trim() ) {
				return __( 'This field is required.', 'gutena-forms' );
			}
			return EMAIL_PATTERN.test( String( value ).trim() )
				? ''
				: __( 'Please enter a valid email address.', 'gutena-forms' );

		case 'select':
			if ( ! String( value || '' ).trim() ) {
				return __( 'This field is required.', 'gutena-forms' );
			}
			return '';

		default:
			return String( value ?? '' ).trim()
				? ''
				: __( 'This field is required.', 'gutena-forms' );
	}
}

/**
 * Validate all preview fields.
 *
 * @param {Array}  normalizedFields Normalized field list.
 * @param {Object} values           Current values keyed by nameAttr.
 * @returns {Object} Field errors keyed by nameAttr.
 */
export function validatePreviewForm( normalizedFields, values ) {
	const errors = {};

	normalizedFields.forEach( ( field ) => {
		const message = validatePreviewField( field, values[ field.id ] );

		if ( message ) {
			errors[ field.id ] = message;
		}
	} );

	return errors;
}
