/**
 * Whether a tour step should render in the current runtime.
 *
 * @since 1.9.0
 * @package Gutena Forms
 */

import { TOUR_TARGET_VIEWS } from '../constants';

/**
 * @param {import('../config/steps').TourStepConfig|undefined} stepConfig
 * @param {'dashboard'|'editor'} runtime
 * @returns {boolean}
 */
export function stepMatchesRuntime( stepConfig, runtime ) {
	if ( ! stepConfig ) {
		return false;
	}

	if ( stepConfig.targetView === TOUR_TARGET_VIEWS.EDITOR ) {
		return runtime === 'editor';
	}

	return runtime === 'dashboard';
}
