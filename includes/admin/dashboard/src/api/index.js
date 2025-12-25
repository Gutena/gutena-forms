import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

/**
 * Gutena Forms REST API Configuration.
 *
 * @since 1.6.0
 * @type {{namespace: string}}
 */
const GutenaFormsRestConfiguration = {
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
