import { __ } from '@wordpress/i18n';
import { gfIsEmpty, getInnerBlocksbyNameAttr } from '../../../shared/utils/helper';

const STANDALONE_FIELD_BLOCKS = [
	'gutena/email-field',
	'gutena/text-field',
	'gutena/textarea-field',
	'gutena/number-field',
	'gutena/url-field',
	'gutena/phone-field',
	'gutena/checkbox-field',
	'gutena/country-field',
	'gutena/state-field',
];

const collectStandaloneFields = ( blocks, fieldType = '' ) => {
	const fields = [];

	STANDALONE_FIELD_BLOCKS.forEach( ( blockName ) => {
		const matches = getInnerBlocksbyNameAttr( blocks, blockName );
		matches.forEach( ( block ) => {
			const attrs = block.attributes || {};
			if ( gfIsEmpty( attrs.nameAttr ) ) {
				return;
			}

			const resolvedType = attrs.fieldType || blockName.replace( 'gutena/', '' ).replace( '-field', '' );
			if ( fieldType && fieldType !== resolvedType ) {
				return;
			}

			fields.push( {
				nameAttr: attrs.nameAttr,
				fieldName: attrs.fieldName || attrs.nameAttr,
				fieldType: resolvedType,
			} );
		} );
	} );

	return fields;
};

export const collectFormFields = ( blocks = [] ) => {
	if ( gfIsEmpty( blocks ) ) {
		return [];
	}

	const fields = [];
	const formFields = getInnerBlocksbyNameAttr( blocks, 'gutena/form-field' );

	formFields.forEach( ( block ) => {
		const attrs = block.attributes || {};
		if ( gfIsEmpty( attrs.nameAttr ) ) {
			return;
		}

		fields.push( {
			nameAttr: attrs.nameAttr,
			fieldName: attrs.fieldName || attrs.nameAttr,
			fieldType: attrs.fieldType || 'text',
		} );
	} );

	collectStandaloneFields( blocks ).forEach( ( field ) => {
		if ( ! fields.some( ( item ) => item.nameAttr === field.nameAttr ) ) {
			fields.push( field );
		}
	} );

	return fields;
};

export const buildEmailFieldOptions = ( blocks = [] ) => {
	const options = [ { label: __( 'Select', 'gutena-forms' ), value: '' } ];

	collectFormFields( blocks )
		.filter( ( field ) => 'email' === field.fieldType )
		.forEach( ( field ) => {
			options.push( {
				label: field.fieldName,
				value: field.nameAttr,
			} );
		} );

	return options;
};

export const buildTextFieldOptions = ( blocks = [] ) => {
	const options = [ { label: __( 'Select', 'gutena-forms' ), value: '' } ];

	collectFormFields( blocks )
		.filter( ( field ) => 'text' === field.fieldType || gfIsEmpty( field.fieldType ) )
		.forEach( ( field ) => {
			options.push( {
				label: field.fieldName,
				value: field.nameAttr,
			} );
		} );

	return options;
};
