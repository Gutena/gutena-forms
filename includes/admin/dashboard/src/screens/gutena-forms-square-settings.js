import { __ } from '@wordpress/i18n';
import { useEffect, useState } from '@wordpress/element';
import { createInterpolateElement } from '@wordpress/element';
import { toast } from 'react-toastify';
import SquareIcon from '../icons/square';
import PaymentModeToggle from '../components/payments/payment-mode-toggle';
import SquareMerchantCurrency from '../components/payments/square-merchant-currency';
import SquareLocationSelect from '../components/payments/square-location-select';
import SquareTestModeModal from '../components/payments/square-test-mode-modal';
import PaymentFeeBanner from '../components/payments/payment-fee-banner';
import { gutenaFormsFetchSettings, gutenaFormsUpdateSettings } from '../api';
import {
	squareConnect,
	squareDisconnect,
	fetchSquareConnectNotice,
	fetchSquareConnectionStatus,
} from '../api/payments';
import SettingsLoading from '../skeletons/settings-loading';

const GutenaFormsSquareSettings = () => {
	const [ loading, setLoading ] = useState( true );
	const [ connecting, setConnecting ] = useState( false );
	const [ paymentMode, setPaymentMode ] = useState( 'test' );
	const [ connectedPaymentMode, setConnectedPaymentMode ] = useState( 'test' );
	const [ connected, setConnected ] = useState( false );
	const [ accountName, setAccountName ] = useState( '' );
	const [ merchantCurrency, setMerchantCurrency ] = useState( '' );
	const [ locationId, setLocationId ] = useState( '' );
	const [ businessLocations, setBusinessLocations ] = useState( [] );
	const [ showTestModal, setShowTestModal ] = useState( false );
	const [ pendingConnect, setPendingConnect ] = useState( false );

	const applySettingsValues = ( values ) => {
		setPaymentMode( values.payment_mode || 'test' );
		setConnectedPaymentMode( values.connected_payment_mode || values.payment_mode || 'test' );
		setConnected( !! values.connected );
		setAccountName( values.account_name || '' );
		setMerchantCurrency( values.merchant_currency || '' );
		setLocationId( values.location_id || '' );
		setBusinessLocations( Array.isArray( values.business_locations ) ? values.business_locations : [] );
	};

	const loadSettings = () => {
		return Promise.all( [
			gutenaFormsFetchSettings( 'square' ),
			fetchSquareConnectionStatus(),
		] ).then( ( [ settings, status ] ) => {
			const values = {
				...( settings?.values || {} ),
				...( status || {} ),
			};

			applySettingsValues( values );
		} );
	};

	useEffect( () => {
		setLoading( true );

		Promise.all( [
			loadSettings(),
			fetchSquareConnectNotice(),
		] )
			.then( ( [ , notice ] ) => {
				if ( notice?.message ) {
					if ( 'success' === notice.type ) {
						return loadSettings().then( () => {
							toast.success( notice.message );
						} );
					}

					toast.error( notice.message );
				}
			} )
			.catch( () => {} )
			.finally( () => {
				setLoading( false );
			} );
	}, [] );

	const handleSave = () => {
		gutenaFormsUpdateSettings( 'square', {
			payment_mode: paymentMode,
			location_id: locationId,
		} ).then( () => {
			toast.success( __( 'Settings updated successfully.', 'gutena-forms' ) );
		} );
	};

	const startConnect = () => {
		setConnecting( true );

		squareConnect( paymentMode )
			.then( ( response ) => {
				window.location.href = response.redirect_url;
			} )
			.catch( ( error ) => {
				toast.error( error.message || __( 'Connection failed. Please try again.', 'gutena-forms' ) );
				setConnecting( false );
			} );
	};

	const handleConnect = () => {
		if ( 'test' === paymentMode ) {
			setPendingConnect( true );
			setShowTestModal( true );
			return;
		}

		startConnect();
	};

	const handlePaymentModeChange = ( nextMode ) => {
		if ( 'test' === nextMode && 'test' === paymentMode ) {
			setPendingConnect( false );
			setShowTestModal( true );
			return;
		}

		setPaymentMode( nextMode );
	};

	const handleTestModalContinue = () => {
		setShowTestModal( false );

		if ( pendingConnect ) {
			setPendingConnect( false );
			startConnect();
		}
	};

	const handleTestModalClose = () => {
		setShowTestModal( false );
		setPendingConnect( false );
	};

	const handleDisconnect = () => {
		squareDisconnect()
			.then( ( response ) => {
				setConnected( false );
				setConnectedPaymentMode( 'test' );
				setAccountName( '' );
				setMerchantCurrency( '' );
				setLocationId( '' );
				setBusinessLocations( [] );
				toast.success( response.message );
			} )
			.catch( ( error ) => {
				toast.error( error.message );
			} );
	};

	if ( loading ) {
		return <SettingsLoading />;
	}

	const modeBadgeLabel = 'live' === ( connected ? connectedPaymentMode : paymentMode )
		? __( 'Live Mode', 'gutena-forms' )
		: __( 'Test Mode', 'gutena-forms' );
	const badgeMode = connected ? connectedPaymentMode : paymentMode;

	return (
		<div className="gutena-forms__square-settings">
			<div className="gutena-forms__square-settings__header">
				<SquareIcon size={ 28 } />
				<div>
					<h1>{ __( 'Square Account Settings', 'gutena-forms' ) }</h1>
					<p>{ __( 'Connect your Square account to start accepting payments through your forms.', 'gutena-forms' ) }</p>
				</div>
			</div>

			<div className="gutena-forms__square-settings__card">
				<div className="gutena-forms__square-settings__body">
					<PaymentModeToggle
						value={ paymentMode }
						onChange={ handlePaymentModeChange }
					/>

					<SquareMerchantCurrency
						connected={ connected }
						merchantCurrency={ merchantCurrency }
					/>

					{ connected ? (
						<>
							<div className="gutena-forms__connection-status">
								<div className="gutena-forms__connection-status__heading">
									<p>{ __( 'Connection Status', 'gutena-forms' ) }</p>
									<span className={ `gutena-forms__connection-status__badge${ 'live' === badgeMode ? ' is-live' : '' }` }>
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

							<SquareLocationSelect
								locations={ businessLocations }
								value={ locationId }
								onChange={ setLocationId }
							/>
						</>
					) : (
						<div className="gutena-forms__square-connect">
							<button
								type="button"
								className="gutena-forms__square-connect__button"
								onClick={ handleConnect }
								disabled={ connecting }
							>
								{ connecting
									? __( 'Connecting…', 'gutena-forms' )
									: __( 'Connect to Square', 'gutena-forms' ) }
							</button>
							<p className="gutena-forms__square-connect__help">
								{ createInterpolateElement(
									__( 'Securely connect to Square with just a few clicks to begin accepting payments! <link>Learn More</link>', 'gutena-forms' ),
									{
										link: (
											<a
												href="https://gutenaforms.com/docs/payments/square/"
												target="_blank"
												rel="noopener noreferrer"
											/>
										),
									}
								) }
							</p>
						</div>
					) }

					<PaymentFeeBanner />

					<button
						type="button"
						className="gutena-forms__square-settings__save"
						onClick={ handleSave }
					>
						{ __( 'Save Changes', 'gutena-forms' ) }
					</button>
				</div>
			</div>

			<SquareTestModeModal
				isOpen={ showTestModal }
				onClose={ handleTestModalClose }
				onContinue={ handleTestModalContinue }
			/>
		</div>
	);
};

export default GutenaFormsSquareSettings;
