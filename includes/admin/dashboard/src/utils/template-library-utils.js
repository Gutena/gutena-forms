/**
 * Template Library UI helpers.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

import { __, sprintf } from '@wordpress/i18n';
import {
	ALL_TEMPLATES_CATEGORY,
	ALL_TEMPLATES_LABEL,
	CATEGORY_ORDER,
	COMING_SOON_CATEGORIES,
} from './template-library-constants';

/**
 * Default form name for a template-based form.
 *
 * @param {string} templateTitle Template title from API.
 * @returns {string}
 */
export function getDefaultFormName( templateTitle ) {
	return sprintf(
		/* translators: %s: template title */
		__( '%s Form', 'gutena-forms' ),
		templateTitle
	);
}

/**
 * Sum template counts across all categories.
 *
 * @param {Object} categoryCounts Category slug => count map.
 * @returns {number}
 */
export function getTotalTemplateCount( categoryCounts = {} ) {
	return Object.values( categoryCounts ).reduce( ( total, count ) => total + Number( count || 0 ), 0 );
}

/**
 * Build ordered category navigation items from API data.
 *
 * @param {Array}  categoriesFromApi Categories from REST API.
 * @param {Object} categoryCounts    Category counts from templates response.
 * @returns {Array}
 */
export function buildCategoryNavItems( categoriesFromApi = [], categoryCounts = {} ) {
	const apiMap = {};

	categoriesFromApi.forEach( ( category ) => {
		apiMap[ category.id ] = category;
	} );

	return CATEGORY_ORDER.map( ( slug ) => ( {
		id: slug,
		label: apiMap[ slug ]?.label || slug,
		count: categoryCounts[ slug ] ?? apiMap[ slug ]?.count ?? 0,
		comingSoon: COMING_SOON_CATEGORIES.includes( slug ),
	} ) );
}

/**
 * Get the active category label for subheading copy.
 *
 * @param {string} activeCategory Active category slug.
 * @param {Array}  categoryItems  Built category nav items.
 * @returns {string}
 */
export function getActiveCategoryLabel( activeCategory, categoryItems = [] ) {
	if ( ALL_TEMPLATES_CATEGORY === activeCategory ) {
		return ALL_TEMPLATES_LABEL;
	}

	const match = categoryItems.find( ( item ) => item.id === activeCategory );
	return match?.label || ALL_TEMPLATES_LABEL;
}
