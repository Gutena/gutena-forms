/**
 * Resolve which tour step to resume after a cross-runtime navigation.
 *
 * @since 1.9.0
 * @package Gutena Forms
 */

import { TOUR_STATUS } from '../constants';
import { isValidTourStep } from '../config/steps';
import { getTourStepFromQuery } from './navigation';
import { getTourHandoffStep } from './tourHandoff';

/**
 * @param {Object} state Tour reducer state.
 * @returns {number|null}
 */
export function resolveResumeTourStep( state ) {
	const queryStep = getTourStepFromQuery();

	if ( Number.isInteger( queryStep ) && isValidTourStep( queryStep ) ) {
		return queryStep;
	}

	const handoffStep = getTourHandoffStep();

	if ( Number.isInteger( handoffStep ) && isValidTourStep( handoffStep ) ) {
		return handoffStep;
	}

	if (
		state.tourStatus === TOUR_STATUS.ACTIVE &&
		Number.isInteger( state.currentStep ) &&
		isValidTourStep( state.currentStep )
	) {
		return state.currentStep;
	}

	return null;
}
