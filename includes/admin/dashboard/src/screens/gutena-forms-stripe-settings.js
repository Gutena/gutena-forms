import { __ } from '@wordpress/i18n';

import { useEffect, useState } from '@wordpress/element';

import { createInterpolateElement } from '@wordpress/element';

import { toast } from 'react-toastify';

import StripeIcon from '../icons/stripe';

import PaymentModeToggle from '../components/payments/payment-mode-toggle';

import CurrencySelect from '../components/payments/currency-select';

import CurrencySignPosition from '../components/payments/currency-sign-position';

import { gutenaFormsFetchSettings, gutenaFormsUpdateSettings } from '../api';

import {

	stripeConnect,

	stripeDisconnect,

	stripeRetryWebhook,

	fetchStripeConnectNotice,

} from '../api/payments';

import SettingsLoading from '../skeletons/settings-loading';



const GutenaFormsStripeSettings = () => {

	const [ loading, setLoading ] = useState( true );

	const [ connecting, setConnecting ] = useState( false );

	const [ paymentMode, setPaymentMode ] = useState( 'test' );

	const [ currency, setCurrency ] = useState( 'USD' );

	const [ currencySignPosition, setCurrencySignPosition ] = useState( 'left' );

	const [ connected, setConnected ] = useState( false );

	const [ accountName, setAccountName ] = useState( '' );

	const [ webhookConnected, setWebhookConnected ] = useState( false );

	const [ webhookSlotsExceeded, setWebhookSlotsExceeded ] = useState( false );

	const [ stripeDashboardUrl, setStripeDashboardUrl ] = useState( 'https://dashboard.stripe.com/webhooks' );

	const [ publishableKeyTest, setPublishableKeyTest ] = useState( '' );

	const [ publishableKeyLive, setPublishableKeyLive ] = useState( '' );



	const applySettingsValues = ( values ) => {

		setPaymentMode( values.payment_mode || 'test' );

		setCurrency( values.currency || 'USD' );

		setCurrencySignPosition( values.currency_sign_position || 'left' );

		setConnected( !! values.connected );

		setAccountName( values.account_name || '' );

		setWebhookConnected( !! values.webhook_connected );

		setWebhookSlotsExceeded( !! values.webhook_slots_exceeded );

		if ( values.stripe_dashboard_url ) {

			setStripeDashboardUrl( values.stripe_dashboard_url );

		}

		setPublishableKeyTest( values.publishable_key_test || '' );

		setPublishableKeyLive( values.publishable_key_live || '' );

	};



	const loadSettings = () => {

		return gutenaFormsFetchSettings( 'stripe' ).then( ( settings ) => {

			applySettingsValues( settings?.values || {} );

		} );

	};



	useEffect( () => {

		setLoading( true );

		Promise.all( [

			loadSettings(),

			fetchStripeConnectNotice(),

		] )

			.then( ( [ , notice ] ) => {

				if ( notice?.message ) {

					if ( 'success' === notice.type ) {

						toast.success( notice.message );

					} else {

						toast.error( notice.message );

					}

				}

			} )

			.catch( () => {} )

			.finally( () => {

				setLoading( false );

			} );

	}, [] );



	const handleSave = () => {

		gutenaFormsUpdateSettings( 'stripe', {

			payment_mode: paymentMode,

			currency,

			currency_sign_position: currencySignPosition,

			publishable_key_test: publishableKeyTest,

			publishable_key_live: publishableKeyLive,

		} ).then( () => {

			toast.success( __( 'Settings updated successfully.', 'gutena-forms' ) );

		} );

	};



	const handleConnect = () => {

		setConnecting( true );

		stripeConnect( paymentMode )

			.then( ( response ) => {

				window.location.href = response.redirect_url;

			} )

			.catch( ( error ) => {

				toast.error( error.message || __( 'Connection failed. Please try again.', 'gutena-forms' ) );

				setConnecting( false );

			} );

	};



	const handleDisconnect = () => {

		stripeDisconnect()

			.then( ( response ) => {

				setConnected( false );

				setAccountName( '' );

				setWebhookConnected( false );

				setWebhookSlotsExceeded( false );

				toast.success( response.message );

			} )

			.catch( ( error ) => {

				toast.error( error.message );

			} );

	};



	const handleRetryWebhook = () => {

		stripeRetryWebhook()

			.then( ( response ) => {

				setWebhookConnected( true );

				setWebhookSlotsExceeded( false );

				toast.success( response.message );

			} )

			.catch( ( error ) => {

				toast.error( error.message );

			} );

	};



	if ( loading ) {

		return <SettingsLoading />;

	}



	const modeBadgeLabel = 'live' === paymentMode

		? __( 'Live Mode', 'gutena-forms' )

		: __( 'Test Mode', 'gutena-forms' );



	return (

		<div className="gutena-forms__stripe-settings">

			<div className="gutena-forms__stripe-settings__header">

				<StripeIcon />

				<div>

					<h1>{ __( 'Stripe Account Settings', 'gutena-forms' ) }</h1>

					<p>{ __( 'Connect your Stripe account to start accepting payments through your forms.', 'gutena-forms' ) }</p>

				</div>

			</div>



			<div className="gutena-forms__stripe-settings__card">

				<div className="gutena-forms__stripe-settings__body">

					<PaymentModeToggle

						value={ paymentMode }

						onChange={ setPaymentMode }

					/>



					<CurrencySelect

						value={ currency }

						onChange={ setCurrency }

					/>



					<CurrencySignPosition

						value={ currencySignPosition }

						onChange={ setCurrencySignPosition }

					/>

					<div className="gutena-forms__stripe-settings__publishable-key">

						<label htmlFor="gutena-forms-stripe-publishable-key">

							{ 'live' === paymentMode

								? __( 'Publishable key (Live)', 'gutena-forms' )

								: __( 'Publishable key (Test)', 'gutena-forms' ) }

						</label>

						<input

							id="gutena-forms-stripe-publishable-key"

							type="text"

							className="gutena-forms__stripe-settings__input"

							placeholder="pk_test_..."

							value={ 'live' === paymentMode ? publishableKeyLive : publishableKeyTest }

							onChange={ ( event ) => {

								if ( 'live' === paymentMode ) {

									setPublishableKeyLive( event.target.value );

									return;

								}

								setPublishableKeyTest( event.target.value );

							} }

							autoComplete="off"

							spellCheck={ false }

						/>

						<p className="gutena-forms__stripe-settings__help">

							{ __( 'Copy this from your connected Stripe account: Dashboard → Developers → API keys. Required for the payment form on the frontend.', 'gutena-forms' ) }

						</p>

					</div>

					{ connected ? (

						<>

							<div className="gutena-forms__connection-status">

								<div className="gutena-forms__connection-status__heading">

									<p>{ __( 'Connection Status', 'gutena-forms' ) }</p>

									<span className="gutena-forms__connection-status__badge">

										{ modeBadgeLabel }

									</span>

								</div>

								<div className="gutena-forms__connection-status__row">

									<div className="gutena-forms__connection-status__account">

										<span className="gutena-forms__connection-status__check" aria-hidden="true" />

										<span>{ accountName || __( 'Account Name', 'gutena-forms' ) }</span>

									</div>

									<button

										type="button"

										className="gutena-forms__connection-status__disconnect"

										onClick={ handleDisconnect }

									>

										{ __( 'Disconnect', 'gutena-forms' ) }

									</button>

								</div>

							</div>



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

											href={ stripeDashboardUrl }

											target="_blank"

											rel="noopener noreferrer"

										>

											{ __( 'Edit your Webhook', 'gutena-forms' ) }

										</a>

										<button

											type="button"

											className="gutena-forms__webhook-error__retry"

											onClick={ handleRetryWebhook }

										>

											{ __( 'Retry Webhook', 'gutena-forms' ) }

										</button>

									</div>

								</div>

							) : webhookConnected && (

								<div className="gutena-forms__webhook-status">

									<p className="gutena-forms__webhook-status__label">{ __( 'Webhook', 'gutena-forms' ) }</p>

									<div className="gutena-forms__webhook-status__message">

										<span className="gutena-forms__connection-status__check" aria-hidden="true" />

										<span>{ __( 'Webhook successfully connected, all Stripe events are being tracked.', 'gutena-forms' ) }</span>

									</div>

								</div>

							) }

						</>

					) : (

						<div className="gutena-forms__stripe-connect">

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

							<p className="gutena-forms__stripe-connect__help">

								{ createInterpolateElement(

									__( 'Securely connect to Stripe with just a few clicks to begin accepting payments! <link>Learn More</link>', 'gutena-forms' ),

									{

										link: (

											<a

												href="https://gutenaforms.com/docs/payments/stripe/"

												target="_blank"

												rel="noopener noreferrer"

											/>

										),

									}

								) }

							</p>

						</div>

					) }



					<button

						type="button"

						className="gutena-forms__stripe-settings__save"

						onClick={ handleSave }

					>

						{ __( 'Save Changes', 'gutena-forms' ) }

					</button>

				</div>

			</div>

		</div>

	);

};



export default GutenaFormsStripeSettings;


