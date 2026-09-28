/**
 * Wait for a dashboard SPA view marker before resolving tour targets.
 *
 * @since 1.9.0
 * @package Gutena Forms
 */

import { TOUR_TARGET_VIEWS } from '../constants';
import { tourTargetSelector } from './tourTarget';
import { waitForTarget } from './waitForTarget';

/**
 * @param {string} targetView
 * @returns {Promise<void>}
 */
export function waitForView( targetView ) {
	if (
		! targetView ||
		targetView === TOUR_TARGET_VIEWS.EDITOR ||
		targetView === TOUR_TARGET_VIEWS.DASHBOARD
	) {
		return Promise.resolve();
	}

	const viewSelector = tourTargetSelector( `view-${ targetView }` );

	return waitForTarget( () => document.querySelector( viewSelector ) ).then(
		() => undefined
	);
}
