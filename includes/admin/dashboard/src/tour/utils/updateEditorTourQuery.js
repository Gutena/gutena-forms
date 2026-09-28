/**
 * Update the tour step query param without reloading the block editor.
 *
 * @since 1.9.0
 * @package Gutena Forms
 */

import { TOUR_QUERY_PARAM } from '../constants';

/**
 * @param {number} stepIndex
 */
export function updateEditorTourQueryParam( stepIndex ) {
	if ( ! Number.isInteger( stepIndex ) ) {
		return;
	}

	const url = new URL( window.location.href );
	url.searchParams.set( TOUR_QUERY_PARAM, String( stepIndex ) );
	window.history.replaceState( null, '', url.toString() );
}
