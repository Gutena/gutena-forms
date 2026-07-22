//Check if undefined, null, empty

export const gfIsEmpty = ( data ) => {
	return 'undefined' === typeof data || null === data || '' === data;
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

/**
 * HTML autocomplete attribute for browser autofill.
 *
 * @param {boolean} enabled Whether the Autocomplete toggle is on.
 * @param {string}  token   Autofill token when enabled (e.g. email, url, on).
 * @return {string} Value for the autocomplete attribute.
 */
export const gfGetAutocompleteAttr = ( enabled, token = 'on' ) => {
	return enabled ? token : 'off';
};

/**
 * Autocomplete token for text fields (username/name heuristics).
 *
 * @param {boolean} enabled   Whether autocomplete is enabled.
 * @param {string}  nameAttr  Field name attribute / Field ID.
 * @param {string}  fieldName Field label.
 * @return {string}
 */
export const gfGetTextAutocompleteAttr = ( enabled, nameAttr = '', fieldName = '' ) => {
	if ( ! enabled ) {
		return 'off';
	}
	const haystack = `${ nameAttr } ${ fieldName }`.toLowerCase();
	if ( /user|login|account/.test( haystack ) ) {
		return 'username';
	}
	if ( /\bname\b|first|last|full/.test( haystack ) ) {
		return 'name';
	}
	return 'on';
};

// Slug to name 
export const slugToName =  slug => gfIsEmpty( slug ) ? '' : slug.split('-').map( word => word.charAt(0).toUpperCase() + word.slice(1) ).join(' ');

//get all inner block by name
export const getInnerBlocksbyNameAttr = ( blocks, blockName, attrName = '', attrValue = '' ) => {	
	let desiredBlocks = [];
	blocks.forEach( (block) => {
		if ( blockName === block.name ) {
			if ( gfIsEmpty( attrName ) || gfIsEmpty( attrValue ) || gfIsEmpty( block.attributes[attrName] ) || attrValue === block.attributes[attrName] ) {
				
				desiredBlocks.push( block );
			}
			
		} else if ( ! gfIsEmpty( block.innerBlocks ) && 0 <  block.innerBlocks.length ) { 
		   let innerBlock = getInnerBlocksbyNameAttr( block.innerBlocks, blockName, attrName, attrValue );
		   desiredBlocks = [
			...desiredBlocks,
			...innerBlock
		   ];
		}
	});
	return desiredBlocks;
}