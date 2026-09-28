/**
 * Cross-runtime tour handoff helpers (dashboard ↔ editor).
 *
 * @since 1.9.0
 * @package Gutena Forms
 */

export const TOUR_HANDOFF_STEP_KEY = 'gf_tour_handoff_step';

/**
 * @param {number} step
 */
export function setTourHandoffStep( step ) {
	try {
		sessionStorage.setItem( TOUR_HANDOFF_STEP_KEY, String( step ) );
	} catch ( error ) {
		// Ignore storage errors.
	}
}

/**
 * @returns {number|null}
 */
export function getTourHandoffStep() {
	try {
		const value = sessionStorage.getItem( TOUR_HANDOFF_STEP_KEY );

		if ( value === null ) {
			return null;
		}

		const parsed = parseInt( value, 10 );

		return Number.isNaN( parsed ) ? null : parsed;
	} catch ( error ) {
		return null;
	}
}

/**
 * Clear the pending runtime handoff marker.
 */
export function clearTourHandoffStep() {
	try {
		sessionStorage.removeItem( TOUR_HANDOFF_STEP_KEY );
	} catch ( error ) {
		// Ignore storage errors.
	}
}
