/**
 * Gutena Forms entries REST API helpers (single entry, prev/next, data, details, delete).
 *
 * @since 1.7.0
 * @package Gutena Forms
 */

import { GutenaFormsRestConfiguration } from '../index';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

/**
 * Fetch previous/next entry context for the given entry ID (for entry detail navigation).
 *
 * @since 1.7.0
 * @param {number|string} id Entry ID.
 * @returns {Promise<Object>} Object with prevEntryId, nextEntryId, totalEntries, serialNo.
 */
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
	};
}

/**
 * Fetch raw entry data (submitted field values) by entry ID.
 *
 * @since 1.7.0
 * @param {number|string} id Entry ID.
 * @returns {Promise<Object>} Entry data object.
 */
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

/**
 * Fetch full entry details (metadata, user name, form name, formatted date) by entry ID.
 *
 * @since 1.7.0
 * @param {number|string} id Entry ID.
 * @returns {Promise<Object>} Entry details object.
 */
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

/**
 * Fetch entries from the same user as the given entry (related entries).
 *
 * @since 1.7.0
 * @param {number|string} id Entry ID.
 * @returns {Promise<Array>} List of related entry objects.
 */
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

/**
 * Fetch entries data or headers for a form by form ID and type ('headers' or 'data').
 *
 * @since 1.7.0
 * @param {number|string} id   Form (block) ID.
 * @param {string}        type One of 'headers' or 'data'.
 * @returns {Promise<Array>} Headers array or entries data array depending on type.
 */
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
	return {
		[ type ]: response[ type ],
		capabilities: response.current_user_can_manage || [],
	};
}

/**
 * Delete a single entry (move to trash) via the Gutena Forms REST API.
 *
 * @since 1.7.0
 * @param {number} entryId Entry ID to delete (move to trash).
 * @returns {Promise<Object>} API response.
 */
export async function gutenaFormsDeleteEntry( entryId ) {
	const response = await apiFetch( {
		method: 'POST',
		path: `${ GutenaFormsRestConfiguration.namespace }entries/delete`,
		data: { entry_id: entryId },
	} );

	if ( response && 'success' === response.status ) {
		return response;
	}

	throw new Error( response?.message || 'Gutena Forms DeleteEntry Error' );
}

/**
 * Delete multiple entries (move to trash) via the Gutena Forms REST API.
 *
 * @since 1.7.0
 * @param {number[]} entryIds Array of entry IDs to delete (move to trash).
 * @returns {Promise<Object>} API response.
 */
export async function deleteMultipleEntries( entryIds ) {
	const response = await apiFetch( {
		method: 'POST',
		path: `${ GutenaFormsRestConfiguration.namespace }entries/delete`,
		data: { entry_ids: entryIds },
	} );

	if ( response && 'success' === response.status ) {
		return response;
	}

	throw new Error( response?.message || 'Gutena Forms DeleteEntries Error' );
}
