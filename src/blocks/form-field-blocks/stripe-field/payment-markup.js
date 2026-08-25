import { STRIPE_COUNTRIES } from '../../../shared/payments/stripe-countries';
import { StripePaymentChrome } from './payment-chrome';
import { CardBrandIcons } from './card-brand-icons';

export function StripePaymentMarkup( {
	fieldId,
	fieldName = 'Payment',
	amountHint = 'Complete the form to view the amount.',
	showSubscriptionNotice = false,
	subscriptionNotice = '',
} ) {
	return (
		<>
			<label
				className="heading-input-label-gutena gutena-forms-stripe-payment__heading"
				htmlFor={ `gutena-stripe-card-number-${ fieldId }` }
			>
				{ fieldName }
			</label>

			<p className="gutena-forms-stripe-payment__amount-hint">{ amountHint }</p>

			<div className="gutena-forms-stripe-payment__panel">
				<StripePaymentChrome />

				<div className="gutena-forms-stripe-payment__fields">
					<div className="gutena-forms-stripe-payment__field gutena-forms-stripe-payment__field--number">
						<label
							className="gutena-forms-stripe-payment__label"
							htmlFor={ `gutena-stripe-card-number-${ fieldId }` }
						>
							Card number
						</label>
						<div className="gutena-forms-stripe-payment__input-wrap gutena-forms-stripe-payment__input-wrap--number">
							<div
								id={ `gutena-stripe-card-number-${ fieldId }` }
								className="gutena-forms-stripe-payment__element gutena-forms-stripe-payment__element--number"
							/>
							<CardBrandIcons />
						</div>
					</div>

					<div className="gutena-forms-stripe-payment__row">
						<div className="gutena-forms-stripe-payment__field gutena-forms-stripe-payment__field--expiry">
							<label
								className="gutena-forms-stripe-payment__label"
								htmlFor={ `gutena-stripe-card-expiry-${ fieldId }` }
							>
								Expiration date
							</label>
							<div className="gutena-forms-stripe-payment__input-wrap">
								<div
									id={ `gutena-stripe-card-expiry-${ fieldId }` }
									className="gutena-forms-stripe-payment__element gutena-forms-stripe-payment__element--expiry"
								/>
							</div>
						</div>

						<div className="gutena-forms-stripe-payment__field gutena-forms-stripe-payment__field--cvc">
							<label
								className="gutena-forms-stripe-payment__label"
								htmlFor={ `gutena-stripe-card-cvc-${ fieldId }` }
							>
								Security code
							</label>
							<div className="gutena-forms-stripe-payment__input-wrap">
								<div
									id={ `gutena-stripe-card-cvc-${ fieldId }` }
									className="gutena-forms-stripe-payment__element gutena-forms-stripe-payment__element--cvc"
								/>
							</div>
						</div>
					</div>

					<div className="gutena-forms-stripe-payment__field gutena-forms-stripe-payment__field--country">
						<label
							className="gutena-forms-stripe-payment__label"
							htmlFor={ `gutena-stripe-country-${ fieldId }` }
						>
							Country
						</label>
						<div className="gutena-forms-stripe-payment__select-wrap">
							<select
								id={ `gutena-stripe-country-${ fieldId }` }
								name={ `${ fieldId }_country` }
								className="gutena-forms-stripe-payment__country gutena-forms-field"
								required
							>
								{ STRIPE_COUNTRIES.map( ( country ) => (
									<option key={ country.value || 'placeholder' } value={ country.value }>
										{ country.label }
									</option>
								) ) }
							</select>
						</div>
					</div>

					{ showSubscriptionNotice && subscriptionNotice ? (
						<p className="gutena-forms-stripe-payment__subscription-notice">{ subscriptionNotice }</p>
					) : null }
				</div>
			</div>

			<p className="gutena-forms-field-error-msg gutena-forms-stripe-payment__error" />
			<input type="hidden" name={ `${ fieldId }_payment_method` } value="" />
		</>
	);
}
