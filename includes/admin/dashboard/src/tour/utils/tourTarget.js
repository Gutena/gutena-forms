/**
 * Stable data-tour attribute helpers.
 *
 * @since 1.9.0
 * @package Gutena Forms
 */

export const TOUR_TARGET_ATTRIBUTE = 'data-tour';

/**
 * @param {string} targetId
 * @returns {string}
 */
export function tourTargetSelector( targetId ) {
	return `[${ TOUR_TARGET_ATTRIBUTE }="${ targetId }"]`;
}

/**
 * @param {HTMLElement} element
 * @param {string} targetId
 */
export function setTourTarget( element, targetId ) {
	if ( element instanceof HTMLElement && targetId ) {
		element.setAttribute( TOUR_TARGET_ATTRIBUTE, targetId );
	}
}
