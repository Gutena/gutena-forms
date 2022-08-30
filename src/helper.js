//Check if undefined, null, empty

export const gfIsEmpty = ( data ) => {
	return 'undefined' === data || null === data || '' == data;
};
