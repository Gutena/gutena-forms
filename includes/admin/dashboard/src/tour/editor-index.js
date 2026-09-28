/**
 * Block editor product tour bootstrap.
 *
 * @since 1.9.0
 * @package Gutena Forms
 */

import domReady from '@wordpress/dom-ready';
import { createRoot, StrictMode, useEffect } from '@wordpress/element';
import TourProvider from './TourProvider';
import { useTour } from './context/TourContext';
import {
	initEditorTourAnchors,
	prepareEditorForTourStep,
	setEditorTourActiveStep,
} from './editor-tour-anchors';
import './tour.scss';

/**
 * Sync live tour step to editor DOM prep (block select + sidebar open).
 */
const EditorTourRuntimeBridge = () => {
	const { isOpen, currentStep } = useTour();

	useEffect( () => {
		setEditorTourActiveStep( isOpen ? currentStep : null );

		if ( isOpen && ( currentStep === 10 || currentStep === 11 ) ) {
			prepareEditorForTourStep( currentStep );
		}
	}, [ isOpen, currentStep ] );

	return null;
};

domReady( () => {
	const tourConfig = window.gutenaFormsTour || {};

	initEditorTourAnchors();

	const containerId = 'gutena-forms-tour-editor-root';
	let container = document.getElementById( containerId );

	if ( ! container ) {
		container = document.createElement( 'div' );
		container.id = containerId;
		document.body.appendChild( container );
	}

	createRoot( container ).render(
		<StrictMode>
			<TourProvider
				runtime="editor"
				initialPreferences={ tourConfig.preferences }
				adminURL={ tourConfig.adminURL || '' }
				dashboardURL={ tourConfig.dashboardURL || '' }
			>
				<EditorTourRuntimeBridge />
			</TourProvider>
		</StrictMode>
	);
} );
