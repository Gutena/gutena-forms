import { useBlockProps } from '@wordpress/block-editor';

const Save = ( { attributes } ) => {
	const { formId } = attributes;
	const blockProps = useBlockProps.save();

	if ( ! formId ) {
		return (
			<div { ...blockProps }></div>
		);
	}

	return (
		<div { ...blockProps }>
			<div className="gutena-forms-existing-form" data-form-id={ formId }>
				{ /* PHP Rendering */ }
			</div>
		</div>
	);
};

export default Save;
