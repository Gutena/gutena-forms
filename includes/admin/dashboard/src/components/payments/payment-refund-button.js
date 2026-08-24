import { __ } from '@wordpress/i18n';
import RefundIcon from '../../icons/refund';
import GutenaFormsProBadge from '../gutena-forms-pro-badge';

const PaymentRefundButton = ( { disabled = false, showProBadge = false, onClick } ) => {
	if ( disabled ) {
		return (
			<span className="gutena-forms__payment-refund-btn is-disabled" aria-disabled="true">
				<RefundIcon color="#9AA0A4" />
				<span>{ __( 'Refund', 'gutena-forms' ) }</span>
			</span>
		);
	}

	return (
		<button type="button" className="gutena-forms__payment-refund-btn" onClick={ onClick }>
			<RefundIcon />
			<span>{ __( 'Refund', 'gutena-forms' ) }</span>
			{ showProBadge && <GutenaFormsProBadge /> }
		</button>
	);
};

export default PaymentRefundButton;
