import { GutenaFormsRestConfiguration } from '../index';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

export async function gutenaFormsFetchPrevNextEntry( id ) {
	const response = await apiFetch( {
		method: 'GET',
		path: addQueryArgs(
			`${ GutenaFormsRestConfiguration.namespace }entries/details`,
			{ id }
		)
	} );

	if ( ! response ) {
		throw new Error( 'No response from server' );
	}

	return {
		prevEntryId: response.details.previous_entry,
		nextEntryId: response.details.next_entry,
		totalEntries: response.details.total_count,
	}
}

export async function gutenaFormsFetchEntryData( id ) {
	const response = await apiFetch( {
		method: 'GET',
		path: addQueryArgs(
			`${ GutenaFormsRestConfiguration.namespace }entry/data`,
			{ id }
		)
	} );

	if ( ! response ) {
		throw new Error( 'No response from server' );
	}

	return response.entry_data;
}
