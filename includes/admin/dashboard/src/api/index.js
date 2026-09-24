/**
 * Gutena Forms dashboard REST API client.
 *
 * @since 1.7.0
 * @package Gutena Forms
 */

import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

/**
 * REST API base namespaces for Gutena Forms and Pro.
 *
 * @since 1.7.0
 * @type {{namespace: string, proNamespace: string}}
 */
export const GutenaFormsRestConfiguration = {
	namespace: 'gutena-forms/v1/',
	proNamespace: 'gutena-forms-pro/v1/',
};

/**
 * Fetch main dashboard menus from the Gutena Forms REST API.
 *
 * @since 1.7.0
 * @returns {Promise<Object>} Menus object keyed by menu slug.
 */
export async function gutenaFormsFetchMenus() {
	const response = await apiFetch( {
		path: `${ GutenaFormsRestConfiguration.namespace }get-menus`,
	} );

	if ( response.menus ) {
		return response.menus;
	}

	throw new Error( 'Gutena Forms FetchMenus Error' );
}

/**
 * Fetch forms list from the Gutena Forms REST API.
 *
 * @since 1.7.0
 * @returns {Promise<Array>} List of forms.
 */
export async function gutenaFormsFetchForms() {
	const response = await apiFetch( {
		path: `${ GutenaFormsRestConfiguration.namespace }forms-list`,
	} );

	if ( response.forms ) {
		return response.forms;
	}

	throw new Error( 'Gutena Forms FetchForms Error' );
}

/**
 * Fetch left navigation settings menus from the Gutena Forms REST API.
 *
 * @since 1.7.0
 * @returns {Promise<Object>} Settings menus object keyed by menu slug.
 */
export async function gutenaFormsFetchSettingsMenu() {
	const response = await apiFetch( {
		path: `${ GutenaFormsRestConfiguration.namespace }left-navigation-menus`,
	} );

	if ( response.menus ) {
		return response.menus;
	}

	throw new Error( 'Gutena Forms FetchSettingsMenu Error' );
}

/**
 * Fetch settings for a given settings screen from the Gutena Forms REST API.
 *
 * @since 1.7.0
 * @param {string} settingsId Settings screen id (e.g. 'forms', 'entries', 'honeypot').
 * @returns {Promise<Object>} Settings payload (fields, title, etc.).
 */
export async function gutenaFormsFetchSettings( settingsId ) {
	const response = await apiFetch( {
		method: 'GET',
		path: addQueryArgs(
			`${ GutenaFormsRestConfiguration.namespace }settings/`,
			{
				settings_id: settingsId,
			}
		),
	} );

	if ( response.settings ) {
		return response.settings;
	}

	throw new Error( 'Gutena Forms FetchSettings Error' );
}

/**
 * Save/update settings for a given settings screen via the Gutena Forms REST API.
 *
 * @since 1.7.0
 * @param {string} settingsId   Settings screen id.
 * @param {Object} settingsData Settings payload to save.
 * @returns {Promise<Object>} API response.
 */
export async function gutenaFormsUpdateSettings( settingsId, settingsData ) {
	const response = await apiFetch({
		method: 'POST',
		path: `${ GutenaFormsRestConfiguration.namespace }save-settings`,
		data: {
			settings_id: settingsId,
			settings_data: settingsData,
		},
	} );

	if ( response.success ) {
		return response;
	}

	throw new Error( 'Gutena Forms UpdateSettings Error' );
}

/**
 * Fetch tags from the Gutena Forms Pro REST API.
 *
 * @since 1.7.0
 * @returns {Promise<Array>} List of tags. Throws if not Pro.
 */
export async function gutenaFormsFetchTags() {
	const response = await apiFetch( {
		method: 'GET',
		path: `${ GutenaFormsRestConfiguration.proNamespace }tags/get-all`,
	} );

	if ( response.tags ) {
		return response.tags;
	}

	throw new Error( 'Upgrade to pro' );
}

/**
 * Fetch status list from the Gutena Forms Pro REST API.
 *
 * @since 1.7.0
 * @returns {Promise<Array>} List of statuses. Throws if not Pro.
 */
export async function gutenaFormsFetchStatus() {
	const response = await apiFetch( {
		method: 'GET',
		path: `${ GutenaFormsRestConfiguration.proNamespace }statuses/get-all`,
	} );

	if ( response.status ) {
		return response.status;
	}

	throw new Error( 'Upgrade to pro' );
}

