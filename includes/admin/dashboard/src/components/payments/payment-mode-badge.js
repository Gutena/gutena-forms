import { __ } from '@wordpress/i18n';

const PaymentModeBadge = ( { mode = 'test' } ) => {
	const isLive = 'live' === mode;

	return (
		<span className={ `gutena-forms__payment-mode-badge${ isLive ? ' is-live' : ' is-test' }` }>
			{ isLive ? __( 'Live Mode', 'gutena-forms' ) : __( 'Test Mode', 'gutena-forms' ) }
		</span>
	);
};

export default PaymentModeBadge;
