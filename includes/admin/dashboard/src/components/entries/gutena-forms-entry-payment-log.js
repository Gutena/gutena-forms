import { __ } from '@wordpress/i18n';
import PaymentModeBadge from '../payments/payment-mode-badge';
import PaymentGatewayIcon from '../payments/payment-gateway-icon';

const formatLogStatus = ( status ) => {
	if ( ! status ) {
		return '—';
	}
	return status;
};

const getLogStatusClass = ( status ) => {
	const normalized = String( status || '' ).toLowerCase();
	if ( normalized.includes( 'success' ) ) {
		return 'succeeded';
	}
	if ( normalized.includes( 'fail' ) ) {
		return 'failed';
	}
	if ( normalized.includes( 'refund' ) ) {
		return 'refunded';
	}
	if ( normalized.includes( 'pending' ) || normalized.includes( 'process' ) ) {
		return 'pending';
	}
	return 'pending';
};

const GutenaFormsEntryPaymentLog = ( { payment } ) => {
	if ( ! payment?.has_payment ) {
		return null;
	}

	return (
		<div className="gutena-froms__entry-meta-box gutena-forms__entry-payment-log-card">
			<h3 className="heading">{ __( 'Payment Log', 'gutena-forms' ) }</h3>
			{ payment.logs?.length ? (
				<div className="gutena-forms__entry-payment-log-wrap">
					<div className="gutena-forms__entry-payment-log">
						<div className="gutena-forms__entry-payment-log__head">
							<span>{ __( 'Event', 'gutena-forms' ) }</span>
							<span>{ __( 'Transaction ID', 'gutena-forms' ) }</span>
							<span>{ __( 'Gateway', 'gutena-forms' ) }</span>
							<span>{ __( 'Amount', 'gutena-forms' ) }</span>
							<span>{ __( 'Status', 'gutena-forms' ) }</span>
							<span>{ __( 'User ID', 'gutena-forms' ) }</span>
							<span>{ __( 'Mode', 'gutena-forms' ) }</span>
						</div>
						{ payment.logs.map( ( log, index ) => (
							<div key={ index } className="gutena-forms__entry-payment-log__row">
								<span className="gutena-forms__entry-payment-log__event">
									{ log.event === 'payment_verification'
										? __( 'Payment Verification', 'gutena-forms' )
										: log.event === 'payment_failed'
											? __( 'Payment Failed', 'gutena-forms' )
											: log.event }
								</span>
								<span className="gutena-forms__entry-payment-log__transaction">{ log.transaction_id || '—' }</span>
								<span className="gutena-forms__entry-payment-log__gateway">
									<span className="gutena-forms__entry-payment-log__gateway-icon">
										<PaymentGatewayIcon gateway={ log.gateway || 'stripe' } size={ 16 } />
									</span>
									<span>{ log.gateway ? log.gateway.charAt( 0 ).toUpperCase() + log.gateway.slice( 1 ) : 'Stripe' }</span>
								</span>
								<span className="gutena-forms__entry-payment-log__amount">{ log.amount || '—' }</span>
								<span>
									{ log.status ? (
										<span className={ `gutena-forms__payment-status is-${ getLogStatusClass( log.status ) }` }>
											{ formatLogStatus( log.status ) }
										</span>
									) : (
										'—'
									) }
								</span>
								<span>{ log.user_id || '—' }</span>
								<span>
									<PaymentModeBadge mode={ log.mode } />
								</span>
							</div>
						) ) }
					</div>
				</div>
			) : (
				<p className="gutena-forms__entry-payment-log-empty">{ __( 'No payment log entries yet.', 'gutena-forms' ) }</p>
			) }
		</div>
	);
};

export default GutenaFormsEntryPaymentLog;
