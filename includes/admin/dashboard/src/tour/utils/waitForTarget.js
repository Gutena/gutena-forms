/**
 * Wait for a tour target element to appear in the DOM.
 *
 * @since 1.9.0
 * @package Gutena Forms
 */

import {
	TOUR_TARGET_POLL_INTERVAL_MS,
	TOUR_TARGET_WAIT_TIMEOUT_MS,
} from '../constants';

/**
 * @param {() => (HTMLElement|null)} resolveTarget Resolves the target element.
 * @param {Object} [options]
 * @param {number} [options.timeout]
 * @param {number} [options.pollInterval]
 * @returns {Promise<HTMLElement>}
 */
export function waitForTarget(
	resolveTarget,
	{
		timeout = TOUR_TARGET_WAIT_TIMEOUT_MS,
		pollInterval = TOUR_TARGET_POLL_INTERVAL_MS,
	} = {}
) {
	return new Promise( ( resolve, reject ) => {
		let observer = null;
		let timeoutId = null;
		let pollId = null;
		let settled = false;

		const cleanup = () => {
			if ( observer ) {
				observer.disconnect();
				observer = null;
			}

			if ( timeoutId ) {
				clearTimeout( timeoutId );
				timeoutId = null;
			}

			if ( pollId ) {
				clearInterval( pollId );
				pollId = null;
			}
		};

		const finishResolve = ( element ) => {
			if ( settled ) {
				return;
			}

			settled = true;
			cleanup();
			resolve( element );
		};

		const finishReject = ( error ) => {
			if ( settled ) {
				return;
			}

			settled = true;
			cleanup();
			reject( error );
		};

		const check = () => {
			try {
				const element = resolveTarget();

				if ( element instanceof HTMLElement ) {
					finishResolve( element );
					return true;
				}
			} catch ( error ) {
				finishReject( error );
				return true;
			}

			return false;
		};

		if ( check() ) {
			return;
		}

		if ( typeof MutationObserver !== 'undefined' && document.body ) {
			observer = new MutationObserver( () => {
				check();
			} );

			observer.observe( document.body, {
				childList: true,
				subtree: true,
				attributes: true,
			} );
		}

		pollId = setInterval( () => {
			check();
		}, pollInterval );

		timeoutId = setTimeout( () => {
			finishReject( new Error( 'Tour target not found within timeout.' ) );
		}, timeout );
	} );
}
