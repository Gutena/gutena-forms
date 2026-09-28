/**
 * Eligibility checks for contextual tour resume triggers.
 *
 * @since 1.9.0
 * @package Gutena Forms
 */

import {
	TOUR_PENDING_CONTEXTUAL_STEP_KEY,
	TOUR_SUPPRESS_TRIGGER_A_KEY,
} from '../constants/contextualTriggers';

/**
 * @returns {boolean}
 */
export function isTriggerASuppressed() {
	try {
		return sessionStorage.getItem( TOUR_SUPPRESS_TRIGGER_A_KEY ) === '1';
	} catch ( error ) {
		return false;
	}
}

/**
 * @param {number} step
 */
export function setPendingContextualStep( step ) {
	try {
		sessionStorage.setItem(
			TOUR_PENDING_CONTEXTUAL_STEP_KEY,
			String( step )
		);
	} catch ( error ) {
		// Ignore storage errors.
	}
}

/**
 * @returns {number|null}
 */
export function getPendingContextualStep() {
	try {
		const value = sessionStorage.getItem(
			TOUR_PENDING_CONTEXTUAL_STEP_KEY
		);

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
 * Suppress Trigger A while Create Form navigation is in flight.
 */
export function suppressTriggerA() {
	try {
		sessionStorage.setItem( TOUR_SUPPRESS_TRIGGER_A_KEY, '1' );
	} catch ( error ) {
		// Ignore storage errors.
	}
}

/**
 * Clear cross-navigation contextual flags.
 */
export function clearContextualNavigationFlags() {
	try {
		sessionStorage.removeItem( TOUR_PENDING_CONTEXTUAL_STEP_KEY );
		sessionStorage.removeItem( TOUR_SUPPRESS_TRIGGER_A_KEY );
	} catch ( error ) {
		// Ignore storage errors.
	}
}

/**
 * @param {string} pathname
 * @returns {boolean}
 */
export function isFormsListRoute( pathname ) {
	return /^\/settings\/forms\/?$/.test( pathname );
}

/**
 * @param {string} pathname
 * @returns {boolean}
 */
export function isEntriesListRoute( pathname ) {
	return /^\/settings\/entries\/?$/.test( pathname );
}

/**
 * @param {string} href
 * @returns {boolean}
 */
export function isCreateFormHref( href ) {
	if ( ! href ) {
		return false;
	}

	return (
		href.includes( 'post-new.php' ) &&
		href.includes( 'post_type=gutena_forms' )
	);
}
