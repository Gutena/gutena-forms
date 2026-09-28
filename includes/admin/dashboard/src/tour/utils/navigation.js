/**
 * Tour cross-view navigation helpers.
 *
 * @since 1.9.0
 * @package Gutena Forms
 */

import { TOUR_QUERY_PARAM, TOUR_TARGET_VIEWS } from '../constants';
import { activateLeftMenu } from '../../utils/functions';

const DASHBOARD_HASH_ROUTES = {
	[ TOUR_TARGET_VIEWS.DASHBOARD ]: '/settings/dashboard',
	[ TOUR_TARGET_VIEWS.FORMS ]: '/settings/forms',
	[ TOUR_TARGET_VIEWS.ENTRIES ]: '/settings/entries',
	[ TOUR_TARGET_VIEWS.EXTRAS ]: '/settings/knowledge-base',
};

const ACTIVE_MENU_SLUGS = {
	[ TOUR_TARGET_VIEWS.DASHBOARD ]: '/dashboard',
	[ TOUR_TARGET_VIEWS.FORMS ]: '/forms',
	[ TOUR_TARGET_VIEWS.ENTRIES ]: '/entries',
	[ TOUR_TARGET_VIEWS.EXTRAS ]: '/knowledge-base',
};

const LEFT_MENU_INDEXES = {
	[ TOUR_TARGET_VIEWS.DASHBOARD ]: 1,
	[ TOUR_TARGET_VIEWS.FORMS ]: 2,
	[ TOUR_TARGET_VIEWS.ENTRIES ]: 4,
	[ TOUR_TARGET_VIEWS.EXTRAS ]: null,
};

/**
 * @param {string} targetView
 * @returns {string|null}
 */
export function getDashboardHashRoute( targetView ) {
	return DASHBOARD_HASH_ROUTES[ targetView ] || null;
}

/**
 * @param {string} adminURL
 * @param {number} stepIndex
 * @returns {string}
 */
export function getEditorTourUrl( adminURL, stepIndex ) {
	const baseUrl = `${ adminURL }post-new.php?post_type=gutena_forms`;

	return `${ baseUrl }&${ TOUR_QUERY_PARAM }=${ stepIndex }`;
}

/**
 * @param {string} dashboardURL
 * @param {string} hashRoute
 * @param {number} stepIndex
 * @returns {string}
 */
export function getDashboardTourUrl( dashboardURL, hashRoute, stepIndex ) {
	const hash = hashRoute.startsWith( '#' ) ? hashRoute : `#${ hashRoute }`;
	const separator = dashboardURL.includes( '?' ) ? '&' : '?';

	return `${ dashboardURL }${ separator }${ TOUR_QUERY_PARAM }=${ stepIndex }${ hash }`;
}

/**
 * @param {string} dashboardURL
 * @param {number} stepIndex
 * @returns {string}
 */
export function getDashboardTourUrlForView( dashboardURL, targetView, stepIndex ) {
	const hashRoute = getDashboardHashRoute( targetView ) || '/settings/dashboard';

	return getDashboardTourUrl( dashboardURL, hashRoute, stepIndex );
}

/**
 * @param {number|null} stepIndex
 * @returns {number|null}
 */
export function getTourStepFromQuery( stepIndex = null ) {
	if ( Number.isInteger( stepIndex ) ) {
		return stepIndex;
	}

	const params = new URLSearchParams( window.location.search );

	if ( ! params.has( TOUR_QUERY_PARAM ) ) {
		return null;
	}

	const parsed = parseInt( params.get( TOUR_QUERY_PARAM ), 10 );

	return Number.isNaN( parsed ) ? null : parsed;
}

/**
 * @param {Function} navigate react-router navigate function.
 * @param {Function|null} setActiveMenu
 * @param {string} targetView
 */
export function navigateDashboardView( navigate, setActiveMenu, targetView ) {
	const hashRoute = getDashboardHashRoute( targetView );

	if ( ! hashRoute || typeof navigate !== 'function' ) {
		return;
	}

	navigate( hashRoute );

	if ( typeof setActiveMenu === 'function' ) {
		const slug = ACTIVE_MENU_SLUGS[ targetView ];

		if ( slug ) {
			setActiveMenu( slug );
		}
	}

	const menuIndex = LEFT_MENU_INDEXES[ targetView ];

	if ( Number.isInteger( menuIndex ) ) {
		activateLeftMenu( menuIndex );
	}
}

/**
 * @param {'dashboard'|'editor'} runtime
 * @param {string} targetView
 * @returns {boolean}
 */
export function requiresRuntimeHandoff( runtime, targetView ) {
	if ( targetView === TOUR_TARGET_VIEWS.EDITOR ) {
		return runtime !== 'editor';
	}

	return runtime === 'editor';
}
