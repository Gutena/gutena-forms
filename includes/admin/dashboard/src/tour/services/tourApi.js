/**
 * REST client for tour preferences.
 *
 * @since 1.9.0
 * @package Gutena Forms
 */

import apiFetch from '@wordpress/api-fetch';
import { GutenaFormsRestConfiguration } from '../../api';

/**
 * @returns {Promise<Object>}
 */
export async function gutenaFormsFetchTourPreferences() {
	const response = await apiFetch( {
		path: `${ GutenaFormsRestConfiguration.namespace }tour/preferences`,
	} );

	if ( response?.preferences ) {
		return response.preferences;
	}

	throw new Error( 'Gutena Forms FetchTourPreferences Error' );
}

/**
 * @param {Object} preferences Partial preferences payload.
 * @returns {Promise<Object>}
 */
export async function gutenaFormsSaveTourPreferences( preferences ) {
	const response = await apiFetch( {
		method: 'POST',
		path: `${ GutenaFormsRestConfiguration.namespace }tour/preferences`,
		data: {
			preferences,
		},
	} );

	if ( response?.preferences ) {
		return response.preferences;
	}

	throw new Error( 'Gutena Forms SaveTourPreferences Error' );
}
