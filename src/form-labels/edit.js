import { RichText, useBlockProps } from '@wordpress/block-editor';
import { select } from '@wordpress/data';
import {gfIsEmpty} from "../helper";

const Edit = ( { attributes, setAttributes, clientId } ) => {

	const parent  = select( 'core/block-editor' ).getBlockParents( clientId );
	var children  = select( 'core/block-editor' ).getBlocksByClientId( parent[1] );
	var elementId = children[0].innerBlocks[0].innerBlocks[1].attributes.nameAttr;
	let {
		content, htmlFor, isRequired, placeholder, className
	} = attributes;

	if ( gfIsEmpty( htmlFor ) && !gfIsEmpty( elementId ) ) {
		htmlFor = elementId;
		setAttributes( { htmlFor: elementId } );
	}

	return (
		<div { ...useBlockProps( { className: className } ) }>
				<label htmlFor={ htmlFor }>
					<RichText
						tagName={ 'p' }
						placeholder={ placeholder }
						value={ content }
						onChange={ ( newContent ) => setAttributes( { content: newContent } ) }
					/>
				</label>
		</div>
	);
};

export default Edit;
