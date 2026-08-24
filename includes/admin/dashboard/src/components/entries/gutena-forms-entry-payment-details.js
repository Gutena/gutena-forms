import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { Link } from 'react-router';
import GutenaFormsEntryPaymentRefundModal from './gutena-forms-entry-payment-refund-modal';
import GutenaFormsProBadge from '../gutena-forms-pro-badge';
import PaymentModeBadge from '../payments/payment-mode-badge';
import PaymentGatewayIcon from '../payments/payment-gateway-icon';
import PaymentRefundButton from '../payments/payment-refund-button';
import ExternalLink from '../../icons/external-link';

const formatPaymentType = ( type, payment ) => {
	if ( 'subscription' === type ) {
		return __( 'Subscription', 'gutena-forms' );
	}
	if ( 'one_time' === type ) {
		return __( 'One Time', 'gutena-forms' );
	}
	return payment?.payment_type_label || type;
};

const GutenaFormsEntryPaymentDetails = ( {
	entryId,
	payment,
	onPaymentUpdated,
	showProPopupHandler,
} ) => {
	const hasPro = ! ! ( typeof gutenaFormsAdmin !== 'undefined' && gutenaFormsAdmin.hasPro );
	const [ refundOpen, setRefundOpen ] = useState( false );

	if ( ! payment?.has_payment ) {
		return null;
	}

	const handleRefundClick = () => {
		if ( ! hasPro ) {
			showProPopupHandler();
			return;
		}
		setRefundOpen( true );
	};

	const statusClass = `gutena-forms__payment-status is-${ payment.status }`;
	const gatewayLabel = payment.gateway_label || payment.payment_method || __( 'Stripe', 'gutena-forms' );

	return (
		<div className="gutena-forms__entry-payment-details">
			<div className="gutena-forms__entry-payment-layout">
				<div className="gutena-froms__entry-meta-box gutena-forms__entry-payment-card">
					<h3 className="heading">{ __( 'Billing Details', 'gutena-forms' ) }</h3>
					<div className="gutena-forms__entry-payment-table">
						<div className="gutena-forms__entry-payment-table__head">
							<span>{ __( 'Amount', 'gutena-forms' ) }</span>
							<span>{ __( 'Status', 'gutena-forms' ) }</span>
							<span>{ __( 'Transaction Date', 'gutena-forms' ) }</span>
							<span>{ __( 'Action', 'gutena-forms' ) }</span>
						</div>
						<div className="gutena-forms__entry-payment-table__row">
							<span className="gutena-forms__entry-payment-table__amount">{ payment.amount_formatted }</span>
							<span>
								<span className={ statusClass }>{ payment.status_label }</span>
							</span>
							<span>{ payment.transaction_date || '—' }</span>
							<span>
								<PaymentRefundButton
									disabled={ ! payment.can_refund }
									showProBadge={ payment.can_refund && ! hasPro }
									onClick={ handleRefundClick }
								/>
							</span>
						</div>
					</div>
				</div>

				<div className="gutena-froms__entry-meta-box gutena-forms__entry-payment-card">
					<div className="gutena-forms__entry-payment-section__header">
						<h3 className="heading">{ __( 'Payment Information', 'gutena-forms' ) }</h3>
						{ payment.stripe_dashboard_url && (
							<a
								className="gutena-forms__payment-stripe-link"
								href={ payment.stripe_dashboard_url }
								target="_blank"
								rel="noopener noreferrer"
							>
								{ __( 'View in Stripe', 'gutena-forms' ) }
								<ExternalLink />
							</a>
						) }
					</div>
					<div className="gutena-forms__entry-data">
						<div className="gutena-forms__entry-data-row">
							<div className="label">{ __( 'Payment ID', 'gutena-forms' ) }</div>
							<div className="value">{ payment.payment_id || '—' }</div>
						</div>
						<div className="gutena-forms__entry-data-row">
							<div className="label">{ __( 'Form Name', 'gutena-forms' ) }</div>
							<div className="value">
								{ payment.form_id ? (
									<Link
										className="gutena-forms__entry-payment-form-link"
										to={ `/settings/entries/${ payment.form_id }` }
									>
										{ payment.form_name }
										<ExternalLink />
									</Link>
								) : (
									payment.form_name
								) }
							</div>
						</div>
						<div className="gutena-forms__entry-data-row">
							<div className="label">{ __( 'Payment Mode', 'gutena-forms' ) }</div>
							<div className="value">
								<PaymentModeBadge mode={ payment.payment_mode } />
							</div>
						</div>
						<div className="gutena-forms__entry-data-row">
							<div className="label">{ __( 'Payment Method', 'gutena-forms' ) }</div>
							<div className="value gutena-forms__entry-payment-method">
								<span className="gutena-forms__entry-payment-method__icon">
									<PaymentGatewayIcon gateway={ payment.gateway } />
								</span>
								<span>{ gatewayLabel }</span>
							</div>
						</div>
						<div className="gutena-forms__entry-data-row">
							<div className="label">{ __( 'Payment Type', 'gutena-forms' ) }</div>
							<div className="value gutena-forms__entry-payment-type-value">
								<span>{ formatPaymentType( payment.payment_type, payment ) }</span>
								{ payment.is_subscription && ! hasPro && <GutenaFormsProBadge /> }
							</div>
						</div>
						<div className="gutena-forms__entry-data-row">
							<div className="label">{ __( 'Transaction ID', 'gutena-forms' ) }</div>
							<div className="value gutena-forms__entry-payment-transaction-id">{ payment.transaction_id || '—' }</div>
						</div>
						<div className="gutena-forms__entry-data-row">
							<div className="label">{ __( 'Customer Name', 'gutena-forms' ) }</div>
							<div className="value">{ payment.customer_name || '—' }</div>
						</div>
						<div className="gutena-forms__entry-data-row">
							<div className="label">{ __( 'Email', 'gutena-forms' ) }</div>
							<div className="value">{ payment.customer_email || '—' }</div>
						</div>
						<div className="gutena-forms__entry-data-row">
							<div className="label">{ __( 'Received On', 'gutena-forms' ) }</div>
							<div className="value">{ payment.received_on || '—' }</div>
						</div>
					</div>
				</div>
			</div>

			{ hasPro && (
				<GutenaFormsEntryPaymentRefundModal
					isOpen={ refundOpen }
					onClose={ () => setRefundOpen( false ) }
					entryId={ entryId }
					payment={ payment }
					onRefunded={ onPaymentUpdated }
				/>
			) }
		</div>
	);
};

export default GutenaFormsEntryPaymentDetails;
