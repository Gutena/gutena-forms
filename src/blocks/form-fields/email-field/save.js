import { useBlockProps } from '@wordpress/block-editor';

const Save = ( { attributes } ) => {
	const {
		nameAttr,
		fieldLabel,
		fieldLabelContent,
		isRequired,
		placeholder,
		defaultValue,
		autocomplete,
		description,
		errorRequiredMsg,
	} = attributes;

	const labelText = fieldLabelContent || fieldLabel;
	const inputClasses = `gutena-forms-field email-field ${
		isRequired ? 'required-field' : ''
	} ${ autocomplete ? 'autocomplete' : '' }`;

	const blockProps = useBlockProps.save( {
		className: 'wp-block-gutena-field-group field-group-type-email',
	} );

	return (
		<div { ...blockProps }>
			<label className="heading-input-label-gutena" htmlFor={ nameAttr || '' }>
				{ labelText }
			</label>
			<input
				id={ nameAttr || '' }
				type="email"
				name={ nameAttr || '' }
				className={ inputClasses.trim() }
				placeholder={ placeholder || '' }
				defaultValue={ defaultValue || '' }
				required={ isRequired }
			/>
			{ description ? <p className="gutena-forms-field-description">{ description }</p> : null }
			<p className="gutena-forms-field-error-msg">{ errorRequiredMsg }</p>
		</div>
	);
};

export default Save;
