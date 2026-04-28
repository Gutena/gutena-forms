import { useBlockProps } from '@wordpress/block-editor';
import { gfIsEmpty } from '../../../shared/utils/helper';

export default function Save( { attributes } ) {
	const {
		nameAttr,
		fieldName,
		isRequired,
		accept,
		allowMultiple,
		description,
	} = attributes;

	const blockProps = useBlockProps.save( {
		className:
			'wp-block-gutena-field-group wp-block-gutena-file-upload-field field-group-type-file standalone-file-upload-field',
	} );

	const inputName = allowMultiple ? `${ nameAttr }[]` : nameAttr;

	return (
		<div { ...blockProps }>
			<label htmlFor={ nameAttr } className="heading-input-label-gutena">
				{ fieldName }
				{ isRequired ? ' *' : '' }
			</label>
			<input
				id={ nameAttr }
				name={ inputName }
				type="file"
				className={ `gutena-forms-field file-upload-field ${
					isRequired ? 'required-field' : ''
				}` }
				accept={ ! gfIsEmpty( accept ) ? accept : undefined }
				multiple={ allowMultiple ? true : undefined }
				required={ isRequired ? 'required' : undefined }
			/>
			{ ! gfIsEmpty( description ) && (
				<p className="gutena-forms-file-upload-field-description">
					{ description }
				</p>
			) }
			<p className="gutena-forms-field-error-msg" />
		</div>
	);
}
