import { createBlock } from '@wordpress/blocks';
import { select, dispatch } from '@wordpress/data';
import { store as blockEditorStore } from '@wordpress/block-editor';
import { LAYOUT_COLUMN_BLOCK } from '../constants/form-allowed-blocks';
import { getColumnCountForPreset } from '../constants/layout-presets';

/**
 * Sync layout-field inner columns to match preset; move orphaned fields below layout.
 *
 * @param {string} layoutClientId Layout block client id.
 * @param {string} presetKey      Preset key.
 */
export function syncLayoutColumnsToPreset( layoutClientId, presetKey ) {
	const columnCount = getColumnCountForPreset( presetKey );
	const { getBlock, getBlockIndex, getBlockRootClientId } =
		select( blockEditorStore );
	const { replaceInnerBlocks, moveBlocksToPosition } =
		dispatch( blockEditorStore );

	const layoutBlock = getBlock( layoutClientId );
	if ( ! layoutBlock ) {
		return;
	}

	const existingCols = ( layoutBlock.innerBlocks || [] ).filter(
		( block ) => block.name === LAYOUT_COLUMN_BLOCK
	);

	const rootClientId = getBlockRootClientId( layoutClientId );
	const layoutIndex = getBlockIndex( layoutClientId );
	let insertPosition = layoutIndex + 1;

	for ( let i = columnCount; i < existingCols.length; i++ ) {
		const column = existingCols[ i ];
		if ( ! column.innerBlocks?.length ) {
			continue;
		}
		const clientIds = column.innerBlocks.map( ( child ) => child.clientId );
		moveBlocksToPosition( clientIds, rootClientId, insertPosition );
		insertPosition += clientIds.length;
	}

	const nextColumns = [];
	for ( let i = 0; i < columnCount; i++ ) {
		if ( i < existingCols.length ) {
			nextColumns.push( existingCols[ i ] );
		} else {
			nextColumns.push( createBlock( LAYOUT_COLUMN_BLOCK ) );
		}
	}

	replaceInnerBlocks( layoutClientId, nextColumns );
}
