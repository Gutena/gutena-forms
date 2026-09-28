/**
 * Centralized policy for whether the tour may auto-launch.
 *
 * @since 1.9.0
 * @package Gutena Forms
 */

import { TOUR_STATUS } from '../constants';
import { isValidTourStep } from '../config/steps';
import {
	isEntriesListRoute,
	isFormsListRoute,
	isTriggerASuppressed,
} from './contextualTriggerEligibility';

/**
 * @typedef {'intro_onboarding'|'query_resume'|'contextual_forms_list'|'contextual_create_form'|'contextual_entries'} TourLaunchContext
 */

/**
 * @param {Object} state Tour reducer state.
 * @returns {boolean}
 */
export function isTourAutoLaunchBlocked( state ) {
	return ! state.isHydrated || state.isOpen || state.doNotShowAgain;
}

/**
 * Decide whether an automatic tour launch is allowed for the given context.
 *
 * @param {Object} params
 * @param {TourLaunchContext} params.context
 * @param {Object} params.state Tour reducer state.
 * @param {Object} [params.options]
 * @param {number} [params.options.step] Step index for query/contextual launches.
 * @param {string} [params.options.pathname] Current dashboard pathname.
 * @returns {boolean}
 */
export function shouldAutoLaunchTour( { context, state, options = {} } ) {
	if ( isTourAutoLaunchBlocked( state ) ) {
		return false;
	}

	switch ( context ) {
		case 'intro_onboarding':
			return state.tourStatus === TOUR_STATUS.NOT_STARTED;

		case 'query_resume': {
			const step = options.step;

			return (
				state.tourStatus === TOUR_STATUS.ACTIVE &&
				Number.isInteger( step ) &&
				isValidTourStep( step )
			);
		}

		case 'contextual_forms_list':
			return (
				state.tourStatus === TOUR_STATUS.SKIPPED &&
				! state.stepsSeen[ 6 ] &&
				isFormsListRoute( options.pathname || '' ) &&
				! isTriggerASuppressed()
			);

		case 'contextual_create_form':
			return (
				state.tourStatus === TOUR_STATUS.SKIPPED &&
				! state.stepsSeen[ 7 ]
			);

		case 'contextual_entries':
			return (
				state.tourStatus === TOUR_STATUS.SKIPPED &&
				! state.stepsSeen[ 13 ] &&
				isEntriesListRoute( options.pathname || '' )
			);

		default:
			return false;
	}
}

/**
 * @param {Object} state
 * @param {number} step
 * @param {string} [pathname]
 * @returns {boolean}
 */
export function shouldAutoLaunchContextualStep( state, step, pathname = '' ) {
	switch ( step ) {
		case 6:
			return shouldAutoLaunchTour( {
				context: 'contextual_forms_list',
				state,
				options: { step, pathname },
			} );
		case 7:
			return shouldAutoLaunchTour( {
				context: 'contextual_create_form',
				state,
				options: { step },
			} );
		case 13:
			return shouldAutoLaunchTour( {
				context: 'contextual_entries',
				state,
				options: { step, pathname },
			} );
		default:
			return false;
	}
}
