//Check if undefined, null, empty

export const gfIsEmpty = ( data ) => {
	return 'undefined' === typeof data || null === data || '' == data;
};
