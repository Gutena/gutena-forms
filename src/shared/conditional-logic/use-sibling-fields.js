/**
 * Editor helpers for resolving sibling form fields from the block-editor store.
 *
 * A "sibling field" is any eligible form field that lives inside the same
 * `gutena/forms` ancestor as the field currently being edited. The current
 * field is excluded from the list (user story section D).
 */

import { select } from '@wordpress/data';
import { ELIGIBLE_FIELD_TYPES } from './operators';

/**
 * Walk up the block tree from a clientId to find the nearest `gutena/forms`
 * ancestor block.
 *
 * @param {string} clientId A block clientId.
 * @return {string|null} The form block clientId, or null when not nested in one.
 */
function findFormAncestor( clientId ) {
	const { getBlockParents, getBlockName } = select( 'core/block-editor' );
	let parents = getBlockParents( clientId, false );
	if ( ! parents || ! parents.length ) {
		return null;
	}
	// getBlockParents returns root-first; iterate from the nearest ancestor down.
	for ( let i = parents.length - 1; i >= 0; i-- ) {
		if ( 'gutena/forms' === getBlockName( parents[ i ] ) ) {
			return parents[ i ];
		}
	}
	return null;
}

/**
 * Recursively collect eligible field blocks nested under the given block.
 *
 * @param {string} rootClientId Root block clientId to walk.
 * @return {Array} Array of { clientId, nameAttr, fieldName, fieldType, options }.
 */
function collectFields( rootClientId ) {
	const { getBlock, getBlockAttributes } = select( 'core/block-editor' );
	const root = getBlock( rootClientId );
	if ( ! root ) {
		return [];
	}

	const results = [];

	const isFieldGroupParent = ( name ) =>
		! name ||
		name === 'gutena/field-group' ||
		/-field-group$/.test( name );

	const walk = ( block ) => {
		if ( ! block ) {
			return;
		}
		const attrs = getBlockAttributes( block.clientId ) || {};
		const fieldType = attrs.fieldType || '';
		const nameAttr = attrs.nameAttr || '';

		// Skip legacy field-group parent blocks: their nameAttr is a placeholder
		// and the real field identity lives on their inner `gutena/form-field`
		// child, which is collected via recursion.
		const isEligibleField =
			nameAttr &&
			fieldType &&
			'hidden' !== fieldType &&
			ELIGIBLE_FIELD_TYPES.indexOf( fieldType ) !== -1 &&
			! isFieldGroupParent( block.name );

		if ( isEligibleField ) {
			results.push( {
				clientId: block.clientId,
				nameAttr,
				fieldName: attrs.fieldName || nameAttr,
				fieldType,
				options: Array.isArray( attrs.selectOptions ) ? attrs.selectOptions : [],
			} );
		}

		if ( Array.isArray( block.innerBlocks ) && block.innerBlocks.length ) {
			block.innerBlocks.forEach( walk );
		}
	};

	walk( root );
	return results;
}

/**
 * Resolve the eligible sibling fields for a given field clientId.
 *
 * @param {string} currentClientId The clientId of the field being edited.
 * @return {Array} Sibling fields (excluding the current field).
 */
export function getSiblingFields( currentClientId ) {
	if ( ! currentClientId ) {
		return [];
	}
	const formClientId = findFormAncestor( currentClientId );
	if ( ! formClientId ) {
		return [];
	}
	const fields = collectFields( formClientId );
	return fields.filter( ( f ) => f.clientId !== currentClientId );
}

/**
 * Find a sibling field by nameAttr.
 *
 * @param {Array}  siblings  Result of getSiblingFields.
 * @param {string} nameAttr  Field name attribute to find.
 * @return {Object|null}
 */
export function findSibling( siblings, nameAttr ) {
	if ( ! Array.isArray( siblings ) || ! nameAttr ) {
		return null;
	}
	return siblings.find( ( s ) => s.nameAttr === nameAttr ) || null;
}
