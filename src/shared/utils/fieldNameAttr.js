/**
 * Field ID (nameAttr) helpers for standalone Gutena form field blocks.
 *
 * @package Gutena Forms
 */

import { useEffect } from '@wordpress/element';
import { select } from '@wordpress/data';
import { store as blockEditorStore } from '@wordpress/block-editor';
import { gfIsEmpty } from './helper';

export const GF_FIELD_ID_PATTERN = /^f_\d+$/;

export const STANDALONE_FIELD_BLOCK_NAMES = [
	'gutena/checkbox-field',
	'gutena/country-field',
	'gutena/date-field',
	'gutena/dropdown-field',
	'gutena/email-field',
	'gutena/file-upload-field',
	'gutena/hidden-field',
	'gutena/number-field',
	'gutena/optin-field',
	'gutena/password-field',
	'gutena/phone-field',
	'gutena/radio-field',
	'gutena/range-field',
	'gutena/rating-field',
	'gutena/state-field',
	'gutena/text-field',
	'gutena/textarea-field',
	'gutena/time-field',
	'gutena/url-field',
];

const MAX_FIELD_ID_INDEX = 5000;

/**
 * @param {string} clientId
 * @return {string|null}
 */
export function getParentFormClientId( clientId ) {
	const parents = select( blockEditorStore ).getBlockParentsByBlockName(
		clientId,
		'gutena/forms',
		true
	);

	return parents?.[ 0 ] ?? null;
}

/**
 * @param {Array} blocks
 * @param {string[]} collected
 * @return {string[]}
 */
function collectFieldBlockClientIds( blocks, collected = [] ) {
	if ( gfIsEmpty( blocks ) || ! Array.isArray( blocks ) ) {
		return collected;
	}

	blocks.forEach( ( block ) => {
		if ( STANDALONE_FIELD_BLOCK_NAMES.includes( block.name ) ) {
			collected.push( block.clientId );
		}

		if ( block.innerBlocks?.length ) {
			collectFieldBlockClientIds( block.innerBlocks, collected );
		}
	} );

	return collected;
}

/**
 * @param {string} formClientId
 * @return {string[]}
 */
export function getFormFieldBlockClientIds( formClientId ) {
	if ( gfIsEmpty( formClientId ) ) {
		return [];
	}

	const formBlock = select( blockEditorStore ).getBlock( formClientId );

	if ( ! formBlock?.innerBlocks?.length ) {
		return [];
	}

	return collectFieldBlockClientIds( formBlock.innerBlocks );
}

/**
 * @param {string} nameAttr
 * @param {string} clientId
 * @return {boolean}
 */
export function isFieldNameAttrReservedInForm( nameAttr, clientId ) {
	if ( gfIsEmpty( nameAttr ) ) {
		return false;
	}

	const formClientId = getParentFormClientId( clientId );
	const fieldClientIds = formClientId
		? getFormFieldBlockClientIds( formClientId )
		: select( blockEditorStore ).getClientIdsWithDescendants();

	if ( gfIsEmpty( fieldClientIds ) ) {
		return false;
	}

	return fieldClientIds.some( ( fieldClientId ) => {
		if ( fieldClientId === clientId ) {
			return false;
		}

		const attrs = select( blockEditorStore ).getBlockAttributes( fieldClientId );

		return (
			! gfIsEmpty( attrs?.nameAttr ) && attrs.nameAttr === nameAttr
		);
	} );
}

/**
 * @param {string} clientId
 * @return {string}
 */
export function getNextFormFieldNameAttr( clientId ) {
	for ( let index = 0; index < MAX_FIELD_ID_INDEX; index++ ) {
		const nextName = `f_${ index }`;

		if ( ! isFieldNameAttrReservedInForm( nextName, clientId ) ) {
			return nextName;
		}
	}

	return '';
}

/**
 * Assign a unique nameAttr when empty or duplicated; grandfather unique custom IDs.
 *
 * @param {string}   clientId
 * @param {string}   nameAttr
 * @param {Function} setAttributes
 */
export function ensureFieldNameAttr( clientId, nameAttr, setAttributes ) {
	if (
		! gfIsEmpty( nameAttr ) &&
		! isFieldNameAttrReservedInForm( nameAttr, clientId )
	) {
		return;
	}

	const nextName = getNextFormFieldNameAttr( clientId );

	if ( ! gfIsEmpty( nextName ) && nextName !== nameAttr ) {
		setAttributes( { nameAttr: nextName } );
	}
}

/**
 * Keep nameAttr unique within the parent form (mount + duplicate/paste).
 *
 * @param {string}   clientId
 * @param {string}   nameAttr
 * @param {Function} setAttributes
 */
export function useEnsureFieldNameAttr( clientId, nameAttr, setAttributes ) {
	useEffect( () => {
		ensureFieldNameAttr( clientId, nameAttr, setAttributes );
	}, [ clientId, nameAttr, setAttributes ] );
}
