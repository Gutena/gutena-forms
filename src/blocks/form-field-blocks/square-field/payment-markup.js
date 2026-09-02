export function SquarePaymentMarkup( { fieldId, fieldName = 'Credit Card' } ) {
	return (
		<>
			<label className="heading-input-label-gutena gutena-forms-square-payment__heading" htmlFor={ `gutena-square-payment-${ fieldId }` }>
				{ fieldName }
			</label>
			<div
				id={ `gutena-square-payment-${ fieldId }` }
				className="gutena-forms-square-payment__container"
				data-square-payment-field={ fieldId }
			/>
			<p className="gutena-forms-field-error-msg gutena-forms-square-payment__error" />
			<input type="hidden" name={ `${ fieldId }_payment_token` } value="" />
		</>
	);
}
