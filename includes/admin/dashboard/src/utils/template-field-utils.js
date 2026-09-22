/**
 * Helpers for rendering template field metadata and previews.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

import { __ } from '@wordpress/i18n';

const FIELD_TYPE_LABELS = {
	text: __( 'Text', 'gutena-forms' ),
	email: __( 'Email', 'gutena-forms' ),
	textarea: __( 'Textarea', 'gutena-forms' ),
	number: __( 'Number', 'gutena-forms' ),
	select: __( 'Dropdown', 'gutena-forms' ),
	radio: __( 'Radio', 'gutena-forms' ),
	checkbox: __( 'Checkbox', 'gutena-forms' ),
	range: __( 'Range', 'gutena-forms' ),
	optin: __( 'Opt-in', 'gutena-forms' ),
	file: __( 'File upload', 'gutena-forms' ),
};

const BLOCK_TYPE_LABELS = {
	'gutena/text-field': __( 'Text', 'gutena-forms' ),
	'gutena/email-field': __( 'Email', 'gutena-forms' ),
	'gutena/textarea-field': __( 'Textarea', 'gutena-forms' ),
	'gutena/number-field': __( 'Number', 'gutena-forms' ),
	'gutena/dropdown-field': __( 'Dropdown', 'gutena-forms' ),
	'gutena/radio-field': __( 'Radio', 'gutena-forms' ),
	'gutena/checkbox-field': __( 'Checkbox', 'gutena-forms' ),
	'gutena/range-field': __( 'Range', 'gutena-forms' ),
	'gutena/optin-field': __( 'Opt-in', 'gutena-forms' ),
	'gutena/file-upload-field': __( 'File upload', 'gutena-forms' ),
};

const BLOCK_TYPE_MAP = {
	'gutena/text-field': 'text',
	'gutena/email-field': 'email',
	'gutena/textarea-field': 'textarea',
	'gutena/number-field': 'number',
	'gutena/dropdown-field': 'select',
	'gutena/radio-field': 'radio',
	'gutena/checkbox-field': 'checkbox',
	'gutena/range-field': 'range',
	'gutena/optin-field': 'optin',
	'gutena/file-upload-field': 'file',
};

/**
 * Human-readable field type label.
 *
 * @param {string} fieldType Block fieldType attribute.
 * @param {string} blockName Registered block name.
 * @returns {string}
 */
export function getFieldTypeLabel( fieldType, blockName = '' ) {
	if ( fieldType && FIELD_TYPE_LABELS[ fieldType ] ) {
		return FIELD_TYPE_LABELS[ fieldType ];
	}

	if ( blockName && BLOCK_TYPE_LABELS[ blockName ] ) {
		return BLOCK_TYPE_LABELS[ blockName ];
	}

	return fieldType || __( 'Field', 'gutena-forms' );
}

/**
 * Extract option labels from field attributes.
 *
 * @param {Object} attributes Field attributes.
 * @returns {string[]}
 */
export function getFieldOptions( attributes = {} ) {
	if ( Array.isArray( attributes.selectOptions ) ) {
		return attributes.selectOptions.filter( Boolean );
	}

	return [];
}

/**
 * Normalize a template field definition for UI rendering.
 *
 * @param {Object} field  Template field from API.
 * @param {number} index  Field index.
 * @returns {Object}
 */
export function resolveTemplateFieldType( field ) {
	const attributes = field?.attributes || {};
	const fieldType = attributes.fieldType || '';

	if ( fieldType ) {
		return fieldType;
	}

	if ( field?.block && BLOCK_TYPE_MAP[ field.block ] ) {
		return BLOCK_TYPE_MAP[ field.block ];
	}

	return 'text';
}

export function normalizeTemplateField( field, index ) {
	const attributes = field?.attributes || {};
	const type = resolveTemplateFieldType( field );

	return {
		id: attributes.nameAttr || `field-${ index }`,
		label: attributes.fieldName || '',
		type,
		typeLabel: getFieldTypeLabel( type, field?.block ),
		required: !! attributes.isRequired,
		options: getFieldOptions( attributes ),
		placeholder: attributes.placeholder || '',
		description: attributes.description || '',
		attributes,
		block: field?.block || '',
	};
}

/**
 * Normalize all fields from a template detail response.
 *
 * @param {Array} fields Template fields array.
 * @returns {Array}
 */
export function normalizeTemplateFields( fields = [] ) {
	if ( ! Array.isArray( fields ) ) {
		return [];
	}

	return fields.map( ( field, index ) => normalizeTemplateField( field, index ) );
}
