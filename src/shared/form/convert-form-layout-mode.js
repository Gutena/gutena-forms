/**
 * Convert gutena/forms inner blocks between basic (stacked) and modern (layout rows).
 */
import { createBlock } from '@wordpress/blocks';
import { applyFilters } from '@wordpress/hooks';
import {
	FREE_STANDALONE_FIELD_BLOCKS,
	LAYOUT_COLUMN_BLOCK,
	LAYOUT_FIELD_BLOCK,
	PRO_STANDALONE_FIELD_BLOCKS,
} from '../constants/form-allowed-blocks';
import { getDisplayRatioForPreset } from '../constants/layout-presets';

export const MODERN_FULL_WIDTH_BLOCKS = [
	'gutena/textarea-field',
	'gutena/file-upload-field',
	'gutena/radio-field',
	'gutena/checkbox-field',
	'gutena/range-field',
	'gutena/optin-field',
	'gutena/rating-field',
];

const FOOTER_BLOCK_NAMES = [
	'gutena/form-confirm-msg',
	'gutena/form-error-msg',
];

function getAllFieldBlockNames() {
	const pro =
		typeof gutenaFormsBlock !== 'undefined' && gutenaFormsBlock?.is_pro
			? PRO_STANDALONE_FIELD_BLOCKS
			: [];
	const filtered = applyFilters(
		'gutena_forms__allowed_inner_blocks',
		pro
	);
	return [ ...FREE_STANDALONE_FIELD_BLOCKS, ...filtered ];
}

/**
 * @param {import('@wordpress/blocks').WPBlock} block
 * @return {boolean}
 */
export function isFooterBlock( block ) {
	if ( ! block?.name ) {
		return false;
	}
	if ( FOOTER_BLOCK_NAMES.includes( block.name ) ) {
		return true;
	}
	if ( block.name === 'core/buttons' ) {
		const className = block.attributes?.className || '';
		return className.includes( 'gutena-forms-submit-buttons' );
	}
	return false;
}

/**
 * @param {import('@wordpress/blocks').WPBlock} block
 * @return {boolean}
 */
export function isFieldBlock( block ) {
	return block?.name && getAllFieldBlockNames().includes( block.name );
}

/**
 * @param {import('@wordpress/blocks').WPBlock} block
 * @return {boolean}
 */
function isPairableFieldBlock( block ) {
	return (
		isFieldBlock( block ) &&
		! MODERN_FULL_WIDTH_BLOCKS.includes( block.name )
	);
}

/**
 * @param {import('@wordpress/blocks').WPBlock[]} blocks
 * @return {boolean}
 */
export function detectLegacyCoreColumns( blocks ) {
	return blocks.some( ( block ) => block.name === 'core/columns' );
}

/**
 * @param {import('@wordpress/blocks').WPBlock[]} blocks
 * @return {boolean}
 */
export function hasLayoutFieldBlocks( blocks ) {
	return blocks.some( ( block ) => block.name === LAYOUT_FIELD_BLOCK );
}

/**
 * @param {import('@wordpress/blocks').WPBlock[]} fieldBlocks
 * @return {import('@wordpress/blocks').WPBlock}
 */
function createLayoutRowForFields( fieldBlocks ) {
	const preset = '50-50';
	const displayRatio = getDisplayRatioForPreset( preset );
	const columns = fieldBlocks.map( ( field ) =>
		createBlock( LAYOUT_COLUMN_BLOCK, {}, [ field ] )
	);
	return createBlock(
		LAYOUT_FIELD_BLOCK,
		{
			layoutLabel: 'Layout',
			preset,
			displayRatio,
		},
		columns
	);
}

/**
 * @param {import('@wordpress/blocks').WPBlock[]} blocks Editor blocks (top-level under form).
 * @return {import('@wordpress/blocks').WPBlock[]}
 */
export function convertToModern( blocks ) {
	if ( hasLayoutFieldBlocks( blocks ) ) {
		return blocks;
	}

	const result = [];
	const fieldQueue = [];
	const footer = [];
	const other = [];

	blocks.forEach( ( block ) => {
		if ( isFooterBlock( block ) ) {
			footer.push( block );
		} else if ( isFieldBlock( block ) ) {
			fieldQueue.push( block );
		} else {
			other.push( block );
		}
	} );

	let i = 0;
	while ( i < fieldQueue.length ) {
		const current = fieldQueue[ i ];
		if ( ! isPairableFieldBlock( current ) ) {
			result.push( current );
			i++;
			continue;
		}
		const next = fieldQueue[ i + 1 ];
		if ( next && isPairableFieldBlock( next ) ) {
			result.push( createLayoutRowForFields( [ current, next ] ) );
			i += 2;
		} else {
			result.push( current );
			i++;
		}
	}

	return [ ...result, ...other, ...footer ];
}

/**
 * @param {import('@wordpress/blocks').WPBlock} layoutBlock
 * @return {import('@wordpress/blocks').WPBlock[]}
 */
function flattenLayoutField( layoutBlock ) {
	const fields = [];
	( layoutBlock.innerBlocks || [] ).forEach( ( column ) => {
		if ( column.name !== LAYOUT_COLUMN_BLOCK ) {
			return;
		}
		( column.innerBlocks || [] ).forEach( ( child ) => {
			fields.push( child );
		} );
	} );
	return fields;
}

/**
 * Flatten layout-field rows into stacked fields at the form root.
 * Use only when explicitly restructuring (not when toggling basic input styles).
 *
 * @param {import('@wordpress/blocks').WPBlock[]} blocks
 * @return {import('@wordpress/blocks').WPBlock[]}
 */
export function flattenLayoutFieldsToStack( blocks ) {
	const result = [];
	blocks.forEach( ( block ) => {
		if ( block.name === LAYOUT_FIELD_BLOCK ) {
			result.push( ...flattenLayoutField( block ) );
		} else {
			result.push( block );
		}
	} );
	return result;
}

/**
 * Basic mode: keep existing layout-field blocks; only auto-flatten when there are none.
 *
 * @param {import('@wordpress/blocks').WPBlock[]} blocks
 * @return {import('@wordpress/blocks').WPBlock[]}
 */
export function convertToBasic( blocks ) {
	if ( hasLayoutFieldBlocks( blocks ) ) {
		return blocks;
	}
	return flattenLayoutFieldsToStack( blocks );
}

/**
 * @param {'basic'|'modern'} targetMode
 * @param {import('@wordpress/blocks').WPBlock[]} blocks
 * @return {import('@wordpress/blocks').WPBlock[]}
 */
export function convertBlocksForLayoutMode( targetMode, blocks ) {
	if ( targetMode === 'modern' ) {
		return convertToModern( blocks );
	}
	return convertToBasic( blocks );
}
