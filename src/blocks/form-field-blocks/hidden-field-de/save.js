import { useBlockProps } from '@wordpress/block-editor';
import { gfIsEmpty } from '../../../shared/utils/helper';

export default function Save( { attributes } ) {
	const { nameAttr, fieldName, defaultValue, description } = attributes;

	const blockProps = useBlockProps.save( {
		className:
			'wp-block-gutena-field-group wp-block-gutena-hidden-field field-group-type-hidden standalone-hidden-field',
	} );

	return (
		<div { ...blockProps }>
			<label htmlFor={ nameAttr } className="screen-reader-text">
				{ fieldName }
			</label>
			<input
				id={ nameAttr }
				name={ nameAttr }
				type="hidden"
				className="gutena-forms-field hidden-field"
				defaultValue={ defaultValue }
			/>
			{ ! gfIsEmpty( description ) && (
				<p className="gutena-forms-hidden-field-description screen-reader-text">
					{ description }
				</p>
			) }
			<p className="gutena-forms-field-error-msg" />
		</div>
	);
}
