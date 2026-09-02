import { __ } from '@wordpress/i18n';
import { useEffect, useMemo, useState } from '@wordpress/element';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { useDispatch, useSelect } from '@wordpress/data';
import { store as blockEditorStore } from '@wordpress/block-editor';
import {
	PanelBody,
	TextControl,
	SelectControl,
	RadioControl,
	Notice,
} from '@wordpress/components';
import PaymentModeToggle from '../../../shared/payments/payment-mode-toggle';
import SquareMerchantCurrency from '../../../shared/payments/square-merchant-currency';
import SquareConnectionPanel from '../../../shared/payments/square-connection-panel';
import SquareTestModeModal from '../../../shared/payments/square-test-mode-modal';
import SquareFieldIcon from './icon';
import { fetchSquareSettings, fetchSquareConnectionStatus, squareConnect } from '../../../shared/payments/api';
import { formatCurrencyAmount, formatSubscriptionSummary, isGutenaFormsPro } from '../../../shared/payments/subscription-summary';
import { gfIsEmpty } from '../../../shared/utils/helper';
import {
	buildFormSquareOverride,
	getAmountFieldOptions,
	getEmailFieldOptions,
	getFormClientId,
	getGlobalSquareDefaults,
	getSingleEmailFieldNameAttr,
	getTextFieldOptions,
	isSquareGatewayExplicitlyDisabled,
	resolveEffectiveSquareSettings,
	validateSquareFieldAttributes,
} from '../../../shared/payments/square-field-utils';

const BILLING_INTERVALS = [
	{ label: __( 'Daily', 'gutena-forms' ), value: 'daily' },
	{ label: __( 'Weekly', 'gutena-forms' ), value: 'weekly' },
	{ label: __( 'Monthly', 'gutena-forms' ), value: 'monthly' },
	{ label: __( 'Quarterly', 'gutena-forms' ), value: 'quarterly' },
	{ label: __( 'Yearly', 'gutena-forms' ), value: 'yearly' },
];

const BILLING_CYCLES = [
	{ label: __( '2 Payments', 'gutena-forms' ), value: '2' },
	{ label: __( '3 Payments', 'gutena-forms' ), value: '3' },
	{ label: __( '4 Payments', 'gutena-forms' ), value: '4' },
	{ label: __( '5 Payments', 'gutena-forms' ), value: '5' },
	{ label: __( 'Never', 'gutena-forms' ), value: 'never' },
	{ label: __( 'Custom', 'gutena-forms' ), value: 'custom' },
];

const FieldError = ( { show, message } ) =>
	show ? (
		<p className="gutena-forms-square-field__error">{ message }</p>
	) : null;

const PreviewWarningIcon = () => (
	<svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
		<path
			d="M8 1.5L15 14H1L8 1.5Z"
			stroke="#C51919"
			strokeWidth="1.25"
			strokeLinejoin="round"
		/>
		<path d="M8 6V9" stroke="#C51919" strokeWidth="1.25" strokeLinecap="round" />
		<circle cx="8" cy="11.25" r="0.75" fill="#C51919" />
	</svg>
);

