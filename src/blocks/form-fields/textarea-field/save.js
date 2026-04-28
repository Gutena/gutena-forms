import { useBlockProps } from '@wordpress/block-editor';

const Save = ( { attributes } ) => {
	const {
		nameAttr,
		fieldLabel,
		fieldLabelContent,
		isRequired,
		placeholder,
		defaultValue,
		textAreaRows,
		description,
		errorRequiredMsg,
	} = attributes;

	const labelText = fieldLabelContent || fieldLabel;
	const inputClasses = `gutena-forms-field textarea-field ${
		isRequired ? 'required-field' : ''
	}`;

	const blockProps = useBlockProps.save( {
		className: 'wp-block-gutena-field-group field-group-type-textarea',
	} );

	return (
		<div { ...blockProps }>
			<label className="heading-input-label-gutena" htmlFor={ nameAttr || '' }>
				{ labelText }
			</label>
			<textarea
				id={ nameAttr || '' }
				name={ nameAttr || '' }
				className={ inputClasses.trim() }
				placeholder={ placeholder || '' }
				defaultValue={ defaultValue || '' }
				required={ isRequired }
				rows={ textAreaRows || 2 }
			/>
			{ description ? <p className="gutena-forms-field-description">{ description }</p> : null }
			<p className="gutena-forms-field-error-msg">{ errorRequiredMsg }</p>
		</div>
	);
};

export default Save;
