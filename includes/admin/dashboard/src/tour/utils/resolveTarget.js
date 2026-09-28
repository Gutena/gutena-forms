/**
 * Resolve a tour step target in the DOM.
 *
 * @since 1.9.0
 * @package Gutena Forms
 */

/**
 * @param {import('../config/steps').TourStepConfig|undefined} stepConfig
 * @returns {HTMLElement|null}
 */
export function resolveStepTarget( stepConfig ) {
	if ( ! stepConfig ) {
		return null;
	}

	if ( typeof stepConfig.resolveTarget === 'function' ) {
		const element = stepConfig.resolveTarget();

		return element instanceof HTMLElement ? element : null;
	}

	if ( stepConfig.targetSelector ) {
		const element = document.querySelector( stepConfig.targetSelector );

		return element instanceof HTMLElement ? element : null;
	}

	return null;
}
