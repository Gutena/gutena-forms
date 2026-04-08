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