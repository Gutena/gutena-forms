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
