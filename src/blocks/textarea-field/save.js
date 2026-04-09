import { useBlockProps } from '@wordpress/block-editor';
import { gfIsEmpty } from '../../shared/utils/helper';

export default function Save( { attributes } ) {
	const {
		nameAttr,
		fieldName,
		placeholder,
		isRequired,
		defaultValue,
		textAreaRows,
		maxlength,
		description,
	} = attributes;

	const blockProps = useBlockProps.save( {
		className: 'wp-block-gutena-field-group wp-block-gutena-textarea-field field-group-type-textarea standalone-textarea-field',
	} );

	return (
		<div { ...blockProps }>
			<label htmlFor={ nameAttr } className="heading-input-label-gutena">
				{ fieldName }
				{ isRequired ? ' *' : '' }
			</label>
			<textarea
				id={ nameAttr }
				name={ nameAttr }
				className={ `gutena-forms-field textarea-field ${ isRequired ? 'required-field' : '' }` }
				placeholder={ placeholder }
				rows={ textAreaRows && textAreaRows > 0 ? textAreaRows : 5 }
				maxLength={ maxlength && maxlength > 0 ? maxlength : undefined }
				required={ isRequired ? 'required' : undefined }
				defaultValue={ defaultValue }
			/>
			{ ! gfIsEmpty( description ) && <p className="gutena-forms-textarea-field-description">{ description }</p> }
			<p className="gutena-forms-field-error-msg" />
		</div>
	);
}