/**
 * Fetch users list from the Gutena Forms Pro REST API.
 *
 * @since 1.7.0
 * @returns {Promise<Array>} List of users. Throws if not Pro.
 */
export async function gutenaFormsFetchUsers() {
	const response = await apiFetch( {
		method: 'GET',
		path: `${ GutenaFormsRestConfiguration.proNamespace }user-list`,
	} );

	if ( response.users ) {
		return response.users;
	}

	throw new Error( 'Upgrade to pro' );
}

/**
 * Fetch all forms from the Gutena Forms REST API.
 *
 * @since 1.7.0
 * @returns {Promise<Array>} List of forms with id, datetime, title, status, entries, author, permalink.
 */
export async function gutenaFormsFetchAllForms() {
	const response = await apiFetch( {
		method: 'GET',
		path: `${ GutenaFormsRestConfiguration.namespace }forms/get-all`,
	} );

	if ( response.forms ) {
		return response.forms;
	}

	throw new Error( 'Gutena Forms FetchAllForms Error' );
}

/**
 * Delete a single form via the Gutena Forms REST API.
 *
 * @since 1.7.0
 * @param {number} formId Form (post) ID to delete.
 * @returns {Promise<Object>} API response.
 */
export async function gutenaFormsDeleteForm( formId ) {
	const response = await apiFetch( {
		method: 'GET',
		path: addQueryArgs(
			`${ GutenaFormsRestConfiguration.namespace }forms/delete/`,
			{
				form_id: formId,
			}
		),
	} );

	if ( 'success' === response.status ) {
		return response;
	}

	throw new Error( 'Gutena Forms DeleteForm Error' );
}

/**
 * Delete multiple forms via the Gutena Forms REST API.
 *
 * @since 1.7.0
 * @param {number[]} formIds Array of form (post) IDs to delete.
 * @returns {Promise<Object>} API response.
 */
export async function deleteMultipleForms( formIds ) {
	const response = await apiFetch( {
		method: 'GET',
		path: addQueryArgs(
			`${ GutenaFormsRestConfiguration.namespace }forms/delete/`,
			{
				form_id: formIds,
			}
		),
	} );

	if ( 'success' === response.status ) {
		return response;
	}

	throw new Error( 'Gutena Forms DeleteForm Error' );
}

/**
 * Fetch all entries from the Gutena Forms REST API.
 *
 * @since 1.7.0
 * @returns {Promise<{entries: *, capabilities}>} List of entries (optionally filtered by form).
 */
export async function gutenaFormsFetchAllEntries() {
	const response = await apiFetch( {
		method: 'GET',
		path: `${ GutenaFormsRestConfiguration.namespace }entries/get-all`,
	} );

	if ( response.entries ) {
		return {
			entries: response.entries,
			capabilities: response.current_user_can_manage || {},
		};
	}

	throw new Error( 'Gutena Forms FetchAllEntries Error' );
}

/**
 * Fetch form search options (id and title) for entry search dropdown.
 *
 * @since 1.7.0
 * @returns {Promise<Array>} List of { id, title } for published forms.
 */
export async function gutenaFromsIdTitle() {
	const response = await apiFetch( {
		method: 'GET',
		path: `${ GutenaFormsRestConfiguration.namespace }forms/entry/search-options`,
	} );

	if ( response.search_options ) {
		return response.search_options;
	}

	throw new Error( 'Gutena Forms FetchAllEntries Error' );
}

/**
 * Fetch all entries for a given form (block) ID.
 *
 * @since 1.7.0
 * @param {string|number} formId Form block ID.
 * @returns {Promise<Array>} List of entries for the form.
 */
export async function gutenaFormsFetchEntriesByFormId( formId ) {
	const response = await apiFetch( {
		method: 'GET',
		path: addQueryArgs(
			`${ GutenaFormsRestConfiguration.namespace }entries/get-by-form-id/`,
			{ formId }
		),
	} );

	if ( response.entries ) {
		return response.entries;
	}

	throw new Error( 'Gutena Forms FetchEntriesByFormId Error' );
}

