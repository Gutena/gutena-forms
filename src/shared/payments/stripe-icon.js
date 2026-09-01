/**
 * Stripe brand icon used in the block editor and admin payment UI.
 */
import stripeIconUrl from './stripe-icon.png';

const StripeIcon = ( { size = 28 } ) => (
	<img
		src={ stripeIconUrl }
		width={ size }
		height={ size }
		alt=""
		aria-hidden="true"
		className="gutena-forms-stripe-icon"
		style={ {
			display: 'block',
			borderRadius: '50%',
			objectFit: 'cover',
		} }
	/>
);

export default StripeIcon;
