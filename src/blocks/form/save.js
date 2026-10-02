import { InnerBlocks, useBlockProps } from '@wordpress/block-editor';

export default function save( props ) {
	const { attributes } = props;

	//Attributes
	const { formID, formClasses, formStyle } = attributes;

	const blockProps = useBlockProps.save( {
		className: formClasses,
	} );

	return (
		<form method="post" encType="multipart/form-data" { ...blockProps }>
			{ formStyle ? <style>{ formStyle }</style> : null }
			<input type="hidden" name="formid" value={ formID } />
			<InnerBlocks.Content />
		</form>
	);
}
