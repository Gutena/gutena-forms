/**
 * Dashboard contextual resume triggers (A and C).
 *
 * @since 1.9.0
 * @package Gutena Forms
 */

import { useEffect, useRef } from '@wordpress/element';
import { useLocation } from 'react-router';
import { CONTEXTUAL_TRIGGER } from '../constants/contextualTriggers';
import { TOUR_TARGET_VIEWS } from '../constants';
import {
	isEntriesListRoute,
	isFormsListRoute,
	isTriggerASuppressed,
} from '../utils/contextualTriggerEligibility';
import { shouldAutoLaunchContextualStep } from '../utils/shouldAutoLaunchTour';
import {
	beginContextualLaunch,
	endContextualLaunch,
	resetContextualLaunchForTrigger,
} from '../utils/contextualLaunchCoordinator';
import { waitForView } from '../utils/waitForView';
import { tourTargetSelector } from '../utils/tourTarget';
import { waitForTarget } from '../utils/waitForTarget';
import { useCreateFormClickInterceptor } from './useCreateFormClickInterceptor';

/**
 * @param {Object} params
 * @param {'dashboard'|'editor'} params.runtime
 * @param {Object} params.state
 * @param {Function} params.resumeContextualTourAt
 * @param {Function} params.markStepSeen
 * @param {Function} params.getTourState
 */
export function useContextualTourTriggers( {
	runtime,
	state,
	resumeContextualTourAt,
	markStepSeen,
	getTourState,
} ) {
	const location = useLocation();
	const launchTokenRef = useRef( 0 );

	useCreateFormClickInterceptor( {
		runtime,
		state,
		markStepSeen,
	} );

	useEffect( () => {
		if ( runtime !== 'dashboard' || ! state.isHydrated ) {
			return undefined;
		}

		if ( ! isFormsListRoute( location.pathname ) ) {
			resetContextualLaunchForTrigger(
				CONTEXTUAL_TRIGGER.FORMS_LIST_STEP_5
			);
			return undefined;
		}

		if (
			! shouldAutoLaunchContextualStep(
				state,
				5,
				location.pathname
			)
		) {
			return undefined;
		}

		const launchToken = ++launchTokenRef.current;
		let cancelled = false;
		const routeKey = location.pathname;

		const launchTriggerA = async () => {
			if ( cancelled || launchToken !== launchTokenRef.current ) {
				return;
			}

			if (
				! beginContextualLaunch(
					CONTEXTUAL_TRIGGER.FORMS_LIST_STEP_5,
					routeKey
				)
			) {
				return;
			}

			try {
				await waitForView( TOUR_TARGET_VIEWS.FORMS );
				await waitForTarget( () =>
					document.querySelector( tourTargetSelector( 'add-new-form' ) )
				);

				if ( cancelled || launchToken !== launchTokenRef.current ) {
					return;
				}

				const latestState = getTourState();

				if (
					isTriggerASuppressed() ||
					! shouldAutoLaunchContextualStep(
						latestState,
						5,
						location.pathname
					)
				) {
					resetContextualLaunchForTrigger(
						CONTEXTUAL_TRIGGER.FORMS_LIST_STEP_5
					);
					return;
				}

				await resumeContextualTourAt( 5, location.pathname );
			} catch ( error ) {
				// Target not ready; allow a future route visit to retry.
				resetContextualLaunchForTrigger(
					CONTEXTUAL_TRIGGER.FORMS_LIST_STEP_5
				);
			} finally {
				endContextualLaunch();
			}
		};

		launchTriggerA();

		return () => {
			cancelled = true;
		};
	}, [
		location.pathname,
		getTourState,
		resumeContextualTourAt,
		runtime,
		state,
		state.isHydrated,
		state.isOpen,
		state.stepsSeen,
		state.tourStatus,
		state.doNotShowAgain,
	] );

	useEffect( () => {
		if ( runtime !== 'dashboard' || ! state.isHydrated ) {
			return undefined;
		}

		if ( ! isEntriesListRoute( location.pathname ) ) {
			resetContextualLaunchForTrigger(
				CONTEXTUAL_TRIGGER.ENTRIES_STEP_12
			);
			return undefined;
		}

		if (
			! shouldAutoLaunchContextualStep(
				state,
				12,
				location.pathname
			)
		) {
			return undefined;
		}

		const launchToken = ++launchTokenRef.current;
		let cancelled = false;
		const routeKey = location.pathname;

		const launchTriggerC = async () => {
			if ( cancelled || launchToken !== launchTokenRef.current ) {
				return;
			}

			if (
				! beginContextualLaunch(
					CONTEXTUAL_TRIGGER.ENTRIES_STEP_12,
					routeKey
				)
			) {
				return;
			}

			try {
				await waitForView( TOUR_TARGET_VIEWS.ENTRIES );
				await waitForTarget( () =>
					document.querySelector( tourTargetSelector( 'entries-table' ) )
				);

				const latestState = getTourState();

				if (
					cancelled ||
					launchToken !== launchTokenRef.current ||
					! shouldAutoLaunchContextualStep(
						latestState,
						12,
						location.pathname
					)
				) {
					resetContextualLaunchForTrigger(
						CONTEXTUAL_TRIGGER.ENTRIES_STEP_12
					);
					return;
				}

				await resumeContextualTourAt( 12, location.pathname );
			} catch ( error ) {
				resetContextualLaunchForTrigger(
					CONTEXTUAL_TRIGGER.ENTRIES_STEP_12
				);
			} finally {
				endContextualLaunch();
			}
		};

		launchTriggerC();

		return () => {
			cancelled = true;
		};
	}, [
		location.pathname,
		getTourState,
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
