/**
 * Waits for the current tour step target element.
 *
 * @since 1.9.0
 * @package Gutena Forms
 */

import { useEffect, useRef } from '@wordpress/element';
import { getTourStep } from '../config/steps';
import {
	TOUR_TARGET_VIEWS,
	TOUR_TARGET_WAIT_TIMEOUT_MS,
} from '../constants';
import { resolveStepTarget } from '../utils/resolveTarget';
import { waitForTarget } from '../utils/waitForTarget';
import { waitForView } from '../utils/waitForView';
import { TOUR_ACTIONS } from '../reducer';
import {
	prepareEditorForTourStep,
	setEditorTourActiveStep,
} from '../editor-tour-anchors';

/**
 * @param {Object} params
 * @param {boolean} params.isOpen
 * @param {number} params.currentStep
 * @param {Function} params.dispatch
 */
export function useTourTarget( { isOpen, currentStep, dispatch } ) {
	const waitTokenRef = useRef( 0 );

	useEffect( () => {
		if ( ! isOpen ) {
			waitTokenRef.current += 1;
			dispatch( { type: TOUR_ACTIONS.SET_CURRENT_TARGET, target: null } );
			return undefined;
		}

		const stepConfig = getTourStep( currentStep );

		if ( ! stepConfig || stepConfig.isIntro || stepConfig.isDone || ! stepConfig.useSpotlight ) {
			dispatch( { type: TOUR_ACTIONS.SET_CURRENT_TARGET, target: null } );
			return undefined;
		}

		const waitToken = ++waitTokenRef.current;

		dispatch( { type: TOUR_ACTIONS.SET_TARGET_WAITING, value: true } );
		dispatch( { type: TOUR_ACTIONS.SET_CURRENT_TARGET, target: null } );

		if ( currentStep === 9 || currentStep === 10 ) {
			setEditorTourActiveStep( currentStep );
			prepareEditorForTourStep( currentStep );
		}

		const targetTimeout =
			stepConfig.targetView === TOUR_TARGET_VIEWS.EDITOR
				? TOUR_TARGET_WAIT_TIMEOUT_MS * 3
				: TOUR_TARGET_WAIT_TIMEOUT_MS;

		waitForView( stepConfig.targetView )
			.then( () =>
				waitForTarget( () => resolveStepTarget( stepConfig ), {
					timeout: targetTimeout,
				} )
			)
			.then( ( target ) => {
				if ( waitToken !== waitTokenRef.current ) {
					return;
				}

				dispatch( { type: TOUR_ACTIONS.SET_CURRENT_TARGET, target } );
			} )
			.catch( () => {
				if ( waitToken !== waitTokenRef.current ) {
					return;
				}

				dispatch( { type: TOUR_ACTIONS.SET_TARGET_WAITING, value: false } );
				dispatch( { type: TOUR_ACTIONS.SET_CURRENT_TARGET, target: null } );
			} );

		return () => {
			waitTokenRef.current += 1;
		};
	}, [ isOpen, currentStep, dispatch ] );
}
