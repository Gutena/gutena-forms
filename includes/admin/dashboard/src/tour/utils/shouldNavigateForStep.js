/**
 * Decide whether entering a step should trigger navigation.
 *
 * @since 1.9.0
 * @package Gutena Forms
 */

import { TOUR_TARGET_VIEWS } from '../constants';
import { requiresRuntimeHandoff } from './navigation';

/**
 * @param {import('../config/steps').TourStepConfig|undefined} stepConfig
 * @param {'dashboard'|'editor'} runtime
 * @returns {boolean}
 */
export function shouldNavigateForStep( stepConfig, runtime ) {
	if ( ! stepConfig ) {
		return false;
	}

	if ( requiresRuntimeHandoff( runtime, stepConfig.targetView ) ) {
		return true;
	}

	if ( runtime === 'editor' && stepConfig.editorRemountOnEnter ) {
		return true;
	}

	return Boolean( stepConfig.navigateOnEnter );
}
