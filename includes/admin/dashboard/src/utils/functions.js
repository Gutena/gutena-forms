export const gutenaFormsUcFirst = ( string ) => {
	string = String( string );

	return string.charAt(0).toUpperCase() + string.slice(1);
};

/**
 * Check if value exists in array
 *
 * @since 1.6.0
 * @param needle
 * @param haystack
 * @param strict
 * @returns {boolean}
 */
export const gutenaFormsInArray = ( needle, haystack, strict = false ) => {
	for ( let i = 0; i < haystack.length; i++ ) {
		if ( ( strict && haystack[ i ] === needle ) || ( ! strict && haystack[ i ] == needle ) ) {
			return true;
		}
	}

	return false;
}
