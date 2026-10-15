/**
 * REST API client for form templates.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import { GutenaFormsRestConfiguration } from '../api';

/**
 * Fetch template categories and templates.
 *
 * @param {Object} params Query params.
 * @param {string} [params.category] Category slug.
 * @param {string} [params.search] Search query.
 * @returns {Promise<Object>}
 */
export async function gutenaFormsFetchTemplates( { category = 'all', search = '' } = {} ) {
	const response = await apiFetch( {
		method: 'GET',
		path: addQueryArgs(
			`${ GutenaFormsRestConfiguration.namespace }templates/list`,
			{ category, search }
		),
	} );

	if ( response.templates ) {
		return response;
	}

	throw new Error( 'Gutena Forms FetchTemplates Error' );
}

/**
 * Fetch a single template by ID.
 *
 * @param {string} templateId Template ID.
 * @returns {Promise<Object>}
 */
export async function gutenaFormsFetchTemplate( templateId ) {
	const response = await apiFetch( {
		method: 'GET',
		path: addQueryArgs(
			`${ GutenaFormsRestConfiguration.namespace }templates/get`,
			{ id: templateId }
		),
	} );

	if ( response.template ) {
		return response.template;
	}

	throw new Error( 'Gutena Forms FetchTemplate Error' );
}

/**
 * Create a form from a template.
 *
 * @param {string} templateId Template ID.
 * @returns {Promise<Object>}
 */
export async function gutenaFormsCreateFromTemplate( templateId ) {
	const response = await apiFetch( {
		method: 'POST',
		path: `${ GutenaFormsRestConfiguration.namespace }forms/create-from-template`,
		data: { template_id: templateId },
	} );

	if ( 'success' === response.status && response.data ) {
		return response.data;
	}

	throw new Error( response.message || 'Gutena Forms CreateFromTemplate Error' );
}

/**
 * Create a blank form.
 *
 * @param {string} [title] Optional form title.
 * @returns {Promise<Object>}
 */
export async function gutenaFormsCreateBlankForm( title = '' ) {
	const response = await apiFetch( {
		method: 'POST',
		path: `${ GutenaFormsRestConfiguration.namespace }forms/create-blank`,
		data: { title },
	} );

	if ( 'success' === response.status && response.data ) {
		return response.data;
	}

	throw new Error( response.message || 'Gutena Forms CreateBlankForm Error' );
}

/**
 * Create a form from an AI prompt.
 *
 * Sends the user's prompt to the middle server (via the local REST endpoint),
 * which returns the edit URL of the newly created draft form.
 *
 * On failure, throws an Error preserving the server error code + message
 * (e.g. `limit_reached`, `invalid_prompt`, `network_error`, `server_error`).
 *
 * @param {string} prompt The user's prompt describing the form.
 * @returns {Promise<Object>} Resolves with `{ post_id, form_id, edit_url }`.
 */
export async function gutenaFormsCreateFromAI( prompt ) {
	const response = await apiFetch( {
		method: 'POST',
		path: `${ GutenaFormsRestConfiguration.namespace }forms/create-from-ai`,
		data: { prompt },
	} );

	if ( 'success' === response.status && response.data ) {
		return response.data;
	}

	const err = new Error( response.message || 'Gutena Forms CreateFromAI Error' );
	if ( response.code ) {
		err.code = response.code;
	}
	throw err;
}
