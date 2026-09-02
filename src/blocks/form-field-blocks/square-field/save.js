import { useBlockProps } from '@wordpress/block-editor';
import { SquarePaymentMarkup } from './payment-markup';

export default function Save( { attributes } ) {
	const { fieldName, nameAttr } = attributes;
	const fieldId = nameAttr || 'square_payment';

	const blockProps = useBlockProps.save( {
		className:
			'wp-block-gutena-field-group wp-block-gutena-square-field field-group-type-square standalone-square-field',
	} );

	return (
		<div { ...blockProps } data-square-field={ fieldId }>
			<SquarePaymentMarkup
				fieldId={ fieldId }
				fieldName={ fieldName || 'Credit Card' }
			/>
		</div>
	);
}
