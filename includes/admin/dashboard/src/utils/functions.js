export const gutenaFormsUcFirst = ( string ) => {
	string = String( string );

	return string.charAt(0).toUpperCase() + string.slice(1);
};
