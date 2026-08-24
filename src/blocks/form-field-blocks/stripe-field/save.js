import { useBlockProps } from '@wordpress/block-editor';
import { StripePaymentMarkup } from './payment-markup';

export default function Save( { attributes } ) {
	const { fieldName, nameAttr } = attributes;
	const fieldId = nameAttr || 'stripe_payment';

	const blockProps = useBlockProps.save( {
		className:
			'wp-block-gutena-field-group wp-block-gutena-stripe-field field-group-type-stripe standalone-stripe-field',
	} );

	return (
		<div { ...blockProps } data-stripe-field={ fieldId }>
			<StripePaymentMarkup
				fieldId={ fieldId }
				fieldName={ fieldName || 'Payment' }
			/>
		</div>
	);
}
