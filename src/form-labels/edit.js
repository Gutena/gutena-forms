import { RichText, useBlockProps } from '@wordpress/block-editor';

const Edit = ( { attributes, setAttributes } ) => {

	let {
		content, htmlFor, isRequired, placeholder, className
	} = attributes;

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
