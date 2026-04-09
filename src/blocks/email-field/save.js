import { useBlockProps } from '@wordpress/block-editor';
import { gfIsEmpty } from '../../shared/utils/helper';

export default function Save( { attributes } ) {
	const {
		nameAttr,
		fieldName,
		placeholder,
		isRequired,
		defaultValue,
		maxlength,
		autocomplete,
		description,
	} = attributes;

	const blockProps = useBlockProps.save( {
		className: 'wp-block-gutena-field-group wp-block-gutena-email-field field-group-type-email standalone-email-field',
	} );

	return (
		<div { ...blockProps }>
			<label htmlFor={ nameAttr } className="heading-input-label-gutena">
				{ fieldName }
				{ isRequired ? ' *' : '' }
			</label>
			<input
				id={ nameAttr }
				name={ nameAttr }
				type="email"
				className={ `gutena-forms-field email-field ${ isRequired ? 'required-field' : '' } ${ autocomplete ? 'autocomplete' : '' }` }
				placeholder={ placeholder }
				defaultValue={ defaultValue }
				maxLength={ maxlength && maxlength > 0 ? maxlength : undefined }
				required={ isRequired ? 'required' : undefined }
			/>
			{ ! gfIsEmpty( description ) && <p className="gutena-forms-email-field-description">{ description }</p> }
			<p className="gutena-forms-field-error-msg" />
		</div>
	);
}
