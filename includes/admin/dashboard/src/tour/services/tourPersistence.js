/**
 * Maps tour engine state to/from persisted user-meta shape.
 *
 * @since 1.9.0
 * @package Gutena Forms
 */

import { TOUR_STEP_COUNT } from '../constants';

/**
 * @param {Object<number, boolean>} stepsSeen
 * @returns {Object<string, boolean>}
 */
export function stepsSeenToPersistence( stepsSeen ) {
	const steps = {};

	for ( let step = 0; step < TOUR_STEP_COUNT; step++ ) {
		steps[ String( step ) ] = Boolean( stepsSeen[ step ] );
	}

	return steps;
}

/**
 * @param {Object} state Tour reducer state.
 * @returns {Object}
 */
export function stateToPersistence( state ) {
	return {
		tour_status: state.tourStatus,
		current_step: state.currentStep,
		steps: stepsSeenToPersistence( state.stepsSeen ),
		do_not_show_again: state.doNotShowAgain,
	};
}

/**
 * @param {Object} preferences Persisted preferences.
 * @returns {Object}
 */
export function persistenceToStatePatch( preferences ) {
	if ( ! preferences || typeof preferences !== 'object' ) {
		return {};
	}

	const stepsSeen = {};

	for ( let step = 0; step < TOUR_STEP_COUNT; step++ ) {
		const key = String( step );
		stepsSeen[ step ] = Boolean( preferences?.steps?.[ key ] );
	}

	return {
		tourStatus: preferences.tour_status || 'not_started',
		currentStep: Number.isInteger( preferences.current_step )
			? preferences.current_step
			: 0,
		stepsSeen,
		doNotShowAgain: Boolean( preferences.do_not_show_again ),
	};
}
