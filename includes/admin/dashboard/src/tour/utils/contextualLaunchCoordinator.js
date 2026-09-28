/**
 * Prevents duplicate contextual tour launches across re-renders and routes.
 *
 * @since 1.9.0
 * @package Gutena Forms
 */

const routeAttemptKeys = new Set();
let launchInFlight = false;

/**
 * @param {string} triggerId
 * @param {string} routeKey
 * @returns {boolean}
 */
export function canAttemptContextualLaunch( triggerId, routeKey ) {
	if ( launchInFlight ) {
		return false;
	}

	const attemptKey = `${ triggerId }:${ routeKey }`;

	return ! routeAttemptKeys.has( attemptKey );
}

/**
 * @param {string} triggerId
 * @param {string} routeKey
 * @returns {boolean}
 */
export function beginContextualLaunch( triggerId, routeKey ) {
	if ( launchInFlight ) {
		return false;
	}

	const attemptKey = `${ triggerId }:${ routeKey }`;

	if ( routeAttemptKeys.has( attemptKey ) ) {
		return false;
	}

	launchInFlight = true;
	routeAttemptKeys.add( attemptKey );

	return true;
}

/**
 * Release the in-flight launch lock.
 */
export function endContextualLaunch() {
	launchInFlight = false;
}

/**
 * Allow a trigger to fire again after leaving a route.
 *
 * @param {string} triggerId
 */
export function resetContextualLaunchForTrigger( triggerId ) {
	for ( const key of routeAttemptKeys ) {
		if ( key.startsWith( `${ triggerId }:` ) ) {
			routeAttemptKeys.delete( key );
		}
	}
}
