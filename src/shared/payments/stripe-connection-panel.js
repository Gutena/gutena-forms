import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { stripeConnect, stripeRetryWebhook } from './api';

const StripeConnectionPanel = ( {
	paymentMode,
	connected,
	accountName,
	webhookConnected,
	webhookSlotsExceeded,
	onConnectionChange,
} ) => {
	const [ connecting, setConnecting ] = useState( false );
	const [ retrying, setRetrying ] = useState( false );

	const handleConnect = async () => {
		setConnecting( true );
		try {
			const response = await stripeConnect( paymentMode );
			window.location.href = response.redirect_url;
		} catch ( error ) {
			// eslint-disable-next-line no-alert
			window.alert( error.message || __( 'Connection failed. Please try again.', 'gutena-forms' ) );
			setConnecting( false );
		}
	};

	const handleRetryWebhook = async () => {
		setRetrying( true );
		try {
			await stripeRetryWebhook();
			onConnectionChange?.( {
				webhook_connected: true,
				webhook_slots_exceeded: false,
			} );
		} catch ( error ) {
			// eslint-disable-next-line no-alert
			window.alert( error.message || __( 'Failed to connect webhook.', 'gutena-forms' ) );
		} finally {
			setRetrying( false );
		}
	};

	return (
		<div className="gutena-forms__stripe-field-connection">
			<p className="gutena-forms__stripe-field-connection__label">
				{ __( 'Stripe Connection', 'gutena-forms' ) }
			</p>

			{ connected ? (
				<>
					<div className="gutena-forms__stripe-field-connection__status">
						<span className="gutena-forms__connection-status__check" aria-hidden="true" />
						<span>{ __( 'Stripe Connected', 'gutena-forms' ) }</span>
					</div>
					{ accountName && (
						<p className="gutena-forms__stripe-field-connection__account">{ accountName }</p>
					) }

					{ webhookSlotsExceeded ? (
						<div className="gutena-forms__webhook-error">
							<p className="gutena-forms__webhook-error__message">
								{ __( 'Gutena Forms could not create a webhook because your Stripe account has run out of free slots. Webhooks are needed to receive updates about payments.', 'gutena-forms' ) }
							</p>
							<p className="gutena-forms__webhook-error__message">
								{ __( 'Please visit Stripe Dashboard, delete an unused webhook, then click below to retry.', 'gutena-forms' ) }
							</p>
							<div className="gutena-forms__webhook-error__actions">
								<a
									className="gutena-forms__webhook-error__link"
									href="https://dashboard.stripe.com/webhooks"
									target="_blank"
									rel="noopener noreferrer"
								>
									{ __( 'Edit your Webhook', 'gutena-forms' ) }
								</a>
								<button
									type="button"
									className="gutena-forms__webhook-error__retry"
									onClick={ handleRetryWebhook }
									disabled={ retrying }
								>
									{ retrying
										? __( 'Retrying…', 'gutena-forms' )
										: __( 'Retry Webhook', 'gutena-forms' ) }
								</button>
							</div>
						</div>
					) : webhookConnected && (
						<div className="gutena-forms__stripe-field-connection__status">
							<span className="gutena-forms__connection-status__check" aria-hidden="true" />
							<span>{ __( 'Webhook Connected', 'gutena-forms' ) }</span>
						</div>
					) }
				</>
			) : (
				<>
					<button
						type="button"
						className="gutena-forms__stripe-connect__button"
						onClick={ handleConnect }
						disabled={ connecting }
					>
						{ connecting
							? __( 'Connecting…', 'gutena-forms' )
							: __( 'Connect to Stripe', 'gutena-forms' ) }
					</button>
				</>
			) }
		</div>
	);
};

export default StripeConnectionPanel;
