import { GutenaFormsRestConfiguration } from '../index';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

export async function gutenaFormsFetchPrevNextEntry( id ) {
	const response = await apiFetch( {
		method: 'GET',
		path: addQueryArgs(
			`${ GutenaFormsRestConfiguration.namespace }entries/next-prev-current`,
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
		serialNo: response.details.serial_no,
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

export async function gutenaFormsFetchEntryDetails( id ) {
	const response = await apiFetch( {
		method: 'GET',
		path: addQueryArgs(
			`${ GutenaFormsRestConfiguration.namespace }entry/details`,
			{ id }
		)
	} );

	if ( ! response ) {
		throw new Error( 'No response from server' );
	}

	return response.entry_details;
}

export async function gutenaFormsFetchRelatedEntries( id ) {
	const response = await apiFetch( {
		method: 'GET',
		path: addQueryArgs(
			`${ GutenaFormsRestConfiguration.namespace }entries/related`,
			{ id }
		),
	} );

	if ( ! response ) {
		throw new Error( 'No response from server' );
	}

	return response.related_entries;
}

export async function gutenaFormsFetchEntriesByFormId( id, type ) {
	const response = await apiFetch( {
		method: 'GET',
		path: addQueryArgs(
			`${ GutenaFormsRestConfiguration.namespace }entries/${ type }`,
			{ form_id: id }
		),
	} );

	if ( ! response ) {
		throw new Error( 'No response from server' );
	}

	return response[ type ];
}
