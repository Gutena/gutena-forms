import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { createInterpolateElement } from '@wordpress/element';
import { squareConnect, squareDisconnect } from './api';
import SquareLocationSelect from './square-location-select';

const SquareConnectionPanel = ( {
	paymentMode,
	connected,
	connectedPaymentMode,
	accountName,
	businessLocations = [],
	locationId,
	onConnectionChange,
	onLocationChange,
} ) => {
	const [ connecting, setConnecting ] = useState( false );
	const [ disconnecting, setDisconnecting ] = useState( false );

	const handleConnect = async () => {
		setConnecting( true );
		try {
			const response = await squareConnect( paymentMode );
			window.location.href = response.redirect_url;
		} catch ( error ) {
			// eslint-disable-next-line no-alert
			window.alert( error.message || __( 'Connection failed. Please try again.', 'gutena-forms' ) );
			setConnecting( false );
		}
	};

	const handleDisconnect = async () => {
		setDisconnecting( true );
		try {
			const response = await squareDisconnect();
			onConnectionChange?.( {
				connected: false,
				connected_payment_mode: 'test',
				account_name: '',
				merchant_currency: '',
				location_id: '',
				business_locations: [],
			} );
			// eslint-disable-next-line no-alert
			window.alert( response.message || __( 'Square account disconnected.', 'gutena-forms' ) );
		} catch ( error ) {
			// eslint-disable-next-line no-alert
			window.alert( error.message || __( 'Failed to disconnect Square.', 'gutena-forms' ) );
		} finally {
			setDisconnecting( false );
		}
	};

	const badgeMode = connected ? ( connectedPaymentMode || paymentMode ) : paymentMode;
	const modeBadgeLabel = 'live' === badgeMode
		? __( 'Production', 'gutena-forms' )
		: __( 'Test', 'gutena-forms' );

	return (
		<div className="gutena-forms__square-field-connection">
			<p className="gutena-forms__square-field-connection__label">
				{ __( 'Square Connection', 'gutena-forms' ) }
			</p>

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
								<span>{ accountName || __( 'Square Account', 'gutena-forms' ) }</span>
							</div>
							<button
								type="button"
								className="gutena-forms__connection-status__disconnect"
								onClick={ handleDisconnect }
								disabled={ disconnecting }
							>
								{ disconnecting
									? __( 'Disconnecting…', 'gutena-forms' )
									: __( 'Disconnect', 'gutena-forms' ) }
							</button>
						</div>
					</div>

					<SquareLocationSelect
						locations={ businessLocations }
						value={ locationId }
						onChange={ onLocationChange }
					/>
				</>
			) : (
				<>
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
										href="https://gutenaforms.com/docs/square-payments/"
										target="_blank"
										rel="noopener noreferrer"
									/>
								),
							}
						) }
					</p>
				</>
			) }
		</div>
	);
};

export default SquareConnectionPanel;
