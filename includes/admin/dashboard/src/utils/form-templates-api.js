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