export default function Edit( { attributes, setAttributes, clientId, isSelected } ) {
	const {
		fieldName,
		paymentType,
		amountType,
		fixedAmount,
		variableAmountField,
		minimumAmount,
		customerEmailField,
		customerNameField,
		subscriptionPlanName,
		billingInterval,
		billingCycles,
		customBillingCycles,
	} = attributes;

	const formClientId = getFormClientId( clientId );
	const globalDefaults = getGlobalSquareDefaults();

	const paymentSquare = useSelect(
		( select ) =>
			formClientId
				? select( blockEditorStore ).getBlockAttributes( formClientId )?.paymentSquare
				: null,
		[ formClientId ]
	);

	const { updateBlockAttributes } = useDispatch( blockEditorStore );
	const effectiveSquare = useMemo(
		() => resolveEffectiveSquareSettings( paymentSquare ),
		[ paymentSquare ]
	);

	const [ validationErrors, setValidationErrors ] = useState( [] );
	const [ showTestModal, setShowTestModal ] = useState( false );
	const [ pendingConnect, setPendingConnect ] = useState( false );
	const [ connecting, setConnecting ] = useState( false );

	useEffect( () => {
		if ( ! formClientId ) {
			return;
		}

		if ( ! paymentSquare?.enable ) {
			updateSquareSettings( { enable: true } );
		}
	}, [ formClientId ] );

	useEffect( () => {
		if ( ! formClientId ) {
			return;
		}

		Promise.all( [ fetchSquareSettings(), fetchSquareConnectionStatus() ] )
			.then( ( [ values, status ] ) => {
				const merged = {
					...( values || {} ),
					...( status || {} ),
				};

				if ( ! merged || Object.keys( merged ).length === 0 ) {
					return;
				}

				const usesGlobal =
					! paymentSquare ||
					false !== paymentSquare.defaultSettings;

				if ( ! usesGlobal ) {
					return;
				}

				updateBlockAttributes( formClientId, {
					paymentSquare: {
						enable: true,
						payment_mode: merged.payment_mode || 'test',
						connected: !! merged.connected,
						connected_payment_mode: merged.connected_payment_mode || merged.payment_mode || 'test',
						account_name: merged.account_name || '',
						merchant_currency: merged.merchant_currency || '',
						location_id: merged.location_id || '',
						business_locations: Array.isArray( merged.business_locations ) ? merged.business_locations : [],
						defaultSettings: true,
					},
				} );
			} )
			.catch( () => {} );
	}, [ formClientId ] );

	useEffect( () => {
		if ( ! formClientId || ! effectiveSquare.connected ) {
			return;
		}

		const autoEmailField = getSingleEmailFieldNameAttr( formClientId );
		if ( autoEmailField && gfIsEmpty( customerEmailField ) ) {
			setAttributes( { customerEmailField: autoEmailField } );
		}
	}, [ formClientId, effectiveSquare.connected, customerEmailField ] );

	useEffect( () => {
		if ( ! formClientId ) {
			setValidationErrors( [] );
			return;
		}
		setValidationErrors( validateSquareFieldAttributes( attributes, formClientId ) );
	}, [ attributes, formClientId, effectiveSquare.connected ] );

	const updateSquareSettings = ( partial ) => {
		if ( ! formClientId ) {
			return;
		}

		updateBlockAttributes( formClientId, {
			paymentSquare: buildFormSquareOverride( paymentSquare, partial, globalDefaults ),
		} );
	};

	const handleConnectionChange = ( partial ) => {
		if ( ! formClientId ) {
			return;
		}

		updateBlockAttributes( formClientId, {
			paymentSquare: {
				...resolveEffectiveSquareSettings( paymentSquare ),
				...partial,
				enable: true,
				defaultSettings: true,
			},
		} );
	};

	const handlePaymentModeChange = ( nextMode ) => {
		if ( 'test' === nextMode && 'test' === ( effectiveSquare.payment_mode || 'test' ) ) {
			setPendingConnect( false );
			setShowTestModal( true );
			return;
		}

		updateSquareSettings( { payment_mode: nextMode } );
	};

	const startConnect = () => {
		setConnecting( true );
		squareConnect( effectiveSquare.payment_mode || 'test' )
			.then( ( response ) => {
				window.location.href = response.redirect_url;
			} )
			.catch( ( error ) => {
				// eslint-disable-next-line no-alert
				window.alert( error.message || __( 'Connection failed. Please try again.', 'gutena-forms' ) );
				setConnecting( false );
			} );
	};

	const handleConfigureClick = () => {
		if ( 'test' === ( effectiveSquare.payment_mode || 'test' ) ) {
			setPendingConnect( true );
			setShowTestModal( true );
			return;
		}

		startConnect();
	};

	const handleTestModalContinue = () => {
		setShowTestModal( false );

		if ( pendingConnect ) {
			setPendingConnect( false );
			startConnect();
		}
	};

	const emailOptions = useMemo(
		() => [
			{ label: __( 'Select', 'gutena-forms' ), value: '' },
			...getEmailFieldOptions( formClientId ),
		],
		[ formClientId, isSelected ]
	);

	const textOptions = useMemo(
		() => [
			{ label: __( 'Select', 'gutena-forms' ), value: '' },
			...getTextFieldOptions( formClientId ),
		],
		[ formClientId, isSelected ]
	);

	const amountOptions = useMemo(
		() => [
			{ label: __( 'Select', 'gutena-forms' ), value: '' },
			...getAmountFieldOptions( formClientId ),
		],
		[ formClientId, isSelected ]
	);

	const blockProps = useBlockProps( {
		className: 'wp-block-gutena-field-group wp-block-gutena-square-field field-group-type-square standalone-square-field',
	} );

	const connected = !! effectiveSquare.connected;
	const hasPro = isGutenaFormsPro();
	const currency = effectiveSquare.merchant_currency || 'USD';

	const handlePaymentTypeChange = ( value ) => {
		if ( 'subscription' === value && ! hasPro ) {
			return;
		}
		setAttributes( { paymentType: value } );
	};

	const subscriptionSummary = useMemo(
		() =>
			'subscription' === paymentType
				? formatSubscriptionSummary( {
					currency,
					fixedAmount,
					billingInterval,
					billingCycles,
					customBillingCycles,
				} )
				: '',
		[ paymentType, currency, fixedAmount, billingInterval, billingCycles, customBillingCycles ]
	);

	if ( isSquareGatewayExplicitlyDisabled() ) {
		return null;
	}

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Field Settings', 'gutena-forms' ) } initialOpen={ true }>
					<div className="gutena-forms-square-field__panel-header">
						<SquareFieldIcon />
						<div>
							<p className="gutena-forms-square-field__panel-title">{ __( 'Square', 'gutena-forms' ) }</p>
							<p className="gutena-forms-square-field__panel-description">
								{ __( 'Allows accepting credit card information to make payments via Square payment gateway.', 'gutena-forms' ) }
							</p>
						</div>
					</div>

					<TextControl
						label={ __( 'Label', 'gutena-forms' ) }
						value={ fieldName }
						onChange={ ( value ) => setAttributes( { fieldName: value } ) }
					/>

					<PaymentModeToggle
						value={ effectiveSquare.payment_mode || 'test' }
						onChange={ handlePaymentModeChange }
					/>

					<SquareMerchantCurrency
						connected={ connected }
						merchantCurrency={ effectiveSquare.merchant_currency }
					/>

					<SquareConnectionPanel
						paymentMode={ effectiveSquare.payment_mode || 'test' }
						connected={ connected }
						connectedPaymentMode={ effectiveSquare.connected_payment_mode }
						accountName={ effectiveSquare.account_name }
						businessLocations={ effectiveSquare.business_locations || [] }
						locationId={ effectiveSquare.location_id || '' }
						onConnectionChange={ handleConnectionChange }
						onLocationChange={ ( value ) => updateSquareSettings( { location_id: value } ) }
					/>

					{ connected && (
						<>
							<div className="gutena-forms-square-field__section">
								<p className="gutena-forms-square-field__section-label">
									{ __( 'Payment Type', 'gutena-forms' ) }
								</p>
								<RadioControl
									selected={ paymentType }
									options={ [
										{ label: __( 'One-time', 'gutena-forms' ), value: 'one_time' },
										{
											label: hasPro
												? __( 'Subscription', 'gutena-forms' )
												: `${ __( 'Subscription', 'gutena-forms' ) } (${ __( 'Pro', 'gutena-forms' ) })`,
											value: 'subscription',
										},
									] }
									onChange={ handlePaymentTypeChange }
								/>
								{ ! hasPro && (
									<p className="gutena-forms-square-field__help">
										{ __( 'Subscription payments are available in Gutena Forms Pro.', 'gutena-forms' ) }
									</p>
								) }
							</div>

							{ 'one_time' === paymentType && (
								<>
									<div className="gutena-forms-square-field__section">
										<p className="gutena-forms-square-field__section-label">
											{ __( 'Amount Type', 'gutena-forms' ) }
										</p>
										<RadioControl
											selected={ amountType }
											options={ [
												{ label: __( 'Fixed', 'gutena-forms' ), value: 'fixed' },
												{ label: __( 'Variable', 'gutena-forms' ), value: 'variable' },
											] }
											onChange={ ( value ) => setAttributes( { amountType: value } ) }
										/>
										<p className="gutena-forms-square-field__help">
											{ __( 'Select a fixed amount or dynamically calculate the amount from other fields.', 'gutena-forms' ) }
										</p>
									</div>

									{ 'fixed' === amountType ? (
										<TextControl
											label={ __( 'Amount', 'gutena-forms' ) }
											type="number"
											min="0"
											step="0.01"
											value={ fixedAmount }
											onChange={ ( value ) =>
												setAttributes( { fixedAmount: parseFloat( value ) || 0 } )
											}
											help={ __( 'Set the exact amount you want to charge. Users won\'t be able to change it.', 'gutena-forms' ) }
										/>
									) : (
										<>
											<SelectControl
												label={ __( 'Choose Amount Field', 'gutena-forms' ) }
												value={ variableAmountField }
												options={ amountOptions }
												onChange={ ( value ) =>
													setAttributes( { variableAmountField: value } )
												}
												help={ __( 'Pick a field from your form, like a number, radio, dropdown, or checkbox, whose value should decide the payment amount.', 'gutena-forms' ) }
											/>
											<FieldError
												show={ validationErrors.includes( 'variable_amount_field' ) }
												message={ __( 'Please select a field for the variable amount.', 'gutena-forms' ) }
											/>
											<TextControl
												label={ __( 'Minimum Amount', 'gutena-forms' ) }
												type="number"
												min="0"
												step="0.01"
												value={ minimumAmount }
												onChange={ ( value ) =>
													setAttributes( { minimumAmount: parseFloat( value ) || 0 } )
												}
												help={ __( 'Set the minimum amount users can enter (0 for no minimum)', 'gutena-forms' ) }
											/>
										</>
									) }

									<SelectControl
										label={ __( 'Customer Email Mapping', 'gutena-forms' ) + ' *' }
										value={ customerEmailField }
										options={ emailOptions }
										onChange={ ( value ) =>
											setAttributes( { customerEmailField: value } )
										}
										help={ __( 'Select the email field that contains the customer\'s email', 'gutena-forms' ) }
									/>
									<FieldError
										show={ validationErrors.includes( 'customer_email_field' ) }
										message={ __( 'Please select a customer email field.', 'gutena-forms' ) }
									/>

									<SelectControl
										label={ __( 'Customer Name Mapping', 'gutena-forms' ) }
										value={ customerNameField }
										options={ textOptions }
										onChange={ ( value ) =>
											setAttributes( { customerNameField: value } )
										}
										help={ __( 'Select the input field that contains the customer name', 'gutena-forms' ) }
									/>
								</>
							) }

							{ 'subscription' === paymentType && hasPro && (
								<>
									<TextControl
										label={ __( 'Subscription Plan Name', 'gutena-forms' ) + ' *' }
										value={ subscriptionPlanName }
										onChange={ ( value ) =>
											setAttributes( { subscriptionPlanName: value } )
										}
									/>
									<FieldError
										show={ validationErrors.includes( 'subscription_plan_name' ) }
										message={ __( 'Please enter a subscription plan name.', 'gutena-forms' ) }
									/>

									<TextControl
										label={ __( 'Amount', 'gutena-forms' ) + ' *' }
										type="number"
										min="0"
										step="0.01"
										value={ fixedAmount }
										onChange={ ( value ) =>
											setAttributes( { fixedAmount: parseFloat( value ) || 0 } )
										}
										help={ __( 'Set the recurring amount charged for each billing cycle.', 'gutena-forms' ) }
									/>
									<FieldError
										show={ validationErrors.includes( 'subscription_amount' ) }
										message={ __( 'Please enter a subscription amount greater than zero.', 'gutena-forms' ) }
									/>

									<SelectControl
										label={ __( 'Billing Interval', 'gutena-forms' ) }
										value={ billingInterval }
										options={ BILLING_INTERVALS }
										onChange={ ( value ) =>
											setAttributes( { billingInterval: value } )
										}
									/>

									<SelectControl
										label={ __( 'Billing Cycles', 'gutena-forms' ) }
										value={ billingCycles }
										options={ BILLING_CYCLES }
										onChange={ ( value ) =>
											setAttributes( { billingCycles: value } )
										}
										help={ __( 'Choose when the subscription will stop automatically.', 'gutena-forms' ) }
									/>

									{ 'custom' === billingCycles && (
										<>
											<TextControl
												label={ __( 'Number of Payments', 'gutena-forms' ) }
												type="number"
												min="1"
												max="100"
												value={ customBillingCycles }
												onChange={ ( value ) =>
													setAttributes( {
														customBillingCycles: parseInt( value, 10 ) || 1,
													} )
												}
												help={ __( 'Enter a number between 1 and 100', 'gutena-forms' ) }
											/>
											<FieldError
												show={ validationErrors.includes( 'custom_billing_cycles' ) }
												message={ __( 'Enter a number between 1 and 100.', 'gutena-forms' ) }
											/>
										</>
									) }

									<SelectControl
										label={ __( 'Customer Email Mapping', 'gutena-forms' ) + ' *' }
										value={ customerEmailField }
										options={ emailOptions }
										onChange={ ( value ) =>
											setAttributes( { customerEmailField: value } )
										}
										help={ __( 'Select the email field that contains the customer\'s email', 'gutena-forms' ) }
									/>
									<FieldError
										show={ validationErrors.includes( 'customer_email_field' ) }
										message={ __( 'Please select a customer email field.', 'gutena-forms' ) }
									/>

									<SelectControl
										label={ __( 'Customer Name Mapping', 'gutena-forms' ) + ' *' }
										value={ customerNameField }
										options={ textOptions }
										onChange={ ( value ) =>
											setAttributes( { customerNameField: value } )
										}
										help={ __( 'Select the input field that contains the customer name', 'gutena-forms' ) }
									/>
									<FieldError
										show={ validationErrors.includes( 'customer_name_field' ) }
										message={ __( 'Please select a customer name field.', 'gutena-forms' ) }
									/>
								</>
							) }
						</>
					) }
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				<h3 className="gutena-forms-square-field__preview-title">
					{ fieldName || __( 'Credit Card', 'gutena-forms' ) }
				</h3>
				{ connected ? (
					<div className="gutena-forms-square-field__preview-connected">
						<p className="gutena-forms-square-field__preview-status">
							{ __( 'Connected!', 'gutena-forms' ) }
						</p>
						<p className="gutena-forms-square-field__preview-description">
							{ __( 'Square account is connected. The payment field will appear when the form is published.', 'gutena-forms' ) }
						</p>
						{ 'subscription' === paymentType && hasPro && subscriptionPlanName && (
							<p className="gutena-forms-square-field__preview-plan">
								{ subscriptionPlanName }
							</p>
						) }
						{ 'subscription' === paymentType && hasPro && subscriptionSummary && (
							<p className="gutena-forms-square-field__preview-summary">
								{ subscriptionSummary }
							</p>
						) }
						{ 'one_time' === paymentType && 'fixed' === amountType && fixedAmount > 0 && (
							<p className="gutena-forms-square-field__preview-summary">
								{ formatCurrencyAmount( currency, fixedAmount ) }
							</p>
						) }
					</div>
				) : (
					<div className="gutena-forms-square-field__preview-unconfigured">
						<div className="gutena-forms-square-field__preview-warning">
							<PreviewWarningIcon />
							<p className="gutena-forms-square-field__preview-description">
								{ __( 'You need to configure a Square account to collect payments from this form.', 'gutena-forms' ) }
							</p>
						</div>
						<button
							type="button"
							className="gutena-forms-square-field__configure-button"
							onClick={ handleConfigureClick }
							disabled={ connecting }
						>
							{ connecting
								? __( 'Connecting…', 'gutena-forms' )
								: __( 'Configure Square Account', 'gutena-forms' ) }
						</button>
					</div>
				) }

				{ validationErrors.length > 0 && isSelected && (
					<Notice status="warning" isDismissible={ false }>
						{ __( 'Some required Square payment settings are incomplete. Complete the field mappings in Field Settings to collect payments.', 'gutena-forms' ) }
					</Notice>
				) }
			</div>

			<SquareTestModeModal
				isOpen={ showTestModal }
				onClose={ () => {
					setShowTestModal( false );
					setPendingConnect( false );
				} }
				onContinue={ handleTestModalContinue }
			/>
		</>
	);
}
