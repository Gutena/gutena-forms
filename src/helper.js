//Check if undefined, null, empty

export const gfIsEmpty = ( data ) => {
	return 'undefined' === typeof data || null === data || '' == data;
};

export const gfSanitizeName = ( name ) => {
	if ( gfIsEmpty( name ) ) {
		name = '';
	} else {
		name = name.toLowerCase().replace( / /g, '_' );
		name = name.replace(/\W/g, '');
	}
	
	return name;
}