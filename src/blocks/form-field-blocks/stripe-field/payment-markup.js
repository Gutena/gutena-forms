import { STRIPE_COUNTRIES } from '../../../shared/payments/stripe-countries';

const CardIcon = () => (
	<svg width="18" height="14" viewBox="0 0 18 14" fill="none" aria-hidden="true">
		<rect x="0.5" y="0.5" width="17" height="13" rx="2" stroke="currentColor" />
		<rect x="1" y="3.5" width="16" height="3" fill="currentColor" opacity="0.35" />
	</svg>
);

const CalendarIcon = () => (
	<svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
		<rect x="1.5" y="2.5" width="13" height="12" rx="1.5" stroke="currentColor" />
		<path d="M1.5 6.5H14.5" stroke="currentColor" />
		<path d="M5 1.5V4" stroke="currentColor" strokeLinecap="round" />
		<path d="M11 1.5V4" stroke="currentColor" strokeLinecap="round" />
	</svg>
);

const LockIcon = () => (
	<svg width="14" height="16" viewBox="0 0 14 16" fill="none" aria-hidden="true">
		<path
			d="M7 0.5C5.067 0.5 3.5 2.067 3.5 4V5.5H3C2.17157 5.5 1.5 6.17157 1.5 7V13.5C1.5 14.3284 2.17157 15 3 15H11C11.8284 15 12.5 14.3284 12.5 13.5V7C12.5 6.17157 11.8284 5.5 11 5.5H10.5V4C10.5 2.067 8.933 0.5 7 0.5ZM7 2C8.10457 2 9 2.89543 9 4V5.5H5V4C5 2.89543 5.89543 2 7 2Z"
			fill="currentColor"
		/>
	</svg>
);

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
				<div className="gutena-forms-stripe-payment__field gutena-forms-stripe-payment__field--number">
					<label
						className="gutena-forms-stripe-payment__label"
						htmlFor={ `gutena-stripe-card-number-${ fieldId }` }
					>
						Card number <span className="gutena-forms-stripe-payment__required">*</span>
					</label>
					<div className="gutena-forms-stripe-payment__input-wrap">
						<div
							id={ `gutena-stripe-card-number-${ fieldId }` }
							className="gutena-forms-stripe-payment__element gutena-forms-stripe-payment__element--number"
						/>
						<span className="gutena-forms-stripe-payment__field-icon">
							<CardIcon />
						</span>
					</div>
				</div>

				<div className="gutena-forms-stripe-payment__row">
					<div className="gutena-forms-stripe-payment__field gutena-forms-stripe-payment__field--expiry">
						<label
							className="gutena-forms-stripe-payment__label"
							htmlFor={ `gutena-stripe-card-expiry-${ fieldId }` }
						>
							Expiry date <span className="gutena-forms-stripe-payment__required">*</span>
						</label>
						<div className="gutena-forms-stripe-payment__input-wrap">
							<div
								id={ `gutena-stripe-card-expiry-${ fieldId }` }
								className="gutena-forms-stripe-payment__element gutena-forms-stripe-payment__element--expiry"
							/>
							<span className="gutena-forms-stripe-payment__field-icon">
								<CalendarIcon />
							</span>
						</div>
					</div>

					<div className="gutena-forms-stripe-payment__field gutena-forms-stripe-payment__field--cvc">
						<label
							className="gutena-forms-stripe-payment__label"
							htmlFor={ `gutena-stripe-card-cvc-${ fieldId }` }
						>
							Security code <span className="gutena-forms-stripe-payment__required">*</span>
						</label>
						<div className="gutena-forms-stripe-payment__input-wrap">
							<div
								id={ `gutena-stripe-card-cvc-${ fieldId }` }
								className="gutena-forms-stripe-payment__element gutena-forms-stripe-payment__element--cvc"
							/>
							<span className="gutena-forms-stripe-payment__field-icon">
								<LockIcon />
							</span>
						</div>
					</div>
				</div>

				<div className="gutena-forms-stripe-payment__field gutena-forms-stripe-payment__field--country">
					<label
						className="gutena-forms-stripe-payment__label"
						htmlFor={ `gutena-stripe-country-${ fieldId }` }
					>
						Country <span className="gutena-forms-stripe-payment__required">*</span>
					</label>
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

				{ showSubscriptionNotice && subscriptionNotice ? (
					<p className="gutena-forms-stripe-payment__subscription-notice">{ subscriptionNotice }</p>
				) : null }
			</div>

			<p className="gutena-forms-field-error-msg gutena-forms-stripe-payment__error" />
			<input type="hidden" name={ `${ fieldId }_payment_method` } value="" />
		</>
	);
}