/**
 * Fetch entries filtered by optional form_id, tag, status (Pro API).
 *
 * @since 1.7.0
 * @param {Object} params Optional filters.
 * @param {string|number} [params.formId] Form block ID.
 * @param {string} [params.tag] Tag slug.
 * @param {string} [params.status] Status slug.
 * @returns {Promise<{entries: *, capabilities}>} List of entries and capabilities.
 */
export async function gutenaFormsFetchEntriesFiltered( { formId, tag, status } = {} ) {
	const query = {};
	if ( formId ) {
		query.form_id = formId;
	}
	if ( tag ) {
		query.tag = tag;
	}
	if ( status ) {
		query.status = status;
	}

	const path = Object.keys( query ).length
		? addQueryArgs( `${ GutenaFormsRestConfiguration.proNamespace }entries/get-all`, query )
		: `${ GutenaFormsRestConfiguration.proNamespace }entries/get-all`;

	const response = await apiFetch( {
		method: 'GET',
		path,
	} );

	if ( response.entries ) {
		return {
			entries: response.entries,
			capabilities: response.current_user_can_manage || {},
		};
	}

	throw new Error( 'Gutena Forms FetchEntriesFiltered Error' );
}

/**
 * Fetch template library categories with counts.
 *
 * @since 2.2.0
 * @returns {Promise<Array>} Category list.
 */
export async function gutenaFormsFetchTemplateCategories() {
	const response = await apiFetch( {
		method: 'GET',
		path: `${ GutenaFormsRestConfiguration.namespace }templates/categories`,
	} );

	if ( response.categories ) {
		return response.categories;
	}

	throw new Error( 'Gutena Forms FetchTemplateCategories Error' );
}

/**
 * Fetch templates with optional search, category filter, and pagination.
 *
 * @since 2.2.0
 * @param {Object} params Query parameters.
 * @param {string} [params.category] Category slug.
 * @param {string} [params.search] Search string.
 * @param {number} [params.page] Page number.
 * @param {number} [params.per_page] Items per page.
 * @returns {Promise<{templates: Array, category_counts: Object, pagination: Object}>}
 */
export async function gutenaFormsFetchTemplates( { category = '', search = '', page = 1, per_page = 20 } = {} ) {
	const query = {
		page,
		per_page,
	};

	if ( category ) {
		query.category = category;
	}

	if ( search ) {
		query.search = search;
	}

	const response = await apiFetch( {
		method: 'GET',
		path: addQueryArgs( `${ GutenaFormsRestConfiguration.namespace }templates/get-all`, query ),
	} );

	if ( response.templates ) {
		return {
			templates: response.templates,
			category_counts: response.category_counts || {},
			pagination: response.pagination || {},
		};
	}

	throw new Error( 'Gutena Forms FetchTemplates Error' );
}

/**
 * Fetch a single template by ID.
 *
 * @since 2.2.0
 * @param {string} templateId Template identifier.
 * @returns {Promise<Object>} Template detail.
 */
export async function gutenaFormsFetchTemplate( templateId ) {
	const response = await apiFetch( {
		method: 'GET',
		path: `${ GutenaFormsRestConfiguration.namespace }templates/${ templateId }`,
	} );

	if ( response.template ) {
		return response.template;
	}

	throw new Error( 'Gutena Forms FetchTemplate Error' );
}

/**
 * Create a form from a registered template ID.
 *
 * @since 2.2.0
 * @param {string} templateId Registered template identifier.
 * @param {string} [formName] Optional custom form name.
 * @returns {Promise<Object>} Created form data including edit_url.
 */
export async function gutenaFormsCreateFromTemplate( templateId, formName = '' ) {
	try {
		const response = await apiFetch( {
			method: 'POST',
			path: `${ GutenaFormsRestConfiguration.namespace }templates/create-from-template`,
			data: {
				template_id: templateId,
				form_name: formName,
			},
		} );

		if ( response?.form ) {
			return response.form;
		}

		const error = new Error(
			response?.message || 'Gutena Forms CreateFromTemplate Error'
		);
		error.code = 'gutena_forms_template_create_failed';
		throw error;
	} catch ( error ) {
		if ( error?.code ) {
			throw error;
		}

		const wrappedError = new Error(
			error?.message || 'Gutena Forms CreateFromTemplate Error'
		);
		wrappedError.code = error?.data?.code || error?.code || 'gutena_forms_template_create_failed';
		throw wrappedError;
	}
}

export { gutenaFormsDeleteEntry, deleteMultipleEntries } from './entries';
