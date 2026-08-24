import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import { Link } from 'react-router';
import PaymentsOnboardingIllustration from '../../icons/PaymentsOnboardingIllustration';

const features = [
	__( 'Collect payments for services, bookings, or donations.', 'gutena-forms' ),
	__( 'Accept one-time payments or recurring subscriptions.', 'gutena-forms' ),
	__( 'Lightweight setup. No more plugin required.', 'gutena-forms' ),
];

const GutenaFormsPaymentsOnboarding = () => {
	return (
		<div className="gutena-forms__payments-onboarding">
			<div className="gutena-forms__payments-onboarding__illustration">
				<PaymentsOnboardingIllustration />
			</div>

			<div className="gutena-forms__payments-onboarding__content">
				<h2>{ __( 'Accept Payments with Gutena Forms', 'gutena-forms' ) }</h2>
				<p>{ __( 'Enable payment gateways and start accepting payments directly through your Gutena Forms.', 'gutena-forms' ) }</p>

				<ul className="gutena-forms__payments-onboarding__features">
					{ features.map( ( feature ) => (
						<li key={ feature }>{ feature }</li>
					) ) }
				</ul>

				<Link to="/settings/settings/payment-methods">
					<Button
						className="gutena-forms__payments-onboarding__button"
						variant="primary"
					>
						{ __( 'Connect Payment Gateway Now', 'gutena-forms' ) }
						<span aria-hidden="true">→</span>
					</Button>
				</Link>
			</div>
		</div>
	);
};

export default GutenaFormsPaymentsOnboarding;
