import { useBlockProps } from '@wordpress/block-editor';
import { gfIsEmpty } from '../../shared/utils/helper';

function numAttr( v ) {
	if ( gfIsEmpty( v ) && v !== 0 && v !== '0' ) {
		return undefined;
	}
	return v;
}

export default function Save( { attributes } ) {
	const {
		nameAttr,
		fieldName,
		placeholder,
		isRequired,
		defaultValue,
		minMaxStep,
		autocomplete,
		description,
	} = attributes;

	const blockProps = useBlockProps.save( {
		className: 'wp-block-gutena-field-group wp-block-gutena-number-field field-group-type-number standalone-number-field',
	} );

	const min = numAttr( minMaxStep?.min );
	const max = numAttr( minMaxStep?.max );
	const step = numAttr( minMaxStep?.step );

	return (
		<div { ...blockProps }>
			<label htmlFor={ nameAttr } className="heading-input-label-gutena">
				{ fieldName }
				{ isRequired ? ' *' : '' }
			</label>
			<input
				id={ nameAttr }
				name={ nameAttr }
				type="number"
				className={ `gutena-forms-field number-field ${ isRequired ? 'required-field' : '' } ${ autocomplete ? 'autocomplete' : '' }` }
				placeholder={ placeholder }
				defaultValue={ defaultValue }
				min={ min }
				max={ max }
				step={ step }
				required={ isRequired ? 'required' : undefined }
			/>
			{ ! gfIsEmpty( description ) && <p className="gutena-forms-number-field-description">{ description }</p> }
			<p className="gutena-forms-field-error-msg" />
		</div>
	);
}
