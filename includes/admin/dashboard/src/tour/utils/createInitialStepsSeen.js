/**
 * Build the initial per-step seen map for steps 0–15.
 *
 * @since 1.9.0
 * @package Gutena Forms
 */

import { TOUR_STEP_COUNT } from '../constants';

/**
 * @param {Object<string, boolean>|undefined} fromPreferences Steps map from persistence.
 * @returns {Object<number, boolean>}
 */
export function createInitialStepsSeen( fromPreferences ) {
	const stepsSeen = {};

	for ( let step = 0; step < TOUR_STEP_COUNT; step++ ) {
		const key = String( step );
		stepsSeen[ step ] = Boolean( fromPreferences?.[ key ] );
	}

	return stepsSeen;
}
