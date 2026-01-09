import { RichText, useBlockProps } from '@wordpress/block-editor';
import { select } from '@wordpress/data';
import { gfIsEmpty } from '../helper';
import { useEffect } from '@wordpress/element';

const Edit = ( { attributes, setAttributes, clientId } ) => {

	/**
	 * Finds the name attribute from parent blocks recursively.
	 *
	 * @since 1.6.0
	 * @param parentBlockIds - Array of parent block client IDs
	 * @param i - Current index
	 * @returns {*}
	 */
	const nameAttributeFinder = ( parentBlockIds, i = -1 ) => {
		++i;

		// melting point for avoid infinite loop.
		if ( gfIsEmpty( parentBlockIds[ i ] ) ) {
			return '';
		}

		var children = select( 'core/block-editor' )
			.getBlocksByClientId( parentBlockIds[ i ] );


		var elementId = children[0]?.innerBlocks[0]?.innerBlocks[1]?.attributes?.nameAttr;
		if ( gfIsEmpty( elementId ) ) {
			return nameAttributeFinder( parentBlockIds, i );
		}

		return elementId;
	};

	let { content, htmlFor, placeholder, className } = attributes;

	useEffect( () => {
		const parent = select( 'core/block-editor' )
			.getBlockParents( clientId );

		if ( gfIsEmpty( htmlFor ) ) {
			htmlFor = nameAttributeFinder( parent, -1 );

			if ( ! gfIsEmpty( htmlFor ) ) {
				setAttributes( { htmlFor: htmlFor } );
			}
		}
	}, [ clientId ] );

	return (
		<div
			{ ...useBlockProps( { className: className } ) }
		>
			<label
				htmlFor={ htmlFor }
			>
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
