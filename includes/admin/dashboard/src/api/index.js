import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

/**
 * Gutena Forms REST API Configuration.
 *
 * @since 1.6.0
 * @type {{namespace: string}}
 */
export const GutenaFormsRestConfiguration = {
	namespace: 'gutena-forms/v1/',
	proNamespace: 'gutena-forms/pro/v1/',
};

/**
 * Fetch menus from the Gutena Forms REST API.
 *
 * @since 1.6.0
 * @returns {Promise<string|Record<string, Omit<({onSearch: NonNullable<({onSearch: (searchString: string) => void} & {search: string})["onSearch"]>, search: NonNullable<({onSearch: (searchString: string) => void} & {search: string})["search"]>} & {backButtonLabel?: string, onBackButtonClick?: React.MouseEventHandler<HTMLElement>, children?: React.ReactNode, className?: string, hasSearch?: boolean, isEmpty?: boolean, isSearchDebouncing?: boolean, menu?: string, parentMenu?: string, title?: string, titleAction?: React.ReactNode}) | ({onSearch?: never} & {search?: string} & {backButtonLabel?: string, onBackButtonClick?: React.MouseEventHandler<HTMLElement>, children?: React.ReactNode, className?: string, hasSearch?: boolean, isEmpty?: boolean, isSearchDebouncing?: boolean, menu?: string, parentMenu?: string, title?: string, titleAction?: React.ReactNode}), "children"> & {menu: string}>|Record<string, Omit<Omit<({onSearch: NonNullable<({onSearch: (searchString: string) => void} & {search: string})["onSearch"]>, search: NonNullable<({onSearch: (searchString: string) => void} & {search: string})["search"]>} & {backButtonLabel?: string, onBackButtonClick?: React.MouseEventHandler<HTMLElement>, children?: React.ReactNode, className?: string, hasSearch?: boolean, isEmpty?: boolean, isSearchDebouncing?: boolean, menu?: string, parentMenu?: string, title?: string, titleAction?: React.ReactNode}) | ({onSearch?: never} & {search?: string} & {backButtonLabel?: string, onBackButtonClick?: React.MouseEventHandler<HTMLElement>, children?: React.ReactNode, className?: string, hasSearch?: boolean, isEmpty?: boolean, isSearchDebouncing?: boolean, menu?: string, parentMenu?: string, title?: string, titleAction?: React.ReactNode}), "children"> & {menu: string}, "children">>|Record<string, Omit<Menu, "children">>|*>}
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
 * Fetch forms from the Gutena Forms REST API.
 *
 * @since 1.6.0
 * @returns {Promise<*>}
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
 * Fetch settings menus from the Gutena Forms REST API.
 *
 * @since 1.6.0
 * @returns {Promise<string|Record<string, Omit<({onSearch: NonNullable<({onSearch: (searchString: string) => void} & {search: string})["onSearch"]>, search: NonNullable<({onSearch: (searchString: string) => void} & {search: string})["search"]>} & {backButtonLabel?: string, onBackButtonClick?: React.MouseEventHandler<HTMLElement>, children?: React.ReactNode, className?: string, hasSearch?: boolean, isEmpty?: boolean, isSearchDebouncing?: boolean, menu?: string, parentMenu?: string, title?: string, titleAction?: React.ReactNode}) | ({onSearch?: never} & {search?: string} & {backButtonLabel?: string, onBackButtonClick?: React.MouseEventHandler<HTMLElement>, children?: React.ReactNode, className?: string, hasSearch?: boolean, isEmpty?: boolean, isSearchDebouncing?: boolean, menu?: string, parentMenu?: string, title?: string, titleAction?: React.ReactNode}), "children"> & {menu: string}>|Record<string, Omit<Omit<({onSearch: NonNullable<({onSearch: (searchString: string) => void} & {search: string})["onSearch"]>, search: NonNullable<({onSearch: (searchString: string) => void} & {search: string})["search"]>} & {backButtonLabel?: string, onBackButtonClick?: React.MouseEventHandler<HTMLElement>, children?: React.ReactNode, className?: string, hasSearch?: boolean, isEmpty?: boolean, isSearchDebouncing?: boolean, menu?: string, parentMenu?: string, title?: string, titleAction?: React.ReactNode}) | ({onSearch?: never} & {search?: string} & {backButtonLabel?: string, onBackButtonClick?: React.MouseEventHandler<HTMLElement>, children?: React.ReactNode, className?: string, hasSearch?: boolean, isEmpty?: boolean, isSearchDebouncing?: boolean, menu?: string, parentMenu?: string, title?: string, titleAction?: React.ReactNode}), "children"> & {menu: string}, "children">>|Record<string, Omit<Menu, "children">>|*>}
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
 * Fetch settings from the Gutena Forms REST API.
 *
 * @since 1.6.0
 * @param settingsId
 *
 * @returns {Promise<*>}
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
 * Update settings via the Gutena Forms REST API.
 *
 * @since 1.6.0
 * @param settingsId
 * @param settingsData
 * @returns {Promise<unknown>}
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
 * @since 1.6.0
 * @returns {Promise<*>}
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
 * Fetch status from the Gutena Forms Pro REST API.
 *
 * @since 1.6.0
 * @returns {Promise<*>}
 */
export async function gutenaFormsFetchStatus() {
	const response = await apiFetch( {
		method: 'GET',
		path: `${ GutenaFormsRestConfiguration.proNamespace }status/get-all`,
	} );

	if ( response.status ) {
		return response.status;
	}

	throw new Error( 'Upgrade to pro' );
}

/**
 * Fetch users from the Gutena Forms Pro REST API.
 *
 * @since 1.6.0
 * @returns {Promise<[*]|(function({byId: {}, queries: {}}=, *): ({byId: *, queries: {}}))|*>}
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
 * @since 1.6.0
 * @returns {Promise<*>}
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
 * Delete form via the Gutena Forms REST API.
 *
 * @since 1.6.0
 * @param formId
 * @returns {Promise<unknown>}
 */
export async function gutenaFormsDeleteForm( formId ) {
	const response = await apiFetch( {
		method: 'DELETE',
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
 * @since 1.6.0
 * @param formIds
 * @returns {Promise<unknown>}
 */
export async function deleteMultipleForms( formIds ) {
	const response = await apiFetch( {
		method: 'DELETE',
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
 * @since 1.6.0
 * @returns {Promise<*>}
 */
export async function gutenaFormsFetchAllEntries() {
	const response = await apiFetch( {
		method: 'GET',
		path: `${ GutenaFormsRestConfiguration.namespace }entries/get-all`,
	} );

	if ( response.entries ) {
		return response.entries;
	}

	throw new Error( 'Gutena Forms FetchAllEntries Error' );
}

export async function gutenaFormsFetchEntriesByForm( formId ) {
	const response = await apiFetch( {
		method: 'GET',
		path: addQueryArgs(
			`${ GutenaFormsRestConfiguration.namespace }entries/get-all`,
			{
				form_id: formId,
			}
		),
	} );

	if ( response.entries ) {
		return response.entries;
	}

	throw new Error( 'Gutena Forms FetchEntriesByForm Error' );
}

export async function gutenaFormsFetchEntryDetails( entryId ) {}

export async function gutenaFormsDeleteEntry( entryId ) {}

export async function gutenaFormsDeleteMultipleEntries( entryIds ) {}
