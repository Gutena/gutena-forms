import { useBlockProps } from '@wordpress/block-editor';
import { gfIsEmpty } from '../../shared/utils/helper';

export default function Save( { attributes } ) {
	const {
		nameAttr,
		fieldName,
		isRequired,
		defaultValue,
		minAttr,
		maxAttr,
		stepAttr,
		autocomplete,
		description,
	} = attributes;

	const blockProps = useBlockProps.save( {
		className:
			'wp-block-gutena-field-group wp-block-gutena-time-field field-group-type-time standalone-time-field',
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
				type="time"
				className={ `gutena-forms-field time-field ${
					isRequired ? 'required-field' : ''
				} ${ autocomplete ? 'autocomplete' : '' }` }
				defaultValue={ defaultValue }
				min={ ! gfIsEmpty( minAttr ) ? minAttr : undefined }
				max={ ! gfIsEmpty( maxAttr ) ? maxAttr : undefined }
				step={ ! gfIsEmpty( stepAttr ) ? stepAttr : undefined }
				required={ isRequired ? 'required' : undefined }
			/>
			{ ! gfIsEmpty( description ) && (
				<p className="gutena-forms-time-field-description">
					{ description }
				</p>
			) }
			<p className="gutena-forms-field-error-msg" />
		</div>
	);
}
