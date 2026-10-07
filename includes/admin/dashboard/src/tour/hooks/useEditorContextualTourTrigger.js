/**
 * Editor contextual resume trigger (B — Create Form → Step 6).
 *
 * @since 1.9.0
 * @package Gutena Forms
 */

import { useEffect, useRef } from '@wordpress/element';
import { CONTEXTUAL_TRIGGER } from '../constants/contextualTriggers';
import {
	clearContextualNavigationFlags,
	getPendingContextualStep,
} from '../utils/contextualTriggerEligibility';
import { shouldAutoLaunchContextualStep } from '../utils/shouldAutoLaunchTour';
import {
	beginContextualLaunch,
	endContextualLaunch,
	resetContextualLaunchForTrigger,
} from '../utils/contextualLaunchCoordinator';
import { tourTargetSelector } from '../utils/tourTarget';
import { waitForTarget } from '../utils/waitForTarget';

/**
 * @param {Object} params
 * @param {'dashboard'|'editor'} params.runtime
 * @param {Object} params.state
 * @param {Function} params.resumeContextualTourAt
 */
export function useEditorContextualTourTrigger( {
	runtime,
	state,
	resumeContextualTourAt,
} ) {
	const launchTokenRef = useRef( 0 );

	useEffect( () => {
		if ( runtime !== 'editor' || ! state.isHydrated ) {
			return undefined;
		}

		if ( ! shouldAutoLaunchContextualStep( state, 6 ) ) {
			return undefined;
		}

		const pendingStep = getPendingContextualStep();
		const routeKey = `editor-layout-picker:${ pendingStep ?? 'direct' }`;
		const launchToken = ++launchTokenRef.current;
		let cancelled = false;

		const launchTriggerB = async () => {
			if ( cancelled || launchToken !== launchTokenRef.current ) {
				return;
			}

			if (
				! beginContextualLaunch(
					CONTEXTUAL_TRIGGER.CREATE_FORM_STEP_6,
					routeKey
				)
			) {
				return;
			}

			try {
				await waitForTarget( () =>
					document.querySelector( tourTargetSelector( 'layout-picker' ) )
				);

				if ( cancelled || launchToken !== launchTokenRef.current ) {
					return;
				}

				const launched = await resumeContextualTourAt( 6 );

				if ( launched ) {
					clearContextualNavigationFlags();
				} else {
					resetContextualLaunchForTrigger(
						CONTEXTUAL_TRIGGER.CREATE_FORM_STEP_6
					);
				}
			} catch ( error ) {
				resetContextualLaunchForTrigger(
					CONTEXTUAL_TRIGGER.CREATE_FORM_STEP_6
				);
			} finally {
				endContextualLaunch();
			}
		};

		launchTriggerB();

		return () => {
			cancelled = true;
		};
	}, [
		resumeContextualTourAt,
		runtime,
		state,
		state.isHydrated,
		state.isOpen,
		state.stepsSeen,
		state.tourStatus,
		state.doNotShowAgain,
	] );
}
