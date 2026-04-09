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
		className: 'wp-block-gutena-field-group wp-block-gutena-password-field field-group-type-password standalone-password-field',
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
				type="password"
				className={ `gutena-forms-field password-field ${ isRequired ? 'required-field' : '' } ${ autocomplete ? 'autocomplete' : '' }` }
				placeholder={ placeholder }
				defaultValue={ defaultValue }
				maxLength={ maxlength && maxlength > 0 ? maxlength : undefined }
				autoComplete={ autocomplete ? 'current-password' : 'new-password' }
				required={ isRequired ? 'required' : undefined }
			/>
			{ ! gfIsEmpty( description ) && <p className="gutena-forms-password-field-description">{ description }</p> }
			<p className="gutena-forms-field-error-msg" />
		</div>
	);
}
