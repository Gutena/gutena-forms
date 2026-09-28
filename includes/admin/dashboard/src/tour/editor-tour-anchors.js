/**
 * Tag native block-editor elements for tour spotlight targets.
 *
 * @since 1.9.0
 * @package Gutena Forms
 */

import { getTourStepFromQuery } from './utils/navigation';
import { setTourTarget } from './utils/tourTarget';

/** @type {number|null} */
let activeEditorTourStep = null;

const EDITOR_BLOCKS_SELECTORS = [
	'.editor-document-tools__inserter-toggle',
	'.block-editor-inserter__toggle',
];

const EDITOR_SAVE_SELECTORS = [
	'.edit-post-header__settings',
	'.editor-header__settings',
];

/**
 * Open the block inspector sidebar (Gutena Form settings tab).
 */
function openBlockSidebar() {
	const data = window.wp?.data;

	if ( ! data?.dispatch ) {
		return;
	}

	try {
		data.dispatch( 'core/interface' )?.enableComplementaryArea?.(
			'core',
			'edit-post/block'
		);
	} catch ( error ) {
		// Ignore unsupported editor stores.
	}

	try {
		data.dispatch( 'core/edit-post' )?.openGeneralSidebar?.(
			'edit-post/block'
		);
	} catch ( error ) {
		// Ignore unsupported editor stores.
	}
}

/**
 * Select the root Gutena Forms block so inspector panels mount.
 */
function selectGutenaFormsBlock() {
	const data = window.wp?.data;

	if ( ! data?.select || ! data?.dispatch ) {
		return;
	}

	const blocks = data.select( 'core/block-editor' )?.getBlocks?.() || [];
	const gutenaBlock = blocks.find(
		( block ) => block?.name === 'gutena/forms'
	);

	if ( ! gutenaBlock?.clientId ) {
		return;
	}

	try {
		data.dispatch( 'core/block-editor' ).selectBlock( gutenaBlock.clientId );
	} catch ( error ) {
		// Ignore selection errors.
	}
}

function tagEditorAnchors() {
	EDITOR_BLOCKS_SELECTORS.forEach( ( selector ) => {
		const element = document.querySelector( selector );

		if ( element ) {
			setTourTarget( element, 'editor-blocks' );
		}
	} );

	EDITOR_SAVE_SELECTORS.forEach( ( selector ) => {
		const element = document.querySelector( selector );

		if ( element ) {
			setTourTarget( element, 'editor-save' );
		}
	} );
}

/**
 * Track the live tour step from React state (URL param may lag behind).
 *
 * @param {number|null} stepIndex
 */
export function setEditorTourActiveStep( stepIndex ) {
	activeEditorTourStep = Number.isInteger( stepIndex ) ? stepIndex : null;
}

function getActiveEditorTourStep() {
	if ( Number.isInteger( activeEditorTourStep ) ) {
		return activeEditorTourStep;
	}

	return getTourStepFromQuery();
}

/**
 * Select the Gutena Forms block and open the block sidebar for inspector steps.
 *
 * @param {number|null} [stepIndex]
 */
export function prepareEditorForTourStep( stepIndex = null ) {
	const tourStep = Number.isInteger( stepIndex )
		? stepIndex
		: getActiveEditorTourStep();

	if ( tourStep === 10 || tourStep === 11 ) {
		selectGutenaFormsBlock();
		openBlockSidebar();
	}

	if ( tourStep === 11 ) {
		openEmbedInPagePanel();
	}
}

/**
 * Expand the Embed in Page inspector panel for the tour spotlight target.
 */
function openEmbedInPagePanel() {
	const embedTarget = document.querySelector( '[data-tour="embed-in-page"]' );

	if ( embedTarget ) {
		const panel = embedTarget.closest( '.components-panel__body' );
		const toggle = panel?.querySelector( '.components-panel__body-toggle' );

		if ( toggle && ! panel?.classList.contains( 'is-opened' ) ) {
			toggle.click();
		}

		return;
	}

	document
		.querySelectorAll( '.components-panel__body-toggle' )
		.forEach( ( toggle ) => {
			if ( ! toggle.textContent?.includes( 'Embed in Page' ) ) {
				return;
			}

			const panel = toggle.closest( '.components-panel__body' );

			if ( panel && ! panel.classList.contains( 'is-opened' ) ) {
				toggle.click();
			}
		} );
}

/**
 * Initialise editor DOM anchors and step-specific editor prep.
 */
export function initEditorTourAnchors() {
	tagEditorAnchors();
	prepareEditorForTourStep();

	if ( typeof MutationObserver === 'undefined' || ! document.body ) {
		return;
	}

	const observer = new MutationObserver( () => {
		tagEditorAnchors();

		const activeTourStep = getActiveEditorTourStep();

		if ( activeTourStep === 10 || activeTourStep === 11 ) {
			prepareEditorForTourStep( activeTourStep );
		}
	} );

	observer.observe( document.body, {
		childList: true,
		subtree: true,
	} );
}
