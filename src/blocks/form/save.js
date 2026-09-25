import { InnerBlocks, useBlockProps } from '@wordpress/block-editor';

export default function save( props ) {
	const { attributes } = props;

	//Attributes
	const { formID, formClasses } = attributes;

	const blockProps = useBlockProps.save( {
		className: formClasses,
	} );

	return (
		<form method="post" encType="multipart/form-data" { ...blockProps }>
			<input type="hidden" name="formid" value={ formID } />
			<div className="gutena-forms-content-wrapper">
				<InnerBlocks.Content />
			</div>
		</form>
	);
}
