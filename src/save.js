import { __ } from '@wordpress/i18n';
import { InnerBlocks, useBlockProps } from '@wordpress/block-editor';

export default function save( props ) {
	const { attributes } = props;

	//Attributes
	const { formID, formClasses } = attributes;

	const blockProps = useBlockProps.save( {
		className: formClasses,
	} );

	return (
		<form method="post" { ...blockProps }>
			<input type="hidden" name="formid" value={ formID } />
			<InnerBlocks.Content />
		</form>
	);
}
