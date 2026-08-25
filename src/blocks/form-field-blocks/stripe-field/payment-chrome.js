/**
 * Shared card-tab + Link banner chrome for Stripe payment field markup.
 */

const TabCardIcon = () => (
	<svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
		<rect x="1" y="3.5" width="14" height="9" rx="1.5" stroke="currentColor" strokeWidth="1.25" />
		<path d="M1 6.5H15" stroke="currentColor" strokeWidth="1.25" />
	</svg>
);

const LinkLockIcon = () => (
	<svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
		<path
			d="M4.5 7V5.25C4.5 3.45507 5.95507 2 7.75 2H8.25C10.0449 2 11.5 3.45507 11.5 5.25V7M3.75 7H12.25C12.9404 7 13.5 7.55964 13.5 8.25V12.75C13.5 13.4404 12.9404 14 12.25 14H3.75C3.05964 14 2.5 13.4404 2.5 12.75V8.25C2.5 7.55964 3.05964 7 3.75 7Z"
			stroke="currentColor"
			strokeWidth="1.25"
			strokeLinecap="round"
			strokeLinejoin="round"
		/>
	</svg>
);

export function StripePaymentChrome() {
	return (
		<>
			<div className="gutena-forms-stripe-payment__tabs" role="tablist" aria-label="Payment method">
				<div
					className="gutena-forms-stripe-payment__tab is-active"
					role="tab"
					aria-selected="true"
					tabIndex={ 0 }
				>
					<span className="gutena-forms-stripe-payment__tab-icon">
						<TabCardIcon />
					</span>
					<span className="gutena-forms-stripe-payment__tab-label">Card</span>
				</div>
			</div>

			<div className="gutena-forms-stripe-payment__link-banner" aria-hidden="true">
				<span className="gutena-forms-stripe-payment__link-icon">
					<LinkLockIcon />
				</span>
				<span className="gutena-forms-stripe-payment__link-text">Secure, fast checkout with Link</span>
				<span className="gutena-forms-stripe-payment__link-chevron" aria-hidden="true">
					<svg width="10" height="6" viewBox="0 0 10 6" fill="none">
						<path d="M1 1L5 5L9 1" stroke="currentColor" strokeWidth="1.25" strokeLinecap="round" strokeLinejoin="round" />
					</svg>
				</span>
			</div>
		</>
	);
}
