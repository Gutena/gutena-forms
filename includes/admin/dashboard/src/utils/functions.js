/**
 * Dashboard utility functions (string helpers, array checks).
 *
 * @since 1.7.0
 * @package Gutena Forms
 */

/**
 * Uppercase the first character of a string.
 *
 * @since 1.7.0
 * @param {string} string Input string (will be coerced via String()).
 * @returns {string} String with first character uppercased.
 */
export const gutenaFormsUcFirst = ( string ) => {
	string = String( string );

	return string.charAt( 0 ).toUpperCase() + string.slice( 1 );
};

/**
 * Check if a value exists in an array (optional strict equality).
 *
 * @since 1.7.0
 * @param {*}      needle   Value to search for.
 * @param {Array}  haystack Array to search in.
 * @param {boolean} [strict=false] If true, use strict equality (===).
 * @returns {boolean} True if needle is in haystack.
 */
export const gutenaFormsInArray = ( needle, haystack, strict = false ) => {
	for ( let i = 0; i < haystack.length; i++ ) {
		if ( ( strict && haystack[ i ] === needle ) || ( ! strict && haystack[ i ] == needle ) ) {
			return true;
		}
	}

	return false;
};

export const gutenaFormsStrContains = ( haystack, needle ) => {
	return haystack.indexOf( needle ) !== -1;
}

export const activateLeftMenu = ( menuTarget ) => {
	const items = document.querySelectorAll( '#toplevel_page_gutena-forms > ul > li' );

	items.forEach( ( item ) => {
		item.classList.remove( 'current' );
	} );

	if ( typeof menuTarget === 'number' ) {
		items[ menuTarget ]?.classList.add( 'current' );
		return;
	}

	const fragment = String( menuTarget ).replace( /^#/, '' );

	items.forEach( ( item ) => {
		const link = item.querySelector( 'a' );

		if ( ! link ) {
			return;
		}

		const href = decodeURIComponent( link.getAttribute( 'href' ) || '' );

		if ( href.includes( fragment ) ) {
			item.classList.add( 'current' );
		}
	} );
};

export const ucFirst = ( string ) => {
	string = String( string );

	return string.charAt( 0 ).toUpperCase() + string.slice( 1 );
}
