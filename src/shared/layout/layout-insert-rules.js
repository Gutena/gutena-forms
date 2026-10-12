import { addFilter } from '@wordpress/hooks';
import { select } from '@wordpress/data';
import { store as blockEditorStore } from '@wordpress/block-editor';
import {
	LAYOUT_COLUMN_BLOCK,
	LAYOUT_FIELD_BLOCK,
	LAYOUT_COLUMN_BLOCKED_BLOCKS,
	getLayoutColumnAllowedBlocks,
} from '../constants/form-allowed-blocks';

const NESTED_LAYOUT_ALERT =
	"The Layout field can't be placed inside a Layout field.";

let nestedLayoutAlertShown = false;

function resetNestedLayoutAlertFlag() {
	setTimeout( () => {
		nestedLayoutAlertShown = false;
	}, 500 );
}

function showNestedLayoutAlert() {
	if ( nestedLayoutAlertShown ) {
		return;
	}
	nestedLayoutAlertShown = true;
	window.alert( NESTED_LAYOUT_ALERT );
	resetNestedLayoutAlertFlag();
}

function getBlockName( clientId ) {
	if ( ! clientId ) {
		return '';
	}
	const block = select( blockEditorStore ).getBlock( clientId );
	return block?.name || '';
}

function hasAncestorBlock( clientId, blockName ) {
	let current = clientId;
	const visited = new Set();

	while ( current && ! visited.has( current ) ) {
		visited.add( current );
		const block = select( blockEditorStore ).getBlock( current );
		if ( ! block ) {
			break;
		}
		if ( block.name === blockName ) {
			return true;
		}
		const parents = select( blockEditorStore ).getBlockParents( current );
		current = parents.length ? parents[ parents.length - 1 ] : null;
	}

	return false;
}

export function registerLayoutInsertRules() {
	addFilter(
		'blockEditor.__unstableCanInsertBlockType',
		'gutena-forms/layout-insert-rules',
		( canInsert, blockType, rootClientId ) => {
			const blockName = blockType?.name;
			if ( ! blockName ) {
				return canInsert;
			}

			const parentName = getBlockName( rootClientId );

			if (
				parentName === LAYOUT_COLUMN_BLOCK &&
				getLayoutColumnAllowedBlocks().includes( blockName ) &&
				! LAYOUT_COLUMN_BLOCKED_BLOCKS.includes( blockName )
			) {
				return true;
			}

			if ( canInsert === false ) {
				return canInsert;
			}

			if ( blockName === LAYOUT_FIELD_BLOCK ) {
				if (
					parentName === LAYOUT_FIELD_BLOCK ||
					parentName === LAYOUT_COLUMN_BLOCK ||
					hasAncestorBlock( rootClientId, LAYOUT_FIELD_BLOCK )
				) {
					showNestedLayoutAlert();
					return false;
				}
			}

			if ( parentName === LAYOUT_COLUMN_BLOCK ) {
				if ( LAYOUT_COLUMN_BLOCKED_BLOCKS.includes( blockName ) ) {
					if ( blockName === LAYOUT_FIELD_BLOCK ) {
						showNestedLayoutAlert();
					}
					return false;
				}
			}

			return canInsert;
		}
	);
}
