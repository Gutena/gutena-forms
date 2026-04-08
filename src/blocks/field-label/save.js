import { RichText, useBlockProps } from '@wordpress/block-editor';

const Save = ( { attributes } ) => {
	let {
		content, htmlFor, placeholder, className
	} = attributes;
	const blockProps = useBlockProps.save( { className: className } );

	return (
		<div { ...blockProps }>
			<label htmlFor={ htmlFor }>
				<RichText.Content
					tagName={ 'p' }
					value={ content }
					placeholder={ placeholder }
				/>
			</label>
		</div>
	);
};

export default Save;
