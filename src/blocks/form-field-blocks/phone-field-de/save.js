import { useBlockProps } from '@wordpress/block-editor';
import { gfIsEmpty } from '../../../shared/utils/helper';

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
		className: 'wp-block-gutena-field-group wp-block-gutena-phone-field field-group-type-phone standalone-phone-field',
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
				type="tel"
				className={ `gutena-forms-field phone-field ${ isRequired ? 'required-field' : '' } ${ autocomplete ? 'autocomplete' : '' }` }
				placeholder={ placeholder }
				defaultValue={ defaultValue }
				maxLength={ maxlength && maxlength > 0 ? maxlength : undefined }
				required={ isRequired ? 'required' : undefined }
			/>
			{ ! gfIsEmpty( description ) && <p className="gutena-forms-phone-field-description">{ description }</p> }
			<p className="gutena-forms-field-error-msg" />
		</div>
	);
}
